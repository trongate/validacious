<!DOCTYPE html>
<html lang="en">

<head>
  <base href="<?= BASE_URL ?>">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Helper Testers Overview</title>
  <link rel="stylesheet" href="css/trongate.css">
</head>

<body>
  <div class="container">
    <div class="text-center">
      <h1>Helper Testers Overview</h1>
      <p>Comprehensive testing suite for all Trongate helper functions</p>
    </div>

    <?php foreach ($testers as $tester): ?>
      <div class="card">
        <div class="card-heading">
          <?= $tester['name'] ?>
        </div>
        <div class="card-body">
          <p><?= $tester['description'] ?></p>
          <?= anchor($tester['url'], 'Run Tests', ['class' => 'button']); ?>
          <?= anchor($tester['readme'], 'View README', ['class' => 'button alt']); ?>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="card">
      <div class="card-heading">
        Suite Overview & Statistics
      </div>
      <div class="card-body">
        <p>This testing suite runs rigorous per-attribute assertions across all core helper functions to map regressions against the defined framework behaviour.</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
          <thead>
            <tr style="background: #f8f9fa; text-align: left; border-bottom: 2px solid #ddd;">
              <th style="padding: 8px;">Target Module</th>
              <th style="padding: 8px; text-align: center;">Active Asserts</th>
              <th style="padding: 8px; text-align: center;">Skipped</th>
              <th style="padding: 8px; text-align: center;">Total Validations</th>
            </tr>
          </thead>
          <tbody>
            <tr style="border-bottom: 1px solid #eee;">
              <td style="padding: 6px;">String Helper</td>
              <td style="padding: 6px; text-align: center;">93</td>
              <td style="padding: 6px; text-align: center;">0</td>
              <td style="padding: 6px; text-align: center; font-weight: bold;">93</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
              <td style="padding: 6px;">Flashdata Helper</td>
              <td style="padding: 6px; text-align: center;">13</td>
              <td style="padding: 6px; text-align: center;">0</td>
              <td style="padding: 6px; text-align: center; font-weight: bold;">13</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
              <td style="padding: 6px;">Form Helper</td>
              <td style="padding: 6px; text-align: center;">113</td>
              <td style="padding: 6px; text-align: center;">5</td>
              <td style="padding: 6px; text-align: center; font-weight: bold;">118</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
              <td style="padding: 6px;">URL Helper</td>
              <td style="padding: 6px; text-align: center;">33</td>
              <td style="padding: 6px; text-align: center;">3</td>
              <td style="padding: 6px; text-align: center; font-weight: bold;">36</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
              <td style="padding: 6px;">Utilities Helper</td>
              <td style="padding: 6px; text-align: center;">20</td>
              <td style="padding: 6px; text-align: center;">4</td>
              <td style="padding: 6px; text-align: center; font-weight: bold;">24</td>
            </tr>
            <tr style="background: #e9ecef; font-weight: bold;">
              <td style="padding: 8px;">Total Operations</td>
              <td style="padding: 8px; text-align: center; color: #28a745;">272</td>
              <td style="padding: 8px; text-align: center; color: #6c757d;">12</td>
              <td style="padding: 8px; text-align: center;">284</td>
            </tr>
          </tbody>
        </table>

        <div style="margin-top: 15px; font-size: 0.9em; color: #555;">
          <strong>Total Test Modules: <?= count($testers); ?></strong>
        </div>
      </div>
    </div>
</body>

</html>