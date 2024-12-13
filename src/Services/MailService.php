<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class MailService
{
    private $mailer;
    private $logger;
    private $templatePath;

    public function __construct($logger)
    {
        $this->logger = $logger;
        $this->templatePath = dirname(__DIR__) . '/Views/emails/';

        $this->mailer = new PHPMailer(true);

        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['MAIL_USERNAME'] ?? '';
        $this->mailer->Password = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $_ENV['MAIL_PORT'] ?? 2525;

        // Default settings
        $this->mailer->isHTML(true);
        $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@agrikonnect.com', $_ENV['MAIL_FROM_NAME'] ?? 'AgriKonnect');
    }

    /**
     * Send an email using a template
     */
    public function sendTemplateEmail(string $to, string $subject, string $template, array $data = []): bool
    {
        try {
            $this->logger->info("Preparing to send template email", [
                'to' => $to,
                'subject' => $subject,
                'template' => $template
            ]);

            // Load template
            $templateFile = $this->templatePath . $template . '.php';
            if (!file_exists($templateFile)) {
                throw new Exception("Email template not found: $template");
            }

            // Extract data for template
            extract($data);

            // Capture template output
            ob_start();
            include $templateFile;
            $body = ob_get_clean();

            // Setup email
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            // Send email
            $result = $this->mailer->send();

            $this->logger->info("Email sent successfully", [
                'to' => $to,
                'subject' => $subject,
                'template' => $template
            ]);

            return $result;
        } catch (Exception $e) {
            $this->logger->error("Failed to send email", [
                'error' => $e->getMessage(),
                'to' => $to,
                'subject' => $subject,
                'template' => $template
            ]);
            return false;
        }
    }

    /**
     * Send customer welcome email
     */
    public function sendCustomerWelcomeEmail(array $customer): bool
    {
        return $this->sendTemplateEmail(
            $customer['email'],
            'Welcome to AgriKonnect',
            'customer-welcome',
            ['customer' => $customer]
        );
    }

    /**
     * Send farmer welcome email
     */
    public function sendFarmerWelcomeEmail(array $farmer): bool
    {
        return $this->sendTemplateEmail(
            $farmer['email'],
            'Welcome to AgriKonnect - Farmer Registration',
            'farmer-welcome',
            ['farmer' => $farmer]
        );
    }

    /**
     * Send farmer approval notification
     */
    public function sendFarmerApprovalEmail(array $farmer): bool
    {
        return $this->sendTemplateEmail(
            $farmer['email'],
            'Your AgriKonnect Farmer Account Has Been Approved',
            'farmer-approval',
            ['farmer' => $farmer]
        );
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(string $email, string $token): bool
    {
        $resetLink = $_ENV['APP_URL'] . '/reset-password?token=' . $token;

        return $this->sendTemplateEmail(
            $email,
            'Reset Your AgriKonnect Password',
            'password-reset',
            ['resetLink' => $resetLink]
        );
    }

    /**
     * Send order confirmation
     */
    public function sendOrderConfirmation(array $order): bool
    {
        return $this->sendTemplateEmail(
            $order['customer_email'],
            'Order Confirmation - AgriKonnect',
            'order-confirmation',
            ['order' => $order]
        );
    }
}
