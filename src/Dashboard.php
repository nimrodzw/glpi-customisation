<?php

/**
 * FrexCore plugin — dashboard cards and widget types.
 *
 * Registered through upstream's published hooks (dashboard_types,
 * dashboard_cards). No core class is overridden, extended or patched.
 *
 * Every provider and renderer here is wrapped. A card that throws must cost
 * one empty tile, never the page: an operations dashboard that 500s because
 * a chart could not count something is worse than one that is missing a
 * chart.
 *
 * @copyright Copyright (C) 2026 by i-Vertix/PGUM.
 * @copyright Copyright (C) 2026 FrexCore and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace GlpiPlugin\Frexcore;

use Session;
use Ticket;

class Dashboard
{
    /** Ticket statuses that still need somebody. Solved (5) and closed (6) do not. */
    private const OPEN_STATUSES = [
        \CommonITILObject::INCOMING,
        \CommonITILObject::ASSIGNED,
        \CommonITILObject::PLANNED,
        \CommonITILObject::WAITING,
    ];

    /**
     * Widget types this plugin can render.
     *
     * A type is the SHAPE of a card. Upstream ships numbers, bars, donuts and
     * lines; what the reference products have and this does not is a readable
     * list — the thing a person opens the service desk to look at.
     */
    public static function dashboardTypes(): array
    {
        return [
            'frexcoreTable' => [
                'label'    => __('FrexCore: list', 'frexcore'),
                'function' => self::class . '::renderTable',
                'image'    => '',
            ],
        ];
    }

    /**
     * Colour palette for every chart on the dashboard.
     *
     * Upstream's default is a categorical rainbow built to separate arbitrary
     * series. Applied to ticket status it is actively misleading: "Closed" —
     * the one state nobody needs to act on — comes out bright magenta and
     * dominates the chart, while "New" is a quiet blue. Colour ends up
     * meaning nothing, which is the single loudest signal that a dashboard
     * was assembled rather than designed.
     *
     * This is ordered rather than categorical, because ticket status IS
     * ordered. It runs dark navy at the start, through teal while work is in
     * progress, amber where something is stuck and waiting on a person, green
     * once resolved, and pale slate for closed so finished work recedes
     * instead of shouting.
     *
     * If this plugin is disabled, upstream falls back to its default palette
     * for any name it does not recognise, so cards referencing "frexcore"
     * degrade to stock colours rather than breaking.
     */
    public static function dashboardPalettes(): array
    {
        return [
            'frexcore' => [
                '#1e3d6b',  // navy      — new, unstarted
                '#4a6694',  // slate     — awaiting approval
                '#007f8b',  // teal deep — in progress, assigned
                '#00a8a8',  // teal      — in progress, planned
                '#b06000',  // amber     — pending, waiting on someone
                '#4a9d7f',  // green     — solved
                '#b9c6da',  // pale      — closed, recedes
                '#8a5fa8',  // violet    — spare, for charts with more series
                '#b3261e',  // red
                '#c9a227',  // ochre
            ],
        ];
    }

    /**
     * Cards available in the card picker.
     *
     * `widgettype` names the shapes a card may be drawn as. Offering the core
     * types alongside ours means a card stays usable if this plugin is ever
     * disabled — the card disappears, the dashboard does not break.
     */
    public static function dashboardCards($cards = []): array
    {
        if (!is_array($cards)) {
            $cards = [];
        }

        return array_merge($cards, [
            'frexcore_open_tickets' => [
                'widgettype' => ['frexcoreTable'],
                'group'      => __('FrexCore', 'frexcore'),
                'label'      => __('Tickets needing attention', 'frexcore'),
                'provider'   => self::class . '::provideOpenTickets',
            ],
        ]);
    }

    // ---------------------------------------------------------------- data

    /**
     * Tickets that are still somebody's problem, oldest first.
     *
     * Oldest first is deliberate: a list sorted newest-first shows the work
     * that has been waiting least, which is the opposite of useful on a
     * morning when the desk is behind.
     */
    public static function provideOpenTickets(array $params = []): array
    {
        // The row count is a setting rather than a constant: how many rows
        // read as the morning's work differs between a two person desk and a
        // twenty person one, and neither should need a deployment to change.
        $defaults = [
            'label' => null,
            'icon'  => 'ti ti-ticket',
            'limit' => max(3, min(40, (int) Settings::get('queue_rows'))),
        ];
        $params   = array_merge($defaults, $params);

        $rows = [];
        try {
            /** @var \DBmysql $DB */
            global $DB;

            $criteria = [
                'SELECT' => ['id', 'name', 'status', 'priority', 'date'],
                'FROM'   => 'glpi_tickets',
                'WHERE'  => [
                    'is_deleted' => 0,
                    'status'     => self::OPEN_STATUSES,
                ],
                'ORDER'  => ['date ASC'],
                'LIMIT'  => (int) $params['limit'],
            ];

            // Respect the entity the viewer is actually in. Without this a
            // card can show one client's ticket subjects to another, which is
            // the single worst thing a multi-tenant dashboard can do.
            $restrict = getEntitiesRestrictCriteria('glpi_tickets');
            if (!empty($restrict)) {
                $criteria['WHERE'][] = $restrict;
            }

            foreach ($DB->request($criteria) as $row) {
                $rows[] = [
                    'id'       => (int) $row['id'],
                    'name'     => (string) $row['name'],
                    'status'   => Ticket::getStatus($row['status']),
                    'priority' => \CommonITILObject::getPriorityName($row['priority']),
                    'prio_raw' => (int) $row['priority'],
                    'age'      => self::humanAge($row['date']),
                    'url'      => Ticket::getFormURLWithID($row['id']),
                ];
            }
        } catch (\Throwable $e) {
            trigger_error('FrexCore card provider failed: ' . $e->getMessage(), E_USER_WARNING);
            $rows = [];
        }

        return [
            'title' => $params['label'] ?? __('Tickets needing attention', 'frexcore'),
            'icon'  => $params['icon'],
            'data'  => $rows,
        ];
    }

    // ------------------------------------------------------------- render

    /**
     * A list of tickets as a readable table.
     *
     * Columns earn their place: what it is, how urgent, how long it has been
     * waiting. Reference and identifier columns are left out — nobody scans a
     * list by primary key.
     */
    public static function renderTable(array $params = []): string
    {
        $p = array_merge(['data' => [], 'title' => '', 'color' => '#ffffff'], $params);

        try {
            $html = '<div class="card frexcore-list" style="background-color:'
                  . htmlspecialchars($p['color'], ENT_QUOTES) . ';">';

            $html .= '<div class="frexcore-list__head">'
                   . '<span class="frexcore-list__title">'
                   . htmlspecialchars((string) $p['title'], ENT_QUOTES)
                   . '</span></div>';

            if (empty($p['data'])) {
                // An empty screen is a statement about the state of the desk,
                // so it should say that rather than look broken.
                $html .= '<div class="frexcore-list__empty">'
                       . htmlspecialchars(__('Nothing waiting. The queue is clear.', 'frexcore'), ENT_QUOTES)
                       . '</div></div>';
                return $html;
            }

            $html .= '<table class="frexcore-list__table"><tbody>';
            foreach ($p['data'] as $row) {
                $prio = isset($row['prio_raw']) && $row['prio_raw'] >= 4 ? ' is-high' : '';
                $html .= '<tr class="frexcore-list__row' . $prio . '">'
                       . '<td class="frexcore-list__subject"><a href="'
                       . htmlspecialchars((string) ($row['url'] ?? '#'), ENT_QUOTES) . '">'
                       . htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES)
                       . '</a></td>'
                       // The numeric level goes on the cell so the stylesheet can
                       // colour the pill by severity. Matching the translated label
                       // instead would break the moment a tenant runs in French.
                       . '<td class="frexcore-list__prio" data-prio="'
                       . (int) ($row['prio_raw'] ?? 0) . '"><span>'
                       . htmlspecialchars((string) ($row['priority'] ?? ''), ENT_QUOTES)
                       . '</span></td>'
                       . '<td class="frexcore-list__age">'
                       . htmlspecialchars((string) ($row['age'] ?? ''), ENT_QUOTES)
                       . '</td>'
                       . '</tr>';
            }
            $html .= '</tbody></table></div>';

            return $html;
        } catch (\Throwable $e) {
            trigger_error('FrexCore card render failed: ' . $e->getMessage(), E_USER_WARNING);
            return '<div class="card frexcore-list"><div class="frexcore-list__empty">'
                 . htmlspecialchars(__('This card could not be displayed.', 'frexcore'), ENT_QUOTES)
                 . '</div></div>';
        }
    }

    // -------------------------------------------------------------- utils

    /**
     * How long something has been waiting, in the roughest unit that is still
     * true. "3d" tells an operator what they need; a timestamp makes them
     * work it out.
     */
    private static function humanAge(?string $datetime): string
    {
        if (empty($datetime)) {
            return '';
        }
        $then = strtotime($datetime);
        if ($then === false) {
            return '';
        }
        $secs = max(0, time() - $then);

        if ($secs < 3600) {
            return max(1, (int) floor($secs / 60)) . 'm';
        }
        if ($secs < 86400) {
            return (int) floor($secs / 3600) . 'h';
        }
        return (int) floor($secs / 86400) . 'd';
    }
}
