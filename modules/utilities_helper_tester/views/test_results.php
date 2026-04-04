<!DOCTYPE html>
<html lang="en">

<head>
  <base href="<?= BASE_URL ?>">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Utilities Helper Tester - Test Results</title>
  <link rel="stylesheet" href="css/trongate.css">
  <style>
    .test-group { margin: 1.5em 0; }
    .test-group-heading {
      font-weight: bold;
      font-size: 1.05em;
      padding: 0.5em 0.75em;
      background: #f0f0f0;
      border-left: 4px solid #888;
      margin-bottom: 0.25em;
    }
    .test-row {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      padding: 0.35em 0.75em;
      border-bottom: 1px solid #eee;
      font-size: 0.92em;
      gap: 1em;
    }
    .test-row.pass { border-left: 3px solid #28a745; }
    .test-row.fail { border-left: 3px solid #dc3545; background: #fff5f5; }
    .test-row.skip { border-left: 3px solid #6c757d; background: #f8f9fa; }
    .test-label { flex: 2; color: #333; }
    .test-expected, .test-actual { flex: 2; font-family: monospace; font-size: 0.88em; word-break: break-all; }
    .test-expected { color: #555; }
    .test-actual.pass { color: #155724; }
    .test-actual.fail { color: #721c24; }
    .test-actual.skip { color: #6c757d; font-style: italic; }
    .badge {
      flex: 0 0 5em;
      text-align: center;
      font-size: 0.82em;
      font-weight: bold;
      padding: 0.15em 0.5em;
      border-radius: 3px;
    }
    .badge.pass { background: #d4edda; color: #155724; }
    .badge.fail { background: #f8d7da; color: #721c24; }
    .badge.skip { background: #e2e3e5; color: #6c757d; }
    .col-header {
      display: flex;
      gap: 1em;
      padding: 0.25em 0.75em;
      font-size: 0.78em;
      color: #555;
      font-weight: 600;
      border-bottom: 2px solid #ccc;
      background: #fff;
      position: sticky;
      top: 0;
      z-index: 10;
    }
    .col-header span:first-child { flex: 2; }
    .col-header span:nth-child(2), .col-header span:nth-child(3) { flex: 2; }
    .col-header span:last-child { flex: 0 0 5em; text-align: center; }
    .summary-bar {
      padding: 0.75em 1em;
      font-weight: bold;
      border-radius: 4px;
      flex: 1;
    }
    .summary-bar.all-pass { background: #d4edda; color: #155724; }
    .summary-bar.has-fail  { background: #f8d7da; color: #721c24; }
  </style>
</head>

<body>
  <div class="container">
    <div class="card">
      <div class="card-heading">Utilities Helper Unit Tests</div>
      <div class="card-body">
        <p>Comprehensive tests for all <code>utilities_helper</code> functions.</p>

        <div class="col-header">
          <span>Test Description</span>
          <span>Expected</span>
          <span>Actual</span>
          <span>Result</span>
        </div>

        <?php
        $grouped = [];
        foreach ($test_results as $row) {
          $grouped[$row['key']][] = $row;
        }

        // Alphabetical order for utility helpers
        $docs_order = [
          'block_url', 'display', 'from_trongate_mx', 'ip_address',
          'json', 'return_file_info', 'sort_by_property', 'sort_rows_by_property',
        ];
        uksort($grouped, function ($a, $b) use ($docs_order) {
          $ai = array_search($a, $docs_order);
          $bi = array_search($b, $docs_order);
          $ai = $ai === false ? PHP_INT_MAX : $ai;
          $bi = $bi === false ? PHP_INT_MAX : $bi;
          return $ai <=> $bi;
        });

        foreach ($grouped as $key => $rows):
          $non_skip = array_filter($rows, fn($r) => !str_ends_with($r['label'], '(SKIPPED)'));
          $group_pass = empty($non_skip) || array_reduce($non_skip, fn($c, $r) => $c && $r['pass'], true);
        ?>
          <div class="test-group">
            <div class="test-group-heading" style="border-left-color: <?= $group_pass ? '#28a745' : '#dc3545' ?>">
              <?= out($key) ?>()
            </div>
            <?php foreach ($rows as $row):
              $is_skip = str_ends_with($row['label'], '(SKIPPED)');
              $cls = $is_skip ? 'skip' : ($row['pass'] ? 'pass' : 'fail');
              $badge_text = $is_skip ? '— SKIP' : ($row['pass'] ? '✓ PASS' : '✗ FAIL');
              $exp = is_bool($row['expected']) ? ($row['expected'] ? 'true' : 'false')
                   : (is_array($row['expected']) ? json_encode($row['expected']) : (string)$row['expected']);
              $act = is_bool($row['actual']) ? ($row['actual'] ? 'true' : 'false')
                   : (is_array($row['actual']) ? json_encode($row['actual']) : (string)$row['actual']);
              if (strlen($exp) > 100) $exp = substr($exp, 0, 97) . '…';
              if (strlen($act) > 100) $act = substr($act, 0, 97) . '…';
            ?>
              <div class="test-row <?= $cls ?>">
                <span class="test-label"><?= out($row['label']) ?></span>
                <span class="test-expected"><?= out($exp) ?></span>
                <span class="test-actual <?= $cls ?>"><?= out($act) ?></span>
                <span class="badge <?= $cls ?>"><?= $badge_text ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>

        <?php
        $non_skip_all = array_filter($test_results, fn($r) => !str_ends_with($r['label'], '(SKIPPED)'));
        $total   = count($non_skip_all);
        $passed  = count(array_filter($non_skip_all, fn($r) => $r['pass']));
        $skipped = count($test_results) - $total;
        $failed  = $total - $passed;
        $all_ok  = $failed === 0;
        ?>
        <div style="display:flex; align-items:center; gap:1em; margin-top:1.5em;">
          <div class="summary-bar <?= $all_ok ? 'all-pass' : 'has-fail' ?>">
            <?= $passed ?> / <?= $total ?> tests passed
            <?= $skipped > 0 ? " — {$skipped} skipped" : '' ?>
            <?= !$all_ok ? " — {$failed} failing" : ' — All tests passing ✓' ?>
          </div>
          <?= anchor('helper_testers', '← Back to Helper Testers', ['class' => 'button alt']) ?>
        </div>
      </div>
    </div>
  </div>
</body>

</html>