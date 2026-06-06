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

<div class="page-header mb-4">
  <div class="page-header-row flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-black text-gray-900 leading-tight">Welcome, <?= $name ?></h1>
      <p class="text-[0.7rem] font-bold text-gray-400 uppercase tracking-widest mt-0.5"><?= date('l, F j, Y') ?></p>
    </div>
  </div>
</div>

<!-- STAT CARDS -->
<div class="stats-grid">
  <a href="<?= Router::url('tenant/bills') ?>" class="stat-card <?= ($stats['unpaidBills'] ?? 0) > 0 ? 'urgent' : '' ?>">
    <div class="stat-header">
      <div class="stat-icon-box">
        <i class="fa-solid fa-file-invoice"></i>
      </div>
      <div class="stat-label">Unpaid Bills</div>
    </div>
    <div class="stat-value"><?= $stats['unpaidBills'] ?? 0 ?></div>
    <div class="stat-footer">
      Click to <span>view bills</span>
    </div>
  </a>
  
  <div class="stat-card <?= ($stats['pendingPayments'] ?? 0) > 0 ? 'urgent' : '' ?>">
    <div class="stat-header">
      <div class="stat-icon-box">
        <i class="fa-solid fa-receipt"></i>
      </div>
      <div class="stat-label">Pending Payments</div>
    </div>
    <div class="stat-value"><?= $stats['pendingPayments'] ?? 0 ?></div>
    <div class="stat-footer">
      Currently <span>awaiting review</span>
    </div>
  </div>
  
  <a href="<?= Router::url('tenant/complaints') ?>" class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <div class="stat-label">Open Complaints</div>
    </div>
    <div class="stat-value"><?= $stats['openComplaints'] ?? 0 ?></div>
    <div class="stat-footer">
      Track your <span>submissions</span>
    </div>
  </a>
  
  <a href="<?= Router::url('tenant/notifications') ?>" class="stat-card <?= ($unreadNotificationCount ?? 0) > 0 ? 'urgent' : '' ?>">
    <div class="stat-header">
      <div class="stat-icon-box">
        <i class="fa-solid fa-bell"></i>
      </div>
      <div class="stat-label">Notifications</div>
    </div>
    <div class="stat-value"><?= ($unreadNotificationCount ?? 0) > 0 ? ($unreadNotificationCount ?? 0) : '0' ?></div>
    <div class="stat-footer">
      Stay <span>updated</span>
    </div>
  </a>
</div>

<div class="grid-content responsive-dashboard-grid">

  <!-- LEFT COLUMN -->
  <div>
    <!-- Room Info -->
    <div class="card" style="margin-bottom:16px;">
      <div class="card-header mb-3">
        <div class="card-title flex items-center gap-2"><i class="fa-solid fa-door-open text-brand-600"></i> My Room</div>
      </div>
      <?php if (!empty($tenant['room_id'])): ?>
        <!-- Room Detail Grid (Responsive & Robust) -->
        <div class="detail-grid responsive-detail-grid" style="margin-bottom:12px;">
          <!-- Room Number -->
          <div class="detail-item p-3 rounded-xl border flex items-center gap-3" style="background:var(--color-brand-light); border-color:var(--color-brand-border);">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 text-xs shadow-sm" style="background:var(--color-brand); color:white;">
              <i class="fa-solid fa-key" style="font-size: 1rem;"></i>
            </div>
            <div>
              <div class="text-[0.65rem] font-bold uppercase tracking-wider mb-0.5" style="color:var(--color-brand);">Room Number</div>
              <div class="text-base font-bold text-gray-900 leading-none"><?= htmlspecialchars($tenant['room_number'] ?? '—') ?></div>
            </div>
          </div>

          <!-- Room Type -->
          <div class="detail-item p-3 rounded-xl border flex items-center gap-3" style="background:var(--color-info-light); border-color:var(--color-info-border);">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 text-xs" style="background:var(--color-surface); border:1px solid var(--color-info-border); color:var(--color-info);">
              <i class="fa-solid fa-users" style="font-size: 1rem;"></i>
            </div>
            <div>
              <div class="text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Room Type</div>
              <div class="text-sm font-bold text-gray-800"><?= ucfirst($tenant['room_type'] ?? 'single') ?></div>
            </div>
          </div>
          
          <!-- Floor Level -->
          <div class="detail-item p-3 rounded-xl border flex items-center gap-3" style="background:var(--color-info-light); border-color:var(--color-info-border);">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 text-xs" style="background:var(--color-brand-light); color:var(--color-brand); border:1px solid var(--color-brand-border);">
              <i class="fa-solid fa-layer-group" style="font-size: 1rem;"></i>
            </div>
            <div>
              <div class="text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Floor Level</div>
              <div class="text-sm font-bold text-gray-800">Floor <?= htmlspecialchars($tenant['floor'] ?? '—') ?></div>
            </div>
          </div>
          
          <!-- Monthly Rent -->
          <div class="detail-item p-3 rounded-xl border flex items-center gap-3" style="background:var(--color-info-light); border-color:var(--color-info-border);">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 text-xs" style="background:var(--color-danger-light); color:var(--color-danger); border:1px solid var(--color-danger-border);">
              <i class="fa-solid fa-tag" style="font-size: 1rem;"></i>
            </div>
            <div>
              <div class="text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Monthly Rent</div>
              <div class="text-sm font-bold text-gray-800">₱<?= number_format($tenant['monthly_rent'] ?? 0, 2) ?></div>
            </div>
          </div>
        </div>

        <?php if (!empty($roommates)): ?>
          <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
              <i class="fa-solid fa-user-group" style="color:var(--color-brand);"></i> Roommates
            </div>
            <div class="flex flex-wrap gap-2">
              <?php foreach ($roommates as $rm): ?>
                <div class="flex items-center gap-2 pr-3 pl-1.5 py-1.5 rounded-full border text-xs font-bold text-gray-700 max-w-full" style="background:var(--color-info-light); border-color:var(--color-info-border);">
                  <div class="w-6 h-6 rounded-full text-white flex items-center justify-center font-bold text-[0.65rem] flex-shrink-0" style="background:var(--color-brand);">
                    <?= strtoupper(substr($rm['name'], 0, 1)) ?>
                  </div>
                  <span class="truncate max-w-[120px]"><?= htmlspecialchars($rm['name']) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="empty-state" style="padding:32px 24px;">
          <i class="fa-solid fa-door-closed"></i>
          <p>Your room assignment is pending</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Recent Bills (hidden on mobile to prevent table crowding/overflow) -->
    <div class="card bt-recent-bills-mobile-hide">
      <div class="card-header">
        <div class="card-title">Recent Bills</div>
        <a href="<?= Router::url('tenant/bills') ?>" class="btn btn-ghost btn-sm">View all</a>
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
                <td style="font-weight:600; color:var(--color-text-primary);">₱<?= number_format($b['amount'], 2) ?></td>
                <td style="color:var(--color-text-muted); font-size:0.85rem;"><?= date('M d, Y', strtotime($b['due_date'])) ?></td>
                <td><span class="badge <?= $bclass[$bs] ?? 'badge-normal' ?>"><?= ucfirst(str_replace('_',' ',$bs)) ?></span></td>
                <td>
                  <?php if (in_array($bs, ['unpaid','overdue'])): ?>
                    <a href="<?= Router::url('tenant/pay-bill/' . $b['id']) ?>" class="btn btn-primary btn-sm" style="border-radius:var(--radius-md); min-height:36px; padding:0 16px;">Pay</a>
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
        <a href="<?= Router::url('tenant/announcements') ?>" class="btn btn-ghost btn-sm">View all</a>
      </div>
      <?php if (!empty($recentAnnouncements)): ?>
        <div class="notif-list" style="max-height: 400px; overflow-y: auto;">
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


  </div>

</div>
