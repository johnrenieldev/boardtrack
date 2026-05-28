<?php
/**
 * BoardTrack | HTML email templates (used with Mailer)
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
             Please wait for approval before you can access all features.</p>
             <p style=\"margin:24px 0;\">
               <a href=\"{$login}\" style=\"display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;\">Sign in to BoardTrack</a>
             </p>
             <p>We will email you again when your application is approved.</p>"
        );
    }

    public static function verificationEmail(string $name, string $link): string
    {
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

        return self::wrap(
            'Verify Your Email',
            "<p>Hi <strong>{$name}</strong>,</p>
             <p>Please verify your email address to complete your registration with <strong>" . APP_NAME . "</strong>.</p>
             <p style=\"margin:24px 0;\">
               <a href=\"{$link}\" style=\"display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;\">Verify Email Address</a>
             </p>
             <p>If the button above doesn't work, copy and paste this link into your browser:</p>
             <p style=\"font-size:12px;color:#6b7280;word-break:break-all;\">{$link}</p>
             <p>This link will expire in 24 hours.</p>"
        );
    }

    public static function passwordReset(string $name, string $link): string
    {
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

        return self::wrap(
            'Password Reset Request',
            "<p>Hi <strong>{$name}</strong>,</p>
             <p>We received a request to reset your password for your <strong>" . APP_NAME . "</strong> account.</p>
             <p style=\"margin:24px 0;\">
               <a href=\"{$link}\" style=\"display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;\">Reset Password</a>
             </p>
             <p>If you did not request a password reset, you can safely ignore this email.</p>
             <p>If the button above doesn't work, copy and paste this link into your browser:</p>
             <p style=\"font-size:12px;color:#6b7280;word-break:break-all;\">{$link}</p>
             <p>This link will expire in 1 hour.</p>"
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
             <p>Great news | your boarding house application has been <strong style=\"color:#16a34a;\">approved</strong>.</p>
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

    public static function tenantPaymentRejected(
        string $tenantName,
        string $billName,
        float $amount,
        string $reason
    ): string {
        $name = htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8');
        $bill = htmlspecialchars($billName, ENT_QUOTES, 'UTF-8');
        $amt = number_format($amount, 2);
        $msg = nl2br(htmlspecialchars($reason, ENT_QUOTES, 'UTF-8'));
        $bills = htmlspecialchars(Router::url('tenant/bills'), ENT_QUOTES, 'UTF-8');

        return self::wrap(
            'Payment Rejected',
            "<p>Hi <strong>{$name}</strong>,</p>
             <p>Your payment for <strong>{$bill}</strong> (₱{$amt}) was <strong style=\"color:#dc2626;\">rejected</strong> by the landlord.</p>
             <p><strong>Reason for rejection:</strong></p>
             <p style=\"background:#fef2f2;border-left:4px solid #dc2626;padding:12px 16px;margin:16px 0;\">{$msg}</p>
             <p>Please review the reason and submit a new payment proof if necessary.</p>
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

    public static function guardianPaymentRejected(
        string $tenantName,
        string $billName,
        float $amount,
        string $reason,
        string $purpose
    ): string {
        $tenant = htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8');
        $bill = htmlspecialchars($billName, ENT_QUOTES, 'UTF-8');
        $amt = number_format($amount, 2);
        $msg = nl2br(htmlspecialchars($reason, ENT_QUOTES, 'UTF-8'));
        $why = nl2br(htmlspecialchars($purpose, ENT_QUOTES, 'UTF-8'));

        return self::wrap(
            'Tenant Payment Rejected',
            "<p>Hello,</p>
             <p>This is an official notice from <strong>" . APP_NAME . "</strong>.</p>
             <p><strong>{$tenant}</strong>'s payment for <strong>{$bill}</strong> (₱{$amt}) was <strong style=\"color:#dc2626;\">rejected</strong> by the landlord.</p>
             <p><strong>Reason for rejection:</strong></p>
             <p style=\"background:#fef2f2;border-left:4px solid #dc2626;padding:12px 16px;margin:16px 0;\">{$msg}</p>
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
            'Payment Reminder | ' . APP_NAME,
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
            'Payment Overdue Notice | ' . APP_NAME,
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

    /**
     * Complaint submitted by tenant → email to landlord.
     */
    public static function complaintSubmittedToLandlord(
        string $tenantName,
        string $tenantEmail,
        string $category,
        string $title,
        string $complaintLink
    ): string {
        $tName  = htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8');
        $tEmail = htmlspecialchars($tenantEmail, ENT_QUOTES, 'UTF-8');
        $cat    = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
        $cTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $link   = htmlspecialchars($complaintLink, ENT_QUOTES, 'UTF-8');

        return self::wrap(
            'New Complaint Submitted',
            "<p>Hello,</p>
             <p>A tenant has submitted a new complaint that requires your review.</p>

             <table style=\"width:100%;border-collapse:collapse;margin:16px 0;\">
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Tenant</td><td style=\"padding:8px 0;font-weight:600;\">{$tName}</td></tr>
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Tenant Email</td><td style=\"padding:8px 0;font-weight:600;\">{$tEmail}</td></tr>
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Category</td><td style=\"padding:8px 0;font-weight:600;\">{$cat}</td></tr>
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Complaint</td><td style=\"padding:8px 0;font-weight:600;\">{$cTitle}</td></tr>
             </table>

             <p style=\"margin:24px 0;\">
               <a href=\"{$link}\" style=\"display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;\">Review Complaint</a>
             </p>
             <p>Please review the details and respond as appropriate in your landlord dashboard.</p>"
        );
    }

    /**
     * Payment proof submitted by tenant → email to landlord.
     */
    public static function paymentSubmittedToLandlord(
        string $tenantName,
        string $tenantEmail,
        string $billName,
        float $amount,
        string $paymentMethod,
        string $paymentLink
    ): string {
        $tName  = htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8');
        $tEmail = htmlspecialchars($tenantEmail, ENT_QUOTES, 'UTF-8');
        $bName  = htmlspecialchars($billName, ENT_QUOTES, 'UTF-8');
        $method = htmlspecialchars($paymentMethod, ENT_QUOTES, 'UTF-8');
        $link   = htmlspecialchars($paymentLink, ENT_QUOTES, 'UTF-8');
        $amt    = number_format($amount, 2);

        return self::wrap(
            'New Payment Submitted',
            "<p>Hello,</p>
             <p>A tenant has submitted a payment proof for your review.</p>

             <table style=\"width:100%;border-collapse:collapse;margin:16px 0;\">
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Tenant</td><td style=\"padding:8px 0;font-weight:600;\">{$tName}</td></tr>
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Tenant Email</td><td style=\"padding:8px 0;font-weight:600;\">{$tEmail}</td></tr>
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Bill</td><td style=\"padding:8px 0;font-weight:600;\">{$bName}</td></tr>
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Amount</td><td style=\"padding:8px 0;font-weight:600;\">₱{$amt}</td></tr>
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Payment Method</td><td style=\"padding:8px 0;font-weight:600;\">{$method}</td></tr>
             </table>

             <p style=\"margin:24px 0;\">
               <a href=\"{$link}\" style=\"display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;\">Review Payment</a>
             </p>
             <p>Please check the Payments section in your landlord dashboard to approve or reject this submission.</p>"
        );
    }

    /**
     * Tenant profile updated → email to landlord.
     */
    public static function tenantProfileUpdatedToLandlord(
        string $tenantName,
        string $tenantEmail,
        string $roomPref,
        string $guardianEmail,
        string $guardianPurpose
    ): string {
        $tName   = htmlspecialchars($tenantName, ENT_QUOTES, 'UTF-8');
        $tEmail  = htmlspecialchars($tenantEmail, ENT_QUOTES, 'UTF-8');
        $roomPref = htmlspecialchars($roomPref, ENT_QUOTES, 'UTF-8');
        $gEmail  = htmlspecialchars($guardianEmail, ENT_QUOTES, 'UTF-8');
        $gPurpose = nl2br(htmlspecialchars($guardianPurpose, ENT_QUOTES, 'UTF-8'));
        $dashboardLink = htmlspecialchars(Router::url('landlord/tenants'), ENT_QUOTES, 'UTF-8');

        return self::wrap(
            'Tenant Profile Updated',
            "<p>Hello,</p>
             <p>A tenant has updated their profile information. Please review the latest changes in the landlord dashboard.</p>

             <table style=\"width:100%;border-collapse:collapse;margin:16px 0;\">
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Tenant</td><td style=\"padding:8px 0;font-weight:600;\">{$tName}</td></tr>
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Tenant Email</td><td style=\"padding:8px 0;font-weight:600;\">{$tEmail}</td></tr>
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Preferred Room Type</td><td style=\"padding:8px 0;font-weight:600;\">{$roomPref}</td></tr>
               <tr><td style=\"padding:8px 0;color:#6b7280;\">Guardian Email</td><td style=\"padding:8px 0;font-weight:600;\">{$gEmail}</td></tr>
             </table>

             <p><strong>Guardian Contact Purpose:</strong></p>
             <p style=\"background:#f9fafb;border-left:4px solid #2563eb;padding:12px 16px;margin:16px 0;\">{$gPurpose}</p>

             <p style=\"margin:24px 0;\">
               <a href=\"{$dashboardLink}\" style=\"display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;\">Review Tenants</a>
             </p>"
        );
    }

    /**
     * Need Help / Contact Us message → email to support/landlord admin.
     */
    public static function contactUs(string $name, string $email, string $subjectLabel, string $message): string
    {
        $n = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $e = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $s = htmlspecialchars($subjectLabel, ENT_QUOTES, 'UTF-8');
        $m = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

        return self::wrap(
            'Contact Form Submission | ' . APP_NAME,
            "<h2 style=\"margin:0 0 12px;\">New Contact Form Submission</h2>
             <p><strong>Name:</strong> {$n}</p>
             <p><strong>Email:</strong> {$e}</p>
             <p><strong>Subject:</strong> {$s}</p>
             <hr style='margin: 20px 0;'>
             <p><strong>Message:</strong></p>
             <p style=\"white-space:pre-wrap;\">{$m}</p>
             <hr style='margin: 20px 0;'>
             <p style=\"color:#666; font-size: 12px;\">This message was sent from the BoardTrack landing page contact form.</p>"
        );
    }
}

