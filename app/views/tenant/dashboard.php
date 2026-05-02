<?php
/**
 * BoardTrack — Tenant Dashboard View
 * app/views/tenant/dashboard.php
 * Layout: tenant.php
 */
$tenant            = $tenant            ?? [];
$user              = $user              ?? [];
$stats             = $stats             ?? [];
$recentBills       = $recentBills       ?? [];
$recentAnnouncements = $recentAnnouncements ?? [];
$roommates         = $roommates         ?? [];
$name = htmlspecialchars(explode(' ', $user['name'] ?? 'Tenant')[0]);
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Welcome, <?= $name ?></h1>
      <p class="page-subtitle"><?= date('l, F j, Y') ?></p>
    </div>
  </div>
</div>

<!-- Stats Row -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
  <div class="stat-card">
    <div class="stat-label">Unpaid Bills</div>
    <div class="stat-value">
      <?= $stats['unpaidBills'] ?? 0 ?>
    </div>
    <div class="stat-meta"><a href="<?= Router::url('tenant/bills') ?>" style="color:var(--primary);">View bills</a></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Pending Payments</div>
    <div class="stat-value"><?= $stats['pendingPayments'] ?? 0 ?></div>
    <div class="stat-meta">Awaiting verification</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Open Complaints</div>
    <div class="stat-value"><?= $stats['openComplaints'] ?? 0 ?></div>
    <div class="stat-meta"><a href="<?= Router::url('tenant/complaints') ?>" style="color:var(--primary);">View</a></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Notifications</div>
    <div class="stat-value"><?= $stats['unreadNotifications'] ?? 0 ?></div>
    <div class="stat-meta">Unread</div>
  </div>
</div>

<div class="grid-content">

  <!-- LEFT COLUMN -->
  <div>
    <!-- Room Info -->
    <div class="card" style="margin-bottom:16px;">
      <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-door-open" style="margin-right:6px;"></i> My Room</div>
      </div>
      <?php if (!empty($tenant['room_id'])): ?>
        <div class="detail-grid" style="margin-bottom:12px;">
          <div class="detail-item">
            <div class="detail-label">Room Number</div>
            <div class="detail-value" style="font-size:1.1rem;font-weight:700;"><?= htmlspecialchars($tenant['room_number'] ?? '—') ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Room Type</div>
            <div class="detail-value">
              <span class="badge badge-<?= $tenant['room_type'] ?? 'single' ?>"><?= ucfirst($tenant['room_type'] ?? 'single') ?></span>
            </div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Floor</div>
            <div class="detail-value">Floor <?= htmlspecialchars($tenant['floor'] ?? '—') ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Monthly Rent</div>
            <div class="detail-value" style="font-weight:600;">₱<?= number_format($tenant['monthly_rent'] ?? 0, 2) ?></div>
          </div>
        </div>
        <?php if (!empty($roommates)): ?>
          <div style="border-top:1px solid var(--gray-100);padding-top:12px;">
            <div class="detail-label" style="margin-bottom:8px;">Roommates</div>
            <?php foreach ($roommates as $rm): ?>
              <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                <div style="width:28px;height:28px;border-radius:var(--radius);background:var(--gray-100);color:var(--gray-500);display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:600;">
                  <?= strtoupper(substr($rm['name'], 0, 1)) ?>
                </div>
                <span style="font-size:0.85rem;color:var(--gray-700);"><?= htmlspecialchars($rm['name']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="empty-state" style="padding:32px 24px;">
          <i class="fa-solid fa-door-closed"></i>
          <p>Your room assignment is pending</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Recent Bills -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">Recent Bills</div>
        <a href="<?= Router::url('tenant/bills') ?>" class="btn btn-secondary btn-sm">View All</a>
      </div>
      <?php if (!empty($recentBills)): ?>
        <div class="table-wrap">
          <table class="bt-table">
            <thead>
              <tr>
                <th>Bill</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentBills as $b):
                $bs = $b['computed_status'] ?? $b['status'];
                $bclass = ['unpaid'=>'badge-unpaid','paid'=>'badge-paid','overdue'=>'badge-overdue','pending_verification'=>'badge-pv'];
              ?>
              <tr>
                <td class="td-name"><?= htmlspecialchars($b['bill_name']) ?></td>
                <td style="font-weight:500;">₱<?= number_format($b['amount'], 2) ?></td>
                <td style="color:var(--gray-400);"><?= date('M d, Y', strtotime($b['due_date'])) ?></td>
                <td><span class="badge <?= $bclass[$bs] ?? 'badge-normal' ?>"><?= ucfirst(str_replace('_',' ',$bs)) ?></span></td>
                <td>
                  <?php if (in_array($bs, ['unpaid','overdue'])): ?>
                    <a href="<?= Router::url('tenant/pay-bill/' . $b['id']) ?>" class="btn btn-primary btn-sm">Pay</a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa-solid fa-file-invoice"></i>
          <p>No bills yet</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- RIGHT COLUMN -->
  <div>
    <!-- Recent Announcements -->
    <div class="card" style="margin-bottom:16px;">
      <div class="card-header">
        <div class="card-title">Announcements</div>
        <a href="<?= Router::url('tenant/announcements') ?>" class="btn btn-secondary btn-sm">View All</a>
      </div>
      <?php if (!empty($recentAnnouncements)): ?>
        <div class="notif-list">
          <?php foreach ($recentAnnouncements as $a): ?>
            <div class="notif-item">
              <div class="notif-icon announcement">
                <i class="fa-solid fa-bullhorn"></i>
              </div>
              <div class="notif-content">
                <div class="notif-title"><?= htmlspecialchars($a['title']) ?></div>
                <div class="notif-msg"><?= date('M d, Y', strtotime($a['created_at'])) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state" style="padding:24px;">
          <i class="fa-solid fa-bullhorn"></i>
          <p>No announcements yet</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Quick Links -->
    <div class="card">
      <div class="card-title" style="margin-bottom:12px;">Quick Links</div>
      <div style="display:flex;flex-direction:column;gap:6px;">
        <a href="<?= Router::url('tenant/bills') ?>" class="btn btn-secondary" style="justify-content:flex-start;">
          <i class="fa-solid fa-file-invoice"></i> My Bills
        </a>
        <a href="<?= Router::url('tenant/complaints') ?>" class="btn btn-secondary" style="justify-content:flex-start;">
          <i class="fa-solid fa-flag"></i> Submit Complaint
        </a>
        <a href="<?= Router::url('tenant/notifications') ?>" class="btn btn-secondary" style="justify-content:flex-start;">
          <i class="fa-solid fa-bell"></i> Notifications
        </a>
        <a href="<?= Router::url('tenant/profile') ?>" class="btn btn-secondary" style="justify-content:flex-start;">
          <i class="fa-solid fa-user"></i> My Profile
        </a>
      </div>
    </div>
  </div>

</div>
