<?php

namespace App\Services;

use DOMDocument;
use DOMElement;

class PatternEmbedSanitizer
{
    /**
     * @var array<int, string>
     */
    private const TRUSTED_HOSTS = [
        'musescore.com',
        'youtube.com',
        'youtube-nocookie.com',
        'soundcloud.com',
    ];

    /**
     * @var array<int, string>
     */
    private const ALLOWED_ATTRIBUTES = [
        'src',
        'width',
        'height',
        'frameborder',
        'allow',
        'allowfullscreen',
    ];

    public static function sanitize(?string $embedCode): ?string
    {
        if (! filled(trim((string) $embedCode))) {
            return null;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?><div>'.$embedCode.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $wrapper = $document->getElementsByTagName('div')->item(0);

        if (! $wrapper instanceof DOMElement) {
            return null;
        }

        foreach ($wrapper->childNodes as $child) {
            if (! $child instanceof DOMElement || $child->tagName !== 'iframe') {
                return null;
            }

            if (! self::isTrustedIframe($child)) {
                return null;
            }
        }

        return trim((string) $document->saveHTML($wrapper));
    }

    private static function isTrustedIframe(DOMElement $iframe): bool
    {
        foreach (iterator_to_array($iframe->attributes) as $attribute) {
            $name = strtolower($attribute->name);

            if (str_starts_with($name, 'on') || ! in_array($name, self::ALLOWED_ATTRIBUTES, true)) {
                return false;
            }
        }

        $src = trim((string) $iframe->getAttribute('src'));

        if ($src === '' || ! filter_var($src, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parsed = parse_url($src);
        $host = strtolower((string) ($parsed['host'] ?? ''));
        $scheme = strtolower((string) ($parsed['scheme'] ?? ''));

        if ($scheme !== 'https') {
            return false;
        }

        foreach (self::TRUSTED_HOSTS as $trustedHost) {
            if ($host === $trustedHost || str_ends_with($host, '.'.$trustedHost)) {
                return true;
            }
        }

        return false;
    }
}
