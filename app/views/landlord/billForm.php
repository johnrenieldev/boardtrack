<?php
/**
 * BoardTrack — Landlord: Create Bill Form (room or individual)
 */
$rooms         = $rooms ?? $billableRooms ?? [];
$activeTenants = $activeTenants ?? [];
?>
<div class="dash-page-header">
  <div>
    <h1 class="dash-page-title">Create Bill</h1>
    <p class="dash-page-sub">Bill per room (shared) or per tenant (individual) — select the type below.</p>
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
  <form action="<?= Router::url('landlord/create-bill') ?>" method="POST" class="confirm-form" id="bulkBillForm"
        data-action="Create Bill" data-message="Are you sure you want to create these bill(s)?">
    <div style="padding: 28px;">

      <div class="form-group">
        <label>Billing Type <span class="text-danger">*</span></label>
        <div style="display:block;">
          <div style="display:flex;gap:16px;align-items:center;margin-bottom:8px;">
            <input type="radio" name="billing_type" value="room_based" checked id="billing_room_based" onchange="toggleBulkBillingType('room_based')" style="width:18px;height:18px;cursor:pointer;">
            <label for="billing_room_based" style="cursor:pointer;padding:8px 12px;background:var(--gray-50);border:1px solid var(--gray-200);border-radius:6px;flex:1;">
              <strong>Per Room</strong> — one bill per room
            </label>
          </div>
          <div style="display:flex;gap:16px;align-items:center;">
            <input type="radio" name="billing_type" value="individual" id="billing_individual" onchange="toggleBulkBillingType('individual')" style="width:18px;height:18px;cursor:pointer;">
            <label for="billing_individual" style="cursor:pointer;padding:8px 12px;background:var(--gray-50);border:1px solid var(--gray-200);border-radius:6px;flex:1;">
              <strong>Individual</strong> — one bill per tenant
            </label>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label>Bill Name <span class="text-danger">*</span></label>
        <input type="text" name="bill_name" class="form-input" required placeholder="e.g., April 2026 Rent">
      </div>

      <div style="display: flex; gap: 16px;">
        <div class="form-group" style="flex: 1;">
          <label>Billing Period Start <span class="text-danger">*</span></label>
          <input type="date" name="period_start" class="form-input" required value="<?= date('Y-m-01') ?>">
        </div>
        <div class="form-group" style="flex: 1;">
          <label>Billing Period End <span class="text-danger">*</span></label>
          <input type="date" name="period_end" class="form-input" required value="<?= date('Y-m-t') ?>">
        </div>
      </div>

      <div style="display: flex; gap: 16px;">
        <div class="form-group" style="flex: 1;">
          <label>Amount (₱) <span class="text-danger">*</span></label>
          <input type="number" name="amount" class="form-input" step="0.01" min="0" required>
        </div>
        <div class="form-group" style="flex: 1;">
          <label>Due Date <span class="text-danger">*</span></label>
          <input type="date" name="due_date" class="form-input" required>
        </div>
      </div>

      <div class="form-group">
        <label>Category</label>
        <select name="charge_category" class="form-input">
          <option value="rent">Rent</option>
          <option value="utility">Utility</option>
          <option value="maintenance">Maintenance</option>
          <option value="penalty">Penalty</option>
          <option value="other">Other</option>
        </select>
      </div>

      <div class="form-group">
        <label>Notes (Optional)</label>
        <textarea name="notes" class="form-textarea" rows="2"></textarea>
      </div>

      <div id="bulkSelectRooms" class="form-group">
        <label>Select Rooms <span class="text-danger">*</span></label>
        <div style="border: 1px solid var(--gray-200); border-radius: var(--radius); overflow: hidden;">
          <?php if (empty($rooms)): ?>
            <div style="padding: 24px; text-align: center; color: var(--gray-400);">No occupied rooms found.</div>
          <?php else: ?>
            <label style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-bottom: 1px solid var(--gray-100); cursor: pointer; font-weight: 600;">
              <input type="checkbox" onchange="toggleAll(this, 'room-checkbox')"> Select All (<?= count($rooms) ?> rooms)
            </label>
            <?php foreach ($rooms as $room): ?>
              <label style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid var(--gray-100); cursor: pointer;">
                <input type="checkbox" name="room_ids[]" value="<?= $room['id'] ?>" class="room-checkbox">
                <div>
                  <div style="font-weight: 500;">Room <?= htmlspecialchars($room['room_number']) ?></div>
                  <div style="font-size: 0.78rem; color: var(--gray-400);"><?= htmlspecialchars($room['tenant_names'] ?? '') ?></div>
                </div>
              </label>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div id="bulkSelectTenants" class="form-group" style="display:none;">
        <label>Select Tenants <span class="text-danger">*</span></label>
        <div style="border: 1px solid var(--gray-200); border-radius: var(--radius); overflow: hidden;">
          <?php if (empty($activeTenants)): ?>
            <div style="padding: 24px; text-align: center; color: var(--gray-400);">No active tenants with rooms.</div>
          <?php else: ?>
            <label style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-bottom: 1px solid var(--gray-100); cursor: pointer; font-weight: 600;">
              <input type="checkbox" onchange="toggleAll(this, 'tenant-checkbox')"> Select All (<?= count($activeTenants) ?> tenants)
            </label>
            <?php foreach ($activeTenants as $tenant): ?>
              <label style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid var(--gray-100); cursor: pointer;">
                <input type="checkbox" name="tenant_ids[]" value="<?= $tenant['id'] ?>" class="tenant-checkbox">
                <div>
                  <div style="font-weight: 500;"><?= htmlspecialchars($tenant['name'] ?? '') ?></div>
                  <div style="font-size: 0.78rem; color: var(--gray-400);">
                    <?= htmlspecialchars($tenant['email'] ?? '') ?>
                    <?= !empty($tenant['room_number']) ? ' — Room ' . htmlspecialchars($tenant['room_number']) : '' ?>
                  </div>
                </div>
              </label>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 8px;">
        <a href="<?= Router::url('landlord/bills') ?>" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Create Bill(s)</button>
      </div>
    </div>
  </form>
</div>

<script>
function toggleBulkBillingType(type) {
  document.getElementById('bulkSelectRooms').style.display = type === 'room_based' ? 'block' : 'none';
  document.getElementById('bulkSelectTenants').style.display = type === 'individual' ? 'block' : 'none';
  document.querySelectorAll('.room-checkbox').forEach(function(c) { c.checked = false; });
  document.querySelectorAll('.tenant-checkbox').forEach(function(c) { c.checked = false; });
}
function toggleAll(master, className) {
  document.querySelectorAll('.' + className).forEach(function(c) { c.checked = master.checked; });
}
</script>
