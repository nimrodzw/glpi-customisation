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
