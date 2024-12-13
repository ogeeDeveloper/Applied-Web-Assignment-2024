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
            background: #28a745;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 20px;
            background: #fff;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
        }

        .status-pending {
            background: #ffc107;
            color: #000;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to AgriKonnect!</h1>
        </div>

        <div class="content">
            <p>Hi <?= htmlspecialchars($farmer['name']) ?>,</p>

            <p>Thank you for registering as a farmer on AgriKonnect! Your account is currently under review.</p>

            <p>Account Status: <span class="status-pending">Pending Review</span></p>

            <p>What happens next?</p>
            <ul>
                <li>Our team will review your application</li>
                <li>You'll receive an email when your account is approved</li>
                <li>Once approved, you can start listing your products</li>
            </ul>

            <p>Farm Details:</p>
            <ul>
                <li>Farm Name: <?= htmlspecialchars($farmer['farm_name']) ?></li>
                <li>Location: <?= htmlspecialchars($farmer['location']) ?></li>
                <li>Farm Type: <?= htmlspecialchars($farmer['farm_type']) ?></li>
            </ul>

            <p>If you have any questions during the review process, please contact our support team.</p>
        </div>

        <div class="footer">
            <p>&copy; <?= date('Y') ?> AgriKonnect. All rights reserved.</p>
        </div>
    </div>
</body>

</html>