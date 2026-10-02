<?php

/**
 * -------------------------------------------------------------------------
 * FrexCore plugin
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of the FrexCore plugin.
 *
 * The FrexCore plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 3 of the License, or (at your
 * option) any later version.
 *
 * It is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for details.
 * -------------------------------------------------------------------------
 * Derived from the UI Branding plugin by i-Vertix/PGUM, whose structure this
 * plugin follows.  @see https://github.com/i-Vertix/glpi-modifications
 *
 * @copyright Copyright (C) 2026 by i-Vertix/PGUM.
 * @copyright Copyright (C) 2026 FrexCore and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Changed 2026-10-02 by FrexCore: asset branding removed — it is applied
 * outside the application tree so upgrades cannot revert it — and dashboard
 * cards and widget types added.
 * -------------------------------------------------------------------------
 */

use GlpiPlugin\Frexcore\Dashboard;

const PLUGIN_FREXCORE_VERSION = '1.0.0';

// The range this plugin is known good against. Outside it the plugin refuses
// to activate rather than loading and failing unpredictably — which is what
// stops a platform upgrade ever being gated on this plugin.
const PLUGIN_FREXCORE_GLPI_MIN = '11.0';
const PLUGIN_FREXCORE_GLPI_MAX = '12.0';

// Shown wherever the application names itself. This is the supported route,
// and cleaner than rewriting titles in the response — though the nginx rules
// stay as a backstop, so a plugin failure never hands a client an interface
// carrying someone else's name.
const PLUGIN_FREXCORE_APP_NAME = 'FrexCore';

function plugin_init_frexcore(): void
{
    global $PLUGIN_HOOKS, $CFG_GLPI;

    $PLUGIN_HOOKS['csrf_compliant']['frexcore'] = true;

    if (!Plugin::isPluginActive('frexcore')) {
        return;
    }

    // Everything below is additive. If any of it throws, the dashboard must
    // still render with upstream's own cards: losing presentation is
    // acceptable, losing the service is not.
    try {
        $CFG_GLPI['app_name'] = PLUGIN_FREXCORE_APP_NAME;

        $PLUGIN_HOOKS['dashboard_types']['frexcore'] = [Dashboard::class, 'dashboardTypes'];
        $PLUGIN_HOOKS['dashboard_cards']['frexcore'] = [Dashboard::class, 'dashboardCards'];
    } catch (\Throwable $e) {
        trigger_error('FrexCore plugin init failed: ' . $e->getMessage(), E_USER_WARNING);
    }
}

function plugin_version_frexcore(): array
{
    return [
        'name'         => 'FrexCore',
        'version'      => PLUGIN_FREXCORE_VERSION,
        'author'       => 'FrexCore — derived from UI Branding by i-Vertix/PGUM',
        'license'      => 'GPLv3',
        'homepage'     => 'https://github.com/nimrodzw/glpi-customisation',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_FREXCORE_GLPI_MIN,
                'max' => PLUGIN_FREXCORE_GLPI_MAX,
            ],
        ],
    ];
}
