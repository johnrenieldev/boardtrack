<?php
/**
 * BoardTrack — Landlord Dashboard View
 * app/views/landlord/dashboard.php
 * Layout: landlord.php
 */
$stats               = $stats               ?? [];
$recentComplaints    = $recentComplaints    ?? [];
$recentAnnouncements = $recentAnnouncements ?? [];
$pendingPayments     = $pendingPayments     ?? [];
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Dashboard</h1>
      <p class="page-subtitle">Overview of BoardTrack operations — <?= date('F j, Y') ?></p>
    </div>
    <a href="<?= Router::url('landlord/tenants') ?>?status=pending" class="btn btn-primary btn-sm">
      <i class="fa-solid fa-user-clock"></i> Review Pending
      <?php if (($stats['pendingCount'] ?? 0) > 0): ?>
        <span style="background:#fff;color:var(--primary);border-radius:10px;font-size:0.7rem;padding:1px 6px;margin-left:4px;font-weight:700;"><?= $stats['pendingCount'] ?></span>
      <?php endif; ?>
    </a>
  </div>
</div>

<!-- STAT CARDS -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-user-clock" style="margin-right:4px;"></i> Pending Tenants</div>
    <div class="stat-value">
      <?= $stats['pendingCount'] ?? 0 ?>
    </div>
    <div class="stat-meta">Awaiting review</div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-users" style="margin-right:4px;"></i> Active Tenants</div>
    <div class="stat-value"><?= $stats['activeCount'] ?? 0 ?></div>
    <div class="stat-meta"><?= $stats['waitingCount'] ?? 0 ?> on waiting list</div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-door-open" style="margin-right:4px;"></i> Available Rooms</div>
    <div class="stat-value"><?= $stats['availableRooms'] ?? 0 ?></div>
    <div class="stat-meta">of <?= $stats['totalRooms'] ?? 0 ?> total</div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-file-invoice" style="margin-right:4px;"></i> Unpaid Bills</div>
    <div class="stat-value">
      <?= $stats['unpaidBills'] ?? 0 ?>
    </div>
    <div class="stat-meta"><?= $stats['pendingPayments'] ?? 0 ?> pending verification</div>
  </div>
</div>

<!-- Second row -->
<div class="stats-grid" style="margin-top:-4px;">
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-triangle-exclamation" style="margin-right:4px;"></i> Open Complaints</div>
    <div class="stat-value"><?= $stats['openComplaints'] ?? 0 ?></div>
    <div class="stat-meta">Need attention</div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-circle-check" style="margin-right:4px;"></i> Payments Approved</div>
    <div class="stat-value"><?= $stats['approvedPayments'] ?? 0 ?></div>
    <div class="stat-meta">This month</div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-wrench" style="margin-right:4px;"></i> Maintenance Rooms</div>
    <div class="stat-value"><?= $stats['maintenanceRooms'] ?? 0 ?></div>
    <div class="stat-meta">Under maintenance</div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-user-xmark" style="margin-right:4px;"></i> Rejected Tenants</div>
    <div class="stat-value"><?= $stats['rejectedCount'] ?? 0 ?></div>
    <div class="stat-meta">Total</div>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="grid-content" style="margin-top:16px;">

  <!-- LEFT: Charts + Tables -->
  <div>

    <!-- Charts -->
    <div class="grid-2" style="margin-bottom:16px;">
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Room Occupancy</div>
            <div class="card-subtitle">Current status</div>
          </div>
        </div>
        <div class="chart-wrap" style="display:flex;align-items:center;justify-content:center;">
          <canvas id="roomChart" style="max-height:180px;max-width:220px;"></canvas>
        </div>
      </div>
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Tenant Status</div>
            <div class="card-subtitle">Distribution</div>
          </div>
        </div>
        <div class="chart-wrap" style="display:flex;align-items:center;justify-content:center;">
          <canvas id="tenantChart" style="max-height:180px;max-width:220px;"></canvas>
        </div>
      </div>
    </div>

    <!-- Recent Complaints -->
    <div class="card" style="margin-bottom:16px;">
      <div class="card-header">
        <div class="card-title">Recent Complaints</div>
        <a href="<?= Router::url('landlord/complaints') ?>" class="btn btn-secondary btn-sm">View all</a>
      </div>
      <?php if (!empty($recentComplaints)): ?>
        <div class="table-wrap">
          <table class="bt-table">
            <thead>
              <tr>
                <th>Tenant</th>
                <th>Category</th>
                <th>Title</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentComplaints as $c): ?>
                <tr>
                  <td><?= $c['is_anonymous'] ? '<em style="color:var(--gray-400)">Anonymous</em>' : htmlspecialchars($c['tenant_name'] ?? '—') ?></td>
                  <td><span class="badge badge-normal"><?= htmlspecialchars(ucfirst(str_replace('_',' ',$c['category']))) ?></span></td>
                  <td class="td-name"><?= htmlspecialchars($c['title']) ?></td>
                  <td>
                    <?php
                      $cbadge = ['pending'=>'badge-open','in_progress'=>'badge-progress','resolved'=>'badge-resolved'];
                    ?>
                    <span class="badge <?= $cbadge[$c['status']] ?? 'badge-normal' ?>">
                      <?= ucfirst(str_replace('_',' ',$c['status'])) ?>
                    </span>
                  </td>
                  <td style="color:var(--gray-400);font-size:0.82rem;"><?= date('M d', strtotime($c['created_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa-solid fa-flag"></i>
          <p>No recent complaints</p>
        </div>
      <?php endif; ?>
    </div>

  </div>

  <!-- RIGHT: Quick Actions + Pending Payments -->
  <div>

    <!-- Quick actions -->
    <div class="card" style="margin-bottom:16px;">
      <div class="card-title" style="margin-bottom:12px;">Quick Actions</div>
      <div style="display:flex;flex-direction:column;gap:6px;">
        <a href="<?= Router::url('landlord/tenants') ?>?status=pending" class="btn btn-secondary" style="justify-content:flex-start;">
          <i class="fa-solid fa-user-clock"></i> Review Pending Tenants
        </a>
        <a href="<?= Router::url('landlord/payments') ?>" class="btn btn-secondary" style="justify-content:flex-start;">
          <i class="fa-solid fa-credit-card"></i> Verify Payments
        </a>
        <a href="<?= Router::url('landlord/bills') ?>" class="btn btn-secondary" style="justify-content:flex-start;">
          <i class="fa-solid fa-file-invoice"></i> Manage Billing
        </a>
        <a href="<?= Router::url('landlord/announcements') ?>" class="btn btn-secondary" style="justify-content:flex-start;">
          <i class="fa-solid fa-bullhorn"></i> Post Announcement
        </a>
        <a href="<?= Router::url('landlord/rooms') ?>" class="btn btn-secondary" style="justify-content:flex-start;">
          <i class="fa-solid fa-door-open"></i> Manage Rooms
        </a>
      </div>
    </div>

    <!-- Pending payments -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">Pending Payments</div>
        <a href="<?= Router::url('landlord/payments') ?>" class="btn btn-secondary btn-sm">All</a>
      </div>
      <?php if (!empty($pendingPayments)): ?>
        <div style="display:flex;flex-direction:column;gap:2px;">
          <?php foreach (array_slice($pendingPayments, 0, 5) as $p): ?>
            <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--gray-100);">
              <div style="width:32px;height:32px;border-radius:var(--radius);background:var(--gray-100);color:var(--gray-500);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.78rem;">
                <i class="fa-solid fa-receipt"></i>
              </div>
              <div style="flex:1;min-width:0;">
                <div style="font-size:0.85rem;font-weight:500;color:var(--gray-900);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($p['tenant_name'] ?? '—') ?></div>
                <div style="font-size:0.75rem;color:var(--gray-400);">₱<?= number_format($p['amount_paid'] ?? 0, 2) ?></div>
              </div>
              <a href="<?= Router::url('landlord/payments') ?>" class="btn btn-primary btn-sm">Review</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa-solid fa-circle-check"></i>
          <p>No pending payments to review</p>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var chartDefaults = {
    plugins: { legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 }, boxWidth: 10 } } },
    responsive: true,
    maintainAspectRatio: true,
  };

  var roomCtx = document.getElementById('roomChart');
  if (roomCtx) {
    new Chart(roomCtx, {
      type: 'doughnut',
      data: {
        labels: ['Available', 'Occupied', 'Maintenance'],
        datasets: [{
          data: [
            <?= (int)($stats['availableRooms'] ?? 0) ?>,
            <?= (int)(($stats['totalRooms'] ?? 0) - ($stats['availableRooms'] ?? 0) - ($stats['maintenanceRooms'] ?? 0)) ?>,
            <?= (int)($stats['maintenanceRooms'] ?? 0) ?>
          ],
          backgroundColor: ['#16a34a','#2563eb','#dc2626'],
          borderWidth: 0,
          hoverOffset: 4,
        }]
      },
      options: { ...chartDefaults, cutout: '68%' }
    });
  }

  var tenantCtx = document.getElementById('tenantChart');
  if (tenantCtx) {
    new Chart(tenantCtx, {
      type: 'doughnut',
      data: {
        labels: ['Active', 'Pending', 'Waiting List', 'Rejected'],
        datasets: [{
          data: [
            <?= (int)($stats['activeCount']   ?? 0) ?>,
            <?= (int)($stats['pendingCount']  ?? 0) ?>,
            <?= (int)($stats['waitingCount']  ?? 0) ?>,
            <?= (int)($stats['rejectedCount'] ?? 0) ?>
          ],
          backgroundColor: ['#16a34a','#d97706','#2563eb','#dc2626'],
          borderWidth: 0,
          hoverOffset: 4,
        }]
      },
      options: { ...chartDefaults, cutout: '68%' }
    });
  }
});
</script>
