<?php
/**
 * BoardTrack — Landlord: Overdue Penalties Dashboard
 */
$eligibleBills = $eligibleBills ?? [];
$billsWithPenalties = $billsWithPenalties ?? [];
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Overdue Penalties</h1>
      <p class="page-subtitle">Manage and process monthly 10% penalties for overdue bills</p>
    </div>
    <form method="POST" action="<?= Router::url('landlord/processNow') ?>" id="processPenaltiesForm">
      <button type="button" class="btn btn-danger btn-sm" onclick="confirmProcessPenalties()">
        <i class="fa-solid fa-bolt"></i> Process Penalties Now
      </button>
    </form>
  </div>
</div>

<script>
async function confirmProcessPenalties() {
  const confirmed = await btConfirm({
    title: 'Process Penalties',
    message: 'Process penalties for all eligible overdue bills? This will apply 10% penalties and send notifications to tenants and guardians.',
    confirmText: 'Process Now',
    cancelText: 'Cancel',
    type: 'danger',
    icon: 'fa-bolt'
  });
  
  if (confirmed) {
    document.getElementById('processPenaltiesForm').submit();
  }
}
</script>

<!-- Stats -->
<div class="stats-grid">
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-clock text-warning-500"></i> Eligible for Penalty
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= count($eligibleBills) ?></div>
    <div class="text-[0.6rem] font-bold text-gray-400 mt-2 uppercase tracking-tighter">Bills overdue this month</div>
  </div>
  
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-bell text-danger-500"></i> Pending Notifications
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= count($billsWithPenalties) ?></div>
    <div class="text-[0.6rem] font-bold text-gray-400 mt-2 uppercase tracking-tighter">Penalties not yet notified</div>
  </div>
  
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-percent text-brand-500"></i> Penalty Rate
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none">10%</div>
    <div class="text-[0.6rem] font-bold text-gray-400 mt-2 uppercase tracking-tighter">Per month overdue</div>
  </div>
</div>

<!-- How It Works -->
<div class="card mb-6 p-6">
  <h3 class="text-lg font-bold text-gray-900 mb-4">
    <i class="fa-solid fa-info-circle text-brand-500"></i> How Overdue Penalties Work
  </h3>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
      <h4 class="font-bold text-gray-900 mb-2">Penalty Calculation</h4>
      <p class="text-sm text-gray-600 mb-2">
        <strong>Formula:</strong> Each month, the current bill total increases by 10% (compounding)
      </p>
      <p class="text-sm text-gray-600 mb-2">
        <strong>Calculation:</strong> New Amount = Previous Amount × 1.10 per month overdue
      </p>
      <p class="text-sm text-gray-600 mb-2">
        <strong>Example (Compounding):</strong>
      </p>
      <ul class="text-sm text-gray-600 list-disc list-inside space-y-1">
        <li>Original bill: ₱1,000</li>
        <li>After 1 month overdue: ₱1,000 × 1.10 = <strong>₱1,100.00</strong></li>
        <li>After 2 months overdue: ₱1,100 × 1.10 = <strong>₱1,210.00</strong></li>
        <li>After 3 months overdue: ₱1,210 × 1.10 = <strong>₱1,331.00</strong></li>
      </ul>
      <p class="text-xs text-gray-500 mt-2 italic">
        Note: Penalty compounds monthly on the current outstanding amount, not the original bill.
      </p>
    </div>
    <div>
      <h4 class="font-bold text-gray-900 mb-2">Automated Processing</h4>
      <ul class="text-sm text-gray-600 list-disc list-inside space-y-1">
        <li>Penalties are calculated based on the original bill amount (not compounded)</li>
        <li>System checks for overdue bills monthly</li>
        <li>10% penalty applied for each month past due date</li>
        <li>Tenants and guardians receive email notifications</li>
        <li>In-app notifications are created automatically</li>
        <li>Penalties continue until bill is paid</li>
      </ul>
    </div>
  </div>
</div>

<!-- Eligible Bills -->
<?php if (!empty($eligibleBills)): ?>
<div class="card mb-6">
  <div class="card-header">
    <h3 class="card-title">Bills Eligible for Penalty (<?= count($eligibleBills) ?>)</h3>
  </div>
  <div class="table-wrap">
    <table class="bt-table w-full">
      <thead>
        <tr>
          <th>Bill Name</th>
          <th>Tenant</th>
          <th>Room</th>
          <th>Original Amount</th>
          <th>Due Date</th>
          <th>Months Overdue</th>
          <th>Penalty to Apply</th>
          <th>New Total</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($eligibleBills as $bill): ?>
          <?php
            $originalAmount = !empty($bill['original_amount']) ? (float) $bill['original_amount'] : (float) $bill['amount'];
            // Use months_overdue from query (calculated via TIMESTAMPDIFF)
            $monthsOverdue = (int) ($bill['months_overdue'] ?? 0);
            $penaltyAmount = $originalAmount * 0.10 * $monthsOverdue;
            $newTotal = $originalAmount + $penaltyAmount;
          ?>
          <tr class="hover:bg-gray-50 transition-colors">
            <td data-label="Bill Name" class="text-sm font-bold text-gray-900"><?= htmlspecialchars($bill['bill_name']) ?></td>
            <td data-label="Tenant" class="text-sm text-gray-700"><?= htmlspecialchars($bill['tenant_name'] ?? 'N/A') ?></td>
            <td data-label="Room" class="text-sm text-gray-700">Room <?= htmlspecialchars($bill['room_number'] ?? 'N/A') ?></td>
            <td data-label="Original Amount" class="text-sm font-bold text-gray-900">₱<?= number_format($originalAmount, 2) ?></td>
            <td data-label="Due Date" class="text-sm text-gray-700"><?= date('M j, Y', strtotime($bill['due_date'])) ?></td>
            <td data-label="Months Overdue" class="text-sm font-bold text-danger-600"><?= $monthsOverdue ?></td>
            <td data-label="Penalty" class="text-sm font-bold text-danger-600">₱<?= number_format($penaltyAmount, 2) ?></td>
            <td data-label="New Total" class="text-sm font-bold text-gray-900">₱<?= number_format($newTotal, 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
<div class="card mb-6 p-12 text-center">
  <i class="fa-solid fa-check-circle text-5xl text-success-500 mb-4"></i>
  <h3 class="text-lg font-bold text-gray-900">No Bills Eligible for Penalty</h3>
  <p class="text-gray-500">All bills are either paid or have already been penalized this month.</p>
</div>
<?php endif; ?>

<!-- Bills with Pending Notifications -->
<?php if (!empty($billsWithPenalties)): ?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Penalties Awaiting Notification (<?= count($billsWithPenalties) ?>)</h3>
  </div>
  <div class="table-wrap">
    <table class="bt-table w-full">
      <thead>
        <tr>
          <th>Bill Name</th>
          <th>Tenant</th>
          <th>Room</th>
          <th>Original Amount</th>
          <th>Penalty Amount</th>
          <th>Current Total</th>
          <th>Missed Cycles</th>
          <th>Penalty Applied</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($billsWithPenalties as $bill): ?>
          <tr class="hover:bg-gray-50 transition-colors">
            <td data-label="Bill Name" class="text-sm font-bold text-gray-900"><?= htmlspecialchars($bill['bill_name']) ?></td>
            <td data-label="Tenant" class="text-sm text-gray-700"><?= htmlspecialchars($bill['tenant_name'] ?? 'N/A') ?></td>
            <td data-label="Room" class="text-sm text-gray-700">Room <?= htmlspecialchars($bill['room_number'] ?? 'N/A') ?></td>
            <td data-label="Original" class="text-sm text-gray-700">₱<?= number_format($bill['original_amount'] ?? $bill['amount'], 2) ?></td>
            <td data-label="Penalty" class="text-sm font-bold text-danger-600">₱<?= number_format($bill['penalty_amount'] ?? 0, 2) ?></td>
            <td data-label="Total" class="text-sm font-bold text-gray-900">₱<?= number_format($bill['amount'], 2) ?></td>
            <td data-label="Cycles" class="text-sm text-gray-700"><?= $bill['missed_cycles'] ?? 0 ?></td>
            <td data-label="Applied" class="text-sm text-gray-700">
              <?= !empty($bill['last_penalty_applied_at']) ? date('M j, Y', strtotime($bill['last_penalty_applied_at'])) : 'N/A' ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<style>
.card-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}
.card-title {
  font-size: 1rem;
  font-weight: 700;
  color: #111827;
}

/* Center column headers and data */
.bt-table th {
  text-align: center !important;
}

.bt-table td {
  text-align: center !important;
}

/* Keep first column (Bill Name) left-aligned */
.bt-table th:first-child,
.bt-table td:first-child {
  text-align: left !important;
}

/* Mobile responsive styles for penalties table */
@media (max-width: 767px) {
  .card {
    max-height: calc(100vh - 280px) !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    -webkit-overflow-scrolling: touch;
  }
  
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
  
  .bt-table tbody tr:last-child {
    margin-bottom: 20px !important;
  }
  
  .bt-table th,
  .bt-table td {
    width: auto !important;
    min-width: 0 !important;
    max-width: 100% !important;
  }
  
  /* Bill Name - full width with grey background */
  .bt-table td:first-child {
    padding: 14px 16px !important;
    background: var(--gray-100) !important;
    font-weight: 700 !important;
    text-align: left !important;
  }
  
  /* Other fields - label on left, value on right */
  .bt-table td[data-label]:not(:first-child)::before {
    width: 130px !important;
    min-width: 130px !important;
    flex-shrink: 0 !important;
    padding-right: 0 !important;
  }
  
  .bt-table td[data-label]:not(:first-child) {
    display: flex !important;
    justify-content: flex-start !important;
    align-items: center !important;
    text-align: left !important;
    gap: 12px !important;
    padding: 10px 14px !important;
  }
  
  /* Prevent text wrapping issues */
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
  
  .bt-table td[data-label]::before {
    white-space: nowrap !important;
    word-break: keep-all !important;
  }
  
  /* Custom scrollbar for card */
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
</style>
