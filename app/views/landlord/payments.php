<?php
/**
 * BoardTrack — Landlord: Payments
 * app/views/landlord/payments.php
 * Layout: landlord.php
 */
$payments   = $payments   ?? [];
$statistics = $statistics ?? [];
$filters    = $filters    ?? [];
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Payments</h1>
      <p class="page-subtitle">Review and verify payment proof submitted by tenants.</p>
    </div>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-credit-card"></i></div>
      <div class="stat-label">Total Payments</div>
    </div>
    <div class="stat-value"><?= $statistics['total'] ?? 0 ?></div>
    <div class="stat-footer">Across <span>all time</span></div>
  </div>

  <div class="stat-card <?= ($statistics['pending'] ?? 0) > 0 ? 'urgent' : '' ?>">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-clock"></i></div>
      <div class="stat-label">Pending Review</div>
    </div>
    <div class="stat-value"><?= $statistics['pending'] ?? 0 ?></div>
    <div class="stat-footer">Requires <span>verification</span></div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-circle-check"></i></div>
      <div class="stat-label">Approved</div>
    </div>
    <div class="stat-value"><?= $statistics['approved'] ?? 0 ?></div>
    <div class="stat-footer">Successfully <span>verified</span></div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-circle-xmark"></i></div>
      <div class="stat-label">Rejected</div>
    </div>
    <div class="stat-value"><?= $statistics['rejected'] ?? 0 ?></div>
    <div class="stat-footer">Invalid <span>submissions</span></div>
  </div>
</div>

<!-- Filters -->
<div class="card mb-4 p-4">
  <form method="GET" action="index.php" class="filter-bar" style="margin-bottom:0;">
    <input type="hidden" name="url" value="landlord/payments">
    <select name="status" class="form-select">
      <option value="">All Statuses</option>
      <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
      <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?= Router::url('landlord/payments') ?>" class="btn btn-secondary btn-sm">Clear</a>
  </form>
</div>

<!-- Payments Table -->
<div class="card overflow-hidden">
  <?php if (empty($payments)): ?>
    <div class="p-12 text-center">
      <i class="fa-solid fa-credit-card text-5xl text-gray-200 mb-4"></i>
      <h3 class="text-lg font-bold text-gray-900">No payments found</h3>
      <p class="text-gray-500">No payments match the current filters.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="bt-table w-full">
        <thead>
          <tr>
            <th>Tenant</th>
            <th>Bill</th>
            <th data-col="amount">Amount Paid</th>
            <th>Date Submitted</th>
            <th>Proof</th>
            <th data-col="status">Status</th>
            <th data-col="actions">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($payments as $payment): ?>
            <tr class="hover:bg-gray-50 transition-colors">
              <td data-label="Tenant">
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="width:32px;height:32px;border-radius:var(--radius);background:var(--primary);color:var(--color-surface);display:flex;align-items:center;justify-content:center;font-weight:600;font-size:0.78rem;flex-shrink:0;">
                    <?= strtoupper(substr($payment['tenant_name'], 0, 1)) ?>
                  </div>
                  <div>
                    <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($payment['tenant_name']) ?></div>
                    <div class="text-xs text-gray-500 font-medium">Room <?= htmlspecialchars($payment['room_number'] ?? 'N/A') ?></div>
                  </div>
                </div>
              </td>
              <td data-label="Bill">
                <div class="flex-center">
                  <?= htmlspecialchars($payment['bill_name']) ?>
                </div>
              </td>
              <td data-label="Amount" data-col="amount" class="text-sm font-bold text-gray-900">
                <div class="flex-center">₱<?= number_format($payment['amount'], 2) ?></div>
              </td>
              <td data-label="Date">
                <div class="flex-center"><?= date('M j, Y', strtotime($payment['payment_date'])) ?></div>
              </td>
              <td data-label="Proof">
                <div class="flex-center">
                  <?php if (!empty($payment['proof_file'])): ?>
                    <button type="button" class="btn btn-secondary btn-sm flex items-center gap-2" onclick="showProofModal('<?= Router::upload('payments', $payment['proof_file']) ?>')">
                      <i class="fa-solid fa-image text-xs"></i> View
                    </button>
                  <?php else: ?>
                    <span class="text-xs text-gray-400">None</span>
                  <?php endif; ?>
                </div>
              </td>
              <td data-label="Status" data-col="status">
                <div class="flex-center">
                  <?php
                    $pBadge = match($payment['status']) {
                      'approved' => 'bg-success-50 text-success-600 border-success-200',
                      'pending'  => 'bg-warning-50 text-warning-600 border-warning-200',
                      'rejected' => 'bg-danger-50 text-danger-600 border-danger-200',
                      default    => 'bg-gray-50 text-gray-600 border-gray-200'
                    };
                  ?>
                  <span class="inline-flex px-2.5 py-1 rounded-full text-[0.7rem] font-bold uppercase tracking-wider border <?= $pBadge ?>">
                    <?= ucfirst($payment['status']) ?>
                  </span>
                </div>
              </td>
              <td data-label="Actions" data-col="actions">
                <div class="flex-center">
                  <a href="<?= Router::url('landlord/view-payment/' . $payment['id']) ?>" class="w-8 h-8 rounded-md bg-white border border-gray-200 text-gray-600 hover:text-brand-600 hover:border-brand-200 flex items-center justify-center transition-all shadow-xs" title="View Details">
                    <i class="fa-solid fa-eye text-xs"></i>
                  </a>
                  <?php if ($payment['status'] === 'pending'): ?>
                    <button type="button" class="w-8 h-8 rounded-md bg-white border border-gray-200 text-success-600 hover:bg-success-50 hover:border-success-200 flex items-center justify-center transition-all shadow-xs" onclick="showApprovePaymentModal(<?= $payment['id'] ?>, '<?= htmlspecialchars(addslashes($payment['tenant_name'])) ?>', <?= $payment['amount'] ?>)" title="Approve">
                      <i class="fa-solid fa-check text-xs"></i>
                    </button>
                    <button type="button" class="w-8 h-8 rounded-md bg-white border border-gray-200 text-danger-600 hover:bg-danger-50 hover:border-danger-200 flex items-center justify-center transition-all shadow-xs" onclick="showRejectPaymentModal(<?= $payment['id'] ?>)" title="Reject">
                      <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Proof Modal -->
<div class="modal-overlay" id="proofModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">Payment Proof</span>
      <button class="modal-close" onclick="closeModal('proofModal')">&times;</button>
    </div>
    <div class="modal-body" style="text-align:center; display: flex; align-items: center; justify-content: center; min-height: 300px;">
      <img id="proofImage" src="" alt="Payment Proof" style="max-width:100%; max-height:70vh; object-fit:contain; border-radius:var(--radius); border:1px solid var(--gray-200);">
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal('proofModal')">Close</button>
    </div>
  </div>
</div>

<script>
function openModal(id) {
  document.getElementById(id).style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
  document.body.style.overflow = '';
}
function showProofModal(src) {
  document.getElementById('proofImage').src = src;
  openModal('proofModal');
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay').forEach(function(m) { m.style.display = 'none'; });
    document.body.style.overflow = '';
  }
});
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.style.display = 'none';
    document.body.style.overflow = '';
  }
});
</script>
