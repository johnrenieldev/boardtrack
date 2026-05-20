<?php
/** Shared approve/reject payment modals for landlord pages */
?>
<div class="modal-overlay" id="approvePaymentModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Approve Payment</span>
      <button type="button" class="modal-close" onclick="closeModal('approvePaymentModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/approve-payment') ?>" method="POST"
          data-confirm="Approve this payment and mark the bill as paid?" data-action="Approve payment">
      <input type="hidden" name="payment_id" id="approvePaymentId">
      <div class="modal-body">
        <p style="margin:0;color:var(--gray-600);">Approve payment from <strong id="approvePaymentTenant"></strong> for <strong>₱<span id="approvePaymentAmount"></span></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('approvePaymentModal')">Cancel</button>
        <button type="submit" class="btn btn-success">Approve</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="rejectPaymentModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Reject Payment</span>
      <button type="button" class="modal-close" onclick="closeModal('rejectPaymentModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/reject-payment') ?>" method="POST"
          data-confirm="Reject this payment? The tenant must submit again."
          data-action="Reject payment" data-color="#dc2626" data-confirm-text="Yes, reject">
      <input type="hidden" name="payment_id" id="rejectPaymentId">
      <div class="modal-body">
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Reason <span class="req">*</span></label>
          <textarea name="reason" class="form-textarea" rows="3" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('rejectPaymentModal')">Cancel</button>
        <button type="submit" class="btn btn-danger">Reject</button>
      </div>
    </form>
  </div>
</div>

<script>
if (typeof window.openModal !== 'function') {
  window.openModal = function(id) {
    var el = document.getElementById(id);
    if (el) { el.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
  };
  window.closeModal = function(id) {
    var el = document.getElementById(id);
    if (el) { el.style.display = 'none'; document.body.style.overflow = ''; }
  };
}
function showApprovePaymentModal(paymentId, tenantName, amount) {
  document.getElementById('approvePaymentId').value = paymentId;
  document.getElementById('approvePaymentTenant').textContent = tenantName;
  document.getElementById('approvePaymentAmount').textContent = Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2 });
  openModal('approvePaymentModal');
}
function showRejectPaymentModal(paymentId) {
  document.getElementById('rejectPaymentId').value = paymentId;
  openModal('rejectPaymentModal');
}
</script>
