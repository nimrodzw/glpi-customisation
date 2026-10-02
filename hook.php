<?php

/**
 * FrexCore plugin — install lifecycle.
 *
 * Deliberately empty of work. The plugin creates no tables and writes no
 * files into the application tree, so there is nothing to install, nothing
 * to migrate, and nothing left behind on removal. Deactivating it returns
 * the platform to upstream's own cards with no cleanup step — which is what
 * makes it safe to disable during an incident.
 *
 * @copyright Copyright (C) 2026 by i-Vertix/PGUM.
 * @copyright Copyright (C) 2026 FrexCore and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Changed 2026-10-02 by FrexCore: resource installation removed.
 */

function plugin_frexcore_install(): bool
{
    return true;
}

function plugin_frexcore_uninstall(): bool
{
    return true;
}

function plugin_frexcore_activate(): bool
{
    return true;
}

function plugin_frexcore_deactivate(): bool
{
    return true;
}
