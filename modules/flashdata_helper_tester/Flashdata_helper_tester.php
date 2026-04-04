<?php

/**
 * Flashdata Helper Tester Module
 *
 * Provides comprehensive unit tests for set_flashdata() and flashdata().
 * Each test records input, expected output, actual output, and pass/fail status.
 *
 * Note on test isolation: each test that sets flashdata must clear the session
 * slot before asserting, since flashdata() consumes and removes the stored
 * message in one call. Tests are run sequentially using this natural consumption.
 */
class Flashdata_helper_tester extends Trongate {

    /**
     * Display the test results page.
     */
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

        // Ensure a clean session state before any test runs
        unset($_SESSION['flashdata']);

        $this->test_set_flashdata($results);
        $this->test_flashdata($results);

        return $results;
    }

    // -------------------------------------------------------------------------
    // Assertion helpers (mirrors string_helper_tester pattern)
    // -------------------------------------------------------------------------

    private function assert_eq(array &$results, string $key, $actual, $expected, string $label): void {
        $results[] = [
            'key'      => $key,
            'label'    => $label,
            'expected' => $expected,
            'actual'   => $actual,
            'pass'     => $actual === $expected,
        ];
    }

    private function assert_true(array &$results, string $key, bool $condition, string $label, $actual = null): void {
        $results[] = [
            'key'      => $key,
            'label'    => $label,
            'expected' => true,
            'actual'   => $actual ?? ($condition ? '(condition met)' : '(condition failed)'),
            'pass'     => $condition,
        ];
    }

    // =========================================================================
    // set_flashdata()
    // =========================================================================

    private function test_set_flashdata(array &$results): void {
        $k = 'set_flashdata';

        // Standard message — must appear in $_SESSION['flashdata']
        set_flashdata('Hello World');
        $this->assert_eq($results, $k, $_SESSION['flashdata'] ?? null, 'Hello World', 'Standard message stored in session');

        // Overwrites previous value — only most recent message is kept
        set_flashdata('First');
        set_flashdata('Second');
        $this->assert_eq($results, $k, $_SESSION['flashdata'] ?? null, 'Second', 'Subsequent call overwrites previous message');

        // Empty string — still stored (not filtered)
        set_flashdata('');
        $this->assert_eq($results, $k, $_SESSION['flashdata'] ?? null, '', 'Empty string is stored as-is');

        // Message with HTML — stored raw, HTML not escaped at storage time
        set_flashdata('<b>Bold</b>');
        $this->assert_eq($results, $k, $_SESSION['flashdata'] ?? null, '<b>Bold</b>', 'HTML in message stored raw (not escaped on set)');

        // Clean up so flashdata() tests start with no message
        unset($_SESSION['flashdata']);
    }

    // =========================================================================
    // flashdata()
    // =========================================================================

    private function test_flashdata(array &$results): void {
        $k = 'flashdata';

        // No message in session — must return null
        $this->assert_eq($results, $k, flashdata(), null, 'No message in session: returns null');

        // Default HTML wrapping — green <p> tag
        set_flashdata('Success!');
        $output = flashdata();
        $this->assert_true($results, $k,
            str_contains($output, '<p style="color: green;">') && str_contains($output, 'Success!') && str_contains($output, '</p>'),
            'Default wrapping: green <p> tag contains message',
            $output
        );

        // flashdata() consumes the message — second call in a row returns null
        $this->assert_eq($results, $k, flashdata(), null, 'Message consumed on retrieval: second call returns null');

        // Custom opening and closing HTML
        set_flashdata('Custom!');
        $output = flashdata('<div class="alert">', '</div>');
        $this->assert_eq($results, $k, $output, '<div class="alert">Custom!</div>', 'Custom HTML: message wrapped in provided tags');

        // Only opening_html provided — closing_html falls back to default </p>
        // Per implementation: if $opening_html is set, $closing_html is used as-is (null → appended as empty string in output)
        set_flashdata('Half custom');
        $output = flashdata('<span>', null);
        $this->assert_true($results, $k,
            str_starts_with($output, '<span>') && str_contains($output, 'Half custom'),
            'Custom opening only: message starts with provided opening tag',
            $output
        );

        // Empty string message — wrapped correctly, not null
        set_flashdata('');
        $output = flashdata();
        $this->assert_true($results, $k,
            $output !== null && str_contains($output, '<p style="color: green;">'),
            'Empty string message: still wrapped (not null)',
            $output
        );

        // Message with special HTML chars — returned raw (no XSS escaping by design)
        set_flashdata('<b>Bold</b>');
        $output = flashdata();
        $this->assert_true($results, $k,
            str_contains($output, '<b>Bold</b>'),
            'HTML in message passed through raw (no escaping)',
            $output
        );

        // FLASHDATA_OPEN / FLASHDATA_CLOSE constant fallback
        // If constants are defined, they should be used when no args given.
        // We test this by checking the current output matches one of the valid
        // wrapping sources (constant or default green p) when no args provided.
        set_flashdata('Constant check');
        $output = flashdata();
        if (defined('FLASHDATA_OPEN') && defined('FLASHDATA_CLOSE')) {
            $this->assert_true($results, $k,
                str_contains($output, FLASHDATA_OPEN) && str_contains($output, FLASHDATA_CLOSE),
                'FLASHDATA_OPEN/CLOSE constants used when no args provided',
                $output
            );
        } else {
            $this->assert_true($results, $k,
                str_contains($output, '<p style="color: green;">'),
                'No constants defined: default green <p> used as fallback',
                $output
            );
        }

        // Function args override constants even when constants are defined
        set_flashdata('Override');
        $output = flashdata('<em>', '</em>');
        $this->assert_eq($results, $k, $output, '<em>Override</em>', 'Function args override FLASHDATA_OPEN/CLOSE constants');

        // Ensure session is clean after all tests
        unset($_SESSION['flashdata']);
    }
}
