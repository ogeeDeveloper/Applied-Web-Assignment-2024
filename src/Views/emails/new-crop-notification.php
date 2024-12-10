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
            <h1>New Crop Added to Your Farm</h1>
        </div>
        <div class="content">
            <p>Hello <?= htmlspecialchars($data['farmer_name']) ?>,</p>

            <p>A new crop has been added to your farm records:</p>

            <div class="detail-box">
                <p><strong>Crop Details:</strong></p>
                <ul>
                    <li>Crop Type: <?= htmlspecialchars($data['crop_name']) ?></li>
                    <li>Location: <?= htmlspecialchars($data['field_location']) ?></li>
                    <li>Planting Date: <?= htmlspecialchars($data['planting_date']) ?></li>
                    <li>Expected Harvest: <?= htmlspecialchars($data['expected_harvest_date']) ?></li>
                    <li>Area: <?= htmlspecialchars($data['area_size']) ?> <?= htmlspecialchars($data['area_unit']) ?></li>
                </ul>
            </div>

            <p>If you did not make this change, please contact our support team immediately.</p>

            <p>Best regards,<br>The AgriKonnect Team</p>
        </div>
    </div>
</body>

</html>