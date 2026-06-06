<?php
/**
 * Shared tenant payment form fields (GCash QR + method + receipt)
 * Expects: $landlordGcash (array), optional $billId for hidden field, optional $bill for amount calculation
 */
$landlordGcash = $landlordGcash ?? ['has_qr' => false, 'qr_url' => null, 'landlord_name' => 'Landlord'];
$hasQr = !empty($landlordGcash['has_qr']) && !empty($landlordGcash['qr_url']);
$bill = $bill ?? null;
$billAmount = $bill ? (float)($bill['amount'] ?? 0) : 0;
$amountPaid = $bill ? (float)($bill['amount_paid'] ?? 0) : 0;
$remainingBalance = max(0, $billAmount - $amountPaid);
?>
<input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
<?php if (!empty($billId)): ?>
  <input type="hidden" name="bill_id" value="<?= (int) $billId ?>">
<?php endif; ?>

<div class="form-group">
  <label class="form-label">Amount to Pay <span class="req">*</span></label>
  <div style="position: relative;">
    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--color-text-secondary); font-weight: 600;">₱</span>
    <input type="number" name="amount_paid" id="amount_paid" class="form-input" 
           style="padding-left: 28px;" 
           step="0.01" min="0.01" 
           max="<?= $remainingBalance > 0 ? $remainingBalance : $billAmount ?>" 
           value="<?= $remainingBalance > 0 ? $remainingBalance : $billAmount ?>" 
           required 
           oninput="validatePaymentAmount()">
  </div>
  <div class="form-help" id="amountHelp">
    <?php if ($remainingBalance > 0): ?>
      You can pay any amount up to ₱<?= number_format($remainingBalance, 2) ?>
    <?php else: ?>
      Enter the amount you are paying (max: ₱<?= number_format($billAmount, 2) ?>)
    <?php endif; ?>
  </div>
  <div class="form-help" id="amountError" style="display: none; color: var(--color-danger);"></div>
</div>

<div class="form-group">
  <label class="form-label">How will you pay? <span class="req">*</span></label>
  <select name="payment_method" id="payment_method" class="form-select" required onchange="togglePaymentMethod()">
    <option value="">— Select —</option>
    <option value="gcash">GCash (scan QR &amp; upload receipt)</option>
    <option value="cash">In person / Cash (upload receipt or proof)</option>
    <option value="other">Other</option>
  </select>
</div>

<div id="gcashQrSection" style="display:none; margin-bottom: 16px;">
  <div style="padding: 16px; background: linear-gradient(135deg, var(--color-brand-light) 0%, var(--color-success-light) 100%); border: 1px solid var(--color-brand-border); border-radius: var(--radius);">
    <div style="font-weight: 600; color: var(--color-text-primary); margin-bottom: 8px;">
      <i class="fa-solid fa-qrcode" style="color: var(--color-brand);"></i> Scan to pay via GCash
    </div>
    <p style="font-size: 0.82rem; color: var(--color-text-secondary); margin: 0 0 12px;">
      Send the exact bill amount to <?= htmlspecialchars($landlordGcash['landlord_name'] ?? 'your landlord') ?>, then upload your GCash receipt below.
    </p>
    <?php if ($hasQr): ?>
      <div style="text-align: center;">
        <img src="<?= htmlspecialchars($landlordGcash['qr_url']) ?>" alt="Landlord GCash QR" id="landlordGcashQrImg"
             style="max-width: min(200px, 70vw); width: 100%; border-radius: 8px; border: 2px solid #fff; box-shadow: var(--shadow-sm);">
      </div>
    <?php else: ?>
      <div class="alert" style="margin: 0; background: var(--warning-light); color: var(--warning); border: none;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        Your landlord has not uploaded a GCash QR yet. Choose <strong>In person / Cash</strong> or contact them.
      </div>
    <?php endif; ?>
  </div>
</div>

<div id="cashInstructions" style="display:none; margin-bottom: 16px; padding: 12px 14px; background: var(--color-canvas); border-radius: var(--radius); font-size: 0.85rem; color: var(--color-text-secondary);">
  <i class="fa-solid fa-hand-holding-dollar"></i> Pay your landlord in person, then upload a photo of your receipt or signed acknowledgment for verification.
</div>

<div class="form-group" id="proofUploadGroup">
  <label class="form-label" id="proofLabel">Payment receipt <span class="req">*</span></label>
  <div class="upload-area" id="uploadZone" onclick="document.getElementById('paymentFile').click()">
    <i class="fa-solid fa-cloud-arrow-up"></i>
    <p id="proofHint">Upload screenshot of GCash receipt or payment proof</p>
    <p style="font-size:0.75rem;color:var(--color-text-muted);margin-top:4px;">JPG or PNG, max 2MB</p>
  </div>
  <input type="file" name="payment_proof" id="paymentFile" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required style="display:none" onchange="updatePaymentFileName(this)">
  <div class="form-help" id="fileName"></div>
  <div id="receiptPreview" style="display:none; margin-top: 10px;">
    <img id="receiptPreviewImg" src="" alt="Receipt preview" style="max-width: 100%; max-height: 180px; border-radius: var(--radius); border: 1px solid var(--color-border);">
  </div>
</div>

<div class="form-group" style="margin-bottom:0;">
  <label class="form-label">Notes (optional)</label>
  <textarea name="notes" class="form-textarea" rows="2" placeholder="Reference no., date paid, or other details..."></textarea>
</div>

<script>
function validatePaymentAmount() {
  var input = document.getElementById('amount_paid');
  var errorDiv = document.getElementById('amountError');
  var helpDiv = document.getElementById('amountHelp');
  
  if (!input) return true;
  
  var amount = parseFloat(input.value);
  var max = parseFloat(input.max);
  var min = parseFloat(input.min);
  
  if (isNaN(amount) || amount <= 0) {
    errorDiv.textContent = 'Amount must be greater than zero.';
    errorDiv.style.display = 'block';
    helpDiv.style.display = 'none';
    input.setCustomValidity('Amount must be greater than zero');
    return false;
  }
  
  if (amount > max) {
    errorDiv.textContent = 'Amount cannot exceed remaining balance of ₱' + max.toFixed(2);
    errorDiv.style.display = 'block';
    helpDiv.style.display = 'none';
    input.setCustomValidity('Amount exceeds remaining balance');
    return false;
  }
  
  errorDiv.style.display = 'none';
  helpDiv.style.display = 'block';
  input.setCustomValidity('');
  return true;
}

function togglePaymentMethod() {
  var method = document.getElementById('payment_method').value;
  var gcashBox = document.getElementById('gcashQrSection');
  var cashBox = document.getElementById('cashInstructions');
  var hint = document.getElementById('proofHint');
  if (gcashBox) gcashBox.style.display = method === 'gcash' ? 'block' : 'none';
  if (cashBox) cashBox.style.display = method === 'cash' ? 'block' : 'none';
  if (hint) {
    if (method === 'gcash') hint.textContent = 'Upload your GCash transaction receipt screenshot';
    else if (method === 'cash') hint.textContent = 'Upload photo of receipt or proof of cash payment';
    else hint.textContent = 'Upload payment proof image';
  }
}
function updatePaymentFileName(input) {
  var el = document.getElementById('fileName');
  var preview = document.getElementById('receiptPreview');
  var img = document.getElementById('receiptPreviewImg');
  if (!input.files || !input.files[0]) {
    if (el) el.textContent = '';
    if (preview) preview.style.display = 'none';
    return;
  }
  if (el) el.textContent = input.files[0].name;
  if (preview && img) {
    var reader = new FileReader();
    reader.onload = function(e) {
      img.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

<style>
/* Mobile-responsive styles for payment modal */
@media (max-width: 480px) {
  /* Reduce QR code size on very small screens */
  #landlordGcashQrImg {
    max-width: min(160px, 65vw) !important;
  }
  
  /* Reduce padding in upload area */
  .upload-area {
    padding: 16px 12px !important;
    font-size: 0.9rem;
  }
  
  .upload-area i {
    font-size: 2rem !important;
  }
  
  /* Reduce padding in form groups */
  .form-group {
    margin-bottom: 14px;
  }
  
  /* Make input fields more compact */
  .form-input,
  .form-select,
  .form-textarea {
    font-size: 0.9rem;
    padding: 10px 12px;
  }
  
  /* Reduce padding in info boxes */
  #gcashQrSection > div,
  #cashInstructions {
    padding: 12px !important;
    font-size: 0.85rem;
  }
  
  /* Make labels more compact */
  .form-label {
    font-size: 0.85rem;
    margin-bottom: 6px;
  }
  
  /* Reduce help text size */
  .form-help {
    font-size: 0.75rem;
  }
}

@media (max-width: 375px) {
  /* Extra small screens - further reduce QR size */
  #landlordGcashQrImg {
    max-width: min(140px, 60vw) !important;
  }
  
  /* More compact upload area */
  .upload-area {
    padding: 12px 10px !important;
  }
  
  /* Smaller text in info sections */
  #gcashQrSection > div,
  #cashInstructions {
    padding: 10px !important;
    font-size: 0.8rem;
  }
}

@media (max-width: 320px) {
  /* iPhone SE and very small devices */
  #landlordGcashQrImg {
    max-width: min(120px, 55vw) !important;
  }
  
  .upload-area {
    padding: 10px 8px !important;
    font-size: 0.85rem;
  }
  
  .upload-area i {
    font-size: 1.75rem !important;
  }
  
  .form-input,
  .form-select,
  .form-textarea {
    font-size: 0.85rem;
    padding: 8px 10px;
  }
}
</style>
