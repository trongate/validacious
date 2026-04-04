<!DOCTYPE html>
<html lang="en">

<head>
  <base href="<?= BASE_URL ?>">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Flashdata Helper Tester - Test Results</title>
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
    .test-label { flex: 2; color: #333; }
    .test-expected, .test-actual { flex: 2; font-family: monospace; font-size: 0.88em; word-break: break-all; }
    .test-expected { color: #555; }
    .test-actual.pass { color: #155724; }
    .test-actual.fail { color: #721c24; }
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
      <div class="card-heading">Flashdata Helper Unit Tests</div>
      <div class="card-body">
        <p>Comprehensive tests for all <code>flashdata_helper</code> functions.</p>

        <div class="col-header">
          <span>Test Description</span>
          <span>Expected</span>
          <span>Actual</span>
          <span>Result</span>
        </div>

        <?php
        // Group results by helper key, preserving docs order: flashdata first, set_flashdata second
        $grouped = [];
        foreach ($test_results as $row) {
          $grouped[$row['key']][] = $row;
        }

        $docs_order = ['flashdata', 'set_flashdata'];
        uksort($grouped, function ($a, $b) use ($docs_order) {
          $ai = array_search($a, $docs_order);
          $bi = array_search($b, $docs_order);
          $ai = $ai === false ? PHP_INT_MAX : $ai;
          $bi = $bi === false ? PHP_INT_MAX : $bi;
          return $ai <=> $bi;
        });

        foreach ($grouped as $key => $rows):
          $group_pass = array_reduce($rows, fn($carry, $r) => $carry && $r['pass'], true);
        ?>
          <div class="test-group">
            <div class="test-group-heading" style="border-left-color: <?= $group_pass ? '#28a745' : '#dc3545' ?>">
              <?= out($key) ?>()
            </div>
            <?php foreach ($rows as $row):
              $cls = $row['pass'] ? 'pass' : 'fail';
              $expected_str = is_bool($row['expected']) ? ($row['expected'] ? 'true' : 'false') : (string)$row['expected'];
              $actual_str   = is_bool($row['actual'])   ? ($row['actual']   ? 'true' : 'false') : (string)$row['actual'];
              if (strlen($expected_str) > 120) $expected_str = substr($expected_str, 0, 117) . '…';
              if (strlen($actual_str)   > 120) $actual_str   = substr($actual_str,   0, 117) . '…';
            ?>
              <div class="test-row <?= $cls ?>">
                <span class="test-label"><?= out($row['label']) ?></span>
                <span class="test-expected"><?= out($expected_str) ?></span>
                <span class="test-actual <?= $cls ?>"><?= out($actual_str) ?></span>
                <span class="badge <?= $cls ?>"><?= $row['pass'] ? '✓ PASS' : '✗ FAIL' ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>

        <?php
        $total  = count($test_results);
        $passed = count(array_filter($test_results, fn($r) => $r['pass']));
        $failed = $total - $passed;
        $all_ok = $failed === 0;
        ?>
        <div style="display:flex; align-items:center; gap:1em; margin-top:1.5em;">
          <div class="summary-bar <?= $all_ok ? 'all-pass' : 'has-fail' ?>">
            <?= $passed ?> / <?= $total ?> tests passed
            <?= !$all_ok ? " — {$failed} failing" : ' — All tests passing ✓' ?>
          </div>
          <?= anchor('helper_testers', '← Back to Helper Testers', ['class' => 'button alt']) ?>
        </div>
      </div>
    </div>
  </div>
</body>

</html>