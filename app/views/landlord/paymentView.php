<?php
/**
 * BoardTrack — Landlord: View Payment Proof
 */
?>
<div class="dash-page-header">
  <div class="flex items-center gap-4">
    <a href="<?= Router::url('landlord/payments') ?>" class="btn btn-sm btn-outline">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>
    <div>
      <h1 class="dash-page-title">Review Payment</h1>
      <p class="dash-page-sub">Review proof of payment submitted by <?= htmlspecialchars($payment['tenant_name']) ?>.</p>
    </div>
  </div>
</div>

<div class="dashboard-grid mt-6">
  <!-- Left Column: Payment Details -->
  <div class="grid-col-7">
    <div class="data-card mb-6">
      <div class="card-header flex justify-between items-center">
        <h3><i class="fa-solid fa-file-invoice-dollar"></i> Payment Information</h3>
        <span class="badge badge-<?= match($payment['status']) {
          'approved' => 'success',
          'pending' => 'warning',
          'rejected' => 'danger',
          default => 'secondary'
        } ?> p-2 px-4">
          <?= ucfirst($payment['status']) ?>
        </span>
      </div>
      <div class="card-body">
        <div class="grid grid-cols-2 gap-6 mb-6">
          <div>
            <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Bill Name</label>
            <div class="font-bold text-gray-900"><?= htmlspecialchars($payment['bill_name']) ?></div>
          </div>
          <div>
            <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Amount Paid</label>
            <div class="text-xl font-bold text-blue-600">₱<?= number_format($payment['amount_paid'], 2) ?></div>
          </div>
          <div>
            <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Payment Date</label>
            <div class="text-gray-700"><?= date('F j, Y, g:i a', strtotime($payment['payment_date'])) ?></div>
          </div>
          <div>
            <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Payment Method</label>
            <div class="text-gray-700"><?= match($payment['payment_method'] ?? '') {
              'gcash' => 'GCash',
              'cash' => 'In person / Cash',
              'bank_transfer' => 'Bank Transfer',
              default => ucfirst(str_replace('_', ' ', $payment['payment_method'] ?? 'other')),
            } ?></div>
          </div>
        </div>

        <?php if ($payment['notes']): ?>
          <div class="mb-6">
            <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Tenant Notes</label>
            <div class="p-3 bg-gray-50 rounded border text-gray-600 text-sm">
              <?= nl2br(htmlspecialchars($payment['notes'])) ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($payment['rejection_reason']): ?>
          <div class="p-4 bg-red-50 rounded border border-red-100">
            <label class="block text-xs font-bold uppercase text-red-400 mb-1">Rejection Reason</label>
            <div class="text-red-900"><?= nl2br(htmlspecialchars($payment['rejection_reason'])) ?></div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Payment receipt -->
    <?php $proofFile = $payment['proof_file_path'] ?? $payment['proof_file'] ?? ''; ?>
    <div class="data-card">
      <div class="card-header flex justify-between items-center">
        <h3><i class="fa-solid fa-receipt"></i> Payment Receipt</h3>
        <?php if ($proofFile): ?>
        <a href="<?= Router::upload('payments', $proofFile) ?>" target="_blank" class="btn btn-sm btn-outline">
          <i class="fa-solid fa-up-right-from-bracket"></i> Open Full Size
        </a>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <?php if ($proofFile): ?>
        <img src="<?= Router::upload('payments', $proofFile) ?>" alt="Payment receipt" style="width: 100%; display: block;">
        <?php else: ?>
        <p style="padding: 20px; color: var(--gray-500);">No receipt image on file.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Right Column: Tenant & Actions -->
  <div class="grid-col-5">
    <div class="data-card mb-6">
      <div class="card-header">
        <h3><i class="fa-solid fa-user"></i> Submitting Tenant</h3>
      </div>
      <div class="card-body">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xl">
            <?= strtoupper(substr($payment['tenant_name'], 0, 1)) ?>
          </div>
          <div>
            <div class="font-bold text-gray-900"><?= htmlspecialchars($payment['tenant_name']) ?></div>
            <div class="text-xs text-gray-500">Room <?= htmlspecialchars($payment['room_number'] ?? 'N/A') ?></div>
          </div>
        </div>
        <a href="<?= Router::url('landlord/view-tenant/' . $payment['tenant_id']) ?>" class="btn btn-sm btn-outline btn-block">
          View Tenant Profile
        </a>
      </div>
    </div>

    <?php if ($payment['status'] === 'pending'): ?>
      <div class="data-card">
        <div class="card-header">
          <h3><i class="fa-solid fa-gears"></i> Review Actions</h3>
        </div>
        <div class="card-body">
          <button type="button" class="btn btn-success btn-block mb-3" onclick="showApprovePaymentModal(<?= (int) $payment['id'] ?>, <?= json_encode($payment['tenant_name']) ?>, <?= (float) $payment['amount_paid'] ?>)">
            <i class="fa-solid fa-check"></i> Approve Payment
          </button>
          <button type="button" class="btn btn-danger btn-block" onclick="showRejectPaymentModal(<?= $payment['id'] ?>)">
            <i class="fa-solid fa-xmark"></i> Reject Payment
          </button>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Implementation Scripts (Omitted for brevity, should be unified) -->

<style>
.grid-col-7 { grid-column: span 7; }
.grid-col-5 { grid-column: span 5; }
</style>
