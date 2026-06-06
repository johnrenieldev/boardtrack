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
$totalRooms          = max(0, (int)($stats['totalRooms'] ?? 0));
$availableRooms      = max(0, (int)($stats['availableRooms'] ?? 0));
$maintenanceRooms    = max(0, (int)($stats['maintenanceRooms'] ?? 0));
$occupiedRooms       = max(0, min($totalRooms, (int)($stats['occupiedRooms'] ?? ($totalRooms - $availableRooms - $maintenanceRooms))));
?>

<div class="page-header mb-4">
  <div class="page-header-row flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl font-black text-gray-900 leading-tight">Dashboard</h1>
      <p class="text-[0.7rem] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Overview · <?= date('F j, Y') ?></p>
    </div>
    <?php if (($stats['pendingCount'] ?? 0) > 0): ?>
      <a href="<?= Router::url('landlord/tenants') ?>&status=pending" class="btn btn-primary btn-sm shadow-sm">
        <i class="fa-solid fa-user-clock text-[0.7rem]"></i> Review Pending
        <span class="ml-1 bg-white/20 px-1.5 py-0.5 rounded text-[0.65rem] font-black"><?= $stats['pendingCount'] ?></span>
      </a>
    <?php endif; ?>
  </div>
</div>

<!-- STAT CARDS -->
<div class="stats-grid">
  <a href="<?= Router::url('landlord/tenants?status=ready_for_review') ?>" class="stat-card <?= ($stats['pendingCount'] ?? 0) > 0 ? 'urgent' : '' ?>">
    <div class="stat-header">
      <div class="stat-icon-box">
        <i class="fa-solid fa-user-clock"></i>
      </div>
      <div class="stat-label">Ready for Review</div>
    </div>
    <div class="stat-value"><?= $stats['pendingCount'] ?? 0 ?></div>
    <div class="stat-footer">
      <span>+<?= $stats['incompleteCount'] ?? 0 ?></span> incomplete profiles
    </div>
  </a>
  
  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box">
        <i class="fa-solid fa-users"></i>
      </div>
      <div class="stat-label">Active Tenants</div>
    </div>
    <div class="stat-value"><?= $stats['activeCount'] ?? 0 ?></div>
    <div class="stat-footer">
      <span><?= $stats['waitingCount'] ?? 0 ?></span> on waiting list
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box">
        <i class="fa-solid fa-door-open"></i>
      </div>
      <div class="stat-label">Available Rooms</div>
    </div>
    <div class="stat-value"><?= $availableRooms ?></div>
    <div class="stat-footer">
      of <span><?= $totalRooms ?></span> total rooms
    </div>
  </div>

  <div class="stat-card <?= ($stats['unpaidBills'] ?? 0) > 0 ? 'urgent' : '' ?>">
    <div class="stat-header">
      <div class="stat-icon-box">
        <i class="fa-solid fa-file-invoice-dollar"></i>
      </div>
      <div class="stat-label">Unpaid Bills</div>
    </div>
    <div class="stat-value"><?= $stats['unpaidBills'] ?? 0 ?></div>
    <div class="stat-footer">
      <span><?= $stats['pendingPayments'] ?? 0 ?></span> pending review
    </div>
  </div>
</div>

<!-- DASHBOARD INSIGHTS ROW: Charts + Pending Payments -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-4 mt-4 md:mt-6 md:items-stretch items-start" style="align-items: start; align-content: start;">

  <!-- Room Occupancy Chart -->
  <div class="card dashboard-insight-card" style="min-width:0;">
    <div class="card-header">
      <div>
        <div class="card-title">Room Occupancy</div>
        <div class="card-subtitle">Current status</div>
      </div>
    </div>
    <!-- Visual Chart: Hidden on Mobile, Shown on Desktop -->
    <div class="chart-wrap responsive-chart-wrap hidden md:block" style="height: 200px;">
      <div class="responsive-chart-container" style="position: relative; height: 100%;">
        <canvas id="roomChart" class="responsive-chart"></canvas>
      </div>
    </div>
    <!-- Text-Based Alternative: Shown on Mobile, Hidden on Desktop -->
    <div class="dashboard-progress-fallback md:hidden" style="margin-top: 6px;">
      <!-- Available Rooms -->
      <div style="margin-bottom: 6px;">
        <div class="flex justify-between items-center text-xs mb-1" style="margin-bottom: 3px;">
          <span class="font-bold text-gray-600 flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background:var(--color-success);"></span> Available</span>
          <span class="font-black text-gray-900"><?= $availableRooms ?> / <?= $totalRooms ?> Rooms</span>
        </div>
        <div class="progress-track">
          <div class="progress-fill" style="background:var(--color-success); width: <?= $totalRooms > 0 ? ($availableRooms / $totalRooms) * 100 : 0 ?>%"></div>
        </div>
      </div>
      <!-- Occupied Rooms -->
      <div>
        <div class="flex justify-between items-center text-xs mb-1" style="margin-bottom: 3px;">
          <span class="font-bold text-gray-600 flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background:var(--color-brand);"></span> Occupied</span>
          <span class="font-black text-gray-900"><?= $occupiedRooms ?> / <?= $totalRooms ?> Rooms</span>
        </div>
        <div class="progress-track">
          <div class="progress-fill" style="background:var(--color-brand); width: <?= $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * 100 : 0 ?>%"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tenant Status Chart -->
  <div class="card dashboard-insight-card" style="min-width:0;">
    <div class="card-header">
      <div>
        <div class="card-title">Tenant Status</div>
        <div class="card-subtitle">Distribution</div>
      </div>
    </div>
    <!-- Visual Chart: Hidden on Mobile, Shown on Desktop -->
    <div class="chart-wrap responsive-chart-wrap hidden md:block" style="height: 200px;">
      <div class="responsive-chart-container" style="position: relative; height: 100%;">
        <canvas id="tenantChart" class="responsive-chart"></canvas>
      </div>
    </div>
    <!-- Text-Based Alternative: Shown on Mobile, Hidden on Desktop -->
    <?php
      $totalTenants = ($stats['activeCount'] ?? 0) + ($stats['totalPendingCount'] ?? 0) + ($stats['waitingCount'] ?? 0) + ($stats['rejectedCount'] ?? 0);
    ?>
    <div class="dashboard-progress-fallback md:hidden" style="margin-top: 6px;">
      <!-- Active Tenants -->
      <div style="margin-bottom: 6px;">
        <div class="flex justify-between items-center text-xs mb-1" style="margin-bottom: 3px;">
          <span class="font-bold text-gray-600 flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-success-500"></span> Active</span>
          <span class="font-black text-gray-900"><?= $stats['activeCount'] ?? 0 ?> / <?= $totalTenants ?> Tenants</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full overflow-hidden" style="height: 8px;">
          <div class="bg-success-500 h-full rounded-full" style="width: <?= $totalTenants > 0 ? (($stats['activeCount'] ?? 0) / $totalTenants) * 100 : 0 ?>%"></div>
        </div>
      </div>
      <!-- Pending Tenants -->
      <div style="margin-bottom: 6px;">
        <div class="flex justify-between items-center text-xs mb-1" style="margin-bottom: 3px;">
          <span class="font-bold text-gray-600 flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-warning-500"></span> Pending</span>
          <span class="font-black text-gray-900"><?= $stats['totalPendingCount'] ?? 0 ?> / <?= $totalTenants ?> Tenants</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full overflow-hidden" style="height: 8px;">
          <div class="bg-warning-500 h-full rounded-full" style="width: <?= $totalTenants > 0 ? (($stats['totalPendingCount'] ?? 0) / $totalTenants) * 100 : 0 ?>%"></div>
        </div>
      </div>
      <!-- Waiting List Tenants -->
      <div style="margin-bottom: 6px;">
        <div class="flex justify-between items-center text-xs mb-1" style="margin-bottom: 3px;">
          <span class="font-bold text-gray-600 flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-brand-500"></span> Waiting List</span>
          <span class="font-black text-gray-900"><?= $stats['waitingCount'] ?? 0 ?> / <?= $totalTenants ?> Tenants</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full overflow-hidden" style="height: 8px;">
          <div class="bg-brand-500 h-full rounded-full" style="width: <?= $totalTenants > 0 ? (($stats['waitingCount'] ?? 0) / $totalTenants) * 100 : 0 ?>%"></div>
        </div>
      </div>
      <!-- Rejected Tenants -->
      <div>
        <div class="flex justify-between items-center text-xs mb-1" style="margin-bottom: 3px;">
          <span class="font-bold text-gray-600 flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-danger-500"></span> Rejected</span>
          <span class="font-black text-gray-900"><?= $stats['rejectedCount'] ?? 0 ?> / <?= $totalTenants ?> Tenants</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full overflow-hidden" style="height: 8px;">
          <div class="bg-danger-500 h-full rounded-full" style="width: <?= $totalTenants > 0 ? (($stats['rejectedCount'] ?? 0) / $totalTenants) * 100 : 0 ?>%"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Pending Payments -->
  <div class="card dashboard-insight-card" style="min-width:0;">
    <div class="card-header">
      <div>
        <div class="card-title">Pending Payments</div>
        <div class="card-subtitle">Awaiting review</div>
      </div>
      <a href="<?= Router::url('landlord/payments') ?>" class="btn btn-ghost btn-sm">View all</a>
    </div>
    <?php if (!empty($pendingPayments)): ?>
      <div class="flex flex-col gap-1.5 overflow-hidden flex-1">
        <?php foreach (array_slice($pendingPayments, 0, 4) as $p): ?>
          <div class="flex items-start gap-2.5 py-2 px-2 border-l-2 border-brand-500 bg-blue-50 rounded min-w-0 text-xs">
            <div style="width:32px;height:32px;border-radius:var(--radius-md);background:var(--brand-light);color:var(--color-brand);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.85rem;">
              <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div class="flex-1 min-w-0 overflow-hidden">
              <div style="font-size:0.8rem;font-weight:700;color:var(--gray-900);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($p['tenant_name'] ?? '—') ?></div>
              <div style="font-size:0.7rem;color:var(--gray-500);margin-top:2px;">
                Amount: <span style="font-weight:600;color:var(--color-brand);">₱<?= number_format($p['amount'] ?? $p['amount_paid'] ?? 0, 2) ?></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state" style="min-height:140px; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:16px; background:var(--color-success-light); border:1px solid var(--color-success-border); border-radius:var(--radius-md);">
        <i class="fa-solid fa-circle-check" style="font-size:2rem; color:var(--color-success); margin-bottom:8px; opacity:0.9;"></i>
        <p style="font-size:0.85rem; font-weight:600; color:var(--color-success); margin-bottom:2px;">All Clear!</p>
        <p style="font-size:0.75rem; color:var(--color-success); opacity:0.8;">No pending payment reviews</p>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- RECENT COMPLAINTS (Full Width) -->
<div class="card" style="margin-top:24px;">
  <div class="card-header">
    <div>
      <div class="card-title">Recent Complaints</div>
      <div class="card-subtitle" style="font-size:0.75rem;">Latest submitted complaints</div>
    </div>
    <a href="<?= Router::url('landlord/complaints') ?>" class="btn btn-ghost btn-sm">View all</a>
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
            <th>Submitted</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentComplaints as $c): ?>
            <tr>
              <td data-label="Tenant">
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="width:32px;height:32px;border-radius:var(--radius);background:var(--primary);color:var(--color-surface);display:flex;align-items:center;justify-content:center;font-weight:600;font-size:0.78rem;flex-shrink:0;">
                    <?= $c['is_anonymous'] ? '?' : strtoupper(substr(htmlspecialchars($c['tenant_name'] ?? 'U'), 0, 1)) ?>
                  </div>
                  <div>
                    <div class="td-name"><?= $c['is_anonymous'] ? '<em style="color:var(--gray-400);">Anonymous</em>' : htmlspecialchars($c['tenant_name'] ?? '—') ?></div>
                  </div>
                </div>
              </td>
              <td data-label="Category">
                <div class="flex-center">
                  <?php
                    $categoryColors = [
                      'maintenance' => ['bg' => 'var(--color-info-light)', 'color' => 'var(--color-info)', 'border' => 'var(--color-info-border)', 'icon' => 'fa-wrench'],
                      'billing' => ['bg' => 'var(--color-warning-light)', 'color' => 'var(--color-warning)', 'border' => 'var(--color-warning-border)', 'icon' => 'fa-file-invoice'],
                      'neighbor' => ['bg' => 'var(--color-brand-light)', 'color' => 'var(--color-brand)', 'border' => 'var(--color-brand-border)', 'icon' => 'fa-users'],
                      'cleanliness' => ['bg' => 'var(--color-success-light)', 'color' => 'var(--color-success)', 'border' => 'var(--color-success-border)', 'icon' => 'fa-broom'],
                      'noise' => ['bg' => 'var(--color-danger-light)', 'color' => 'var(--color-danger)', 'border' => 'var(--color-danger-border)', 'icon' => 'fa-volume-high'],
                      'other' => ['bg' => 'var(--gray-50)', 'color' => 'var(--gray-600)', 'border' => 'var(--gray-200)', 'icon' => 'fa-circle-info'],
                    ];
                    $cat = $categoryColors[$c['category']] ?? $categoryColors['other'];
                  ?>
                  <span class="badge" style="background:<?= $cat['bg'] ?>;color:<?= $cat['color'] ?>;border-color:<?= $cat['border'] ?? 'transparent' ?>;gap:4px;">
                    <i class="fa-solid <?= $cat['icon'] ?>" style="font-size:0.7rem;"></i>
                    <?= htmlspecialchars(ucfirst(str_replace('_',' ',$c['category']))) ?>
                  </span>
                </div>
              </td>
              <td data-label="Title" class="td-name">
                <div class="flex-center">
                  <div style="font-weight:600;color:var(--gray-900);"><?= htmlspecialchars(substr($c['title'], 0, 40)) ?></div>
                </div>
              </td>
              <td data-label="Status">
                <div class="flex-center">
                  <?php
                    $cbadge = [
                      'pending' => ['bg' => 'var(--color-warning-light)', 'color' => 'var(--color-warning)', 'border' => 'var(--color-warning-border)', 'icon' => 'fa-clock', 'label' => 'Pending'],
                      'in_progress' => ['bg' => 'var(--color-brand-light)', 'color' => 'var(--color-brand)', 'border' => 'var(--color-brand-border)', 'icon' => 'fa-spinner', 'label' => 'In Progress'],
                      'resolved' => ['bg' => 'var(--color-success-light)', 'color' => 'var(--color-success)', 'border' => 'var(--color-success-border)', 'icon' => 'fa-check', 'label' => 'Resolved'],
                    ];
                    $status = $cbadge[$c['status']] ?? $cbadge['pending'];
                  ?>
                  <span class="badge" style="background:<?= $status['bg'] ?>;color:<?= $status['color'] ?>;border-color:<?= $status['border'] ?? 'transparent' ?>;gap:4px;">
                    <i class="fa-solid <?= $status['icon'] ?>" style="font-size:0.65rem;"></i>
                    <?= $status['label'] ?>
                  </span>
                </div>
              </td>
              <td data-label="Submitted">
                <div class="flex-center" style="color:var(--gray-500);font-size:0.82rem;white-space:nowrap;flex-direction:column;gap:0;">
                  <div><?= date('M d', strtotime($c['created_at'])) ?></div>
                  <div style="font-size:0.75rem;color:var(--gray-400);"><?= date('h:i A', strtotime($c['created_at'])) ?></div>
                </div>
              </td>
              <td data-label="Action">
                <div class="flex-center">
                  <a href="<?= Router::url('landlord/view-complaint/' . $c['id']) ?>" class="btn btn-outline btn-xs" style="border-radius:var(--radius-md); min-height:32px;">View</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state" style="padding:40px 20px;text-align:center;">
      <i class="fa-solid fa-inbox" style="font-size:3rem;color:var(--gray-300);margin-bottom:12px;display:block;"></i>
      <h3 style="font-size:1rem;font-weight:600;color:var(--gray-700);margin-bottom:4px;">No Complaints Yet</h3>
      <p style="font-size:0.85rem;color:var(--gray-400);">All is well! No tenant complaints have been submitted.</p>
    </div>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Debug: Log chart data to console
  console.log('Room Chart Data:', {
    available: <?= $availableRooms ?>,
    occupied: <?= $occupiedRooms ?>,
    total: <?= $totalRooms ?>
  });
  console.log('Tenant Chart Data:', {
    active: <?= (int)($stats['activeCount'] ?? 0) ?>,
    pending: <?= (int)($stats['totalPendingCount'] ?? 0) ?>,
    waiting: <?= (int)($stats['waitingCount'] ?? 0) ?>,
    rejected: <?= (int)($stats['rejectedCount'] ?? 0) ?>
  });
  console.log('Chart.js loaded:', typeof Chart !== 'undefined');

  if (typeof Chart === 'undefined') {
    console.error('Chart.js library not loaded!');
    document.querySelectorAll('.chart-wrap').forEach(function(el) { el.style.display = 'none'; });
    document.querySelectorAll('.dashboard-progress-fallback').forEach(function(el) { el.classList.remove('md:hidden'); });
    return;
  }

  var chartDefaults = {
    plugins: { 
      legend: { 
        position: 'bottom', 
        labels: { 
          padding: 12, 
          font: { size: 11 }, 
          boxWidth: 10 
        } 
      },
      tooltip: {
        enabled: true,
        callbacks: {
          label: function(context) {
            var label = context.label || '';
            var value = context.parsed || 0;
            var total = context.dataset.data.reduce((a, b) => a + b, 0);
            var percentage = total > 0 ? Math.round((value / total) * 100) : 0;
            return label + ': ' + value + ' (' + percentage + '%)';
          }
        }
      }
    },
    responsive: true,
    maintainAspectRatio: false,
  };

  var roomCtx = document.getElementById('roomChart');
  if (roomCtx) {
    var roomData = [
      <?= $availableRooms ?>,
      <?= $occupiedRooms ?>
    ];
    var roomTotal = roomData.reduce((a, b) => a + b, 0);
    
    console.log('Creating room chart with total:', roomTotal);
    
    if (roomTotal === 0) {
      // Show empty state message
      roomCtx.parentElement.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--gray-400);font-size:0.85rem;text-align:center;padding:20px;">No room data available yet.<br>Add rooms to see statistics.</div>';
    } else {
      new Chart(roomCtx, {
        type: 'doughnut',
        data: {
          labels: ['Available', 'Occupied'],
          datasets: [{
            data: roomData,
            backgroundColor: ['#047857','#2563eb'],
            borderWidth: 0,
            hoverOffset: 4,
          }]
        },
        options: { ...chartDefaults, cutout: '68%' }
      });
    }
  }

  var tenantCtx = document.getElementById('tenantChart');
  if (tenantCtx) {
    var tenantData = [
      <?= (int)($stats['activeCount']   ?? 0) ?>,
      <?= (int)($stats['totalPendingCount']  ?? 0) ?>,
      <?= (int)($stats['waitingCount']  ?? 0) ?>,
      <?= (int)($stats['rejectedCount'] ?? 0) ?>
    ];
    var tenantTotal = tenantData.reduce((a, b) => a + b, 0);
    
    console.log('Creating tenant chart with total:', tenantTotal);
    
    if (tenantTotal === 0) {
      // Show empty state message
      tenantCtx.parentElement.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--gray-400);font-size:0.85rem;text-align:center;padding:20px;">No tenant data available yet.<br>Add tenants to see statistics.</div>';
    } else {
      new Chart(tenantCtx, {
        type: 'doughnut',
        data: {
          labels: ['Active', 'Pending', 'Waiting List', 'Rejected'],
          datasets: [{
            data: tenantData,
            backgroundColor: ['#047857','#f59e0b','#2563eb','#be123c'],
            borderWidth: 0,
            hoverOffset: 4,
          }]
        },
        options: { ...chartDefaults, cutout: '68%' }
      });
    }
  }
});
</script>
