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

<!-- Statistics - Horizontal Scroll on Mobile -->
<div class="stats-scroll-container mb-6" onclick="event.stopPropagation(); return false;">
  <div class="card p-4 stat-card-scroll" onclick="event.stopPropagation(); event.preventDefault(); return false;">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-file-invoice" style="color: var(--color-danger);"></i> Unpaid Bills
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $statistics['unpaid'] ?? 0 ?></div>
  </div>
  
  <div class="card p-4 stat-card-scroll" onclick="event.stopPropagation(); event.preventDefault(); return false;">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-coins" style="color: var(--color-warning);"></i> Partial
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $statistics['partial'] ?? 0 ?></div>
    <div class="text-[0.6rem] font-bold text-gray-400 mt-2 uppercase tracking-tighter">Partially paid</div>
  </div>
  
  <div class="card p-4 stat-card-scroll" onclick="event.stopPropagation(); event.preventDefault(); return false;">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-clock" style="color: var(--color-warning);"></i> Pending
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $statistics['pending'] ?? 0 ?></div>
    <div class="text-[0.6rem] font-bold text-gray-400 mt-2 uppercase tracking-tighter">Awaiting verification</div>
  </div>
  
  <div class="card p-4 stat-card-scroll" onclick="event.stopPropagation(); event.preventDefault(); return false;">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-circle-check" style="color: var(--color-success);"></i> Paid
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $statistics['paid'] ?? 0 ?></div>
  </div>
  
  <div class="card p-4 stat-card-scroll" onclick="event.stopPropagation(); event.preventDefault(); return false;">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-hand-holding-dollar" style="color: var(--color-brand);"></i> Total Due
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none">₱<?= number_format($statistics['total_unpaid'] ?? 0, 0) ?></div>
  </div>
</div>

<!-- Filter Bar -->
<div class="card mb-4 p-4">
  <div class="filter-bar" style="margin-bottom:0;">
    <select class="form-select" id="statusFilter">
      <option value="all">All Statuses</option>
      <option value="unpaid">Unpaid</option>
      <option value="partial">Partially Paid</option>
      <option value="pending_verification">Pending Verification</option>
      <option value="paid">Paid</option>
      <option value="overdue">Overdue</option>
    </select>
    <button type="button" class="btn btn-primary btn-sm" onclick="applyFilter()">
      Filter
    </button>
    <button type="button" class="btn btn-secondary btn-sm" onclick="clearFilter()">
      <i class="fa-solid fa-times"></i> Clear
    </button>
  </div>
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
              'partial' => 'badge-warning',
              'overdue' => 'badge-overdue',
              'pending_verification' => 'badge-pv',
            ];
            
            // Calculate remaining balance - ONLY approved payments count
            $billTotal = (float)($bill['amount'] ?? 0);
            $amountPaid = (float)($bill['amount_paid'] ?? 0); // Only approved payments
            $remainingBalance = max(0, $billTotal - $amountPaid);
            
            // Determine display states
            $isFullyPaid = ($status === 'paid'); // Only consider fully paid if status is explicitly 'paid'
            $isPartial = ($status === 'partial') && $remainingBalance > 0;
            $isPendingVerification = ($status === 'pending_verification');
            $canPay = in_array($status, ['unpaid', 'partial', 'overdue']) && !$isPendingVerification;
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
            <td data-label="Amount" data-col="amount">
              <?php if ($isPartial || $isPendingVerification): ?>
                <div>
                  <span style="font-weight: 700; color: var(--danger); white-space: nowrap; font-size: 0.95rem;">₱<?= number_format($remainingBalance, 2) ?></span>
                  <span style="font-size: 0.7rem; color: var(--gray-400); white-space: nowrap;">Balance</span>
                </div>
              <?php elseif ($isFullyPaid): ?>
                <div>
                  <span style="font-size: 0.95rem; color: var(--success); white-space: nowrap;">₱<?= number_format($billTotal, 2) ?></span>
                  <span style="font-size: 0.7rem; color: var(--gray-400); white-space: nowrap;">Fully Paid</span>
                </div>
              <?php else: ?>
                <span style="white-space: nowrap; font-size: 0.95rem;">₱<?= number_format($billTotal, 2) ?></span>
              <?php endif; ?>
            </td>
            <td data-label="Due Date" style="color:var(--gray-500);"><?= date('M d, Y', strtotime($bill['due_date'])) ?></td>
            <td data-label="Status" data-col="status">
              <span class="badge <?= $badgeMap[$status] ?? 'badge-normal' ?>">
                <?= $isPendingVerification ? 'Awaiting Approval' : ucfirst(str_replace('_', ' ', $status)) ?>
              </span>
            </td>
            <td data-label="Action" data-col="actions">
              <div class="flex items-center justify-center gap-2">
                <?php if ($isPendingVerification): ?>
                  <span style="font-size:0.7rem;color:var(--warning-600); font-weight: 700;">
                    <i class="fa-solid fa-clock"></i> PENDING REVIEW
                  </span>
                <?php elseif ($canPay): ?>
                  <button type="button" class="btn btn-primary btn-sm" onclick="openPaymentModal(<?= $bill['id'] ?>, '<?= htmlspecialchars(addslashes($bill['bill_name'])) ?>', <?= $remainingBalance ?>, '<?= htmlspecialchars($bill['due_date']) ?>')">
                    <i class="fa-solid fa-upload text-xs"></i> <?= $isPartial ? 'Pay Remaining' : 'Pay Now' ?>
                  </button>
                <?php elseif ($isFullyPaid): ?>
                  <span style="font-size:0.7rem;color:var(--success); font-weight: 700;">
                    <i class="fa-solid fa-check-circle"></i> PAID<?php if (!empty($bill['paid_at'])): ?> <?= date('M d', strtotime($bill['paid_at'])) ?><?php endif; ?>
                  </span>
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
  
  // Update amount_paid field with correct max and value (remaining balance)
  var amountField = document.getElementById('amount_paid');
  if (amountField) {
    amountField.max = amount;
    amountField.value = amount;
  }
  
  // Update the help text to reflect the correct remaining balance
  var helpEl = document.getElementById('amountHelp');
  if (helpEl) {
    helpEl.textContent = 'You can pay any amount up to ₱' + parseFloat(amount).toFixed(2);
  }
  
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

function applyFilter() {
  var status = document.getElementById('statusFilter').value;
  filterBills(status);
}

function clearFilter() {
  document.getElementById('statusFilter').value = 'all';
  filterBills('all');
}

function filterBills(status) {
  // Filter table rows
  var rows = document.querySelectorAll('#billsTable tbody tr');
  var visibleCount = 0;
  rows.forEach(function(row) {
    if (status === 'all' || row.getAttribute('data-status') === status) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });
  
  // Show empty state if no results
  var emptyState = document.getElementById('filterEmptyState');
  if (visibleCount === 0 && !emptyState) {
    var tbody = document.querySelector('#billsTable tbody');
    if (tbody) {
      var emptyRow = document.createElement('tr');
      emptyRow.id = 'filterEmptyState';
      emptyRow.innerHTML = '<td colspan="7" style="text-align:center;padding:40px;color:var(--gray-500);"><i class="fa-solid fa-filter" style="font-size:2rem;margin-bottom:12px;display:block;opacity:0.3;"></i>No bills found with this filter</td>';
      tbody.appendChild(emptyRow);
    }
  } else if (visibleCount > 0 && emptyState) {
    emptyState.remove();
  }
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

<style>
/* Table column width optimization for better alignment */
.bt-table th,
.bt-table td {
  padding: 10px 12px !important;
  vertical-align: middle !important;
  font-size: 0.875rem !important;
}

.bt-table th {
  font-size: 0.8rem !important;
  font-weight: 600 !important;
}

/* Specific column widths for better alignment */
.bt-table th:nth-child(1),
.bt-table td:nth-child(1) {
  width: 16%;
  min-width: 130px;
}

.bt-table th:nth-child(2),
.bt-table td:nth-child(2) {
  width: 10%;
  min-width: 90px;
  text-align: center;
}

.bt-table th:nth-child(3),
.bt-table td:nth-child(3) {
  width: 18%;
  min-width: 150px;
  font-size: 0.8rem !important;
  text-align: center;
}

.bt-table th:nth-child(4),
.bt-table td:nth-child(4) {
  width: 13%;
  min-width: 110px;
  text-align: center;
}

.bt-table th:nth-child(5),
.bt-table td:nth-child(5) {
  width: 11%;
  min-width: 100px;
  font-size: 0.8rem !important;
  text-align: center;
}

.bt-table th:nth-child(6),
.bt-table td:nth-child(6) {
  width: 10%;
  min-width: 90px;
  text-align: center;
}

.bt-table th:nth-child(7),
.bt-table td:nth-child(7) {
  width: 15%;
  min-width: 130px;
  text-align: center;
}

/* Make bill names more compact */
.bt-table .td-name {
  font-size: 0.875rem !important;
  font-weight: 600 !important;
  line-height: 1.3 !important;
}

.bt-table .td-sub {
  font-size: 0.75rem !important;
  color: var(--gray-500) !important;
  margin-top: 2px !important;
}

/* Make amount column more compact and centered */
.bt-table td[data-col="amount"] {
  padding: 8px 12px !important;
  text-align: center !important;
}

.bt-table td[data-col="amount"] > div {
  gap: 1px !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
}

.bt-table td[data-col="amount"] span {
  line-height: 1.3 !important;
}

/* Make status badges more compact */
.bt-table td[data-col="status"] .badge {
  padding: 4px 10px !important;
  font-size: 0.7rem !important;
}

/* Make action buttons smaller and more compact */
.bt-table td[data-col="actions"] {
  padding: 8px 12px !important;
}

.bt-table td[data-col="actions"] .btn {
  padding: 5px 10px !important;
  font-size: 0.7rem !important;
  white-space: nowrap !important;
}

.bt-table td[data-col="actions"] .btn i {
  font-size: 0.65rem !important;
  margin-right: 4px !important;
}

.bt-table td[data-col="actions"] span {
  font-size: 0.7rem !important;
}

/* Make type badges more compact */
.bt-table td[data-label="Type"] .badge {
  font-size: 0.7rem !important;
  padding: 3px 8px !important;
}

/* Mobile card fixes - prevent vertical text */
@media (max-width: 767px) {
  /* Create a scrollable container for cards on mobile */
  .card {
    max-height: calc(100vh - 280px) !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    -webkit-overflow-scrolling: touch;
  }
  
  /* Force table to be full width and prevent horizontal scroll */
  .table-wrap {
    overflow-x: visible !important;
    overflow-y: visible !important;
    width: 100% !important;
    max-width: 100% !important;
  }
  
  .bt-table {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    table-layout: auto !important;
  }
  
  .bt-table tbody tr {
    width: 100% !important;
    max-width: 100% !important;
    margin-bottom: 16px !important;
  }
  
  /* Add padding to last card so it's visible in scroll container */
  .bt-table tbody tr:last-child {
    margin-bottom: 20px !important;
  }
  
  /* Remove all column width constraints on mobile */
  .bt-table th,
  .bt-table td {
    width: auto !important;
    min-width: 0 !important;
    max-width: 100% !important;
  }
  
  /* Fix mobile card layout - reduce label width and gap */
  .bt-table td[data-label]:not(:first-child)::before {
    width: 85px !important;
    min-width: 85px !important;
    flex-shrink: 0 !important;
    padding-right: 0 !important;
  }
  
  .bt-table td[data-label]:not(:first-child) {
    display: flex !important;
    justify-content: flex-start !important;
    align-items: center !important;
    text-align: left !important;
    gap: 12px !important;
  }
  
  /* Override global dashboard.css word-break that causes vertical text */
  .bt-table,
  .bt-table th,
  .bt-table td,
  .bt-table th *,
  .bt-table td * {
    word-break: normal !important;
    overflow-wrap: normal !important;
    word-wrap: normal !important;
    white-space: normal !important;
  }
  
  /* Allow wrapping only for long bill names */
  .bt-table td:first-child .td-name {
    word-break: break-word !important;
    overflow-wrap: break-word !important;
  }
  
  /* Fix amount display - prevent vertical stacking */
  .bt-table td[data-col="amount"] span {
    display: block !important;
    white-space: nowrap !important;
    word-break: keep-all !important;
  }
  
  /* Remove the white box (empty badge container) from mobile cards */
  .bt-table td:first-child > div > div:empty {
    display: none !important;
  }
  
  /* Ensure status badges don't create white boxes */
  .bt-table td[data-col="status"] {
    min-height: auto !important;
  }
  
  /* Fix card header layout - remove white box */
  .bt-table tbody tr {
    position: relative !important;
  }
  
  .bt-table td:first-child {
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
  }
  
  /* Ensure proper text rendering */
  .bt-table td[data-label]::before {
    white-space: nowrap !important;
    word-break: keep-all !important;
  }
  
  /* Make mobile cards more compact */
  .bt-table td {
    padding: 10px 14px !important;
  }
  
  .bt-table td:first-child {
    padding: 14px 16px !important;
  }
  
  /* Ensure action buttons fit properly */
  .bt-table td[data-col="actions"] {
    justify-content: center !important;
    text-align: center !important;
  }
  
  .bt-table td[data-col="actions"]::before {
    display: none !important;
  }
  
  .bt-table td[data-col="actions"] .btn {
    width: auto !important;
    justify-content: center !important;
    margin: 0 auto !important;
  }
  
  .bt-table td[data-col="actions"] > div {
    width: 100% !important;
    justify-content: center !important;
  }
  
  /* Style the scrollbar for better UX */
  .card::-webkit-scrollbar {
    width: 6px;
  }
  
  .card::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
  }
  
  .card::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
  }
  
  .card::-webkit-scrollbar-thumb:hover {
    background: #555;
  }
}

/* Horizontal Scrolling Stats Container */
.stats-scroll-container {
  display: flex;
  gap: 12px;
  overflow-x: auto;
  overflow-y: hidden;
  padding: 4px 0 12px 0;
  margin: 0 -16px 24px -16px;
  padding-left: 16px;
  padding-right: 16px;
  scroll-snap-type: x mandatory;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  scrollbar-color: var(--gray-300) transparent;
}

.stats-scroll-container::-webkit-scrollbar {
  height: 6px;
}

.stats-scroll-container::-webkit-scrollbar-track {
  background: transparent;
}

.stats-scroll-container::-webkit-scrollbar-thumb {
  background: var(--gray-300);
  border-radius: 3px;
}

.stats-scroll-container::-webkit-scrollbar-thumb:hover {
  background: var(--gray-400);
}

.stat-card-scroll {
  flex: 0 0 auto;
  min-width: 160px;
  max-width: 180px;
  scroll-snap-align: start;
  cursor: default !important;
  pointer-events: auto !important;
  user-select: none;
}

/* Prevent stat cards from being clickable */
.stats-scroll-container .card {
  cursor: default !important;
  pointer-events: none !important;
}

.stats-scroll-container .card * {
  pointer-events: none !important;
}

/* Desktop: Show as grid */
@media (min-width: 1024px) {
  .stats-scroll-container {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    overflow-x: visible;
    margin: 0 0 24px 0;
    padding: 0;
    gap: 16px;
  }
  
  .stat-card-scroll {
    min-width: unset;
    max-width: unset;
  }
}

/* Tablet: Show as grid with 3 columns */
@media (min-width: 768px) and (max-width: 1023px) {
  .stats-scroll-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    overflow-x: visible;
    margin: 0 0 24px 0;
    padding: 0;
    gap: 12px;
  }
  
  .stat-card-scroll {
    min-width: unset;
    max-width: unset;
  }
}

/* Mobile-responsive modal styles for payment modal */
@media (max-width: 640px) {
  /* Make modal full-width on small screens */
  #paymentModal .modal-lg {
    max-width: 95vw;
    margin: 10px;
  }
  
  /* Reduce modal padding */
  #paymentModal .modal-body {
    padding: 16px;
  }
  
  #paymentModal .modal-header {
    padding: 14px 16px;
  }
  
  #paymentModal .modal-footer {
    padding: 14px 16px;
    flex-direction: column;
    gap: 8px;
  }
  
  #paymentModal .modal-footer .btn {
    width: 100%;
  }
  
  /* Make bill info box more compact */
  #paymentModal .modal-body > div:first-child {
    padding: 12px 14px !important;
    margin-bottom: 14px !important;
  }
  
  #paymentModal .modal-body > div:first-child > div {
    font-size: 0.8rem;
  }
  
  #paymentModal .modal-body > div:first-child strong {
    font-size: 0.82rem !important;
  }
}

@media (max-width: 480px) {
  /* Extra small screens - maximize space */
  #paymentModal .modal-lg {
    max-width: 100vw;
    margin: 0;
    border-radius: 0;
    max-height: 100vh;
  }
  
  #paymentModal .modal-body {
    padding: 12px;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
  }
  
  #paymentModal .modal-header {
    padding: 12px 14px;
  }
  
  #paymentModal .modal-footer {
    padding: 12px 14px;
  }
  
  /* Reduce title size */
  #paymentModal .modal-title {
    font-size: 1rem;
  }
}

@media (max-width: 375px) {
  /* iPhone SE and similar */
  #paymentModal .modal-body {
    padding: 10px;
  }
  
  #paymentModal .modal-header,
  #paymentModal .modal-footer {
    padding: 10px 12px;
  }
  
  /* Make buttons more compact */
  #paymentModal .modal-footer .btn {
    padding: 10px 16px;
    font-size: 0.9rem;
  }
}

@media (max-width: 320px) {
  /* Very small devices */
  #paymentModal .modal-body {
    padding: 8px;
  }
  
  #paymentModal .modal-header,
  #paymentModal .modal-footer {
    padding: 8px 10px;
  }
  
  #paymentModal .modal-title {
    font-size: 0.95rem;
  }
  
  #paymentModal .modal-footer .btn {
    padding: 8px 14px;
    font-size: 0.85rem;
  }
}
</style>
