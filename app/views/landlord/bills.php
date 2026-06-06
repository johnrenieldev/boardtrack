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
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
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

  <div class="card p-4 border-blue-200">
    <div class="text-[0.65rem] font-black text-blue-500 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-hourglass-half text-blue-500"></i> Awaiting Review
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $statistics['pending_review'] ?? 0 ?></div>
    <div class="text-[0.6rem] font-bold text-gray-400 mt-2 uppercase tracking-tighter">Payment submitted by tenant</div>
  </div>
</div>

<!-- Filters -->
<div class="card mb-4 p-4">
  <form method="GET" action="index.php" class="filter-bar" style="margin-bottom:0;">
    <input type="hidden" name="url" value="landlord/bills">
    <input type="text" name="search" class="form-input" placeholder="Search tenant or bill name..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>" style="flex: 1; min-width: 200px;">
    <select name="status" class="form-select">
      <option value="">All Statuses</option>
      <option value="unpaid" <?= ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
      <option value="partial" <?= ($filters['status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partially Paid</option>
      <option value="pending_verification" <?= ($filters['status'] ?? '') === 'pending_verification' ? 'selected' : '' ?>>Pending Verification</option>
      <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
      <option value="overdue" <?= ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
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
    <div class="table-wrap">
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
                      'partial' => 'bg-warning-50 text-warning-600 border-warning-200',
                      'overdue' => 'bg-danger-50 text-danger-600 border-danger-200',
                      'pending_verification' => 'bg-blue-50 text-blue-700 border-blue-300',
                      default => 'bg-gray-50 text-gray-600 border-gray-200'
                    };
                    $bLabel = match($bill['status']) {
                      'pending_verification' => 'Payment Submitted — Review Needed',
                      default => str_replace('_', ' ', ucfirst($bill['status']))
                    };
                  ?>
                  <span class="inline-flex px-2.5 py-1 rounded-full text-[0.7rem] font-bold uppercase tracking-wider border <?= $bBadge ?>">
                    <?= $bLabel ?>
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
            <div id="edit_amount_paid_warning" style="display:none;font-size:0.8rem;color:var(--warning-600);padding:8px;background:var(--warning-50);border-radius:var(--radius);margin-top:6px;">
              <i class="fa-solid fa-info-circle"></i> <span id="edit_amount_paid_text"></span> has already been approved for this bill. New amount must be ≥ that value.
            </div>
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
            <option value="partial">Partially Paid</option>
            <!-- pending_verification is set automatically by the system when a tenant submits payment; not available for manual override -->
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
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
            <select name="tenant_id" class="form-select" id="billTenantSelect" required disabled>
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
            <input type="number" name="amount" id="billAmount" class="form-input" step="0.01" min="0.01" max="5000" required placeholder="5000.00">
            <span class="form-help">Maximum: ₱5,000.00</span>
          </div>
          <div class="form-group">
            <label class="form-label">Due Date <span class="req">*</span></label>
            <input type="date" name="due_date" class="form-input" required min="<?= date('Y-m-d') ?>">
            <span class="form-help">Cannot be in the past</span>
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
  
  // Show warning if bill has approved payments
  var amountPaid = parseFloat(bill.amount_paid || 0);
  var warningDiv = document.getElementById('edit_amount_paid_warning');
  var warningText = document.getElementById('edit_amount_paid_text');
  
  if (amountPaid > 0) {
    warningText.textContent = '₱' + amountPaid.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    warningDiv.style.display = 'block';
    
    // Set minimum value on amount input
    document.getElementById('edit_amount').min = amountPaid;
  } else {
    warningDiv.style.display = 'none';
    document.getElementById('edit_amount').removeAttribute('min');
  }
  
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
    roomSelect.disabled = false;
    tenantSelect.required = false;
    tenantSelect.disabled = true; // Disable when hidden to prevent validation
    tenantSelect.value = ''; // Clear tenant selection
  } else {
    roomDiv.style.display = 'none';
    tenantDiv.style.display = 'block';
    roomSelect.required = false;
    roomSelect.disabled = true; // Disable when hidden to prevent validation
    tenantSelect.required = true;
    tenantSelect.disabled = false;
    roomSelect.value = ''; // Clear room selection
  }
}

function prefillRent(select) {
  var opt = select.options[select.selectedIndex];
  var rent = opt && opt.getAttribute('data-rent');
  var amountEl = document.getElementById('billAmount');
  if (rent && amountEl && !amountEl.value) amountEl.value = rent;
}

function confirmDeleteBill(billId) {
  if (!billId) {
    btToast('Invalid bill ID', 'error');
    return;
  }
  
  btConfirm({
    title: 'Delete Bill',
    message: 'Are you sure you want to delete this bill? This action cannot be undone.\n\nNote: If this bill has payments, they will also be deleted.',
    confirmText: 'Yes, Delete',
    cancelText: 'Cancel',
    type: 'danger',
    icon: 'fa-trash-can'
  }).then(confirmed => {
    if (confirmed) {
      // Create a form and submit it
      var form = document.createElement('form');
      form.method = 'POST';
      form.action = '<?= Router::url('landlord/delete-bill') ?>';
      
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'bill_id';
      input.value = billId;
      form.appendChild(input);
      
      // Add CSRF token if available
      var csrfToken = document.querySelector('meta[name="csrf-token"]');
      if (csrfToken) {
        var csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = csrfToken.getAttribute('content');
        form.appendChild(csrfInput);
      }
      
      document.body.appendChild(form);
      form.submit();
    }
  });
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
