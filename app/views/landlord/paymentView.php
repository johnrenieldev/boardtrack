<?php
/**
 * BoardTrack — Landlord: View Payment Proof
 */
?>
<div class="dash-page-header mb-4">
  <div class="flex items-center justify-between w-full">
    <div class="flex items-center gap-3">
      <a href="<?= Router::url('landlord/payments') ?>" id="backButton" class="w-8 h-8 rounded-md border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors shadow-xs" title="Go back">
        <i class="fa-solid fa-arrow-left text-xs"></i>
      </a>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var backButton = document.getElementById('backButton');
  if (backButton && sessionStorage.getItem('fromNotifications') === 'true') {
    backButton.href = '<?= Router::url('landlord/notifications') ?>';
    sessionStorage.removeItem('fromNotifications');
  }
});
</script>
      <div>
        <h1 class="text-xl font-black text-gray-900 leading-tight">Review Payment</h1>
        <p class="text-[0.7rem] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Reference ID: #<?= str_pad($payment['id'], 5, '0', STR_PAD_LEFT) ?></p>
      </div>
    </div>
    
    <div class="flex items-center gap-2">
      <span class="inline-flex px-2.5 py-1 rounded-full text-[0.65rem] font-black uppercase tracking-widest border <?= match($payment['status']) {
        'approved' => 'bg-success-50 text-success-600 border-success-200',
        'pending' => 'bg-warning-50 text-warning-600 border-warning-200',
        'rejected' => 'bg-danger-50 text-danger-600 border-danger-200',
        default => 'bg-gray-50 text-gray-600 border-gray-200'
      } ?>">
        <?= ucfirst($payment['status']) ?>
      </span>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
  <!-- Left Column: Payment Details -->
  <div class="lg:col-span-4">
    <div class="card mb-5">
      <div class="p-4 border-b border-gray-100 bg-white">
        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
          <i class="fa-solid fa-file-invoice-dollar text-brand-600"></i> Payment Details
        </h3>
      </div>
      <div class="p-5 space-y-4">
        <div>
          <label class="block text-[0.6rem] font-black text-gray-400 uppercase tracking-widest mb-1">Bill Name</label>
          <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($payment['bill_name']) ?></div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-[0.6rem] font-black text-gray-400 uppercase tracking-widest mb-1">Amount Paid</label>
            <div class="text-lg font-black text-brand-600">₱<?= number_format($payment['amount_paid'], 2) ?></div>
          </div>
          <div>
            <label class="block text-[0.6rem] font-black text-gray-400 uppercase tracking-widest mb-1">Method</label>
            <div class="text-sm font-bold text-gray-900"><?= match($payment['payment_method'] ?? '') {
              'gcash' => 'GCash',
              'cash' => 'Cash',
              'bank_transfer' => 'Bank Transfer',
              default => ucfirst(str_replace('_', ' ', $payment['payment_method'] ?? 'other')),
            } ?></div>
          </div>
        </div>

        <div>
          <label class="block text-[0.6rem] font-black text-gray-400 uppercase tracking-widest mb-1">Date Submitted</label>
          <div class="text-[0.8rem] font-bold text-gray-700"><?= date('M j, Y | g:i a', strtotime($payment['payment_date'])) ?></div>
        </div>

        <?php if ($payment['notes']): ?>
          <div class="pt-4 border-t border-gray-100">
            <label class="block text-[0.6rem] font-black text-gray-400 uppercase tracking-widest mb-1">Tenant Notes</label>
            <div class="text-[0.75rem] text-gray-500 leading-relaxed italic">
              "<?= nl2br(htmlspecialchars($payment['notes'])) ?>"
            </div>
          </div>
        <?php endif; ?>

        <?php if ($payment['rejection_reason']): ?>
          <div class="p-3 bg-danger-50 rounded-lg border border-danger-100">
            <label class="block text-[0.6rem] font-black text-danger-400 uppercase tracking-widest mb-1">Rejection Reason</label>
            <div class="text-danger-900 text-[0.75rem] font-bold"><?= nl2br(htmlspecialchars($payment['rejection_reason'])) ?></div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Submitting Tenant -->
    <div class="card mb-5">
      <div class="p-5">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-black text-sm border border-brand-100 shadow-xs">
            <?= strtoupper(substr($payment['tenant_name'], 0, 1)) ?>
          </div>
          <div>
            <div class="font-bold text-gray-900 text-sm"><?= htmlspecialchars($payment['tenant_name']) ?></div>
            <div class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-widest">Room <?= htmlspecialchars($payment['room_number'] ?? 'N/A') ?></div>
          </div>
        </div>
        <a href="<?= Router::url('landlord/view-tenant/' . $payment['tenant_id']) ?>" class="btn btn-secondary w-full py-2 flex items-center justify-center gap-2 text-xs font-bold">
          <i class="fa-solid fa-user-circle"></i> Profile
        </a>
      </div>
    </div>

    <?php if ($payment['status'] === 'pending'): ?>
      <div class="space-y-2">
        <button type="button" class="btn btn-success w-full py-3 flex items-center justify-center gap-2 text-sm font-black shadow-sm uppercase tracking-widest" onclick="showApprovePaymentModal(<?= (int) $payment['id'] ?>, <?= json_encode($payment['tenant_name']) ?>, <?= (float) $payment['amount_paid'] ?>)">
          <i class="fa-solid fa-check-circle"></i> Approve
        </button>
        <button type="button" class="btn btn-danger w-full py-3 flex items-center justify-center gap-2 text-sm font-black shadow-sm uppercase tracking-widest" onclick="showRejectPaymentModal(<?= $payment['id'] ?>)">
          <i class="fa-solid fa-times-circle"></i> Reject
        </button>
      </div>
    <?php endif; ?>
  </div>

  <!-- Right Column: Receipt Image -->
  <div class="lg:col-span-8">
    <div class="card overflow-hidden h-full flex flex-col">
      <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-white shrink-0">
        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
          <i class="fa-solid fa-receipt text-brand-600"></i> Payment Proof
        </h3>
        <?php $proofFile = $payment['proof_file_path'] ?? $payment['proof_file'] ?? ''; ?>
        <?php if ($proofFile): ?>
        <a href="<?= Router::upload('payments', $proofFile) ?>" target="_blank" class="text-[0.65rem] font-black text-brand-600 uppercase tracking-widest hover:underline flex items-center gap-1">
          <i class="fa-solid fa-expand"></i> Full Screen
        </a>
        <?php endif; ?>
      </div>
      <div class="preview-container flex-1 min-h-[400px] flex items-center justify-center bg-gray-50">
        <?php if ($proofFile): ?>
          <div class="relative group max-w-full">
            <img src="<?= Router::upload('payments', $proofFile) ?>" alt="Payment receipt" 
                 class="img-breathe max-h-[600px] max-w-full object-contain bg-white rounded-lg shadow-sm">
          </div>
        <?php else: ?>
          <div class="text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 text-gray-300 flex items-center justify-center text-2xl mx-auto mb-3">
              <i class="fa-solid fa-image-slash"></i>
            </div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">No receipt on file</p>
          </div>
        <?php endif; ?>
      </div>
      <div class="p-4 bg-white border-t border-gray-100 shrink-0">
         <p class="text-[0.65rem] text-gray-400 text-center italic">
           <i class="fa-solid fa-info-circle mr-1"></i> Verify all transaction details (Reference No., Amount, Date) before approving.
         </p>
      </div>
    </div>
  </div>
</div>

<!-- Modal Implementation Scripts (Omitted for brevity, should be unified) -->

<style>
.grid-col-7 { grid-column: span 7; }
.grid-col-5 { grid-column: span 5; }
</style>
