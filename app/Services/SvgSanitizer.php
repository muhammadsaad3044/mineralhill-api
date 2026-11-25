<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;

class SvgSanitizer
{
    protected static array $blockedTags = [
        'script', 'foreignobject', 'iframe', 'object', 'embed',
        'animate', 'animatetransform', 'animatemotion', 'set',
        'audio', 'video', 'source', 'track', 'canvas', 'form',
        'input', 'button', 'select', 'textarea', 'link', 'style',
    ];

    /**
     * Sanitize an SVG file or content string.
     * Returns sanitized SVG string or false if invalid/unsafe.
     */
    public static function sanitize(string $svgContent): string|false
    {
        if (trim($svgContent) === '') {
            return false;
        }

        // Prevent external entity injection (XXE) and entity expansion attacks
        $prevEntityLoader = libxml_disable_entity_loader(true);
        $prevInternalErrors = libxml_use_internal_errors(true);

        try {
            $dom = new DOMDocument();
            $dom->formatOutput = false;
            $dom->preserveWhiteSpace = true;

            $loaded = $dom->loadXML($svgContent, LIBXML_NONET | LIBXML_NOENT | LIBXML_NOWARNING | LIBXML_NOERROR);
            if (! $loaded) {
                return false;
            }

            // Verify root element is svg
            $root = $dom->documentElement;
            if (! $root || strtolower($root->nodeName) !== 'svg') {
                return false;
            }

            $xpath = new DOMXPath($dom);

            // 1. Remove dangerous tags
            foreach (static::$blockedTags as $tag) {
                $nodes = $xpath->query("//*[local-name()='$tag']");
                if ($nodes) {
                    foreach ($nodes as $node) {
                        $node->parentNode?->removeChild($node);
                    }
                }
            }

            // 2. Iterate through all remaining elements to clean attributes
            $allElements = $xpath->query('//*');
            if ($allElements) {
                foreach ($allElements as $element) {
                    if (! ($element instanceof DOMElement)) {
                        continue;
                    }

                    $attributesToRemove = [];
                    foreach ($element->attributes as $attr) {
                        $attrName = strtolower($attr->nodeName);
                        $attrValue = strtolower(trim($attr->nodeValue));

                        // Check for inline event handlers (onclick, onload, etc.)
                        if (str_starts_with($attrName, 'on')) {
                            $attributesToRemove[] = $attr->nodeName;
                            continue;
                        }

                        // Check for javascript: or dangerous URI schemes
                        if (in_array($attrName, ['href', 'xlink:href', 'src', 'data', 'action'])) {
                            if (
                                str_starts_with($attrValue, 'javascript:') ||
                                str_starts_with($attrValue, 'vbscript:') ||
                                str_starts_with($attrValue, 'data:text/html') ||
                                str_starts_with($attrValue, 'data:text/javascript')
                            ) {
                                $attributesToRemove[] = $attr->nodeName;
                            }
                        }
                    }

                    foreach ($attributesToRemove as $attrName) {
                        $element->removeAttribute($attrName);
                    }
                }
            }

            return $dom->saveXML();
        } catch (Exception) {
            return false;
        } finally {
            libxml_use_internal_errors($prevInternalErrors);
            if ($prevEntityLoader !== null) {
                libxml_disable_entity_loader($prevEntityLoader);
            }
        }
    }
}
