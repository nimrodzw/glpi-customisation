# FrexCore plugin

Dashboard cards and product naming for the FrexCore ITSM platform.

Derived from the [UI Branding plugin](https://github.com/i-Vertix/glpi-modifications)
by i-Vertix/PGUM, whose structure this follows. GPLv3, as the original.
Original copyright retained — see `COPYRIGHT`.

## What it does

**Dashboard cards and widget types.** Upstream ships numbers, bars, donuts
and lines. What it has no shape for is a readable list — the thing people
actually open a service desk to look at. Registered through the published
`dashboard_types` and `dashboard_cards` hooks.

**Product naming**, via `$CFG_GLPI['app_name']` — the supported route, and
cleaner than rewriting page titles in the response.

## What it deliberately does not do

**It does not brand assets.** The original rewrote logo files inside the
application tree, backing up the originals first. FrexCore applies brand
assets from outside that tree instead, so an upgrade cannot revert them and
there is nothing to restore on uninstall.

**It creates no database tables and writes no files.** Nothing to install,
nothing to migrate, nothing left behind. Deactivating returns the platform to
upstream's own cards with no cleanup step.

**It overrides no core class.** Published hooks only.

## Failing soft

The platform must survive this plugin breaking. That is the condition under
which having a plugin at all was agreed (`docs/decisions/0003` in the
platform repository), and it is a requirement rather than an aspiration:

- Registration is wrapped; a failure during init logs and continues.
- Every provider and renderer is wrapped. A card that throws costs one empty
  tile, never the page — an operations dashboard that 500s because a chart
  could not count something is worse than one missing a chart.
- The GLPI version range is declared, so an unsupported release refuses to
  activate rather than half-loading.
- The nginx-level product naming in the platform repository stays
  independent, so a plugin failure never hands a client an interface carrying
  someone else's name.

**The test:** with this plugin disabled, the platform runs, is patchable, and
works on upstream's cards. If that stops being true, the plugin is wrong and
gets changed until it is true again. One that cannot be switched off during
an incident is a liability however useful it is the rest of the time.

## Entity scoping

Card providers apply `getEntitiesRestrictCriteria`. Without it, a card on a
shared instance shows one client's ticket subjects to another — the worst
thing a multi-tenant dashboard can do. Any new provider must do the same;
it is the first thing to check in review.

## Install

The plugin directory must be named `frexcore`:

```bash
sudo git clone https://github.com/nimrodzw/glpi-customisation \
    /srv/frexcore/tenants/<slug>/app/plugins/frexcore
sudo chown -R frex-<slug>:frex-<slug> \
    /srv/frexcore/tenants/<slug>/app/plugins/frexcore
```

Then Setup → Plugins → install, then activate.

## Cards

| Card | Shape | Shows |
|---|---|---|
| Tickets needing attention | FrexCore: list | Open tickets, oldest first, with priority and how long each has waited |

Oldest first is deliberate. Newest-first shows the work that has been waiting
least, which is the opposite of useful on a morning when the desk is behind.

## Demonstration data

```bash
# preview without writing anything
sudo -u frex-demo php /srv/frexcore/tenants/demo/app/bin/console \
    plugins:frexcore:seed-demo --dry-run

# load it
sudo -u frex-demo php /srv/frexcore/tenants/demo/app/bin/console \
    plugins:frexcore:seed-demo --org="Shiloh Financial Group"

# take it back out
sudo -u frex-demo php /srv/frexcore/tenants/demo/app/bin/console \
    plugins:frexcore:seed-demo --purge
```

Builds a running company, not a sample. Forty five staff across eight sites
in seven countries, roughly one hundred and sixty assets with owners,
warranties, purchase dates and book values, a licence register with seat
counts and renewal dates, suppliers and contracts including data processing
agreements, service level targets by priority, a knowledge base, open
problems and changes, and a year of ticket history behind a live queue.

**Tickets carry the conversation, not just the subject.** A ticket with a
title, an assignee and nothing else is a row in a table. What a service desk
is actually bought for is what happens after someone presses submit, and that
is the second screen a prospect opens. Every ticket here has a thread written
against its own subject: the requester coming back with the detail that was
missing, the technician saying what they found, internal notes the requester
never sees, work logged with real durations against it, and a resolution that
answers the specific problem rather than a line of filler.

Around one ticket in six changes hands. A stolen laptop opens with the Service
Desk, moves to Information Security once the wipe is issued, and picks up the
Data Protection Office when the question becomes whether it is notifiable. The
outgoing assignee comes off the record when it moves, so a handover looks like
a handover rather than a ticket quietly accumulating six owners. Reassignments
are written to the history, managers appear as observers where the subject
warrants it, and a handful of items sit with a team and no individual because
nobody has picked them up yet.

Threads vary across tickets built from the same template, and open tickets
play only as far through their thread as their age allows. A queue where every
item is at the same stage is as much of a tell as one where every item is
identical.

A prospect clicks past the dashboard inside a minute. What decides the
meeting is whether the next five screens hold up. Thin data behind a good
dashboard is worse than no demo, because it teaches them the product is a
shell.

**Why a console command and not a .sql file.** Tickets are not one table.
Actors live in `glpi_tickets_users`, solutions are their own records, and the
application maintains history alongside. Hand-written INSERTs that miss those
produce a demo where every ticket has no requester, which gets noticed in the
first thirty seconds of a presentation. Running inside the console means the
application's own object layer does the work.

**Safety.** This writes data. Against a live tenant it would put invented
policyholders into a real client's system, so it refuses to run on any
instance that already holds tickets unless forced, supports `--dry-run`, and
tags everything it creates so `--purge` removes what it made.

**The compliance framing is the point.** Categories name the obligation they
answer to: GDPR Article 33 and its 72 hour clock, POPIA, the Zimbabwe Cyber
and Data Protection Act, Kenya's Data Protection Act 2019, Nigeria's NDPA
2023, Botswana's DPA 2018, the AU Malabo Convention, ISO 27001 Annex A and
PCI DSS. An ITSM demo showing tickets is unremarkable. One showing a subject
access request with a statutory clock against it speaks to whoever in the
room owns compliance, which is usually whoever signs.

**Shape over volume.** Ticket history rises across the year rather than
sitting flat, because no real service desk has ever had a flat year. Roughly
94 percent of work is closed and around 8 percent of what remains is
breaching its target. An earlier version derived resolution targets from the
open date, which made almost every open ticket breached and painted the demo
organisation as collapsing rather than coping.

The dataset lives in `src/Demo/DemoData.php`. A prospect-specific variant is
a copy of that file, not a fork of the seeder.

## Favourite menu items

The product ships seventy nine menu destinations behind six collapsed
menus, and most people use five of them every day. A star appears on each
menu entry on hover; starred pages are pinned to a Favourites block at the
top of the sidebar, one click away.

Favourites are stored in the browser, keyed to the signed-in user, so two
people sharing a machine do not inherit each other's shortcuts.

**Why not server side.** That would mean a database table, which would mean
install and uninstall migrations, which would break the property that makes
this plugin safe to switch off during an incident: nothing of it is left
behind. Per-device favourites are the right first version. If they need to
follow a person between devices, that is a considered change with its own
migration rather than a default nobody chose.

Storage can be absent or throw, in private windows and where site data is
blocked, so every read and write is guarded and the menu works without it.
