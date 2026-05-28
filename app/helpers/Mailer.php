<?php
/**
 * SMTP mail transport wrapper using PHPMailer.
 *
 * Reads MAIL_* constants from application configuration.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer autoloader for PHPMailer classes.
require_once ROOT_PATH . '/vendor/autoload.php';

class Mailer
{
    /**
     * Send HTML email through configured SMTP server.
     */
    public static function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Guard against missing config when MAIL_ENABLED is true.
            $required = ['MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM', 'MAIL_FROM_NAME'];
            foreach ($required as $c) {
                if (!defined($c)) {
                    throw new Exception("Missing mail config constant: {$c}");
                }
            }

            if (empty(MAIL_HOST) || empty(MAIL_PORT) || (string) MAIL_PORT === '0') {
                throw new Exception('Invalid SMTP host/port configuration');
            }

            // SMTP transport settings.
            $mail->isSMTP();

            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = (MAIL_PORT == 587) ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = MAIL_PORT;


            // PHPMailer debug output only in development to avoid polluting production responses/logs.
            if (defined('APP_ENV') && APP_ENV === 'development') {
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = static function ($str, $level) {
                    error_log('[BoardTrack Mailer][SMTPDebug][L' . $level . '] ' . trim($str));
                };
            }

            // Apply strict TLS in production; relaxed checks only for local development.
            if (defined('APP_ENV') && APP_ENV === 'development') {
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true
                    ]
                ];
            } else {
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => true,
                        'verify_peer_name'  => true,
                        'allow_self_signed' => false
                    ]
                ];
            }

            // Envelope and recipients.
            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress($toEmail, $toName);

            // Message content.
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log(
                '[BoardTrack Mailer] Send failed' .
                ' to=' . $toEmail .
                ' subject=' . $subject .
                ' host=' . (defined('MAIL_HOST') ? MAIL_HOST : '-') .
                ' port=' . (defined('MAIL_PORT') ? MAIL_PORT : '-') .
                ' err=' . $e->getMessage()
            );
            return false;
        }
    }
}

