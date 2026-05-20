<?php
/**
 * Shared tenant payment form fields (GCash QR + method + receipt)
 * Expects: $landlordGcash (array), optional $billId for hidden field
 */
$landlordGcash = $landlordGcash ?? ['has_qr' => false, 'qr_url' => null, 'landlord_name' => 'Landlord'];
$hasQr = !empty($landlordGcash['has_qr']) && !empty($landlordGcash['qr_url']);
?>
<?php if (!empty($billId)): ?>
  <input type="hidden" name="bill_id" value="<?= (int) $billId ?>">
<?php endif; ?>

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
  <div style="padding: 16px; background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%); border: 1px solid var(--primary-border); border-radius: var(--radius);">
    <div style="font-weight: 600; color: var(--gray-800); margin-bottom: 8px;">
      <i class="fa-solid fa-qrcode" style="color: var(--primary);"></i> Scan to pay via GCash
    </div>
    <p style="font-size: 0.82rem; color: var(--gray-500); margin: 0 0 12px;">
      Send the exact bill amount to <?= htmlspecialchars($landlordGcash['landlord_name'] ?? 'your landlord') ?>, then upload your GCash receipt below.
    </p>
    <?php if ($hasQr): ?>
      <div style="text-align: center;">
        <img src="<?= htmlspecialchars($landlordGcash['qr_url']) ?>" alt="Landlord GCash QR" id="landlordGcashQrImg"
             style="max-width: 200px; width: 100%; border-radius: 8px; border: 2px solid #fff; box-shadow: var(--shadow-sm);">
      </div>
    <?php else: ?>
      <div class="alert" style="margin: 0; background: var(--warning-light); color: var(--warning); border: none;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        Your landlord has not uploaded a GCash QR yet. Choose <strong>In person / Cash</strong> or contact them.
      </div>
    <?php endif; ?>
  </div>
</div>

<div id="cashInstructions" style="display:none; margin-bottom: 16px; padding: 12px 14px; background: var(--gray-50); border-radius: var(--radius); font-size: 0.85rem; color: var(--gray-600);">
  <i class="fa-solid fa-hand-holding-dollar"></i> Pay your landlord in person, then upload a photo of your receipt or signed acknowledgment for verification.
</div>

<div class="form-group" id="proofUploadGroup">
  <label class="form-label" id="proofLabel">Payment receipt <span class="req">*</span></label>
  <div class="upload-area" id="uploadZone" onclick="document.getElementById('paymentFile').click()">
    <i class="fa-solid fa-cloud-arrow-up"></i>
    <p id="proofHint">Upload screenshot of GCash receipt or payment proof</p>
    <p style="font-size:0.75rem;color:var(--gray-400);margin-top:4px;">JPG or PNG, max 2MB</p>
  </div>
  <input type="file" name="payment_proof" id="paymentFile" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required style="display:none" onchange="updatePaymentFileName(this)">
  <div class="form-help" id="fileName"></div>
  <div id="receiptPreview" style="display:none; margin-top: 10px;">
    <img id="receiptPreviewImg" src="" alt="Receipt preview" style="max-width: 100%; max-height: 180px; border-radius: var(--radius); border: 1px solid var(--gray-200);">
  </div>
</div>

<div class="form-group" style="margin-bottom:0;">
  <label class="form-label">Notes (optional)</label>
  <textarea name="notes" class="form-textarea" rows="2" placeholder="Reference no., date paid, or other details..."></textarea>
</div>

<script>
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
