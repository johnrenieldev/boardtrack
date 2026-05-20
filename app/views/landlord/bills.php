<?php
/**
 * BoardTrack — Landlord: Billing (room or individual)
 */
$bills         = $bills         ?? [];
$statistics    = $statistics    ?? [];
$filters       = $filters       ?? [];
$billableRooms = $billableRooms ?? [];
$activeTenants = $activeTenants ?? [];
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Billing</h1>
      <p class="page-subtitle">Bill per room (shared by occupants) or individually per tenant — your choice each time.</p>
    </div>
    <button onclick="openModal('createBillModal')" class="btn btn-primary btn-sm">
      <i class="fa-solid fa-plus"></i> Create Bill
    </button>
  </div>
</div>

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

<div class="card" style="margin-bottom:16px;padding:14px 20px;">
  <form method="GET" action="<?= Router::url('landlord/bills') ?>" class="filter-bar" style="margin-bottom:0;">
    <input type="hidden" name="url" value="landlord/bills">
    <select name="status" class="form-select">
      <option value="">All Statuses</option>
      <option value="unpaid" <?= ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
      <option value="pending_verification" <?= ($filters['status'] ?? '') === 'pending_verification' ? 'selected' : '' ?>>Pending Verification</option>
      <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
      <option value="overdue" <?= ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
    <a href="<?= Router::url('landlord/bills') ?>" class="btn btn-ghost btn-sm">Clear</a>
  </form>
</div>

<div class="card">
  <?php if (empty($bills)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-file-invoice"></i>
      <h3>No bills found</h3>
      <p>Create a room bill or an individual tenant bill to get started.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="bt-table">
        <thead>
          <tr>
            <th>Bill Name</th>
            <th>Type</th>
            <th>Room</th>
            <th>Billed To</th>
            <th>Amount</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bills as $bill):
            $isIndividual = ($bill['billing_type'] ?? '') === 'individual';
            $billedTo = $isIndividual
              ? ($bill['billed_tenant_name'] ?? '—')
              : ($bill['room_tenant_names'] ?? 'All occupants');
          ?>
            <tr>
              <td class="td-name"><?= htmlspecialchars($bill['bill_name']) ?></td>
              <td>
                <span class="badge <?= $isIndividual ? 'badge-info' : 'badge-normal' ?>">
                  <?= $isIndividual ? 'Individual' : 'Room' ?>
                </span>
              </td>
              <td style="font-weight:600;color:var(--gray-800);">
                Room <?= htmlspecialchars($bill['room_number'] ?? '—') ?>
              </td>
              <td style="color:var(--gray-500);font-size:0.82rem;"><?= htmlspecialchars($billedTo) ?></td>
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
                <span class="badge <?= $badgeClass ?>"><?= ucfirst(str_replace('_', ' ', $status)) ?></span>
              </td>
              <td>
                <form action="<?= Router::url('landlord/delete-bill') ?>" method="POST" data-confirm="Delete this bill? This cannot be undone.">
                  <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete"><i class="fa-solid fa-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<div class="modal-overlay" id="createBillModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">Create New Bill</span>
      <button class="modal-close" onclick="closeModal('createBillModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/create-bill') ?>" method="POST" id="createBillForm" class="confirm-form"
          data-action="Create bill" data-message="Create this bill and notify the affected tenant(s)?">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Billing Type <span class="req">*</span></label>
          <div style="display:block;">
            <div style="display:flex;gap:12px;align-items:center;margin-bottom:8px;">
              <input type="radio" name="billing_type" value="room_based" checked id="modal_billing_room" onchange="toggleBillingType('room_based')" style="width:18px;height:18px;cursor:pointer;">
              <label for="modal_billing_room" style="cursor:pointer;padding:6px 10px;background:var(--gray-50);border:1px solid var(--gray-200);border-radius:4px;flex:1;">
                <strong>Per Room</strong> — one bill per room
              </label>
            </div>
            <div style="display:flex;gap:12px;align-items:center;">
              <input type="radio" name="billing_type" value="individual" id="modal_billing_individual" onchange="toggleBillingType('individual')" style="width:18px;height:18px;cursor:pointer;">
              <label for="modal_billing_individual" style="cursor:pointer;padding:6px 10px;background:var(--gray-50);border:1px solid var(--gray-200);border-radius:4px;flex:1;">
                <strong>Individual</strong> — one bill per tenant
              </label>
            </div>
          </div>
        </div>
        <div id="billTargetRoom">
          <div class="form-group">
            <label class="form-label">Room <span class="req">*</span></label>
            <select name="room_id" class="form-select" id="billRoomSelect" onchange="prefillRent(this)" required>
              <option value="">— Select Room —</option>
              <?php foreach ($billableRooms as $room): ?>
                <option value="<?= $room['id'] ?>" data-rent="<?= htmlspecialchars((string) ($room['monthly_rent'] ?? '')) ?>">
                  Room <?= htmlspecialchars($room['room_number']) ?>
                  (<?= (int) ($room['occupant_count'] ?? 0) ?> tenant<?= ((int) ($room['occupant_count'] ?? 0)) !== 1 ? 's' : '' ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div id="billTargetTenant" style="display:none;">
          <div class="form-group">
            <label class="form-label">Tenant <span class="req">*</span></label>
            <select name="tenant_id" class="form-select" id="billTenantSelect" required>
              <option value="">— Select Tenant —</option>
              <?php foreach ($activeTenants as $tenant): ?>
                <option value="<?= $tenant['id'] ?>">
                  <?= htmlspecialchars($tenant['name'] ?? '') ?>
                  <?php if (!empty($tenant['room_number'])): ?>
                    (Room <?= htmlspecialchars($tenant['room_number']) ?>)
                  <?php endif; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Bill Name <span class="req">*</span></label>
            <input type="text" name="bill_name" class="form-input" required placeholder="e.g., Monthly Rent - January 2026">
          </div>
          <div class="form-group">
            <label class="form-label">Category</label>
            <select name="charge_category" class="form-select">
              <option value="rent">Rent</option>
              <option value="utility">Utility</option>
              <option value="maintenance">Maintenance</option>
              <option value="penalty">Penalty</option>
              <option value="other">Other</option>
            </select>
          </div>
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
            <input type="number" name="amount" id="billAmount" class="form-input" step="0.01" required placeholder="5000.00">
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

function toggleBillingType(type) {
  var roomDiv = document.getElementById('billTargetRoom');
  var tenantDiv = document.getElementById('billTargetTenant');
  var roomSelect = document.getElementById('billRoomSelect');
  var tenantSelect = document.getElementById('billTenantSelect');
  
  if (type === 'room_based') {
    roomDiv.style.display = 'block';
    tenantDiv.style.display = 'none';
    roomSelect.required = true;
    tenantSelect.required = false;
  } else {
    roomDiv.style.display = 'none';
    tenantDiv.style.display = 'block';
    roomSelect.required = false;
    tenantSelect.required = true;
  }
}

function prefillRent(select) {
  var opt = select.options[select.selectedIndex];
  var rent = opt && opt.getAttribute('data-rent');
  var amountEl = document.getElementById('billAmount');
  if (rent && amountEl && !amountEl.value) amountEl.value = rent;
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
