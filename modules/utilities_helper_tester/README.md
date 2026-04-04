# Utilities Helper Tester Module

Comprehensive unit tests for all `utilities_helper` functions in the Trongate framework.

## Usage

1. Navigate to `/utilities_helper_tester` in your browser.
2. Results are grouped by helper in alphabetical order, showing description, expected, actual, and PASS/FAIL/SKIP per assertion.

## Tests Performed

### block_url()
- **SKIPPED** (x2): `block_url()` sends `HTTP 403` headers and finalises the request with `die()`. It cannot be safely tested dynamically in a normal test runner execution.

### display()
- **SKIPPED**: `display()` loads view files and echoes them directly to the output buffer without returning content. Testing this live immediately breaks the HTML structure of the test results page.

### from_trongate_mx()
- Validates that standard browser requests accurately return `false`.

### ip_address()
- Validates the return is a valid, non-empty, formatted IP address sequence.

### json()
- **SKIPPED** for `kill_script = true`: Terminates the request unexpectedly.
- Validates encoding layout: Output buffers `json()` with `kill_script = false` to guarantee that output contains standard `<pre>` wrappers and correctly encoded associative mapping.

### return_file_info()
- Validates extraction of filenames with and without extension.
- Validates precise functionality against multiple-extension filenames (e.g., `.tar.gz`).
- Notes quirk behavior: When testing a file *without* an extension (e.g., `README`), the engine correctly identifies the `.file_name` as `README` but assigns `.file_extension` as `.`.

### sort_by_property()
- Validates standard ascending numeric sorts on arrays of associative arrays.
- Validates strictly descending `strcasecmp` alphabetical sorts.

### sort_rows_by_property()
- Validates standard ascending numeric sorts on arrays of objects.
- Validates strictly descending `strcasecmp` alphabetical sorts.
