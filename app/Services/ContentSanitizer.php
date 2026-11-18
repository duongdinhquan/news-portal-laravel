<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Str;

class ContentSanitizer
{
    public function sanitize(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') return '';

        // Images are restricted to media served by this application; remote images are removed.
        $html = preg_replace_callback('/<img\\b[^>]*>/i', function (array $match): string {
            $tag = $match[0];
            if (!preg_match('/\\bsrc\\s*=\\s*(["\\\'])(.*?)\\1/i', $tag, $srcMatch)) return '';
            $src = html_entity_decode($srcMatch[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $parts = parse_url($src);
            $path = is_array($parts) ? ($parts['path'] ?? '') : '';
            if (!Str::startsWith($path, '/storage/')) return '';

            // Permit relative storage paths or absolute URLs on this application's configured origin only.
            $hasOrigin = isset($parts['scheme']) || isset($parts['host']);
            if ($hasOrigin) {
                $appOrigin = parse_url((string) config('app.url')) ?: [];
                $sameScheme = strtolower((string) ($parts['scheme'] ?? '')) === strtolower((string) ($appOrigin['scheme'] ?? ''));
                $sameHost = strtolower((string) ($parts['host'] ?? '')) === strtolower((string) ($appOrigin['host'] ?? ''));
                $samePort = (int) ($parts['port'] ?? 0) === (int) ($appOrigin['port'] ?? 0);
                if (!$sameScheme || !$sameHost || !$samePort) return '';

                $tag = preg_replace_callback('/\\bsrc\\s*=\\s*(["\\\']).*?\\1/i', static function () use ($path): string {
                    return 'src="'.htmlspecialchars($path, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"';
                }, $tag, 1) ?? $tag;
            }
            return $tag;
        }, $html) ?? $html;

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,h2,h3,strong,b,em,i,u,s,ul,ol,li,blockquote,a[href|title|target|rel],img[src|alt|title],hr,br');
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.SafeIframe', false);
        $config->set('CSS.AllowedProperties', []);
        $cachePath = storage_path('framework/cache/htmlpurifier');
        if (!is_dir($cachePath)) @mkdir($cachePath, 0775, true);
        $config->set('Cache.SerializerPath', $cachePath);
        $purifier = new HTMLPurifier($config);

        $clean = $purifier->purify($html);
        // Enforce safe rel values on links opened in a new tab.
        $clean = preg_replace_callback('/<a\\b([^>]*)>/i', function (array $match): string {
            $attrs = $match[1];
            if (preg_match('/target\\s*=\\s*(["\\\'])_blank\\1/i', $attrs)) {
                $attrs = preg_replace('/\\srel\\s*=\\s*(["\\\']).*?\\1/i', '', $attrs) ?? $attrs;
                $attrs .= ' rel="noopener noreferrer"';
            }
            return '<a'.$attrs.'>';
        }, $clean) ?? $clean;

        return $clean;
    }
}
