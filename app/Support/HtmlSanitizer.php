<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Keeps the formatting a product description editor can produce and removes everything else
 * (scripts, event handlers, unsafe links, unknown tags).
 */
class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'sub', 'sup',
        'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'blockquote', 'pre', 'code', 'hr',
        'a', 'img', 'span', 'div',
    ];

    private const DROP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select',
        'svg', 'math', 'noscript', 'template', 'link', 'meta', 'base', 'video', 'audio',
    ];

    private const ALLOWED_STYLES = ['color', 'background-color', 'text-align'];

    public static function looksLikeHtml(?string $value): bool
    {
        return (bool) preg_match('/<\s*\/?\s*(p|br|h[1-6]|ul|ol|li|strong|em|b|i|u|a|img|span|div|blockquote)\b/i', (string) $value);
    }

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="sanitize-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = (new DOMXPath($document))->query('//div[@id="sanitize-root"]')->item(0);

        if (! $root) {
            return e(strip_tags($html));
        }

        self::walk($root);

        $output = '';

        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    private static function walk(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node->nodeType === XML_COMMENT_NODE || $node->nodeType === XML_PI_NODE || $node->nodeType === XML_CDATA_SECTION_NODE) {
                $parent->removeChild($node);

                continue;
            }

            if (! $node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->tagName);

            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $parent->removeChild($node);

                continue;
            }

            self::walk($node);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }

                $parent->removeChild($node);

                continue;
            }

            self::cleanAttributes($node, $tag);
        }
    }

    private static function cleanAttributes(DOMElement $element, string $tag): void
    {
        $keep = [];

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim($attribute->value);

            if ($name === 'class') {
                $classes = array_filter(preg_split('/\s+/', $value) ?: [], fn ($class) => preg_match('/^ql-[a-z0-9-]+$/', $class));

                if ($classes) {
                    $keep['class'] = implode(' ', $classes);
                }
            } elseif ($name === 'style') {
                if ($style = self::cleanStyle($value)) {
                    $keep['style'] = $style;
                }
            } elseif ($tag === 'a' && $name === 'href' && self::safeUrl($value, ['http', 'https', 'mailto', 'tel'])) {
                $keep['href'] = $value;
            } elseif ($tag === 'img' && $name === 'src' && self::safeUrl($value, ['http', 'https'])) {
                $keep['src'] = $value;
            } elseif ($tag === 'img' && in_array($name, ['alt', 'width', 'height'], true)) {
                $keep[$name] = $value;
            }
        }

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $element->removeAttribute($attribute->name);
        }

        foreach ($keep as $name => $value) {
            $element->setAttribute($name, $value);
        }

        if ($tag === 'a' && $element->hasAttribute('href')) {
            $element->setAttribute('target', '_blank');
            $element->setAttribute('rel', 'noopener noreferrer nofollow');
        }

        if ($tag === 'img' && ! $element->hasAttribute('src')) {
            $element->parentNode?->removeChild($element);
        }
    }

    /** @param  list<string>  $schemes */
    private static function safeUrl(string $url, array $schemes): bool
    {
        if ($url === '' || preg_match('/[\x00-\x1F\x7F]/', $url)) {
            return false;
        }

        if (str_starts_with($url, '//')) {
            return false;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return is_string($scheme) && in_array(strtolower($scheme), $schemes, true);
    }

    private static function cleanStyle(string $style): string
    {
        $clean = [];

        foreach (explode(';', $style) as $declaration) {
            [$property, $value] = array_pad(explode(':', $declaration, 2), 2, '');
            $property = strtolower(trim($property));
            $value = trim($value);

            if (in_array($property, self::ALLOWED_STYLES, true) && preg_match('/^[#a-zA-Z0-9(),.\s%-]+$/', $value) && ! preg_match('/url|expression|javascript/i', $value)) {
                $clean[] = "{$property}: {$value}";
            }
        }

        return implode('; ', $clean);
    }
}
