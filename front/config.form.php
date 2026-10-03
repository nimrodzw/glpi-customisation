<?php

/**
 * FrexCore plugin — settings panel.
 *
 * Reached from the cog beside FrexCore on the plugins page. Everything a
 * tenant is allowed to differ on is here, and nothing here requires a file
 * to be edited, a repository to be pulled or a web server to be reloaded.
 *
 * Written as plain markup rather than against the application's form
 * components deliberately. Those components move between releases, and a
 * settings page that stops rendering after an upgrade takes the ability to
 * fix the instance with it, which is the worst moment to lose it.
 *
 * @copyright Copyright (C) 2026 FrexCore and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 */

use GlpiPlugin\Frexcore\Settings;

Session::checkRight('config', READ);

$self = Plugin::getWebDir('frexcore', false) . '/front/config.form.php';

/** Escape for HTML. Everything below goes through it, without exception. */
$e = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

// ---------------------------------------------------------------- saving

if (isset($_POST['frexcore_save'])) {
    Session::checkRight('config', UPDATE);

    $messages = [];

    [$saved, $errors] = Settings::save($_POST);

    foreach (array_keys(Settings::IMAGE_KEYS) as $key) {
        if (isset($_POST['clear_' . $key])) {
            Settings::clearImage($key);
            continue;
        }
        if (isset($_FILES[$key])) {
            $problem = Settings::storeImage($key, $_FILES[$key]);
            if ($problem !== null) {
                $errors[] = $problem;
            }
        }
    }

    foreach ($errors as $problem) {
        Session::addMessageAfterRedirect(htmlspecialchars($problem, ENT_QUOTES, 'UTF-8'), false, ERROR);
    }
    if ($errors === []) {
        Session::addMessageAfterRedirect('FrexCore settings saved.');
    }

    Html::back();
}

if (isset($_POST['frexcore_reset'])) {
    Session::checkRight('config', UPDATE);
    foreach (array_keys(Settings::IMAGE_KEYS) as $key) {
        Settings::clearImage($key);
    }
    Config::deleteConfigurationValues(
        Settings::CONTEXT,
        array_merge(array_keys(Settings::DEFAULTS), array_keys(Settings::IMAGE_KEYS))
    );
    Session::addMessageAfterRedirect('FrexCore settings returned to the shipped defaults.');
    Html::back();
}

// --------------------------------------------------------------- the page

Html::header('FrexCore', $_SERVER['PHP_SELF'], 'config', 'plugins');

$s        = Settings::all();
$writable = Settings::imageDir();
$canWrite = $writable !== '' && (is_writable($writable) || is_writable(dirname($writable)));
$readOnly = !Session::haveRight('config', UPDATE);

/** One labelled row. */
$row = static function (string $label, string $help, string $control) use ($e): void {
    echo '<div class="row mb-3 align-items-start">';
    echo '<div class="col-sm-4"><label class="form-label mb-0">' . $e($label) . '</label>';
    if ($help !== '') {
        echo '<div class="form-hint" style="font-size:.75rem;color:#7b8899;">' . $e($help) . '</div>';
    }
    echo '</div>';
    echo '<div class="col-sm-8">' . $control . '</div>';
    echo '</div>';
};

$text = static fn(string $k, string $val, int $max = 60, string $extra = ''): string =>
    sprintf(
        '<input type="text" class="form-control" name="%s" value="%s" maxlength="%d" %s>',
        htmlspecialchars($k, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($val, ENT_QUOTES, 'UTF-8'),
        $max,
        $extra
    );

$colour = static fn(string $k, string $val, string $extra = ''): string => sprintf(
    '<div class="d-flex align-items-center gap-2">'
    . '<input type="color" name="%1$s" value="%2$s" style="width:3rem;height:2rem;padding:2px;" %3$s>'
    . '<code style="font-size:.8125rem;color:#5a6b80;">%2$s</code></div>',
    htmlspecialchars($k, ENT_QUOTES, 'UTF-8'),
    htmlspecialchars($val, ENT_QUOTES, 'UTF-8'),
    $extra
);

$select = static function (string $k, string $val, string $extra = '') use ($e): string {
    $out = '<select class="form-select" name="' . $e($k) . '" ' . $extra . '>';
    foreach (Settings::CHOICES[$k] as $value => $label) {
        $out .= sprintf(
            '<option value="%s"%s>%s</option>',
            $e($value),
            $value === $val ? ' selected' : '',
            $e($label)
        );
    }
    return $out . '</select>';
};

$toggle = static function (string $k, string $val, string $label, string $extra = '') use ($e): string {
    return sprintf(
        '<label class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" '
        . 'name="%s" value="1"%s %s><span class="form-check-label">%s</span></label>',
        $e($k),
        $val === '1' ? ' checked' : '',
        $extra,
        $e($label)
    );
};

$disabled = $readOnly ? 'disabled' : '';

echo '<div class="container-fluid" style="max-width:62rem;">';

echo '<form method="post" action="' . $e($self) . '" enctype="multipart/form-data">';

// --- Identity ---------------------------------------------------------
echo '<div class="card mb-3"><div class="card-header"><h3 class="card-title">Identity</h3></div>'
   . '<div class="card-body">';

$row(
    'Product name',
    'Shown wherever the application names itself. Page titles and the footer are also rewritten at the web server, which stays in place as a backstop if this plugin is ever switched off.',
    $text('product_name', $s['product_name'], 40, $disabled)
);

echo '</div></div>';

// --- Colour -----------------------------------------------------------
echo '<div class="card mb-3"><div class="card-header"><h3 class="card-title">Colour</h3></div>'
   . '<div class="card-body">';

$row('Brand', 'Sidebar, primary buttons and the active state. Take it from the logo rather than picking something near it.',
    $colour('colour_primary', $s['colour_primary'], $disabled));
$row('Brand, darker', 'The foot of the sidebar gradient, and the chrome one step down from the brand colour.',
    $colour('colour_primary_deep', $s['colour_primary_deep'], $disabled));
$row('Accent', 'Links, focus rings and the marker on the active menu item. Used sparingly on purpose.',
    $colour('colour_accent', $s['colour_accent'], $disabled));
$row('Sidebar', 'The largest continuous surface in the product and the thing a client looks at all day.',
    $select('sidebar_style', $s['sidebar_style'], $disabled));

echo '<p class="mb-0" style="font-size:.75rem;color:#7b8899;">Hover, tint and focus colours are derived from these three. '
   . 'Setting them individually produces a palette that drifts out of relation to itself over a few changes.</p>';

echo '</div></div>';

// --- Layout -----------------------------------------------------------
echo '<div class="card mb-3"><div class="card-header"><h3 class="card-title">Layout</h3></div>'
   . '<div class="card-body">';

$row('Density', 'Compact fits more rows on a list, which is what an operator working a queue wants. Comfortable is the base theme spacing.',
    $select('density', $s['density'], $disabled));
$row('Corners', 'Square reads as a system of record. Soft reads as a web application.',
    $select('corner_style', $s['corner_style'], $disabled));
$row(
    'Activity stream',
    'The base theme lays a ticket out as a two-sided message thread. A single column with the author and time on one line is easier to read on a record that has been through several teams.',
    $toggle('timeline_single_column', $s['timeline_single_column'], 'Single column', $disabled)
);
$row('Favourite menu items', 'A star on each menu entry, pinning it to a Favourites block at the top of the sidebar. Stored per browser.',
    $toggle('favourites', $s['favourites'], 'Enabled', $disabled));
$row('Queue card rows', 'How many tickets the "needing attention" dashboard card lists before it scrolls. Between 3 and 40.',
    sprintf('<input type="number" class="form-control" name="queue_rows" value="%s" min="3" max="40" style="max-width:8rem;" %s>',
        $e($s['queue_rows']), $disabled));

echo '</div></div>';

// --- Images -----------------------------------------------------------
echo '<div class="card mb-3"><div class="card-header"><h3 class="card-title">Brand images</h3></div>'
   . '<div class="card-body">';

if (!$canWrite) {
    echo '<div class="alert alert-warning" style="font-size:.8125rem;">Images cannot be saved: '
       . $e($writable !== '' ? $writable . ' is not writable by the web server.' : 'this instance has no plugin data directory.')
       . ' Everything else on this page still works.</div>';
}

echo '<p style="font-size:.75rem;color:#7b8899;">PNG, JPEG, GIF, WebP or SVG, up to 2 MB. '
   . 'Files are stored outside the application tree, so an upgrade cannot revert them. '
   . 'Leave a field empty to keep what is already there.</p>';

$assetBase = Plugin::getWebDir('frexcore', false) . '/front/asset.php?name=';
$hash      = Settings::hash();

foreach (Settings::IMAGE_KEYS as $key => [$label, $help]) {
    $control = sprintf(
        '<input type="file" class="form-control" name="%s" accept="image/*" %s>',
        $e($key),
        $canWrite && !$readOnly ? '' : 'disabled'
    );
    if (Settings::hasImage($key)) {
        $control .= sprintf(
            '<div class="mt-2 d-flex align-items-center gap-3">'
            . '<img src="%s%s&v=%s" alt="" style="max-height:48px;max-width:200px;background:#eef2f7;padding:4px;border:1px solid #dbe2ec;">'
            . '<label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="clear_%s" value="1" %s>'
            . '<span class="form-check-label" style="font-size:.8125rem;">Remove</span></label></div>',
            $e($assetBase),
            $e($key),
            $e($hash),
            $e($key),
            $disabled
        );
    }
    $row($label, $help, $control);
}

echo '</div></div>';

// --- Demonstration ----------------------------------------------------
echo '<div class="card mb-3"><div class="card-header"><h3 class="card-title">Demonstration</h3></div>'
   . '<div class="card-body">';

$row(
    'Banner',
    'A fixed strip across the top of every page. A tenant holding invented policyholders should say so on every screen, so nobody mistakes a demonstration for a client system.',
    $toggle('demo_banner', $s['demo_banner'], 'Show the banner', $disabled)
);
$row('Banner text', 'Up to 120 characters.',
    $text('demo_banner_text', $s['demo_banner_text'], 120, $disabled));

echo '</div></div>';

// --- Actions ----------------------------------------------------------
if (!$readOnly) {
    echo '<div class="d-flex gap-2 mb-4">';
    echo '<button type="submit" name="frexcore_save" value="1" class="btn btn-primary">Save</button>';
    echo '<button type="submit" name="frexcore_reset" value="1" class="btn btn-secondary" '
       . 'onclick="return confirm(\'Return every FrexCore setting to the shipped default, and delete uploaded images?\');">'
       . 'Reset to defaults</button>';
    echo '</div>';
} else {
    echo '<p class="mb-4" style="font-size:.8125rem;color:#7b8899;">You have read access to configuration but not update, so this page is a view.</p>';
}

Html::closeForm();

echo '</div>';

Html::footer();
