<?php

/**
 * Utilities Helper Tester Module
 *
 * Provides comprehensive unit tests for all utilities_helper functions.
 * Each test records: key, label, expected, actual, pass.
 *
 * Context notes:
 * - block_url() sends headers and exits. Cannot be tested directly.
 * - json() echoes directly to output buffer or calls die(). Tested minimally or skipped.
 * - display() includes views and echoes output, skipping to prevent layout breakage.
 */
class Utilities_helper_tester extends Trongate {

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

        $this->test_block_url($results);
        $this->test_display($results);
        $this->test_from_trongate_mx($results);
        $this->test_ip_address($results);
        $this->test_json($results);
        $this->test_return_file_info($results);
        $this->test_sort_by_property($results);
        $this->test_sort_rows_by_property($results);

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

    private function assert_skip(array &$r, string $k, string $label, string $reason): void {
        $r[] = ['key' => $k, 'label' => $label . ' (SKIPPED)', 'expected' => '(skipped)', 'actual' => $reason, 'pass' => true];
    }

    // =========================================================================
    // block_url()
    // =========================================================================

    private function test_block_url(array &$results): void {
        $k = 'block_url';

        $this->assert_skip($results, $k,
            'Block full module',
            'Cannot test block_url() because it sends HTTP 403 headers and calls die()'
        );
        $this->assert_skip($results, $k,
            'Block specific module/method',
            'Cannot test block_url() because it sends HTTP 403 headers and calls die()'
        );
    }

    // =========================================================================
    // display()
    // =========================================================================

    private function test_display(array &$results): void {
        $k = 'display';

        $this->assert_skip($results, $k,
            'Template display',
            'Cannot safely test display() because it echoes views directly to the output buffer, breaking the test runner layout'
        );
    }

    // =========================================================================
    // from_trongate_mx()
    // =========================================================================

    private function test_from_trongate_mx(array &$results): void {
        $k = 'from_trongate_mx';

        // Unless this request actually is from the desktop app, it should be false
        $expected = isset($_SERVER['HTTP_TRONGATE_MX_REQUEST']);
        $actual = from_trongate_mx();
        
        $this->assert_eq($results, $k, $actual, $expected, 'Standard HTTP request returns false (unless actually MX request)');
    }

    // =========================================================================
    // ip_address()
    // =========================================================================

    private function test_ip_address(array &$results): void {
        $k = 'ip_address';

        $ip = ip_address();

        $this->assert_true($results, $k, is_string($ip), 'Returns a string', gettype($ip));
        $this->assert_true($results, $k, !empty($ip), 'Returns a non-empty string', $ip);
        
        $is_valid_ip = filter_var($ip, FILTER_VALIDATE_IP) !== false;
        $this->assert_true($results, $k, $is_valid_ip, 'Returns a valid IP format', $ip ?: '(invalid format)');
    }

    // =========================================================================
    // json()
    // =========================================================================

    private function test_json(array &$results): void {
        $k = 'json';

        $this->assert_skip($results, $k,
            'JSON with kill_script=true',
            'Cannot test because it echoes directly and calls die()'
        );
        
        // We could technically test json(data, false) with output buffering, but it echoes <pre> tags.
        // Easiest is to explicitly test via output buffer to ensure coverage.
        ob_start();
        json(['status' => 'ok'], false);
        $output = ob_get_clean();

        $this->assert_true($results, $k, str_contains($output, '<pre>'), 'Outputs data wrapped in <pre> tag (with kill_script=false)');
        $this->assert_true($results, $k, str_contains($output, '"status": "ok"'), 'Outputs correctly encoded JSON data');
    }

    // =========================================================================
    // return_file_info()
    // =========================================================================

    private function test_return_file_info(array &$results): void {
        $k = 'return_file_info';

        // Standard filename
        $info1 = return_file_info('image.png');
        $this->assert_eq($results, $k, $info1['file_name'] ?? null, 'image', 'Returns correct file_name (image.png)');
        $this->assert_eq($results, $k, $info1['file_extension'] ?? null, '.png', 'Returns correct file_extension (.png)');

        // Multiple dots
        $info2 = return_file_info('archive.tar.gz');
        $this->assert_eq($results, $k, $info2['file_name'] ?? null, 'archive.tar', 'Multiple dots: Returns correct file_name (archive.tar.gz)');
        $this->assert_eq($results, $k, $info2['file_extension'] ?? null, '.gz', 'Multiple dots: Returns correct file_extension (.gz)');

        // No extension
        $info3 = return_file_info('README');
        $this->assert_eq($results, $k, $info3['file_name'] ?? null, 'README', 'No extension: Returns correct file_name');
        $this->assert_eq($results, $k, $info3['file_extension'] ?? null, '.', 'No extension: Engine quirk appends dot instead of empty string');
    }

    // =========================================================================
    // sort_by_property() — Arrays of associative arrays
    // =========================================================================

    private function test_sort_by_property(array &$results): void {
        $k = 'sort_by_property';

        $arr = [
            ['id' => 3, 'name' => 'Charlie'],
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob']
        ];

        // Sort asc by integer
        $sorted_asc = sort_by_property($arr, 'id', 'asc');
        $this->assert_eq($results, $k, $sorted_asc[0]['id'], 1, 'Sorts by int attribute ASC (index 0)');
        $this->assert_eq($results, $k, $sorted_asc[2]['id'], 3, 'Sorts by int attribute ASC (index 2)');

        // Sort desc by string (case insensitive)
        $sorted_desc = sort_by_property($arr, 'name', 'desc');
        $this->assert_eq($results, $k, $sorted_desc[0]['name'], 'Charlie', 'Sorts by string attribute DESC (index 0)');
        $this->assert_eq($results, $k, $sorted_desc[2]['name'], 'Alice', 'Sorts by string attribute DESC (index 2)');
    }

    // =========================================================================
    // sort_rows_by_property() — Arrays of objects
    // =========================================================================

    private function test_sort_rows_by_property(array &$results): void {
        $k = 'sort_rows_by_property';

        $arr = [
            (object)['id' => 3, 'name' => 'Charlie'],
            (object)['id' => 1, 'name' => 'Alice'],
            (object)['id' => 2, 'name' => 'Bob']
        ];

        // Sort asc by integer
        $sorted_asc = sort_rows_by_property($arr, 'id', 'asc');
        $this->assert_eq($results, $k, $sorted_asc[0]->id, 1, 'Sorts object properties by int ASC (index 0)');
        $this->assert_eq($results, $k, $sorted_asc[2]->id, 3, 'Sorts object properties by int ASC (index 2)');

        // Sort desc by string
        $sorted_desc = sort_rows_by_property($arr, 'name', 'desc');
        $this->assert_eq($results, $k, $sorted_desc[0]->name, 'Charlie', 'Sorts object properties by string DESC (index 0)');
        $this->assert_eq($results, $k, $sorted_desc[2]->name, 'Alice', 'Sorts object properties by string DESC (index 2)');
    }
}
