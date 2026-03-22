<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>File Upload Tester</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>document_tester_module/css/style.css">
</head>
<body>
    <h1>File Upload Tester</h1>
    
    <?= $this->validation->display_errors() ?>

    <div class="info">
        <p><strong>Allowed:</strong> PDF, ZIP | <strong>Max Size:</strong> 2MB</p>
    </div>

    <?php
    echo form_open_upload('document_tester/submit');
    echo form_label('Select Document:', ['for' => 'userfile']);

    $attr = [
        'id' => 'userfile',
        'accept' => '.pdf,.zip,.txt'
    ];

    echo form_file_select('userfile', $attr);
    echo '<br><br>';
    echo form_submit('submit', 'Upload and Validate');
    echo form_close();
    ?>
</body>
</html>
