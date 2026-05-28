<?php
/**
 * BoardTrack — Tenant: Request Maintenance
 */
?>
<div class="dash-page-header">
  <div>
    <h1 class="dash-page-title">Request Maintenance</h1>
    <p class="dash-page-sub">Submit a maintenance request for your room.</p>
  </div>
</div>

<div class="form-card max-w-2xl">
  <?php if (!$room): ?>
    <div class="alert alert-info">
      <i class="fa-solid fa-info-circle"></i>
      <p>You must be assigned to a room before you can request maintenance.</p>
    </div>
    <div class="form-actions">
      <a href="<?= Router::url('tenant/dashboard') ?>" class="btn btn-primary">Back to Dashboard</a>
    </div>
  <?php else: ?>
    <div class="card mb-6">
      <div class="card-header">
        <div class="card-title">Room Information</div>
      </div>
      <div class="detail-grid">
        <div class="detail-item">
          <div class="detail-label">Room Number</div>
          <div class="detail-value"><?= htmlspecialchars($room['room_number']) ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Room Type</div>
          <div class="detail-value"><?= ucfirst($room['room_type']) ?></div>
        </div>
      </div>
    </div>

    <form action="<?= Router::url('tenant/save-maintenance') ?>" method="POST" class="dash-form">
      <div class="form-group">
        <label for="category">Category <span class="required">*</span></label>
        <select name="category" id="category" class="form-select" required>
          <option value="">— Select Category —</option>
          <option value="plumbing">Plumbing</option>
          <option value="electrical">Electrical</option>
          <option value="carpentry">Carpentry</option>
          <option value="painting">Painting</option>
          <option value="cleaning">Cleaning</option>
          <option value="appliance">Appliance</option>
          <option value="other">Other</option>
        </select>
      </div>

      <div class="form-group">
        <label for="priority">Priority <span class="required">*</span></label>
        <select name="priority" id="priority" class="form-select" required>
          <option value="low">Low</option>
          <option value="medium" selected>Medium</option>
          <option value="high">High</option>
          <option value="urgent">Urgent</option>
        </select>
      </div>

      <div class="form-group">
        <label for="title">Title <span class="required">*</span></label>
        <input type="text" name="title" id="title" class="form-input" required placeholder="Brief summary of the maintenance issue">
      </div>

      <div class="form-group">
        <label for="description">Description <span class="required">*</span></label>
        <textarea name="description" id="description" class="form-textarea" rows="6" required placeholder="Please provide detailed information about the maintenance issue..."></textarea>
      </div>

      <div class="form-actions">
        <a href="<?= Router::url('tenant/maintenance') ?>" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary">Submit Request</button>
      </div>
    </form>
  <?php endif; ?>
</div>
