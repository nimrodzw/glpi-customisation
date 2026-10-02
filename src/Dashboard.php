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
        $defaults = ['label' => null, 'icon' => 'ti ti-ticket', 'limit' => 8];
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
