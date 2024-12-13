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
            <h1>Harvest Recorded Successfully</h1>
        </div>
        <div class="content">
            <p>Hello <?= htmlspecialchars($data['farmer_name']) ?>,</p>

            <p>A harvest has been recorded for your crop:</p>

            <div class="detail-box">
                <p><strong>Harvest Details:</strong></p>
                <ul>
                    <li>Crop: <?= htmlspecialchars($data['crop_name']) ?></li>
                    <li>Quantity: <?= htmlspecialchars($data['quantity']) ?> <?= htmlspecialchars($data['unit']) ?></li>
                    <li>Quality Grade: <?= htmlspecialchars($data['quality_grade']) ?></li>
                    <li>Storage Location: <?= htmlspecialchars($data['storage_location']) ?></li>
                    <li>Harvest Date: <?= htmlspecialchars($data['harvest_date']) ?></li>
                </ul>
            </div>

            <?php if ($data['create_product']): ?>
                <p><strong>A product listing has been created automatically from this harvest.</strong></p>
            <?php endif; ?>

            <p>If you did not record this harvest, please contact our support team immediately.</p>

            <p>Best regards,<br>The AgriKonnect Team</p>
        </div>
    </div>
</body>

</html>