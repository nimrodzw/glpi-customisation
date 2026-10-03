<?php

/**
 * -------------------------------------------------------------------------
 * FrexCore plugin — configurable settings.
 * -------------------------------------------------------------------------
 *
 * @copyright Copyright (C) 2026 by i-Vertix/PGUM.
 * @copyright Copyright (C) 2026 FrexCore and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * -------------------------------------------------------------------------
 */

namespace GlpiPlugin\Frexcore;

/**
 * Everything about a tenant that is allowed to differ from every other
 * tenant, and the one place it is written down.
 *
 * FrexCore runs one isolated instance per client. Before this existed,
 * giving a new client their colours meant editing a stylesheet, committing
 * it, pulling on the server and reloading nginx — which is a deployment for
 * something a client will ask to change twice in the first week, and which
 * cannot be delegated to anyone who is not comfortable on the command line.
 *
 * Values live in the application's own configuration table under a context
 * of our own, so the plugin still creates no tables. Uninstalling deletes
 * the rows and the instance goes back to the stylesheet defaults, which are
 * the values below.
 *
 * Those defaults are deliberately the same as the ones compiled into
 * frexcore.css. An instance nobody has configured looks exactly as it did
 * before this panel existed, and the stylesheet remains readable on its own
 * rather than becoming a file of empty placeholders.
 */
final class Settings
{
    public const CONTEXT = 'plugin:frexcore';

    /**
     * Images are written here rather than into the application tree.
     *
     * The tree is replaced wholesale by an upgrade. Branding written into it
     * is reverted by the next security update, silently, in front of a
     * client — which is the whole reason the original plugin's approach was
     * dropped. This directory is outside it and survives.
     */
    public const IMAGE_KEYS = [
        'logo_wide'        => ['Wide logo', 'Sidebar when expanded. Around 250x70.'],
        'logo_mark'        => ['Square mark', 'Sidebar when collapsed, and the browser tab. Square, around 100x100.'],
        'logo_login'       => ['Sign-in logo', 'Shown above the sign-in form. Around 220x130.'],
        'login_background' => ['Sign-in background', 'Fills the sign-in page behind the form. Landscape, 1600 wide or more.'],
    ];

    /** Accepted image types, by the bytes they actually start with. */
    private const MAGIC = [
        "\x89PNG\r\n\x1a\n" => 'image/png',
        "\xff\xd8\xff"      => 'image/jpeg',
        'GIF87a'            => 'image/gif',
        'GIF89a'            => 'image/gif',
        'RIFF'              => 'image/webp',
        '<svg'              => 'image/svg+xml',
        '<?xm'              => 'image/svg+xml',
    ];

    public const MAX_IMAGE_BYTES = 2097152;   // 2 MiB

    /**
     * Defaults. Changing one here changes the behaviour of every instance
     * that has not overridden it, so these are the shipped product.
     */
    public const DEFAULTS = [
        'product_name'           => 'FrexCore',
        'colour_primary'         => '#1e3d6b',
        'colour_primary_deep'    => '#142a4a',
        'colour_accent'          => '#00a8a8',
        'sidebar_style'          => 'gradient',
        'density'                => 'compact',
        'corner_style'           => 'square',
        'timeline_single_column' => '1',
        'favourites'             => '1',
        'queue_rows'             => '8',
        'demo_banner'            => '0',
        'demo_banner_text'       => 'Demonstration data. Not a live client system.',
    ];

    /** Choices offered in the panel, and what each one means in the page. */
    public const CHOICES = [
        'sidebar_style' => [
            'gradient' => 'Gradient',
            'solid'    => 'Solid',
            'ink'      => 'Near black',
        ],
        'density' => [
            'compact'     => 'Compact',
            'comfortable' => 'Comfortable',
        ],
        'corner_style' => [
            'square' => 'Square',
            'soft'   => 'Soft',
        ],
    ];

    /** @var array<string,string>|null */
    private static ?array $cache = null;

    /** Every setting, stored value where there is one and the default where there is not. */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $stored = [];
        try {
            $stored = \Config::getConfigurationValues(self::CONTEXT);
        } catch (\Throwable $e) {
            // A configuration table that cannot be read is not a reason to
            // fail the page. Defaults are a working product.
        }

        $out = self::DEFAULTS;
        foreach ($out as $key => $default) {
            if (isset($stored[$key]) && $stored[$key] !== '') {
                $out[$key] = (string) $stored[$key];
            }
        }
        foreach (array_keys(self::IMAGE_KEYS) as $key) {
            $out[$key] = isset($stored[$key]) ? (string) $stored[$key] : '';
        }

        return self::$cache = $out;
    }

    public static function get(string $key): string
    {
        return self::all()[$key] ?? (self::DEFAULTS[$key] ?? '');
    }

    public static function isOn(string $key): bool
    {
        return self::get($key) === '1';
    }

    /**
     * Validate and store. Returns the messages to show the operator.
     *
     * Validation is not politeness. These values are interpolated into a
     * stylesheet served to every page, so a colour that is not a colour is
     * a hole, not a typo.
     *
     * @return array{0: array<string>, 1: array<string>} [saved, rejected]
     */
    public static function save(array $input): array
    {
        $clean = [];
        $errors = [];

        foreach (self::DEFAULTS as $key => $default) {
            if (!array_key_exists($key, $input)) {
                // An unchecked box posts nothing at all, which is how a
                // toggle is turned off rather than left alone.
                if (in_array($default, ['0', '1'], true)) {
                    $clean[$key] = '0';
                }
                continue;
            }

            $value = is_string($input[$key]) ? trim($input[$key]) : '';

            switch ($key) {
                case 'colour_primary':
                case 'colour_primary_deep':
                case 'colour_accent':
                    if (preg_match('/^#[0-9a-fA-F]{6}$/', $value) !== 1) {
                        $errors[] = sprintf('%s is not a six digit colour.', self::label($key));
                        continue 2;
                    }
                    $clean[$key] = strtolower($value);
                    break;

                case 'sidebar_style':
                case 'density':
                case 'corner_style':
                    if (!isset(self::CHOICES[$key][$value])) {
                        $errors[] = sprintf('%s is not one of the offered options.', self::label($key));
                        continue 2;
                    }
                    $clean[$key] = $value;
                    break;

                case 'queue_rows':
                    $n = (int) $value;
                    if ($n < 3 || $n > 40) {
                        $errors[] = 'Queue card rows must be between 3 and 40.';
                        continue 2;
                    }
                    $clean[$key] = (string) $n;
                    break;

                case 'product_name':
                    // The name is written into the page, so it is stripped
                    // rather than trusted. Empty falls back to the default
                    // instead of leaving a product with no name at all.
                    $value = preg_replace('/[\x00-\x1f<>]/', '', $value) ?? '';
                    $value = mb_substr($value, 0, 40);
                    $clean[$key] = $value !== '' ? $value : self::DEFAULTS[$key];
                    break;

                case 'demo_banner_text':
                    $value = preg_replace('/[\x00-\x1f<>]/', '', $value) ?? '';
                    $clean[$key] = mb_substr($value, 0, 120);
                    break;

                default:
                    $clean[$key] = in_array($default, ['0', '1'], true)
                        ? ($value !== '' && $value !== '0' ? '1' : '0')
                        : $value;
            }
        }

        if ($clean !== []) {
            \Config::setConfigurationValues(self::CONTEXT, $clean);
            self::$cache = null;
        }

        return [array_keys($clean), $errors];
    }

    public static function label(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }

    // ------------------------------------------------------------- images

    /** Where uploaded images live, outside the application tree. */
    public static function imageDir(): string
    {
        if (defined('GLPI_PLUGIN_DOC_DIR')) {
            $base = GLPI_PLUGIN_DOC_DIR;
        } elseif (defined('GLPI_VAR_DIR')) {
            $base = GLPI_VAR_DIR . '/_plugins';
        } else {
            return '';
        }
        return $base . '/frexcore';
    }

    public static function imagePath(string $key): string
    {
        $stored = self::get($key);
        $dir    = self::imageDir();
        if ($stored === '' || $dir === '') {
            return '';
        }
        // The stored value is a basename this code wrote. It is still
        // checked, because a configuration row is only as trustworthy as
        // everything that can write to the database.
        if (preg_match('/^[a-z_]+\.[a-z0-9]{2,4}$/', $stored) !== 1) {
            return '';
        }
        $path = $dir . '/' . $stored;
        return is_file($path) ? $path : '';
    }

    public static function hasImage(string $key): bool
    {
        return self::imagePath($key) !== '';
    }

    /**
     * Accept an upload.
     *
     * The type is taken from the leading bytes rather than from the name or
     * the browser's claim, both of which are supplied by whoever is
     * uploading. The extension written to disk is derived from what the
     * bytes actually are.
     */
    public static function storeImage(string $key, array $file): ?string
    {
        if (!isset(self::IMAGE_KEYS[$key])) {
            return 'Unknown image.';
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;       // nothing offered, nothing to do
        }
        if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) {
            return sprintf('%s did not upload.', self::IMAGE_KEYS[$key][0]);
        }
        if (($file['size'] ?? 0) > self::MAX_IMAGE_BYTES) {
            return sprintf('%s is larger than 2 MB.', self::IMAGE_KEYS[$key][0]);
        }

        $tmp = $file['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return sprintf('%s was not a genuine upload.', self::IMAGE_KEYS[$key][0]);
        }

        $head = (string) file_get_contents($tmp, false, null, 0, 16);
        $mime = null;
        foreach (self::MAGIC as $magic => $candidate) {
            if (str_starts_with($head, $magic)) {
                $mime = $candidate;
                break;
            }
        }
        if ($mime === null) {
            return sprintf('%s is not a PNG, JPEG, GIF, WebP or SVG.', self::IMAGE_KEYS[$key][0]);
        }

        $dir = self::imageDir();
        if ($dir === '') {
            return 'No writable location for images on this instance.';
        }
        if (!is_dir($dir) && !@mkdir($dir, 0o775, true) && !is_dir($dir)) {
            return 'Could not create the image directory.';
        }
        if (!is_writable($dir)) {
            return sprintf('The image directory is not writable: %s', $dir);
        }

        $ext  = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif',
                 'image/webp' => 'webp', 'image/svg+xml' => 'svg'][$mime];
        $name = $key . '.' . $ext;

        // An earlier upload of the same image in another format would
        // otherwise be left behind and served by nothing.
        foreach (glob($dir . '/' . $key . '.*') ?: [] as $old) {
            @unlink($old);
        }

        if (!@move_uploaded_file($tmp, $dir . '/' . $name)) {
            return sprintf('Could not save %s.', self::IMAGE_KEYS[$key][0]);
        }
        @chmod($dir . '/' . $name, 0o644);

        \Config::setConfigurationValues(self::CONTEXT, [$key => $name]);
        self::$cache = null;
        return null;
    }

    public static function clearImage(string $key): void
    {
        if (!isset(self::IMAGE_KEYS[$key])) {
            return;
        }
        $dir = self::imageDir();
        if ($dir !== '') {
            foreach (glob($dir . '/' . $key . '.*') ?: [] as $old) {
                @unlink($old);
            }
        }
        \Config::setConfigurationValues(self::CONTEXT, [$key => '']);
        self::$cache = null;
    }

    public static function mimeFor(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png'  => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            default => 'application/octet-stream',
        };
    }

    // ------------------------------------------------------------- colour

    /** Mix a colour towards white or black. Used for hover and tint states. */
    public static function shift(string $hex, float $amount): string
    {
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $hex) !== 1) {
            return $hex;
        }
        $out = '#';
        for ($i = 1; $i < 7; $i += 2) {
            $c = hexdec(substr($hex, $i, 2));
            $c = $amount >= 0
                ? $c + ((255 - $c) * $amount)
                : $c * (1 + $amount);
            $out .= str_pad(dechex((int) round(max(0, min(255, $c)))), 2, '0', STR_PAD_LEFT);
        }
        return $out;
    }

    /** "30, 61, 107", which is what the base theme's own tokens expect. */
    public static function rgb(string $hex): string
    {
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $hex) !== 1) {
            return '0, 0, 0';
        }
        return implode(', ', [
            hexdec(substr($hex, 1, 2)),
            hexdec(substr($hex, 3, 2)),
            hexdec(substr($hex, 5, 2)),
        ]);
    }

    /**
     * A short hash of everything that affects the rendered page.
     *
     * Appended to the stylesheet URL, so a colour changed in the panel
     * reaches a browser that has the old one cached. Without it the operator
     * changes a colour, sees nothing, and concludes the panel is broken.
     */
    public static function hash(): string
    {
        $all = self::all();
        foreach (array_keys(self::IMAGE_KEYS) as $key) {
            $path = self::imagePath($key);
            $all['mtime_' . $key] = $path !== '' ? (string) @filemtime($path) : '';
        }
        return substr(sha1(serialize($all)), 0, 10);
    }
}
