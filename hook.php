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
 * Changed 2026-10-03 by FrexCore: uninstall clears the settings it stores in
 * the application's configuration table, and the brand images it writes
 * outside the application tree. Install is still empty: an unconfigured
 * instance runs on the defaults compiled into the stylesheet.
 */

use GlpiPlugin\Frexcore\Settings;

function plugin_frexcore_install(): bool
{
    return true;
}

function plugin_frexcore_uninstall(): bool
{
    // Settings live in the application's own configuration table under a
    // context of our own, so there is still no table to drop. Removing the
    // rows returns the instance to the stylesheet defaults rather than to a
    // half-configured state.
    try {
        \Config::deleteConfigurationValues(
            Settings::CONTEXT,
            array_merge(array_keys(Settings::DEFAULTS), array_keys(Settings::IMAGE_KEYS))
        );
    } catch (\Throwable $e) {
        // An uninstall that fails on cleanup leaves a plugin that cannot be
        // removed, which is worse than a few orphaned configuration rows.
    }

    // Uploaded brand images are the one thing this plugin writes outside its
    // own directory, so it is the one thing it has to clear up.
    try {
        $dir = Settings::imageDir();
        if ($dir !== '' && is_dir($dir)) {
            foreach (glob($dir . '/*') ?: [] as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            @rmdir($dir);
        }
    } catch (\Throwable $e) {
        // As above.
    }

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
