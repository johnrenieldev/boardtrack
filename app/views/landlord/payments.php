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
    <div class="stat-label"><i class="fa-solid fa-credit-card" style="margin-right:4px;"></i> Total</div>
    <div class="stat-value"><?= $statistics['total'] ?? 0 ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-clock" style="margin-right:4px;"></i> Pending</div>
    <div class="stat-value"><?= $statistics['pending'] ?? 0 ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-circle-check" style="margin-right:4px;"></i> Approved</div>
    <div class="stat-value"><?= $statistics['approved'] ?? 0 ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-circle-xmark" style="margin-right:4px;"></i> Rejected</div>
    <div class="stat-value"><?= $statistics['rejected'] ?? 0 ?></div>
  </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:16px;padding:14px 20px;">
  <form method="GET" action="<?= Router::url('landlord/payments') ?>" class="filter-bar" style="margin-bottom:0;">
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
<div class="card">
  <?php if (empty($payments)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-credit-card"></i>
      <h3>No payments found</h3>
      <p>No payments match the current filters.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="bt-table">
        <thead>
          <tr>
            <th>Tenant</th>
            <th>Bill</th>
            <th>Amount Paid</th>
            <th>Date Submitted</th>
            <th>Proof</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $payment): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="width:32px;height:32px;border-radius:var(--radius);background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:0.78rem;flex-shrink:0;">
                    <?= strtoupper(substr($payment['tenant_name'], 0, 1)) ?>
                  </div>
                  <div>
                    <div class="td-name"><?= htmlspecialchars($payment['tenant_name']) ?></div>
                    <div class="td-sub">Room <?= htmlspecialchars($payment['room_number'] ?? 'N/A') ?></div>
                  </div>
                </div>
              </td>
              <td><?= htmlspecialchars($payment['bill_name']) ?></td>
              <td style="font-weight:600;color:var(--gray-800);">₱<?= number_format($payment['amount'], 2) ?></td>
              <td style="font-size:0.82rem;color:var(--gray-500);"><?= date('M j, Y', strtotime($payment['payment_date'])) ?></td>
              <td>
                <?php if (!empty($payment['proof_file'])): ?>
                  <button type="button" class="btn btn-secondary btn-sm" onclick="showProofModal('<?= Router::upload('payments', $payment['proof_file']) ?>')">
                    <i class="fa-solid fa-image"></i> View
                  </button>
                <?php else: ?>
                  <span style="color:var(--gray-400);font-size:0.82rem;">None</span>
                <?php endif; ?>
              </td>
              <td>
                <?php
                  $pBadge = match($payment['status']) {
                    'approved' => 'badge-approved',
                    'pending'  => 'badge-pending',
                    'rejected' => 'badge-rejected',
                    default    => 'badge-normal'
                  };
                ?>
                <span class="badge <?= $pBadge ?>">
                  <?= ucfirst($payment['status']) ?>
                </span>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:4px;">
                  <a href="<?= Router::url('landlord/view-payment/' . $payment['id']) ?>" class="btn btn-secondary btn-sm btn-icon" title="View Details">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                  <?php if ($payment['status'] === 'pending'): ?>
                    <button type="button" class="btn btn-success btn-sm btn-icon" onclick="showApprovePaymentModal(<?= $payment['id'] ?>, '<?= htmlspecialchars(addslashes($payment['tenant_name'])) ?>', <?= $payment['amount'] ?>)" title="Approve">
                      <i class="fa-solid fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="showRejectPaymentModal(<?= $payment['id'] ?>)" title="Reject">
                      <i class="fa-solid fa-xmark"></i>
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
    <div class="modal-body" style="text-align:center;">
      <img id="proofImage" src="" alt="Payment Proof" style="max-width:100%;border-radius:var(--radius);border:1px solid var(--gray-200);">
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
