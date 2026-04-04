# Flashdata Helper Tester Module

This module provides comprehensive unit tests for the `flashdata_helper` functions in the Trongate framework.

## Usage

1. Navigate to `/flashdata_helper_tester` in your browser.
2. Results are grouped by helper, showing test description, expected value, actual value, and PASS/FAIL per assertion.

## Tests Performed

### flashdata()
- No message in session: returns `null`
- Default wrapping: green `<p style="color: green;">` tag used when no args provided
- Message consumed on retrieval: second immediate call returns `null`
- Custom opening and closing HTML: message wrapped in provided tags exactly
- Custom opening only (`null` closing): message starts with provided opening tag
- Empty string message: still wrapped, not `null`
- HTML in message passed through raw (no XSS escaping on retrieval)
- `FLASHDATA_OPEN`/`FLASHDATA_CLOSE` constants used when defined and no args given
- Function args override constants even when constants are defined

### set_flashdata()
- Standard message stored correctly in `$_SESSION['flashdata']`
- Subsequent call overwrites the previous message
- Empty string stored as-is (not filtered)
- HTML in message stored raw (not escaped at storage time)

## Notes

- Tests run with session isolation: `$_SESSION['flashdata']` is cleared before the first test and after the last.
- The session-based consume-on-read contract is verified explicitly.
- `FLASHDATA_OPEN`/`FLASHDATA_CLOSE` constant tests adapt to whether the constants are defined in the current environment.

## Documentation Mismatch Noted

The official docs declare `flashdata()` return type as `void`, but the actual implementation returns `string|null`. The implementation is correct; the docs are inaccurate.

## Dependencies

- Requires the `flashdata` module to be available.
- Assumes `flashdata_helper.php` is loaded (auto-loaded in Trongate).
- Requires an active PHP session (`session_start()` must have been called).
