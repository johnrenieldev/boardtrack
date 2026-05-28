<?php
/**
 * BoardTrack — Tenant: My Maintenance Requests
 */
?>
<div class="dash-page-header">
  <div>
    <h1 class="dash-page-title">My Maintenance Requests</h1>
    <p class="dash-page-sub">Track and manage your room maintenance requests.</p>
  </div>
  <div class="dash-page-actions">
    <a href="<?= Router::url('tenant/create-maintenance') ?>" class="btn btn-primary">
      <i class="fa-solid fa-plus"></i> New Request
    </a>
  </div>
</div>

<?php if (empty($requests)): ?>
  <div class="empty-state">
    <i class="fa-solid fa-wrench"></i>
    <p>No maintenance requests yet.</p>
    <a href="<?= Router::url('tenant/create-maintenance') ?>" class="btn btn-primary">Submit Your First Request</a>
  </div>
<?php else: ?>
  <div class="card">
    <div class="table-wrap">
      <table class="bt-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Requested</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($requests as $req): ?>
            <tr>
              <td data-label="Title">
                <div class="td-name"><?= htmlspecialchars($req['title']) ?></div>
                <?php if (!empty($req['room_number'])): ?>
                  <div class="td-sub">Room <?= htmlspecialchars($req['room_number']) ?></div>
                <?php endif; ?>
              </td>
              <td data-label="Category">
                <span class="badge badge-normal"><?= ucfirst($req['category']) ?></span>
              </td>
              <td data-label="Priority">
                <?php
                  $priorityColors = [
                    'low' => 'badge-normal',
                    'medium' => 'badge-waiting',
                    'high' => 'badge-pending',
                    'urgent' => 'badge-overdue'
                  ];
                  $pclass = $priorityColors[$req['priority']] ?? 'badge-normal';
                ?>
                <span class="badge <?= $pclass ?>"><?= ucfirst($req['priority']) ?></span>
              </td>
              <td data-label="Status">
                <?php
                  $statusColors = [
                    'pending' => 'badge-pending',
                    'in_progress' => 'badge-waiting',
                    'completed' => 'badge-paid',
                    'rejected' => 'badge-rejected',
                    'cancelled' => 'badge-normal'
                  ];
                  $sclass = $statusColors[$req['status']] ?? 'badge-normal';
                ?>
                <span class="badge <?= $sclass ?>"><?= ucfirst(str_replace('_', ' ', $req['status'])) ?></span>
              </td>
              <td data-label="Requested"><?= date('M d, Y', strtotime($req['requested_at'])) ?></td>
              <td data-label="Actions">
                <?php if ($req['status'] === 'pending'): ?>
                  <a href="<?= Router::url('tenant/maintenance') ?>?id=<?= $req['id'] ?>&action=delete" 
                     class="btn btn-sm btn-danger" 
                     onclick="return confirm('Are you sure you want to delete this request?');">
                    <i class="fa-solid fa-trash"></i>
                  </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
