<?php

/**
 * FrexCore plugin — serve a brand image uploaded through the settings panel.
 *
 * The images live outside the application tree, which is what stops an
 * upgrade reverting them, and that is exactly why they need serving: nothing
 * else can reach that directory over HTTP.
 *
 * This runs before sign-in, because the sign-in page is where the logo and
 * the background are most needed. It therefore takes no input beyond a name
 * that must match one of a fixed set, and it reads from one directory. There
 * is no path to traverse because there is no path in the request.
 *
 * @copyright Copyright (C) 2026 FrexCore and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 */

use GlpiPlugin\Frexcore\Settings;

$name = isset($_GET['name']) && is_string($_GET['name']) ? $_GET['name'] : '';

if (!isset(Settings::IMAGE_KEYS[$name])) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Not found\n";
    return;
}

$path = Settings::imagePath($name);
if ($path === '') {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Not configured\n";
    return;
}

$mime = Settings::mimeFor($path);

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) filesize($path));
header('X-Content-Type-Options: nosniff');

// An uploaded SVG is a document that can carry script, and it is being served
// from the application's own origin. The sandbox is what keeps a brand asset
// from becoming a way into the session of everyone who loads a page.
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; sandbox");

// The URL carries a hash of the settings, so a replaced image arrives under a
// new URL and this can be cached hard.
header('Cache-Control: public, max-age=604800, immutable');

readfile($path);
