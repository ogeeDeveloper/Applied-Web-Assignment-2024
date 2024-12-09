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
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to AgriKonnect!</h1>
        </div>

        <div class="content">
            <p>Hi <?= htmlspecialchars($customer['name']) ?>,</p>

            <p>Thank you for joining AgriKonnect! We're excited to have you as part of our community.</p>

            <p>With your new account, you can:</p>
            <ul>
                <li>Browse fresh, local produce</li>
                <li>Connect directly with farmers</li>
                <li>Track your orders</li>
                <li>Save your favorite products</li>
            </ul>

            <p>Get started by browsing our selection of fresh produce:</p>
            <p><a href="<?= $_ENV['APP_URL'] ?>/shop" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Start Shopping</a></p>

            <p>If you have any questions, our support team is here to help!</p>
        </div>

        <div class="footer">
            <p>&copy; <?= date('Y') ?> AgriKonnect. All rights reserved.</p>
        </div>
    </div>
</body>

</html>