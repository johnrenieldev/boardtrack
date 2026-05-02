<?php
/**
 * BoardTrack — Landlord: Create Bill Form
 */
?>
<div class="dash-page-header">
  <div>
    <h1 class="dash-page-title">Create Bill</h1>
    <p class="dash-page-sub">Issue a new bill for one or more active tenants.</p>
  </div>
  <div class="dash-page-actions">
    <a href="<?= Router::url('landlord/bills') ?>" class="btn btn-outline">
      <i class="fa-solid fa-arrow-left"></i> Back to Bills
    </a>
  </div>
</div>

<div class="data-card" style="max-width: 760px; margin: 0 auto;">
  <div class="db-card-header" style="padding: 20px 28px; border-bottom: 1px solid var(--gray-100);">
    <h3 class="db-card-title"><i class="fa-solid fa-file-invoice-dollar"></i> Bill Details</h3>
  </div>
  <form action="<?= Router::url('landlord/create-bill') ?>" method="POST" class="confirm-form" data-action="Create Bill" data-message="Are you sure you want to create this bill?">
    <div style="padding: 28px;">

      <div class="form-group">
        <label>Bill Name <span class="text-danger">*</span></label>
        <input type="text" name="bill_name" class="form-input" required placeholder="e.g., April 2026 Rent, Electricity Fee">
      </div>

      <div style="display: flex; gap: 16px;">
        <div class="form-group" style="flex: 1;">
          <label>Billing Period Start <span class="text-danger">*</span></label>
          <input type="date" name="period_start" class="form-input" required>
        </div>
        <div class="form-group" style="flex: 1;">
          <label>Billing Period End <span class="text-danger">*</span></label>
          <input type="date" name="period_end" class="form-input" required>
        </div>
      </div>

      <div style="display: flex; gap: 16px;">
        <div class="form-group" style="flex: 1;">
          <label>Amount (₱) <span class="text-danger">*</span></label>
          <input type="number" name="amount" class="form-input" step="0.01" min="0" required placeholder="5000.00">
        </div>
        <div class="form-group" style="flex: 1;">
          <label>Due Date <span class="text-danger">*</span></label>
          <input type="date" name="due_date" class="form-input" required>
        </div>
      </div>

      <div class="form-group">
        <label>Notes (Optional)</label>
        <textarea name="notes" class="form-textarea" rows="3" placeholder="Additional notes about this bill..."></textarea>
      </div>

      <div class="form-group">
        <label>Select Tenants <span class="text-danger">*</span></label>
        <small class="form-help" style="display: block; margin-bottom: 10px;">Hold Ctrl/Cmd to select multiple tenants, or use the checkboxes below.</small>
        <div style="border: 1px solid var(--gray-200); border-radius: var(--radius); overflow: hidden;">
          <?php if (empty($tenants)): ?>
            <div style="padding: 24px; text-align: center; color: var(--gray-400);">
              <i class="fa-solid fa-users" style="font-size: 1.5rem; display: block; margin-bottom: 8px;"></i>
              No active tenants found. Approve some tenants first.
            </div>
          <?php else: ?>
            <label style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-bottom: 1px solid var(--gray-100); cursor: pointer; font-weight: 600;">
              <input type="checkbox" id="selectAll" onchange="toggleAll(this)"> Select All (<?= count($tenants) ?> tenants)
            </label>
            <?php foreach ($tenants as $tenant): ?>
              <label style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid var(--gray-100); cursor: pointer; transition: background 0.15s;">
                <input type="checkbox" name="tenant_ids[]" value="<?= $tenant['id'] ?>" class="tenant-checkbox">
                <div>
                  <div style="font-weight: 500; color: var(--gray-800);"><?= htmlspecialchars($tenant['user_name'] ?? $tenant['name'] ?? '') ?></div>
                  <div style="font-size: 0.78rem; color: var(--gray-400);"><?= htmlspecialchars($tenant['user_email'] ?? $tenant['email'] ?? '') ?> <?= !empty($tenant['room_number']) ? '— Room ' . htmlspecialchars($tenant['room_number']) : '' ?></div>
                </div>
              </label>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 8px;">
        <a href="<?= Router::url('landlord/bills') ?>" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Create Bill</button>
      </div>
    </div>
  </form>
</div>

<script>
function toggleAll(cb) {
  document.querySelectorAll('.tenant-checkbox').forEach(c => c.checked = cb.checked);
}
</script>
