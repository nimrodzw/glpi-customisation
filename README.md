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

**A settings panel**, reached from the cog beside FrexCore on the plugins
page. See below.

## What it deliberately does not do

**It does not brand assets.** The original rewrote logo files inside the
application tree, backing up the originals first. FrexCore applies brand
assets from outside that tree instead, so an upgrade cannot revert them and
there is nothing to restore on uninstall.

**It creates no database tables.** Settings live in the application's own
configuration table under a context of our own, so there is nothing to
install and nothing to migrate. Uninstalling deletes those rows and the
instance returns to the defaults compiled into the stylesheet.

**It writes nothing into the application tree.** Uploaded brand images go to
the plugin data directory outside it, which is what stops an upgrade
reverting them — the failure mode that made the original plugin's approach
untenable. Uninstalling removes them.

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

## Settings panel

Everything a tenant is allowed to differ on, in one form, with no file to
edit, no repository to pull and no web server to reload.

| Setting | What it reaches |
|---|---|
| Product name | Wherever the application names itself |
| Brand, brand darker, accent | Sidebar, buttons, links, focus, active state |
| Sidebar | Gradient, solid or near black |
| Density | Row height and type size across every list |
| Corners | Square or soft, everywhere at once |
| Activity stream | Single column, or the base theme's two-sided thread |
| Favourite menu items | On or off |
| Queue card rows | How many tickets the attention card lists |
| Brand images | Wide logo, square mark, sign-in logo, sign-in background |
| Demonstration banner | A fixed strip, with its text |

**Why this exists.** FrexCore runs one isolated instance per client. Without
a panel, giving a new client their colours is a stylesheet edit, a commit, a
pull on the server and an nginx reload — a deployment, for something a client
will ask to change twice in the first week, and one that cannot be delegated
to anyone who is not comfortable on a command line.

**Hover, tint and focus colours are derived**, not set. Three colours go in
and the rest are computed from them. Offering ten pickers produces a palette
that drifts out of relation to itself within a few changes, and the person
using the panel is not being asked to be a designer.

**What is deliberately not configurable.** Hiding the upstream project links
is fixed in the stylesheet and absent from the panel. It is served by nginx
from outside the application tree so that a plugin failure can never hand a
client an interface carrying someone else's name — and a white-label
guarantee with an off switch in the interface is not a guarantee.

**Division of labour with the stylesheet.** `frexcore.css` carries the
structure and ships with a default for every value the panel can change, so
an instance with this plugin switched off still gets the whole theme rather
than a half-painted one. The panel emits only the overrides, at one step more
specific than the base, so it wins whichever order the two files arrive in.
The one exception is the activity stream, which is the only place the theme
rearranges the page rather than recolouring it; that lives here, because it
is an operator's choice.

**Validation is not politeness.** These values are interpolated into a
stylesheet served to every page. A colour that is not a colour is a hole, not
a typo, so colours are matched against a pattern, choices against the list
offered, and the product name is stripped. Uploads are typed by their leading
bytes rather than by their name or the browser's claim, and an uploaded SVG
is served under a sandbox policy, because an SVG is a document that can carry
script and it is being served from the application's own origin.

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

**The stylesheet and script live under `public/`.** GLPI 11 serves plugin
files over HTTP only from a plugin's `public/` directory, or from `/ajax`,
`/front` and `/report`. They were at the plugin root, which is none of those,
so on 11.0.10 they were requested and not served and the feature was silently
absent. The registered paths are unchanged, because the URL
`/plugins/frexcore/css/favourites.css` is resolved against `public/` already.
