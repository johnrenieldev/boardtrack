<?php
/**
 * BoardTrack — Tenant: Pay Bill
 */
?>
<div class="dash-page-header">
  <div>
    <h1 class="dash-page-title">Pay Bill</h1>
    <p class="dash-page-sub">Upload your payment proof for landlord verification.</p>
  </div>
</div>

<div class="form-card max-w-xl">
  <div class="bill-summary-card mb-6">
    <div class="bill-summary-header">
      <h3 class="bill-name"><?= htmlspecialchars($bill['bill_name']) ?></h3>
      <span class="bill-amount">₱<?= number_format($bill['amount'], 2) ?></span>
    </div>
    <div class="bill-summary-body">
      <div class="detail-row">
        <span>Period:</span>
        <span><?= htmlspecialchars(($bill['billing_period_start'] ?? '') . ' — ' . ($bill['billing_period_end'] ?? '')) ?></span>
      </div>
      <div class="detail-row">
        <span>Due Date:</span>
        <span class="text-danger"><?= date('M j, Y', strtotime($bill['due_date'])) ?></span>
      </div>
    </div>
  </div>

  <form action="<?= Router::url('tenant/submit-payment') ?>" method="POST" enctype="multipart/form-data" class="dash-form">
    <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">

    <div class="form-group">
      <label for="payment_method">Payment Method <span class="required">*</span></label>
      <select name="payment_method" id="payment_method" class="form-select" required>
        <option value="">— Select Method —</option>
        <option value="gcash">GCash</option>
        <option value="bank_transfer">Bank Transfer</option>
        <option value="cash">Cash (Directly to Landlord)</option>
        <option value="other">Other</option>
      </select>
    </div>

    <div class="form-group">
      <label for="payment_proof">Upload Proof of Payment (JPG/PNG, max 2MB) <span class="required">*</span></label>
      <input type="file" name="payment_proof" id="payment_proof" class="form-input" accept="image/jpeg,image/png" required onchange="previewImage(this)">
      <div id="imagePreview" class="mt-3" style="display: none;">
        <img id="preview" src="" alt="Proof Preview" style="max-width: 100%; border-radius: 8px; border: 1px solid var(--gray-200);">
      </div>
    </div>

    <div class="form-group">
      <label for="notes">Notes (Optional)</label>
      <textarea name="notes" id="notes" class="form-textarea" rows="3" placeholder="Reference number or any additional information..."></textarea>
    </div>

    <div class="form-actions">
      <a href="<?= Router::url('tenant/bills') ?>" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">Submit Payment Proof</button>
    </div>
  </form>
</div>

<script>
function previewImage(input) {
  const preview = document.getElementById('preview');
  const previewDiv = document.getElementById('imagePreview');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      preview.src = e.target.result;
      previewDiv.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.src = '';
    previewDiv.style.display = 'none';
  }
}
</script>
