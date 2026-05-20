<?php
/**
 * BoardTrack — HTML email templates (used with Mailer)
 */
class EmailTemplates
{
    public static function wrap(string $title, string $bodyHtml): string
    {
        $app = htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8');
        $year = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>{$title}</title></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Inter,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:32px 16px;">
    <tr><td align="center">
      <table width="100%" style="max-width:560px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">
        <tr>
          <td style="background:linear-gradient(135deg,#2563eb,#1d4ed8);padding:24px 28px;">
            <div style="font-size:22px;font-weight:700;color:#fff;">{$app}</div>
            <div style="font-size:13px;color:#dbeafe;margin-top:4px;">Boarding House Management</div>
          </td>
        </tr>
        <tr>
          <td style="padding:28px;color:#374151;font-size:15px;line-height:1.6;">
            {$bodyHtml}
          </td>
        </tr>
        <tr>
          <td style="padding:16px 28px 24px;border-top:1px solid #e5e7eb;font-size:12px;color:#9ca3af;">
            This is an automated message from {$app}. Please do not reply directly to this email.
            <br>&copy; {$year} {$app}
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
    }

    public static function registrationReceived(string $tenantName): string
    {
        $name = htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8');
        $login = htmlspecialchars(Router::url('auth/login'), ENT_QUOTES, 'UTF-8');

        return self::wrap(
            'Registration Received',
            "<p>Hi <strong>{$name}</strong>,</p>
             <p>Thank you for registering with <strong>" . APP_NAME . "</strong>.</p>
             <p>Your account has been created and is <strong>pending landlord review</strong>.
             Please complete the personality questionnaire after logging in, then wait for approval.</p>
             <p style=\"margin:24px 0;\">
               <a href=\"{$login}\" style=\"display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;\">Sign in to BoardTrack</a>
             </p>
             <p>We will email you again when your application is approved.</p>"
        );
    }

    public static function guardianRegistrationNotice(string $tenantName, string $guardianName, string $purpose): string
    {
        $tenant = htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8');
        $guardian = htmlspecialchars($guardianName, ENT_QUOTES, 'UTF-8');
        $why = nl2br(htmlspecialchars($purpose, ENT_QUOTES, 'UTF-8'));

        return self::wrap(
            'Emergency Contact Registered',
            "<p>Hello <strong>{$guardian}</strong>,</p>
             <p><strong>{$tenant}</strong> listed you as their emergency guardian/parent contact on " . APP_NAME . ".</p>
             <p><strong>Why we may contact you:</strong></p>
             <p style=\"background:#f9fafb;border-left:4px solid #2563eb;padding:12px 16px;margin:16px 0;\">{$why}</p>
             <p>You may receive emails when their account is approved or when a payment is confirmed by the landlord.</p>"
        );
    }

    public static function tenantApproved(string $tenantName, string $statusMessage): string
    {
        $name = htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8');
        $msg = htmlspecialchars($statusMessage, ENT_QUOTES, 'UTF-8');
        $login = htmlspecialchars(Router::url('auth/login'), ENT_QUOTES, 'UTF-8');

        return self::wrap(
            'Application Approved',
            "<p>Hi <strong>{$name}</strong>,</p>
             <p>Great news — your boarding house application has been <strong style=\"color:#16a34a;\">approved</strong>.</p>
             <p>{$msg}</p>
             <p style=\"margin:24px 0;\">
               <a href=\"{$login}\" style=\"display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;\">Open your tenant portal</a>
             </p>"
        );
    }

    public static function guardianTenantApproved(string $tenantName, string $purpose): string
    {
        $tenant = htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8');
        $why = nl2br(htmlspecialchars($purpose, ENT_QUOTES, 'UTF-8'));

        return self::wrap(
            'Tenant Application Approved',
            "<p>Hello,</p>
             <p>This is to inform you that <strong>{$tenant}</strong> has been <strong>approved</strong> as a tenant at our boarding house.</p>
             <p><strong>Contact purpose on file:</strong></p>
             <p style=\"background:#f9fafb;border-left:4px solid #2563eb;padding:12px 16px;\">{$why}</p>
             <p>They can now access the tenant portal on " . APP_NAME . ".</p>"
        );
    }

    public static function tenantPaymentApproved(
        string $tenantName,
        string $billName,
        float $amount,
        string $methodLabel = ''
    ): string {
        $name = htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8');
        $bill = htmlspecialchars($billName, ENT_QUOTES, 'UTF-8');
        $amt = number_format($amount, 2);
        $method = $methodLabel !== '' ? ' via ' . htmlspecialchars($methodLabel, ENT_QUOTES, 'UTF-8') : '';
        $bills = htmlspecialchars(Router::url('tenant/bills'), ENT_QUOTES, 'UTF-8');

        return self::wrap(
            'Payment Confirmed',
            "<p>Hi <strong>{$name}</strong>,</p>
             <p>Your landlord has <strong style=\"color:#16a34a;\">confirmed</strong> your payment{$method}.</p>
             <table style=\"width:100%;border-collapse:collapse;margin:16px 0;\">
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Bill</td><td style=\"padding:8px 0;font-weight:600;\">{$bill}</td></tr>
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Amount</td><td style=\"padding:8px 0;font-weight:600;\">₱{$amt}</td></tr>
             </table>
             <p>You can view your billing history in your BoardTrack account.</p>
             <p style=\"margin:24px 0;\">
               <a href=\"{$bills}\" style=\"display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;\">View My Bills</a>
             </p>"
        );
    }

    public static function guardianPaymentApproved(
        string $tenantName,
        string $billName,
        float $amount,
        string $purpose
    ): string {
        $tenant = htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8');
        $bill = htmlspecialchars($billName, ENT_QUOTES, 'UTF-8');
        $amt = number_format($amount, 2);
        $why = nl2br(htmlspecialchars($purpose, ENT_QUOTES, 'UTF-8'));

        return self::wrap(
            'Tenant Payment Confirmed',
            "<p>Hello,</p>
             <p>This is an official notice from <strong>" . APP_NAME . "</strong>.</p>
             <p><strong>{$tenant}</strong>'s payment has been <strong style=\"color:#16a34a;\">verified and confirmed</strong> by the landlord.</p>
             <table style=\"width:100%;border-collapse:collapse;margin:16px 0;\">
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Bill</td><td style=\"padding:8px 0;font-weight:600;\">{$bill}</td></tr>
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Amount paid</td><td style=\"padding:8px 0;font-weight:600;\">₱{$amt}</td></tr>
             </table>
             <p><strong>Why you receive these updates:</strong></p>
             <p style=\"background:#f9fafb;border-left:4px solid #2563eb;padding:12px 16px;\">{$why}</p>"
        );
    }

    public static function paymentReminder(
        string $tenantName,
        string $billName,
        float $amount,
        string $dueDate,
        int $daysUntilDue,
        int $reminderLevel = 1
    ): string {
        $name = htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8');
        $bill = htmlspecialchars($billName, ENT_QUOTES, 'UTF-8');
        $amt = number_format($amount, 2);
        $due = htmlspecialchars(date('F j, Y', strtotime($dueDate)), ENT_QUOTES, 'UTF-8');
        $days = $daysUntilDue === 1 ? 'tomorrow' : "in {$daysUntilDue} days";
        $bills = htmlspecialchars(Router::url('tenant/bills'), ENT_QUOTES, 'UTF-8');
        
        $urgency = match($reminderLevel) {
            1 => 'This is a friendly reminder.',
            2 => 'This is your second reminder.',
            3 => 'This is your final reminder.',
            default => 'This is a reminder.'
        };

        return self::wrap(
            'Payment Reminder — ' . APP_NAME,
            "<p>Hi <strong>{$name}</strong>,</p>
             <p><strong>{$urgency}</strong></p>
             <p>Your bill <strong>{$bill}</strong> of <strong>₱{$amt}</strong> is due <strong>{$days}</strong> on <strong>{$due}</strong>.</p>
             <p>Please make your payment before the due date to avoid late fees.</p>
             <p style=\"margin:24px 0;\">
               <a href=\"{$bills}\" style=\"display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;\">View & Pay Bill</a>
             </p>
             <p>If you have already paid, please disregard this message.</p>"
        );
    }

    public static function paymentOverdue(
        string $tenantName,
        string $billName,
        float $amount,
        string $dueDate,
        int $daysOverdue
    ): string {
        $name = htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8');
        $bill = htmlspecialchars($billName, ENT_QUOTES, 'UTF-8');
        $amt = number_format($amount, 2);
        $due = htmlspecialchars(date('F j, Y', strtotime($dueDate)), ENT_QUOTES, 'UTF-8');
        $daysText = $daysOverdue === 1 ? '1 day' : "{$daysOverdue} days";
        $bills = htmlspecialchars(Router::url('tenant/bills'), ENT_QUOTES, 'UTF-8');

        return self::wrap(
            'Payment Overdue Notice — ' . APP_NAME,
            "<p>Hi <strong>{$name}</strong>,</p>
             <p style=\"color:#dc2626;font-weight:600;\">Your payment is overdue.</p>
             <p>Your bill <strong>{$bill}</strong> of <strong>₱{$amt}</strong> was due on <strong>{$due}</strong> and is now <strong>{$daysText} overdue</strong>.</p>
             <p>Please make your payment as soon as possible to avoid further late fees or penalties.</p>
             <p style=\"margin:24px 0;\">
               <a href=\"{$bills}\" style=\"display:inline-block;background:#dc2626;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;\">View & Pay Bill Now</a>
             </p>
             <p>If you have already paid, please contact your landlord immediately.</p>"
        );
    }
}
