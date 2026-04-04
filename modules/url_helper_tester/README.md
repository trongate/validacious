# URL Helper Tester Module

Comprehensive unit tests for all `url_helper` functions in the Trongate framework.

## Usage

1. Navigate to `/url_helper_tester` in your browser.
2. Results are grouped by helper in official docs order, showing description, expected, actual, and PASS/FAIL/SKIP per assertion.

## Tests Performed

### anchor()
- Contains `<a>` tag, `href` attribute, provided route, link text, extra attributes, closing `</a>`
- Absolute URL (`https://`) used as-is, not prepended with BASE_URL
- Protocol-relative URL (`//`) preserved
- Null text: URL used as link text
- HTML in text: allowed (not escaped) to support embedded tags

### current_url()
- Returns a non-empty string
- Starts with `http://` or `https://`
- Contains `BASE_URL`
- Contains current module path

### get_last_segment()
- Returns a string
- Does not contain slashes

### get_num_segments()
- Returns an integer
- Value is non-negative
- Segment count is `2` for `/url_helper_tester` (due to auto-appended 'index')

### previous_url()
- Returns a string (empty string when no referrer)
- If non-empty: starts with `http://` or `https://`
- **SKIPPED** (conditionally): format check when no `HTTP_REFERER` present

### redirect()
- **SKIPPED** (×2): sends `Location` header and calls `exit()` — cannot be tested in a live page context

### remove_query_string()
- Standard query string stripped
- Multiple params stripped
- URL without query string returned unchanged
- Trailing `?` only: question mark removed
- Fragment + query: query params stripped

### segment()
- `segment(1)` returns `"url_helper_tester"` for this page
- Out-of-range segment returns empty string
- Out-of-range + cast `int`: returns `0`
- Out-of-range + cast `bool`: returns `false`
- Out-of-range + cast `array`: returns an array containing an empty string
- `null` var_type: returns string

## Notes

- `current_url()`, `segment()`, `get_num_segments()`, `get_last_segment()`, and `previous_url()` are request-context-dependent. Tests validate type and structural correctness against the live `/url_helper_tester` URL.
- SKIPPED tests are not counted as failures.
- `redirect()` is entirely untestable from a live page — it finalises the response.

## Dependencies

- Requires the `url` module to be available.
- Assumes `url_helper.php` is loaded (auto-loaded in Trongate).
