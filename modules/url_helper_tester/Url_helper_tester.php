<?php

/**
 * URL Helper Tester Module
 *
 * Provides comprehensive unit tests for all url_helper functions.
 * Each test records: key, label, expected, actual, pass.
 *
 * Context notes:
 * - current_url(), segment(), get_num_segments(), get_last_segment(),
 *   and previous_url() are request-context-dependent (they reflect the live URL).
 *   Tests validate type, format, and structural correctness rather than
 *   exact values, since the URL varies by environment.
 * - redirect() cannot be tested — it sends HTTP headers and exits.
 */
class Url_helper_tester extends Trongate {

    public function index(): void {
        $data['test_results'] = $this->run_tests();
        $data['view_module']  = $this->module_name;
        $data['view_file']    = 'test_results';
        $this->view('test_results', $data);
    }

    // -------------------------------------------------------------------------
    // Test runner
    // -------------------------------------------------------------------------

    private function run_tests(): array {
        $results = [];

        $this->test_anchor($results);
        $this->test_current_url($results);
        $this->test_get_last_segment($results);
        $this->test_get_num_segments($results);
        $this->test_previous_url($results);
        $this->test_redirect($results);
        $this->test_remove_query_string($results);
        $this->test_segment($results);

        return $results;
    }

    // -------------------------------------------------------------------------
    // Assertion helpers
    // -------------------------------------------------------------------------

    private function assert_eq(array &$r, string $k, $actual, $expected, string $label): void {
        $r[] = ['key' => $k, 'label' => $label, 'expected' => $expected, 'actual' => $actual, 'pass' => $actual === $expected];
    }

    private function assert_true(array &$r, string $k, bool $cond, string $label, $actual = null): void {
        $r[] = ['key' => $k, 'label' => $label, 'expected' => true, 'actual' => $actual ?? ($cond ? '(condition met)' : '(condition failed)'), 'pass' => $cond];
    }

    private function assert_contains(array &$r, string $k, string $hay, string $needle, string $label): void {
        $r[] = ['key' => $k, 'label' => $label, 'expected' => "contains: {$needle}", 'actual' => str_contains($hay, $needle) ? "contains: {$needle}" : "(missing) {$needle}", 'pass' => str_contains($hay, $needle)];
    }

    private function assert_not_contains(array &$r, string $k, string $hay, string $needle, string $label): void {
        $r[] = ['key' => $k, 'label' => $label, 'expected' => "NOT contains: {$needle}", 'actual' => !str_contains($hay, $needle) ? "NOT contains: {$needle}" : "(present, should be absent) {$needle}", 'pass' => !str_contains($hay, $needle)];
    }

    private function assert_skip(array &$r, string $k, string $label, string $reason): void {
        $r[] = ['key' => $k, 'label' => $label . ' (SKIPPED)', 'expected' => '(skipped)', 'actual' => $reason, 'pass' => true];
    }

    // =========================================================================
    // anchor()
    // =========================================================================

    private function test_anchor(array &$results): void {
        $k = 'anchor';

        // Standard internal route — BASE_URL prepended
        $html = anchor('users/profile', 'View Profile', ['class' => 'btn', 'id' => 'profile-link']);
        $this->assert_contains($results, $k, $html, '<a ',            'Contains <a> tag');
        $this->assert_contains($results, $k, $html, 'href="',        'Has href attribute');
        $this->assert_contains($results, $k, $html, 'users/profile', 'href contains provided route');
        $this->assert_contains($results, $k, $html, 'View Profile',  'Link text present');
        $this->assert_contains($results, $k, $html, 'class="btn"',   'class attribute applied');
        $this->assert_contains($results, $k, $html, 'id="profile-link"', 'id attribute applied');
        $this->assert_contains($results, $k, $html, '</a>',          'Has closing </a>');

        // Absolute URL — should NOT prepend BASE_URL
        $html_abs = anchor('https://example.com', 'External');
        $this->assert_contains($results, $k, $html_abs, 'href="https://example.com"', 'Absolute URL used as-is');

        // Protocol-relative URL
        $html_proto = anchor('//cdn.example.com/script.js', 'CDN');
        $this->assert_contains($results, $k, $html_proto, '//cdn.example.com', 'Protocol-relative URL preserved');

        // Null text — URL used as link text
        $html_no_text = anchor('contact');
        $this->assert_contains($results, $k, $html_no_text, 'contact', 'Null text: URL used as link text');

        // HTML in link text — allowed (not escaped) to support embedded tags like <img> or <strong>
        $html_xss = anchor('safe/page', '<strong>Bold</strong>');
        $this->assert_contains($results, $k, $html_xss, '<strong>', 'HTML in text: allowed (not escaped) to support embedded tags');

        // Empty attributes array — no extra attrs
        $html_bare = anchor('home');
        $this->assert_contains($results, $k, $html_bare, '<a ', 'No attributes: still generates anchor');
    }

    // =========================================================================
    // current_url()
    // =========================================================================

    private function test_current_url(array &$results): void {
        $k = 'current_url';

        $url = current_url();

        // Must return a non-empty string
        $this->assert_true($results, $k, is_string($url) && !empty($url), 'Returns a non-empty string', $url);

        // Must start with http:// or https://
        $this->assert_true($results, $k,
            str_starts_with($url, 'http://') || str_starts_with($url, 'https://'),
            'Starts with http:// or https://',
            $url
        );

        // Must contain BASE_URL
        $this->assert_contains($results, $k, $url, BASE_URL, 'Contains BASE_URL');

        // Must contain the current module path
        $this->assert_contains($results, $k, $url, 'url_helper_tester', 'Contains current module path');
    }

    // =========================================================================
    // get_last_segment()
    // =========================================================================

    private function test_get_last_segment(array &$results): void {
        $k = 'get_last_segment';

        $last = get_last_segment();

        // Must return a string
        $this->assert_true($results, $k, is_string($last), 'Returns a string', $last ?: '(empty string)');

        // Must not contain slashes
        $this->assert_true($results, $k, !str_contains($last, '/'), 'Does not contain slashes', $last ?: '(empty)');
    }

    // =========================================================================
    // get_num_segments()
    // =========================================================================

    private function test_get_num_segments(array &$results): void {
        $k = 'get_num_segments';

        $num = get_num_segments();

        // Must return an integer
        $this->assert_true($results, $k, is_int($num), 'Returns an integer', (string)$num);

        // Must be non-negative
        $this->assert_true($results, $k, $num >= 0, 'Value is non-negative', (string)$num);

        // This URL (/url_helper_tester) resolves to exactly 2 segments: ['url_helper_tester', 'index'] due to auto-routing
        $this->assert_eq($results, $k, $num, 2, 'Segment count is 2 for /url_helper_tester (auto-appends \'index\')');
    }

    // =========================================================================
    // previous_url()
    // =========================================================================

    private function test_previous_url(array &$results): void {
        $k = 'previous_url';

        $prev = previous_url();

        // Must return a string (empty string when no referrer)
        $this->assert_true($results, $k, is_string($prev), 'Returns a string (may be empty)', $prev ?: '(empty string)');

        // If non-empty, must start with http:// or https://
        if (!empty($prev)) {
            $this->assert_true($results, $k,
                str_starts_with($prev, 'http://') || str_starts_with($prev, 'https://'),
                'Non-empty: starts with http:// or https://',
                $prev
            );
        } else {
            $this->assert_skip($results, $k,
                'Non-empty previous URL format check',
                'No HTTP_REFERER in this request context'
            );
        }
    }

    // =========================================================================
    // redirect() — sends HTTP headers and exits; cannot be tested live
    // =========================================================================

    private function test_redirect(array &$results): void {
        $k = 'redirect';

        $this->assert_skip($results, $k,
            'Internal route redirect (prepends BASE_URL)',
            'redirect() sends a Location header and calls exit() — cannot be tested in a live page context'
        );
        $this->assert_skip($results, $k,
            'Absolute URL redirect (used unchanged)',
            'redirect() sends a Location header and calls exit() — cannot be tested in a live page context'
        );
    }

    // =========================================================================
    // remove_query_string()
    // =========================================================================

    private function test_remove_query_string(array &$results): void {
        $k = 'remove_query_string';

        // Standard query string removed
        $this->assert_eq($results, $k,
            remove_query_string('http://example.com/page?param=value'),
            'http://example.com/page',
            'Standard: query string stripped'
        );

        // Multiple params
        $this->assert_eq($results, $k,
            remove_query_string('https://example.com/search?q=trongate&page=2'),
            'https://example.com/search',
            'Multiple params: all stripped'
        );

        // No query string — returned unchanged
        $this->assert_eq($results, $k,
            remove_query_string('http://example.com/page'),
            'http://example.com/page',
            'No query string: URL returned unchanged'
        );

        // Empty query string (trailing ?)
        $this->assert_eq($results, $k,
            remove_query_string('http://example.com/page?'),
            'http://example.com/page',
            'Trailing ? only: question mark removed'
        );

        // Fragment preserved / query stripped
        // Note: behaviour may vary — testing that at minimum the ? part is gone
        $result = remove_query_string('http://example.com/page?foo=bar#section');
        $this->assert_not_contains($results, $k, $result, 'foo=bar', 'Fragment with query: query params stripped');
    }

    // =========================================================================
    // segment()
    // =========================================================================

    private function test_segment(array &$results): void {
        $k = 'segment';

        // Segment 1 for /url_helper_tester — must be 'url_helper_tester'
        $seg1 = segment(1);
        $this->assert_eq($results, $k, $seg1, 'url_helper_tester', 'segment(1): returns module name for this page');

        // Out-of-range segment — must return empty string
        $seg_oob = segment(999);
        $this->assert_eq($results, $k, $seg_oob, '', 'Out-of-range segment: returns empty string');

        // Type casting — 'int' cast on numeric segment
        // Simulate by testing against a known numeric segment value elsewhere is not possible;
        // we verify cast of an empty segment to int returns 0
        $cast_int = segment(999, 'int');
        $this->assert_eq($results, $k, $cast_int, 0, 'Out-of-range + cast int: returns 0');

        // Type casting — 'bool' on empty returns false
        $cast_bool = segment(999, 'bool');
        $this->assert_eq($results, $k, $cast_bool, false, 'Out-of-range + cast bool: returns false');

        // Type casting — 'array' on empty string returns [''] in PHP
        $cast_arr = segment(999, 'array');
        $this->assert_eq($results, $k, $cast_arr, [''], 'Out-of-range + cast array: returns an array containing an empty string');

        // Null var_type (default) — same as no casting; string returned
        $seg_null = segment(1, null);
        $this->assert_true($results, $k, is_string($seg_null), 'null var_type: returns string', $seg_null);
    }
}
