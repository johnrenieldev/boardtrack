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

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-file-invoice text-brand-500"></i> Total Bills
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $statistics['total_bills'] ?? 0 ?></div>
  </div>
  
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-clock text-warning-500"></i> Unpaid
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $statistics['unpaid'] ?? 0 ?></div>
  </div>
  
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-circle-check text-success-500"></i> Paid
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $statistics['paid'] ?? 0 ?></div>
  </div>
  
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-circle-exclamation text-danger-500"></i> Overdue
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $statistics['overdue'] ?? 0 ?></div>
    <div class="text-[0.6rem] font-bold text-gray-400 mt-2 uppercase tracking-tighter">₱<?= number_format($statistics['total_unpaid'] ?? 0, 2) ?> total unpaid</div>
  </div>
</div>

<!-- Filters -->
<div class="card mb-4 p-4">
  <form method="GET" action="<?= Router::url('landlord/bills') ?>" class="filter-bar" style="margin-bottom:0;">
    <input type="hidden" name="url" value="landlord/bills">
    <select name="status" class="form-select">
      <option value="">All Statuses</option>
      <option value="unpaid" <?= ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
      <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
      <option value="overdue" <?= ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
      <option value="pending_verification" <?= ($filters['status'] ?? '') === 'pending_verification' ? 'selected' : '' ?>>Verification Pending</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?= Router::url('landlord/bills') ?>" class="btn btn-secondary btn-sm">Clear</a>
  </form>
</div>

<!-- Bills Table -->
<div class="card overflow-hidden">
  <?php if (empty($bills)): ?>
    <div class="p-12 text-center">
      <i class="fa-solid fa-file-invoice-dollar text-5xl text-gray-200 mb-4"></i>
      <h3 class="text-lg font-bold text-gray-900">No bills found</h3>
      <p class="text-gray-500">No bills match the current filters or have been created yet.</p>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="bt-table w-full">
        <thead>
          <tr>
            <th>Bill Name</th>
            <th>Tenant / Room</th>
            <th data-col="amount">Amount</th>
            <th>Due Date</th>
            <th data-col="status">Status</th>
            <th data-col="actions">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($bills as $bill): ?>
            <tr class="hover:bg-gray-50 transition-colors">
              <td data-label="Bill Name" class="text-sm font-bold text-gray-900"><?= htmlspecialchars($bill['bill_name']) ?></td>
              <td data-label="Tenant / Room">
                <div class="flex-center">
                  <div style="text-align:center;">
                    <div class="text-sm font-medium text-gray-700">
                      <?= !empty($bill['tenant_name']) ? htmlspecialchars($bill['tenant_name']) : '<span class="text-gray-400 italic">No active tenants</span>' ?>
                    </div>
                    <div class="text-xs text-gray-500">Room <?= htmlspecialchars($bill['room_number'] ?? 'N/A') ?></div>
                  </div>
                </div>
              </td>
              <td data-label="Amount" data-col="amount" class="text-sm font-bold text-gray-900">
                <div class="flex-center">₱<?= number_format($bill['amount'], 2) ?></div>
              </td>
              <td data-label="Due Date">
                <div class="flex-center"><?= date('M j, Y', strtotime($bill['due_date'])) ?></div>
              </td>
              <td data-label="Status" data-col="status">
                <div class="flex-center">
                  <?php
                    $bBadge = match($bill['status']) {
                      'paid' => 'bg-success-50 text-success-600 border-success-200',
                      'unpaid' => 'bg-gray-50 text-gray-600 border-gray-200',
                      'overdue' => 'bg-danger-50 text-danger-600 border-danger-200',
                      'pending_verification' => 'bg-warning-50 text-warning-600 border-warning-200',
                      default => 'bg-gray-50 text-gray-600 border-gray-200'
                    };
                  ?>
                  <span class="inline-flex px-2.5 py-1 rounded-full text-[0.7rem] font-bold uppercase tracking-wider border <?= $bBadge ?>">
                    <?= str_replace('_', ' ', ucfirst($bill['status'])) ?>
                  </span>
                </div>
              </td>
              <td data-label="Actions" data-col="actions">
                <div class="flex-center">
                  <button type="button" class="w-8 h-8 rounded-md bg-white border border-gray-200 text-brand-600 hover:bg-brand-50 hover:border-brand-200 flex items-center justify-center transition-all shadow-xs" onclick="openEditBillModal(<?= htmlspecialchars(json_encode($bill)) ?>)" title="Edit Bill">
                    <i class="fa-solid fa-pen text-xs"></i>
                  </button>
                  <button type="button" class="w-8 h-8 rounded-md bg-white border border-gray-200 text-danger-600 hover:bg-danger-50 hover:border-danger-200 flex items-center justify-center transition-all shadow-xs" onclick="confirmDeleteBill(<?= $bill['id'] ?>)" title="Delete Bill">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<div class="modal-overlay" id="editBillModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">Edit Bill</span>
      <button class="modal-close" onclick="closeModal('editBillModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/update-bill') ?>" method="POST" id="editBillForm">
      <div class="modal-body">
        <input type="hidden" name="bill_id" id="edit_bill_id">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Bill Name <span class="req">*</span></label>
            <input type="text" name="bill_name" id="edit_bill_name" class="form-input" required>
          </div>
          <div class="form-group">
            <label class="form-label">Category</label>
            <select name="charge_category" id="edit_charge_category" class="form-select">
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
            <input type="date" name="period_start" id="edit_period_start" class="form-input">
          </div>
          <div class="form-group">
            <label class="form-label">Billing Period End</label>
            <input type="date" name="period_end" id="edit_period_end" class="form-input">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Amount (₱) <span class="req">*</span></label>
            <input type="number" name="amount" id="edit_amount" class="form-input" step="0.01" required>
          </div>
          <div class="form-group">
            <label class="form-label">Due Date <span class="req">*</span></label>
            <input type="date" name="due_date" id="edit_due_date" class="form-input" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" id="edit_status" class="form-select">
            <option value="unpaid">Unpaid</option>
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
            <option value="pending_verification">Pending Verification</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Notes</label>
          <textarea name="notes" id="edit_notes" class="form-textarea" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('editBillModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Bill</button>
      </div>
    </form>
  </div>
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

function openEditBillModal(bill) {
  document.getElementById('edit_bill_id').value = bill.id;
  document.getElementById('edit_bill_name').value = bill.bill_name;
  document.getElementById('edit_charge_category').value = bill.charge_category;
  document.getElementById('edit_period_start').value = bill.billing_period_start;
  document.getElementById('edit_period_end').value = bill.billing_period_end;
  document.getElementById('edit_amount').value = bill.amount;
  document.getElementById('edit_due_date').value = bill.due_date;
  document.getElementById('edit_status').value = bill.status;
  document.getElementById('edit_notes').value = bill.notes || '';
  openModal('editBillModal');
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
