<?php
?>
<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 20px;
        }

        .detail-box {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Profile Update Notification</h1>
        </div>
        <div class="content">
            <p>Hello <?= htmlspecialchars($data['farmer_name']) ?>,</p>

            <p>Your farmer profile has been updated with the following changes:</p>

            <div class="detail-box">
                <?php foreach ($data['changes'] as $field => $values): ?>
                    <p>
                        <strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $field))) ?>:</strong><br>
                        From: <?= htmlspecialchars($values['old']) ?><br>
                        To: <?= htmlspecialchars($values['new']) ?>
                    </p>
                <?php endforeach; ?>
            </div>

            <p style="color: #d32f2f; font-weight: bold;">If you did not make these changes, please contact our support team immediately!</p>

            <p>Best regards,<br>The AgriKonnect Team</p>
        </div>
    </div>
</body>

</html>