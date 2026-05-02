<?php
/**
 * BoardTrack — Tenant: Submit Complaint
 */
?>
<div class="dash-page-header">
  <div>
    <h1 class="dash-page-title">Submit Complaint</h1>
    <p class="dash-page-sub">Report an issue to the landlord. You can choose to be anonymous for roommate conflicts.</p>
  </div>
</div>

<div class="form-card max-w-2xl">
  <form action="<?= Router::url('tenant/save-complaint') ?>" method="POST" class="dash-form">
    <div class="form-group">
      <label for="category">Category <span class="required">*</span></label>
      <select name="category" id="category" class="form-select" required onchange="toggleAnonymousOption()">
        <option value="">— Select Category —</option>
        <option value="maintenance">Maintenance Issue</option>
        <option value="roommate_conflict">Roommate Conflict</option>
        <option value="billing">Billing Concern</option>
        <option value="room_change">Room Change Request</option>
        <option value="other">Other</option>
      </select>
    </div>

    <div class="form-group">
      <label for="title">Subject / Title <span class="required">*</span></label>
      <input type="text" name="title" id="title" class="form-input" required placeholder="Brief summary of the issue">
    </div>

    <div class="form-group">
      <label for="description">Detailed Description <span class="required">*</span></label>
      <textarea name="description" id="description" class="form-textarea" rows="6" required placeholder="Please provide as much detail as possible..."></textarea>
    </div>

    <div id="anonymousOption" class="form-group checkbox-group" style="display: none;">
      <label class="checkbox-label">
        <input type="checkbox" name="is_anonymous" value="1">
        <span>Submit anonymously (Only for Roommate Conflicts)</span>
      </label>
      <p class="form-help">Your name will be hidden from the landlord, but the issue will still be tracked.</p>
    </div>

    <div class="form-actions">
      <a href="<?= Router::url('tenant/complaints') ?>" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">Submit Complaint</button>
    </div>
  </form>
</div>

<script>
function toggleAnonymousOption() {
  const category = document.getElementById('category').value;
  const anonOption = document.getElementById('anonymousOption');
  if (category === 'roommate_conflict') {
    anonOption.style.display = 'block';
  } else {
    anonOption.style.display = 'none';
    anonOption.querySelector('input').checked = false;
  }
}
</script>
