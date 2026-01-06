<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php'; // Path to composer autoload
require_once __DIR__ . '/../config/mail.php';

class MailService {
    
    public function sendOtp($toEmail, $otp) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;

            // Recipients
            $mail->setFrom(FROM_EMAIL, FROM_NAME);
            $mail->addAddress($toEmail);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your CineSync Registration OTP';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd;'>
                    <h2>Welcome to CineSync!</h2>
                    <p>Your One-Time Password (OTP) for registration is:</p>
                    <h1 style='color: #E50914; letter-spacing: 5px;'>$otp</h1>
                    <p>This code is valid for 10 minutes.</p>
                    <p>If you did not request this, please ignore this email.</p>
                </div>
            ";
            $mail->AltBody = "Your CineSync OTP is: $otp"; // Plain text version

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
