<?php
/**
 * BoardTrack mail facade.
 *
 * Provides feature-level mail methods and delegates transport to Mailer.
 */

require_once ROOT_PATH . '/app/helpers/EmailTemplates.php';
require_once ROOT_PATH . '/app/helpers/Mailer.php';

class BoardTrackMail
{
    public static function isEnabled(): bool
    {
        return defined('MAIL_ENABLED') && MAIL_ENABLED;
    }

    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        if (!self::isEnabled() || empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return Mailer::send($toEmail, $toName, $subject, $htmlBody);
    }

    public static function registrationReceived(string $email, string $name): bool
    {
        return self::send($email, $name, 'Registration Received | ' . APP_NAME, EmailTemplates::registrationReceived($name));
    }

    public static function complaintSubmittedToLandlord(
        string $landlordEmail,
        string $landlordName,
        string $tenantName,
        string $tenantEmail,
        string $category,
        string $complaintTitle,
        string $complaintLink
    ): bool {
        return self::send(
            $landlordEmail,
            $landlordName,
            'New Complaint Submitted | ' . APP_NAME,
            EmailTemplates::complaintSubmittedToLandlord($tenantName, $tenantEmail, $category, $complaintTitle, $complaintLink)
        );
    }

    public static function paymentSubmittedToLandlord(
        string $landlordEmail,
        string $landlordName,
        string $tenantName,
        string $tenantEmail,
        string $billName,
        float $amount,
        string $paymentMethod,
        string $paymentLink
    ): bool {
        return self::send(
            $landlordEmail,
            $landlordName,
            'New Payment Submitted | ' . APP_NAME,
            EmailTemplates::paymentSubmittedToLandlord($tenantName, $tenantEmail, $billName, $amount, $paymentMethod, $paymentLink)
        );
    }

    public static function tenantProfileUpdatedToLandlord(
        string $landlordEmail,
        string $landlordName,
        string $tenantName,
        string $tenantEmail,
        string $roomPref,
        string $guardianEmail,
        string $guardianPurpose
    ): bool {
        return self::send(
            $landlordEmail,
            $landlordName,
            'Tenant Profile Updated | ' . APP_NAME,
            EmailTemplates::tenantProfileUpdatedToLandlord($tenantName, $tenantEmail, $roomPref, $guardianEmail, $guardianPurpose)
        );
    }


    public static function contactUs(
        string $supportEmail,
        string $supportName,
        string $name,
        string $email,
        string $subjectLabel,
        string $message
    ): bool {
        return self::send(
            $supportEmail,
            $supportName,
            '[BoardTrack Contact] ' . $subjectLabel,
            EmailTemplates::contactUs($name, $email, $subjectLabel, $message)
        );
    }

    public static function verificationEmail(string $email, string $name, string $token): bool
    {
        $link = BASE_URL . '/index.php?url=auth/verify/' . $token;

        return self::send($email, $name, 'Verify Your Email | ' . APP_NAME, EmailTemplates::verificationEmail($name, $link));
    }

    public static function passwordReset(string $email, string $name, string $token): bool
    {
        $link = BASE_URL . '/index.php?url=auth/resetPassword/' . $token;
        return self::send($email, $name, 'Password Reset Request | ' . APP_NAME, EmailTemplates::passwordReset($name, $link));
    }

    public static function guardianRegistrationNotice(string $guardianEmail, string $guardianName, string $tenantName, string $purpose): bool
    {
        return self::send(
            $guardianEmail,
            $guardianName,
            'You Were Listed as Emergency Contact | ' . APP_NAME,
            EmailTemplates::guardianRegistrationNotice($tenantName, $guardianName, $purpose)
        );
    }

    public static function tenantApproved(string $email, string $name, string $statusMessage): bool
    {
        return self::send($email, $name, 'Your Application Has Been Approved | ' . APP_NAME, EmailTemplates::tenantApproved($name, $statusMessage));
    }

    public static function guardianTenantApproved(string $guardianEmail, string $guardianName, string $tenantName, string $purpose): bool
    {
        return self::send($guardianEmail, $guardianName, 'Tenant Approval Notice | ' . APP_NAME, EmailTemplates::guardianTenantApproved($tenantName, $purpose));
    }

    public static function tenantPaymentApproved(string $email, string $name, string $billName, float $amount, string $methodLabel = ''): bool
    {
        return self::send($email, $name, 'Payment Confirmed | ' . APP_NAME, EmailTemplates::tenantPaymentApproved($name, $billName, $amount, $methodLabel));
    }

    public static function tenantPaymentRejected(string $email, string $name, string $billName, float $amount, string $reason): bool
    {
        return self::send($email, $name, 'Payment Rejected | ' . APP_NAME, EmailTemplates::tenantPaymentRejected($name, $billName, $amount, $reason));
    }

    public static function guardianPaymentApproved(string $guardianEmail, string $guardianName, string $tenantName, string $billName, float $amount, string $purpose): bool
    {
        return self::send(
            $guardianEmail,
            $guardianName,
            'Payment Confirmed for ' . $tenantName . ' | ' . APP_NAME,
            EmailTemplates::guardianPaymentApproved($tenantName, $billName, $amount, $purpose)
        );
    }

    public static function guardianPaymentRejected(
        string $guardianEmail,
        string $guardianName,
        string $tenantName,
        string $billName,
        float $amount,
        string $reason,
        string $purpose
    ): bool {
        return self::send(
            $guardianEmail,
            $guardianName,
            'Payment Rejected for ' . $tenantName . ' | ' . APP_NAME,
            EmailTemplates::guardianPaymentRejected($tenantName, $billName, $amount, $reason, $purpose)
        );
    }

    public static function paymentMethodLabel(?string $method): string
    {
        return match ($method ?? '') {
            'gcash'          => 'GCash',
            'cash'           => 'Cash (in person)',
            'bank_transfer'  => 'Bank transfer',
            default          => $method ? ucfirst(str_replace('_', ' ', $method)) : '',
        };
    }

    public static function paymentReminder(
        string $email,
        string $name,
        string $billName,
        float $amount,
        string $dueDate,
        int $daysUntilDue,
        int $reminderLevel = 1
    ): bool {
        return self::send(
            $email,
            $name,
            'Payment Reminder | ' . APP_NAME,
            EmailTemplates::paymentReminder($name, $billName, $amount, $dueDate, $daysUntilDue, $reminderLevel)
        );
    }

    public static function paymentOverdue(string $email, string $name, string $billName, float $amount, string $dueDate, int $daysOverdue): bool
    {
        return self::send($email, $name, 'Payment Overdue Notice | ' . APP_NAME, EmailTemplates::paymentOverdue($name, $billName, $amount, $dueDate, $daysOverdue));
    }
}
