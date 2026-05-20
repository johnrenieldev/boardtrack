<?php
/**
 * BoardTrack — Mailer Helper
 * app/helpers/Mailer.php
 *
 * Wraps PHPMailer for simple one-call email sending.
 * All MAIL_* constants come from config/config.php (loaded by index.php).
 * Do NOT require config/mail.php here — constants are already defined.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer autoloader so PHPMailer namespace resolves
require_once ROOT_PATH . '/vendor/autoload.php';

class Mailer
{
    /**
     * Send an HTML email via Gmail SMTP.
     *
     * @param string $toEmail   Recipient email address
     * @param string $toName    Recipient display name
     * @param string $subject   Email subject
     * @param string $body      HTML email body
     * @return bool             true on success, false on failure
     */
    public static function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        $mail = new PHPMailer(true);

        try {
// Server settings
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;                      // smtp.gmail.com
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;                  // Gmail address
            $mail->Password   = MAIL_PASSWORD;                  // App password (no spaces)
            $mail->SMTPSecure = (MAIL_PORT == 587) ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = MAIL_PORT;
// Local development SSL bypass
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                ]
            ];
// Sender & recipient
            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress($toEmail, $toName);
// Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log('[BoardTrack Mailer] Send failed to ' . $toEmail . ': ' . $e->getMessage());
            return false;
        }
    }
}