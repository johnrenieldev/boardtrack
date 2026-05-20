<?php
/**
 * BoardTrack — Tenant: Pay Bill
 */
$bill          = $bill          ?? [];
$landlordGcash = $landlordGcash ?? ['has_qr' => false, 'qr_url' => null, 'landlord_name' => 'Landlord'];
?>
<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Pay Bill</h1>
      <p class="page-subtitle">Choose GCash or in-person payment, then upload your receipt for landlord verification.</p>
    </div>
    <a href="<?= Router::url('tenant/bills') ?>" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
  </div>
</div>

<div class="card" style="max-width: 640px;">
  <div style="padding: 20px 24px; border-bottom: 1px solid var(--gray-100); background: var(--gray-50);">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
      <div>
        <div style="font-weight:600;font-size:1.05rem;color:var(--gray-900);"><?= htmlspecialchars($bill['bill_name']) ?></div>
        <div style="font-size:0.82rem;color:var(--gray-500);margin-top:4px;">
          <?= htmlspecialchars(($bill['billing_period_start'] ?? '') . ' — ' . ($bill['billing_period_end'] ?? '')) ?>
        </div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:0.75rem;color:var(--gray-500);">Amount due</div>
        <div style="font-size:1.35rem;font-weight:700;color:var(--primary);">₱<?= number_format($bill['amount'], 2) ?></div>
        <div style="font-size:0.78rem;color:var(--danger);">Due <?= date('M j, Y', strtotime($bill['due_date'])) ?></div>
      </div>
    </div>
  </div>

  <form action="<?= Router::url('tenant/submit-payment') ?>" method="POST" enctype="multipart/form-data" class="confirm-form"
        data-action="Submit payment" data-message="Submit this payment receipt to your landlord for verification?"
        style="padding: 24px;">
    <?php
    $billId = (int) ($bill['id'] ?? 0);
    require APP_PATH . '/views/tenant/partials/payment_fields.php';
    ?>
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--gray-100);">
      <a href="<?= Router::url('tenant/bills') ?>" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload"></i> Submit Payment</button>
    </div>
  </form>
</div>
