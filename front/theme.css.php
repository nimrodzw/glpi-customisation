<?php

/**
 * FrexCore plugin — the configured half of the theme.
 *
 * The structural half lives in frexcore.css, which nginx serves from outside
 * the application tree so an upgrade cannot revert it and a plugin failure
 * cannot remove it. What is here is only what an operator is allowed to
 * change: colour, density, geometry, the activity stream arrangement and the
 * brand images. Every value has a default compiled into that stylesheet, so
 * if this file never loads the instance looks exactly as it did before the
 * panel existed rather than looking broken.
 *
 * Variables are set at `:root:root` rather than `:root`. That is one step
 * more specific than the base stylesheet without being `!important`, so it
 * wins whichever order the two files happen to arrive in — and the ordering
 * of a plugin stylesheet against an nginx-injected one is not something to
 * depend on.
 *
 * @copyright Copyright (C) 2026 FrexCore and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 */

use GlpiPlugin\Frexcore\Settings;

// This runs on the sign-in page, where nobody is authenticated yet, so it
// must not assume a session. It reads configuration and writes CSS; it is
// deliberately incapable of doing anything else.

$s = Settings::all();

$primary = $s['colour_primary'];
$deep    = $s['colour_primary_deep'];
$accent  = $s['colour_accent'];

$lift       = Settings::shift($primary, 0.18);
$wash       = Settings::shift($primary, 0.90);
$accentDeep = Settings::shift($accent, -0.25);
$primaryRgb = Settings::rgb($primary);
$accentRgb  = Settings::rgb($accent);

$sidebar = match ($s['sidebar_style']) {
    'solid' => $deep,
    'ink'   => '#10151d',
    default => sprintf('linear-gradient(180deg, %s 0%%, %s 100%%)', $primary, $deep),
};

[$padY, $padX] = $s['density'] === 'comfortable' ? ['.5rem', '.75rem'] : ['.3rem', '.55rem'];
[$radius, $radiusLg] = $s['corner_style'] === 'soft' ? ['6px', '8px'] : ['2px', '3px'];

$assets = \Plugin::getWebDir('frexcore', false) . '/front/asset.php?name=';
$v      = '&v=' . Settings::hash();

$css = [];

$css[] = <<<CSS
/* FrexCore, configured values. Generated from the plugin settings panel. */
:root:root,
:root:root[data-bs-theme="light"],
:root:root[data-bs-theme="dark"] {
  --fx-navy:        {$primary};
  --fx-navy-deep:   {$deep};
  --fx-navy-lift:   {$lift};
  --fx-teal:        {$accent};
  --fx-teal-deep:   {$accentDeep};
  --fx-sidebar-bg:  {$sidebar};
  --fx-radius:      {$radius};
  --fx-radius-lg:   {$radiusLg};
  --fx-row-pad-y:   {$padY};
  --fx-row-pad-x:   {$padX};

  --tblr-primary:          {$primary};
  --tblr-primary-rgb:      {$primaryRgb};
  --tblr-primary-darken:   {$deep};
  --tblr-primary-lt:       {$wash};
  --tblr-link-color:       {$accentDeep};
  --tblr-link-hover-color: {$primary};
  --tblr-active-bg:        {$wash};
  --tblr-accent:           {$accent};
  --tblr-focus-ring-color: rgba({$accentRgb}, .3);

  --bs-primary:            {$primary};
  --bs-primary-rgb:        {$primaryRgb};
  --bs-link-color:         {$accentDeep};
  --bs-link-hover-color:   {$primary};
  --bs-focus-ring-color:   rgba({$accentRgb}, .3);

  --glpi-mainmenu-bg:      {$deep};
  --glpi-primary:          {$primary};
  --glpi-accent:           {$accent};
}
CSS;

// The two rules the base stylesheet cannot express as a variable, because a
// gradient and a flat colour are different value types in the same property.
$css[] = <<<CSS
.navbar-vertical,
.navbar-vertical.navbar-expand-lg,
aside.navbar,
.sidebar,
#navbar-menu.navbar-vertical {
  background: var(--fx-sidebar-bg) !important;
}
CSS;

// --- Brand images -----------------------------------------------------
// Set as backgrounds on the elements that carry them, so no template is
// patched and no file inside the application tree is replaced.

if (Settings::hasImage('logo_wide')) {
    $css[] = <<<CSS
.glpi-logo {
  background-image: url("{$assets}logo_wide{$v}") !important;
  background-size: contain !important;
  background-repeat: no-repeat !important;
  background-position: left center !important;
}
CSS;
}

if (Settings::hasImage('logo_mark')) {
    $css[] = <<<CSS
.navbar-vertical.navbar-collapsed .glpi-logo,
.navbar-collapsed .glpi-logo {
  background-image: url("{$assets}logo_mark{$v}") !important;
  background-position: center !important;
}
CSS;
}

if (Settings::hasImage('logo_login')) {
    $css[] = <<<CSS
.login-container img[src*="login_logo"],
.login-container .navbar-brand img,
.card-login img {
  content: url("{$assets}logo_login{$v}");
  max-height: 130px;
  width: auto;
}
CSS;
}

if (Settings::hasImage('login_background')) {
    $css[] = <<<CSS
body.page-login,
.login-page,
body:has(.login-container) {
  background-image: url("{$assets}login_background{$v}") !important;
  background-size: cover !important;
  background-position: center !important;
  background-repeat: no-repeat !important;
}
/* The form has to stay readable over a photograph nobody vetted for
   contrast, so it sits on its own opaque surface rather than trusting
   whatever was uploaded. */
.login-container .card,
.card-login {
  background: rgba(255, 255, 255, .97) !important;
  backdrop-filter: saturate(140%) blur(2px);
}
CSS;
}

// --- Activity stream --------------------------------------------------
// Optional because it is the one place the theme changes arrangement rather
// than appearance. Turned off, the record falls back to the base theme's
// two-sided thread, which is a different look rather than a broken one.

if ($s['timeline_single_column'] === '1') {
    $css[] = <<<'CSS'
.itil-timeline .timeline-item,
.itil-timeline .timeline-item.ms-auto {
  margin-left: 0 !important;
  margin-right: 0 !important;
  width: 100%;
  max-width: 100%;
  margin-bottom: .5rem !important;
}
.itil-timeline .timeline-item .user-part,
.itil-timeline .timeline-item .user-part.order-sm-last {
  order: -1 !important;
  margin-left: 0 !important;
  margin-right: .25rem;
}
.itil-timeline .timeline-content,
.itil-timeline .timeline-content.t-right,
.itil-timeline .timeline-content.t-middle,
.itil-timeline .timeline-content.t-left {
  margin-top: 0 !important;
  border: 1px solid #e3e9f1 !important;
  border-left: 3px solid #c6d0dd !important;
  border-radius: var(--fx-radius) !important;
  box-shadow: none !important;
  background: #fff;
}
.itil-timeline .timeline-header {
  padding: .3rem .6rem;
  border-bottom: 1px solid #eef1f6;
  font-size: .75rem;
  align-items: center;
}
.itil-timeline .timeline-header .creator { font-weight: 600; color: var(--fx-ink); }
.itil-timeline .timeline-header .creator a { color: var(--fx-ink) !important; }
.itil-timeline .timeline-header time,
.itil-timeline .timeline-header .text-muted {
  font-size: .6875rem;
  color: #7b8899 !important;
  font-variant-numeric: tabular-nums;
}
.itil-timeline .timeline-content .card-body,
.itil-timeline .read-only-content {
  padding: .5rem .6rem !important;
  font-size: .8125rem;
  line-height: 1.5;
}
.itil-timeline .timeline-item .avatar {
  width: 1.75rem !important;
  height: 1.75rem !important;
  font-size: .625rem !important;
  border-radius: var(--fx-radius) !important;
}

/* Entry kinds. Edge colour only, and only three meanings: what the
   requester saw, what the desk kept to itself, and what closed the record. */
.itil-timeline .timeline-item.ITILFollowup .timeline-content {
  border-left-color: #93a1b5 !important;
}
.itil-timeline .timeline-item.private-item .timeline-content {
  background: #fdf8ec !important;
  border-left-color: #b06000 !important;
  border-color: #f0e4cc !important;
}
.itil-timeline .timeline-item.private-item .timeline-header { border-bottom-color: #f0e4cc; }
.itil-timeline .timeline-item.private-item .is-private { color: #b06000 !important; }
.itil-timeline .timeline-item.ITILTask .timeline-content {
  border-left-color: var(--fx-navy) !important;
  background: #fafcff;
}
.itil-timeline .timeline-item.ITILTask.todo .timeline-content {
  border-left-color: #b06000 !important;
}
.itil-timeline .timeline-item.ITILSolution .timeline-content {
  border-left-color: #2e7d53 !important;
  background: #f4faf6 !important;
  border-color: #d8e9de !important;
}
.itil-timeline .timeline-item.ITILSolution .timeline-header { border-bottom-color: #d8e9de; }
.itil-timeline .timeline-item.ITILValidation .timeline-content {
  border-left-color: #6b4fa8 !important;
}
.itil-timeline .timeline-item.tasks-title,
.itil-timeline .timeline-item.validations-title {
  font-size: var(--fx-label);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .05em;
  color: #7b8899;
}
CSS;
}

// --- Demonstration banner ---------------------------------------------
// Drawn in CSS rather than injected into the page, so it cannot be removed
// by a stray script and cannot break a template. A tenant holding invented
// policyholders should say so on every screen.

if ($s['demo_banner'] === '1' && $s['demo_banner_text'] !== '') {
    $text = addcslashes($s['demo_banner_text'], "\\\"\n\r");
    $css[] = <<<CSS
body::before {
  content: "{$text}";
  display: block;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 2000;
  padding: .2rem .75rem;
  background: #b06000;
  color: #fff;
  font-size: .6875rem;
  font-weight: 600;
  letter-spacing: .05em;
  text-transform: uppercase;
  text-align: center;
  pointer-events: none;
}
body { padding-top: 1.35rem !important; }
@media print { body::before { display: none; } body { padding-top: 0 !important; } }
CSS;
}

$body = implode("\n\n", $css) . "\n";

header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: public, max-age=86400');
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . strlen($body));

echo $body;
