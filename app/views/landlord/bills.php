<?php
/**
 * BoardTrack — Landlord: Billing
 * app/views/landlord/bills.php
 * Layout: landlord.php
 */
$bills         = $bills         ?? [];
$statistics    = $statistics    ?? [];
$filters       = $filters       ?? [];
$activeTenants = $activeTenants ?? [];
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Billing</h1>
      <p class="page-subtitle">Issue and track rent bills for all active tenants.</p>
    </div>
    <button onclick="openModal('createBillModal')" class="btn btn-primary btn-sm">
      <i class="fa-solid fa-plus"></i> Create Bill
    </button>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-file-invoice" style="margin-right:4px;"></i> Total Bills</div>
    <div class="stat-value"><?= $statistics['total_bills'] ?? 0 ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-clock" style="margin-right:4px;"></i> Unpaid</div>
    <div class="stat-value"><?= $statistics['unpaid'] ?? 0 ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-circle-check" style="margin-right:4px;"></i> Paid</div>
    <div class="stat-value"><?= $statistics['paid'] ?? 0 ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-exclamation-circle" style="margin-right:4px;"></i> Overdue</div>
    <div class="stat-value"><?= $statistics['overdue'] ?? 0 ?></div>
    <div class="stat-meta">₱<?= number_format($statistics['total_unpaid'] ?? 0, 2) ?> total unpaid</div>
  </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:16px;padding:14px 20px;">
  <form method="GET" action="<?= Router::url('landlord/bills') ?>" class="filter-bar" style="margin-bottom:0;">
    <select name="status" class="form-select">
      <option value="">All Statuses</option>
      <option value="unpaid" <?= ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
      <option value="pending_verification" <?= ($filters['status'] ?? '') === 'pending_verification' ? 'selected' : '' ?>>Pending Verification</option>
      <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
      <option value="overdue" <?= ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?= Router::url('landlord/bills') ?>" class="btn btn-secondary btn-sm">Clear</a>
  </form>
</div>

<!-- Bills Table -->
<div class="card">
  <?php if (empty($bills)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-file-invoice"></i>
      <h3>No bills found</h3>
      <p>Create a new bill to get started.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="bt-table">
        <thead>
          <tr>
            <th>Bill Name</th>
            <th>Tenant</th>
            <th>Room</th>
            <th>Amount</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bills as $bill): ?>
            <tr>
              <td class="td-name"><?= htmlspecialchars($bill['bill_name']) ?></td>
              <td><?= htmlspecialchars($bill['tenant_name']) ?></td>
              <td style="color:var(--gray-500);"><?= !empty($bill['room_number']) ? htmlspecialchars($bill['room_number']) : '—' ?></td>
              <td style="font-weight:600;color:var(--gray-800);">₱<?= number_format($bill['amount'], 2) ?></td>
              <td style="font-size:0.82rem;color:var(--gray-500);"><?= date('M j, Y', strtotime($bill['due_date'])) ?></td>
              <td>
                <?php
                  $status = $bill['computed_status'] ?? $bill['status'];
                  $badgeClass = match($status) {
                    'paid'                 => 'badge-paid',
                    'unpaid'               => 'badge-unpaid',
                    'pending_verification' => 'badge-pv',
                    'overdue'              => 'badge-overdue',
                    default                => 'badge-normal'
                  };
                ?>
                <span class="badge <?= $badgeClass ?>">
                  <?= ucfirst(str_replace('_', ' ', $status)) ?>
                </span>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:4px;">
                  <form action="<?= Router::url('landlord/delete-bill') ?>" method="POST" data-confirm="Are you sure you want to delete this bill? This cannot be undone.">
                    <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Create Bill Modal -->
<div class="modal-overlay" id="createBillModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">Create New Bill</span>
      <button class="modal-close" onclick="closeModal('createBillModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/create-bill') ?>" method="POST">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Tenant <span class="req">*</span></label>
          <select name="tenant_id" class="form-select" required>
            <option value="">— Select Tenant —</option>
            <?php foreach ($activeTenants as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?> <?= !empty($t['room_number']) ? '(Room ' . htmlspecialchars($t['room_number']) . ')' : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Bill Name <span class="req">*</span></label>
          <input type="text" name="bill_name" class="form-input" required placeholder="e.g., Monthly Rent - January 2026">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Billing Period Start</label>
            <input type="date" name="period_start" class="form-input" value="<?= date('Y-m-01') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Billing Period End</label>
            <input type="date" name="period_end" class="form-input" value="<?= date('Y-m-t') ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Amount (₱) <span class="req">*</span></label>
            <input type="number" name="amount" class="form-input" step="0.01" required placeholder="5000.00">
          </div>
          <div class="form-group">
            <label class="form-label">Due Date <span class="req">*</span></label>
            <input type="date" name="due_date" class="form-input" required>
          </div>
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-textarea" rows="2" placeholder="Optional notes..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('createBillModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Create Bill</button>
      </div>
    </form>
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
