<?php
/**
 * Render a card to a standalone HTML page, without GLPI and without deploying.
 *
 *   php tools/preview.php > /tmp/preview.html
 *
 * Card styling lives in the platform repository's stylesheet while the markup
 * lives here, so the only way to see a card before this existed was to push
 * both, pull on a server and look. That loop was slow enough that styling got
 * shipped unseen, which is how a suffix ended up twice the size of its figure.
 *
 * The wrapper below reproduces what the dashboard puts around a card —
 * .grid-stack-item > .grid-stack-item-content, absolutely positioned — because
 * the stylesheet's selectors depend on that nesting, and a flat harness
 * silently fails to apply half of them.
 *
 * @license GPLv3
 */

function __($s, $d = null) { return $s; }

$pluginRoot = dirname(__DIR__);
$cssPath = getenv('FREXCORE_CSS') ?: dirname($pluginRoot) . '/frexcore/branding/frexcore.css';

$src = file_get_contents($pluginRoot . '/src/Dashboard.php');
$src = preg_replace('/^use (Session|Ticket);$/m', '', $src);
eval(preg_replace('/^<\?php/', '', $src));

use GlpiPlugin\Frexcore\Dashboard;

// Representative rather than tidy: a subject long enough to truncate, every
// priority level, and ages spanning minutes to days.
$rows = [
    ['name' => 'VPN access not working from home',                            'priority' => 'Very high', 'prio_raw' => 5, 'age' => '4d',  'url' => '#'],
    ['name' => 'Laptop performance slow after the Windows update last week',  'priority' => 'High',      'prio_raw' => 4, 'age' => '2d',  'url' => '#'],
    ['name' => 'Cannot access the shared network drive',                      'priority' => 'Medium',    'prio_raw' => 3, 'age' => '1d',  'url' => '#'],
    ['name' => 'Software installation request — Adobe Acrobat',               'priority' => 'Low',       'prio_raw' => 2, 'age' => '18h', 'url' => '#'],
    ['name' => 'New starter account setup for Monday',                        'priority' => 'Low',       'prio_raw' => 2, 'age' => '47m', 'url' => '#'],
];

$css   = is_readable($cssPath) ? file_get_contents($cssPath) : '/* stylesheet not found */';
$card  = Dashboard::renderTable(['data' => $rows, 'title' => 'Tickets needing attention', 'color' => '#ffffff']);
$empty = Dashboard::renderTable(['data' => [],    'title' => 'Tickets needing attention', 'color' => '#ffffff']);

$tile = static fn(string $c): string =>
    "<div class='grid-stack-item'><div class='grid-stack-item-content'>$c</div></div>";

echo "<!doctype html><html><head><meta charset='utf-8'><title>FrexCore card preview</title><style>
body { margin:0; padding:20px; background:#f4f6f9;
       font-family:ui-sans-serif,system-ui,'Segoe UI',Roboto,sans-serif; }
.row { display:flex; gap:16px; align-items:stretch; height:300px; }
.grid-stack-item { flex:1; position:relative; }
.grid-stack-item:last-child { flex:0 0 320px; }
.grid-stack-item-content { position:absolute; inset:0; }
$css
</style></head><body><div class='row'>" . $tile($card) . $tile($empty) . "</div></body></html>";
