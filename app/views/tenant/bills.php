<?php
/**
 * BoardTrack — Tenant: My Bills
 * app/views/tenant/bills.php
 * Layout: tenant.php
 */
$bills         = $bills         ?? [];
$statistics    = $statistics    ?? [];
$landlordGcash = $landlordGcash ?? ['has_qr' => false, 'qr_url' => null, 'landlord_name' => 'Landlord'];
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">My Bills</h1>
      <p class="page-subtitle">Room bills (shared with roommates) and individual bills assigned only to you.</p>
    </div>
  </div>
</div>

<!-- Statistics -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-file-invoice text-danger-500"></i> Unpaid Bills
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $statistics['unpaid'] ?? 0 ?></div>
  </div>
  
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-clock text-warning-500"></i> Pending
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $statistics['pending'] ?? 0 ?></div>
    <div class="text-[0.6rem] font-bold text-gray-400 mt-2 uppercase tracking-tighter">Awaiting verification</div>
  </div>
  
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-circle-check text-success-500"></i> Paid
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $statistics['paid'] ?? 0 ?></div>
  </div>
  
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-hand-holding-dollar text-brand-500"></i> Total Due
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none">₱<?= number_format($statistics['total_unpaid'] ?? 0, 0) ?></div>
  </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
  <select class="form-select" id="statusFilter" onchange="filterBills(this.value)">
    <option value="all">All Statuses</option>
    <option value="unpaid">Unpaid</option>
    <option value="pending_verification">Pending Verification</option>
    <option value="paid">Paid</option>
    <option value="overdue">Overdue</option>
  </select>
</div>

<!-- Bills Table -->
<div class="card">
  <?php if (!empty($noRoom)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-door-closed"></i>
      <h3>No Room Assigned</h3>
      <p>Bills are issued per room. You will see bills here once a room is assigned to you.</p>
    </div>
  <?php elseif (empty($bills)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-file-invoice-dollar"></i>
      <h3>No Bills Yet</h3>
      <p>You don't have any bills at the moment.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="bt-table" id="billsTable">
        <thead>
          <tr>
            <th>Bill</th>
            <th>Type</th>
            <th>Period</th>
            <th data-col="amount">Amount</th>
            <th>Due Date</th>
            <th data-col="status">Status</th>
            <th data-col="actions">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bills as $bill):
            $status = $bill['computed_status'] ?? $bill['status'];
            $badgeMap = [
              'unpaid' => 'badge-unpaid',
              'paid' => 'badge-paid',
              'overdue' => 'badge-overdue',
              'pending_verification' => 'badge-pv',
            ];
          ?>
            <tr data-status="<?= htmlspecialchars($status) ?>">
            <td data-label="Bill">
              <div class="td-name"><?= htmlspecialchars($bill['bill_name']) ?></div>
              <?php if (!empty($bill['notes'])): ?>
                <div class="td-sub"><?= htmlspecialchars($bill['notes']) ?></div>
              <?php endif; ?>
            </td>
            <td data-label="Type">
              <span class="badge <?= ($bill['billing_type'] ?? '') === 'individual' ? 'badge-info' : 'badge-normal' ?>">
                <?= ($bill['billing_type'] ?? '') === 'individual' ? 'Individual' : 'Room' ?>
              </span>
            </td>
            <td data-label="Period" style="color:var(--gray-500);font-size:0.82rem;"><?= htmlspecialchars(($bill['billing_period_start'] ?? '') . ' — ' . ($bill['billing_period_end'] ?? '')) ?></td>
            <td data-label="Amount" data-col="amount" style="font-weight:600;">₱<?= number_format($bill['amount'], 2) ?></td>
            <td data-label="Due Date" style="color:var(--gray-500);"><?= date('M d, Y', strtotime($bill['due_date'])) ?></td>
            <td data-label="Status" data-col="status"><span class="badge <?= $badgeMap[$status] ?? 'badge-normal' ?>"><?= ucfirst(str_replace('_', ' ', $status)) ?></span></td>
            <td data-label="Action" data-col="actions">
              <div class="flex items-center justify-end">
                <?php if ($status === 'unpaid' || $status === 'overdue'): ?>
                  <button type="button" class="btn btn-primary btn-sm" onclick="openPaymentModal(<?= $bill['id'] ?>, '<?= htmlspecialchars(addslashes($bill['bill_name'])) ?>', <?= $bill['amount'] ?>, '<?= htmlspecialchars($bill['due_date']) ?>')">
                    <i class="fa-solid fa-upload text-xs"></i> Pay Now
                  </button>
                <?php elseif ($status === 'pending_verification'): ?>
                  <span style="font-size:0.78rem;color:var(--gray-400); font-weight: 700;"><i class="fa-solid fa-clock"></i> VERIFYING</span>
                <?php elseif ($status === 'paid'): ?>
                  <span style="font-size:0.78rem;color:var(--success); font-weight: 700;"><i class="fa-solid fa-check-circle"></i> PAID<?php if (!empty($bill['paid_at'])): ?> <?= date('M d', strtotime($bill['paid_at'])) ?><?php endif; ?></span>
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

<!-- Payment Modal -->
<div class="modal-overlay" id="paymentModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <div class="modal-title">Pay Bill</div>
      <button class="modal-close" onclick="closeModal('paymentModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="<?= Router::url('tenant/submit-payment') ?>" method="POST" enctype="multipart/form-data" class="confirm-form"
          data-action="Submit payment" data-message="Submit this payment receipt to your landlord for verification?">
      <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
      <input type="hidden" name="bill_id" id="paymentBillId">
      <div class="modal-body">
        <div style="background:var(--gray-50);padding:14px 16px;border-radius:var(--radius);margin-bottom:16px;">
          <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
            <span style="color:var(--gray-500);font-size:0.82rem;">Bill</span>
            <strong id="paymentBillName" style="font-size:0.85rem;color:var(--gray-900);"></strong>
          </div>
          <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
            <span style="color:var(--gray-500);font-size:0.82rem;">Amount</span>
            <strong id="paymentAmount" style="font-size:0.85rem;color:var(--gray-900);"></strong>
          </div>
          <div style="display:flex;justify-content:space-between;">
            <span style="color:var(--gray-500);font-size:0.82rem;">Due Date</span>
            <strong id="paymentDueDate" style="font-size:0.85rem;color:var(--gray-900);"></strong>
          </div>
        </div>
        <?php require APP_PATH . '/views/tenant/partials/payment_fields.php'; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('paymentModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload"></i> Submit Payment</button>
      </div>
    </form>
  </div>
</div>

<script>
function openPaymentModal(billId, billName, amount, dueDate) {
  document.getElementById('paymentBillId').value = billId;
  document.getElementById('paymentBillName').textContent = billName;
  document.getElementById('paymentAmount').textContent = '₱' + amount.toLocaleString('en-PH', {minimumFractionDigits: 2});
  document.getElementById('paymentDueDate').textContent = dueDate;
  var method = document.getElementById('payment_method');
  if (method) method.value = '';
  var fileInput = document.getElementById('paymentFile');
  if (fileInput) fileInput.value = '';
  if (typeof togglePaymentMethod === 'function') togglePaymentMethod();
  var preview = document.getElementById('receiptPreview');
  if (preview) preview.style.display = 'none';
  document.getElementById('paymentModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeModal(id) {
  document.getElementById(id).style.display = 'none';
  document.body.style.overflow = '';
}


function filterBills(status) {
  var rows = document.querySelectorAll('#billsTable tbody tr');
  rows.forEach(function(row) {
    if (status === 'all' || row.getAttribute('data-status') === status) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}

// Modal close on overlay click or Escape
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.style.display = 'none';
    document.body.style.overflow = '';
  }
});
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay').forEach(function(m) {
      m.style.display = 'none';
    });
    document.body.style.overflow = '';
  }
});

// Drag and drop
var dropZone = document.getElementById('uploadZone');
if (dropZone) {
  dropZone.addEventListener('dragover', function(e) { e.preventDefault(); dropZone.style.borderColor = 'var(--primary)'; dropZone.style.background = 'var(--primary-light)'; });
  dropZone.addEventListener('dragleave', function() { dropZone.style.borderColor = ''; dropZone.style.background = ''; });
  dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    dropZone.style.borderColor = '';
    dropZone.style.background = '';
    if (e.dataTransfer.files.length) {
      document.getElementById('paymentFile').files = e.dataTransfer.files;
      updateFileName(document.getElementById('paymentFile'));
    }
  });
}
</script>
