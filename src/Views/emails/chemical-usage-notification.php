<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #f44336;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 20px;
            background: #fff;
        }

        .warning {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Chemical Application Record</h1>
        </div>

        <div class="content">
            <p>Hello <?= htmlspecialchars($data['farmer_name']) ?>,</p>

            <p>A chemical application has been recorded for your farm:</p>

            <div class="details">
                <h3>Application Details:</h3>
                <ul>
                    <li><strong>Farm:</strong> <?= htmlspecialchars($data['farm_name']) ?></li>
                    <li><strong>Crop:</strong> <?= htmlspecialchars($data['crop_name']) ?></li>
                    <li><strong>Location:</strong> <?= htmlspecialchars($data['field_location']) ?></li>
                    <li><strong>Chemical:</strong> <?= htmlspecialchars($data['chemical_name']) ?></li>
                    <li><strong>Type:</strong> <?= htmlspecialchars($data['chemical_type']) ?></li>
                    <li><strong>Amount Used:</strong> <?= htmlspecialchars($data['amount_used']) ?> <?= htmlspecialchars($data['unit_of_measurement']) ?></li>
                    <li><strong>Date Applied:</strong> <?= htmlspecialchars($data['date_applied']) ?></li>
                </ul>
            </div>

            <div class="warning">
                <h3>⚠️ Safety Information</h3>
                <p>Safety Period: <?= htmlspecialchars($data['safety_period_days']) ?> days</p>
                <p>Safe to Harvest After: <?= htmlspecialchars($data['safe_harvest_date']) ?></p>
                <p>Please ensure no harvesting occurs before the safety period has elapsed.</p>
            </div>

            <p>If you did not make this record, please contact our support team immediately.</p>

            <p>Best regards,<br>The AgriKonnect Team</p>
        </div>
    </div>
</body>

</html>