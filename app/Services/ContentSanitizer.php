<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Service for sanitizing HTML content to prevent XSS attacks while allowing basic formatting.
 */

namespace App\Services;

/**
 * Simple HTML content sanitizer.
 *
 * Strips all HTML tags except a safe whitelist for pedagogical content.
 * In production, consider using HTMLPurifier for more robust sanitization.
 */
class ContentSanitizer
{
    /**
     * Allowed HTML tags for pedagogical content.
     */
    private const ALLOWED_TAGS = [
        'h2', 'h3', 'h4', 'p', 'br', 'hr',
        'ul', 'ol', 'li',
        'strong', 'em', 'b', 'i', 'u', 'code', 'pre',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'blockquote', 'span', 'div',
        'a', 'img',
    ];

    /**
     * Allowed attributes per tag.
     */
    private const ALLOWED_ATTRS = [
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'width', 'height'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
    ];

    /**
     * Sanitize HTML content. Strips dangerous tags and attributes.
     */
    public static function sanitize(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        // Strip all tags except allowed ones
        $allowedTagString = '<' . implode('><', self::ALLOWED_TAGS) . '>';
        $cleaned = strip_tags($html, $allowedTagString);

        // Remove all event handler attributes (onclick, onerror, etc.)
        $cleaned = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $cleaned);
        $cleaned = preg_replace('/\s+on\w+\s*=\s*\S+/i', '', $cleaned);

        // Remove javascript: protocol in href/src
        $cleaned = preg_replace('/\bhref\s*=\s*["\']?\s*javascript\s*:/i', 'href="', $cleaned);
        $cleaned = preg_replace('/\bsrc\s*=\s*["\']?\s*javascript\s*:/i', 'src="', $cleaned);

        // Remove data: protocol in src (prevents data URI XSS)
        $cleaned = preg_replace('/\bsrc\s*=\s*["\']?\s*data\s*:/i', 'src="', $cleaned);

        // Remove style attributes (can be used for CSS injection)
        $cleaned = preg_replace('/\bstyle\s*=\s*["\'][^"\']*["\']/i', '', $cleaned);

        return $cleaned;
    }

    /**
     * Render sanitized HTML content for Blade views.
     * Use this instead of {!! $content !!} directly.
     */
    public static function render(?string $html): string
    {
        return self::sanitize($html);
    }
}
