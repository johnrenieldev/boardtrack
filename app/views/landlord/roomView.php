<?php
/**
 * BoardTrack — Landlord: View Room
 */
?>
<div class="dash-page-header">
  <div class="flex items-center gap-4">
    <a href="<?= Router::url('landlord/rooms') ?>" class="btn btn-sm btn-outline">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>
    <div>
      <h1 class="dash-page-title">Room <?= htmlspecialchars($room['room_number']) ?></h1>
      <p class="dash-page-sub">Room Details & Occupants</p>
    </div>
  </div>
</div>

<div class="dashboard-grid mt-6">
  <!-- Left: Room Info -->
  <div class="grid-col-4">
    <div class="data-card mb-6">
      <div class="card-header">
        <h3><i class="fa-solid fa-circle-info"></i> Room Information</h3>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Status</label>
          <span class="badge badge-<?= match($room['status']) {
            'available' => 'success',
            'occupied' => 'warning',
            'maintenance' => 'danger',
            default => 'secondary'
          } ?> p-2 px-3">
            <?= ucfirst($room['status']) ?>
          </span>
        </div>
        
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Floor</label>
            <div class="font-bold text-gray-800"><?= $room['floor'] ?></div>
          </div>
          <div>
            <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Type</label>
            <div class="font-bold text-gray-800"><?= ucfirst($room['room_type']) ?></div>
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Monthly Rent</label>
          <div class="text-xl font-bold text-blue-600">₱<?= number_format($room['monthly_rent'], 2) ?></div>
        </div>

        <div class="mb-4">
          <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Occupancy</label>
          <div class="flex items-center gap-3">
            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
              <div class="h-full bg-blue-500" style="width: <?= ($room['actual_occupants'] / $room['max_occupants']) * 100 ?>%"></div>
            </div>
            <span class="text-sm font-bold text-gray-700"><?= $room['actual_occupants'] ?> / <?= $room['max_occupants'] ?></span>
          </div>
        </div>

        <?php if ($room['description']): ?>
          <div class="mb-4">
            <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Description</label>
            <p class="text-sm text-gray-600"><?= nl2br(htmlspecialchars($room['description'])) ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Right: Occupants -->
  <div class="grid-col-8">
    <div class="data-card">
      <div class="card-header flex justify-between items-center">
        <h3><i class="fa-solid fa-users"></i> Current Occupants</h3>
        <?php if ($room['actual_occupants'] < $room['max_occupants']): ?>
          <span class="text-xs font-bold text-green-500"><i class="fa-solid fa-circle-check"></i> Has Available Space</span>
        <?php else: ?>
          <span class="text-xs font-bold text-red-500"><i class="fa-solid fa-circle-xmark"></i> Room Full</span>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <?php if (empty($room['occupants'])): ?>
          <div class="text-center py-10">
            <i class="fa-solid fa-user-slash text-4xl text-gray-200 mb-3"></i>
            <p class="text-gray-400">No tenants currently assigned to this room.</p>
          </div>
        <?php else: ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Tenant Name</th>
                <th>Status</th>
                <th>Joined On</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($room['occupants'] as $occupant): ?>
                <tr>
                  <td>
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                        <?= strtoupper(substr($occupant['name'], 0, 1)) ?>
                      </div>
                      <span class="font-medium text-gray-800"><?= htmlspecialchars($occupant['name']) ?></span>
                    </div>
                  </td>
                  <td>
                    <span class="badge badge-success">Active</span>
                  </td>
                  <td><?= date('M j, Y', strtotime($occupant['joined_at'] ?? $occupant['created_at'])) ?></td>
                  <td>
                    <a href="<?= Router::url('landlord/view-tenant/' . $occupant['id']) ?>" class="btn btn-sm btn-outline">
                      <i class="fa-solid fa-user"></i> Profile
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
.grid-col-4 { grid-column: span 4; }
.grid-col-8 { grid-column: span 8; }
</style>
