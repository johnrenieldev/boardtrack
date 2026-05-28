<?php
/**
 * BoardTrack — Landlord: Rooms
 * app/views/landlord/rooms.php
 * Layout: landlord.php
 */
$rooms      = $rooms      ?? [];
$filters    = $filters    ?? [];
$statistics = $statistics ?? [];
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Rooms</h1>
      <p class="page-subtitle">Manage room records, capacity, and occupancy.</p>
    </div>
    <button onclick="openModal('addRoomModal')" class="btn btn-primary btn-sm">
      <i class="fa-solid fa-plus"></i> Add Room
    </button>
  </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:16px;padding:14px 20px;">
  <form method="GET" action="<?= Router::url('landlord/rooms') ?>" class="filter-bar" style="margin-bottom:0;">
    <select name="air_conditioned" class="form-select">
      <option value="" <?= empty($filters['air_conditioned'] ?? '') ? 'selected' : '' ?>>All AC Types</option>
      <option value="1" <?= (string)($filters['air_conditioned'] ?? '') === '1' ? 'selected' : '' ?>>Air-conditioned only</option>
      <option value="0" <?= (string)($filters['air_conditioned'] ?? '') === '0' ? 'selected' : '' ?>>Non-air-conditioned only</option>
    </select>
    <select name="allowed_gender" class="form-select">
      <option value="" <?= empty($filters['allowed_gender'] ?? '') ? 'selected' : '' ?>>All Genders</option>
      <option value="male" <?= ($filters['allowed_gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male only</option>
      <option value="female" <?= ($filters['allowed_gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female only</option>
      <option value="any" <?= ($filters['allowed_gender'] ?? '') === 'any' ? 'selected' : '' ?>>Any/Mixed</option>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
    <a href="<?= Router::url('landlord/rooms') ?>" class="btn btn-ghost btn-sm">Clear</a>
  </form>
</div>

<!-- Stats -->
<div class="stats-grid grid-5-cols">
  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-door-open"></i></div>
      <div class="stat-label">Total Rooms</div>
    </div>
    <div class="stat-value"><?= $statistics['total_rooms'] ?? 0 ?></div>
    <div class="stat-footer">Across <span>all floors</span></div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-circle-check"></i></div>
      <div class="stat-label">Available</div>
    </div>
    <div class="stat-value"><?= $statistics['available'] ?? 0 ?></div>
    <div class="stat-footer">Ready for <span>occupancy</span></div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-user"></i></div>
      <div class="stat-label">Occupied</div>
    </div>
    <div class="stat-value"><?= $statistics['occupied'] ?? 0 ?></div>
    <div class="stat-footer">Active <span>tenants</span></div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-mars"></i></div>
      <div class="stat-label">Male Only</div>
    </div>
    <div class="stat-value"><?= $statistics['male_only'] ?? 0 ?></div>
    <div class="stat-footer">Assigned <span>rooms</span></div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-venus"></i></div>
      <div class="stat-label">Female Only</div>
    </div>
    <div class="stat-value"><?= $statistics['female_only'] ?? 0 ?></div>
    <div class="stat-footer">Assigned <span>rooms</span></div>
  </div>
</div>

<!-- Rooms Table -->
<div class="card">
  <?php if (empty($rooms)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-door-open"></i>
      <h3>No Rooms Yet</h3>
      <p>Start by adding your first room to the system.</p>
      <button onclick="openModal('addRoomModal')" class="btn btn-primary" style="margin-top:12px;">
        <i class="fa-solid fa-plus"></i> Add Room
      </button>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="bt-table">
        <thead>
          <tr>
            <th>Room No.</th>
            <th>Floor</th>
            <th>Type</th>
            <th>Allowed Gender</th>
            <th>Aircon</th>
            <th>Occupancy</th>
            <th data-col="amount">Monthly Rent</th>
            <th data-col="status">Status</th>
            <th data-col="actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rooms as $room): ?>
            <tr>
              <td data-label="Room No.">
                <span style="font-weight:700;font-size:0.95rem;color:var(--color-text-primary);"><?= htmlspecialchars($room['room_number']) ?></span>
              </td>
              <td data-label="Floor">
                <div class="flex-center" style="color:var(--color-text-secondary);">Floor <?= (int)$room['floor'] ?></div>
              </td>
              <td data-label="Type">
                <div class="flex-center">
                  <span class="badge <?= $room['room_type'] === 'single' ? 'badge-single' : 'badge-shared' ?>">
                    <?= ucfirst($room['room_type']) ?>
                  </span>
                </div>
              </td>
              <td data-label="Gender">
                <div class="flex-center">
                  <span class="badge <?= match($room['allowed_gender'] ?? 'any') {
                    'male'   => 'badge-occupied',
                    'female' => 'badge-pending',
                    'any'    => 'badge-normal',
                    default  => 'badge-normal'
                  } ?>">
                    <i class="fa-solid <?= match($room['allowed_gender'] ?? 'any') {
                      'male'   => 'fa-mars',
                      'female' => 'fa-venus',
                      'any'    => 'fa-venus-mars',
                      default  => 'fa-venus-mars'
                    } ?> text-[10px]"></i>
                    <?= match($room['allowed_gender'] ?? 'any') {
                      'any'   => 'Any/Mixed',
                      default => ucfirst($room['allowed_gender'])
                    } ?>
                  </span>
                </div>
              </td>
              <td data-label="Aircon">
                <div class="flex-center">
                  <span class="badge <?= !empty($room['air_conditioned']) ? 'badge-available' : 'badge-normal' ?>">
                    <?= !empty($room['air_conditioned']) ? 'A/C' : 'No A/C' ?>
                  </span>
                </div>
              </td>
              <td data-label="Occupancy">
                <div class="flex-center" style="width: 100%;">
                  <div style="display:flex;align-items:center;gap:10px;width: 100%;max-width: 120px;">
                    <div style="flex:1;background:var(--gray-200);border-radius:99px;height:6px;min-width:60px;">
                      <div style="background:var(--primary);height:6px;border-radius:99px;width:<?= $room['max_occupants'] > 0 ? min(100, round(($room['actual_occupants'] / $room['max_occupants']) * 100)) : 0 ?>%;"></div>
                    </div>
                    <span style="font-size:0.82rem;color:var(--color-text-secondary);white-space:nowrap;"><?= (int)$room['actual_occupants'] ?>/<?= (int)$room['max_occupants'] ?></span>
                  </div>
                </div>
              </td>
              <td data-label="Rent" data-col="amount">
                <div class="flex-center" style="font-weight:600;color:var(--color-text-primary);">₱<?= number_format($room['monthly_rent'], 2) ?></div>
              </td>
              <td data-label="Status" data-col="status">
                <div class="flex-center">
                  <span class="badge <?= match($room['status']) {
                    'available'   => 'badge-available',
                    'occupied'    => 'badge-occupied',
                    'maintenance' => 'badge-maintenance',
                    default       => 'badge-normal'
                  } ?>">
                    <?= ucfirst($room['status']) ?>
                  </span>
                </div>
              </td>
              <td data-label="Actions" data-col="actions">
                <div class="flex-center">
                  <a href="<?= Router::url('landlord/view-room/' . $room['id']) ?>" class="btn btn-secondary btn-sm btn-icon" title="View Details">
                    <i class="fa-solid fa-eye text-xs"></i>
                  </a>
                  <button type="button" class="btn btn-secondary btn-sm btn-icon" title="Edit" onclick="openEditRoomModal(<?= htmlspecialchars(json_encode($room)) ?>)">
                    <i class="fa-solid fa-pen text-xs"></i>
                  </button>
                  <form action="<?= Router::url('landlord/delete-room') ?>" method="POST" style="display:inline;"
                        data-confirm="Are you sure you want to delete Room <?= htmlspecialchars($room['room_number']) ?>?"
                        data-action="Delete room" data-color="#dc2626" data-confirm-text="Yes, delete">
                    <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete">
                      <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Add Room Modal -->
<div class="modal-overlay" id="addRoomModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">Add New Room</span>
      <button class="modal-close" onclick="closeModal('addRoomModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/add-room') ?>" method="POST">
      <div class="modal-body">
        <input type="hidden" name="room_id" value="">
        <div class="form-group">
          <label class="form-label">Room Number <span class="req">*</span></label>
          <input type="text" name="room_number" class="form-input" required placeholder="e.g., 101, A-1">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Floor</label>
            <input type="number" name="floor" class="form-input" value="1" min="1">
          </div>
          <div class="form-group">
            <label class="form-label">Type <span class="req">*</span></label>
            <select name="room_type" class="form-select" required>
              <option value="single">Single</option>
              <option value="shared">Shared</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Allowed Gender <span class="req">*</span></label>
            <select name="allowed_gender" class="form-select" required>
              <option value="any">Any / Mixed</option>
              <option value="male">Male only</option>
              <option value="female">Female only</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" style="display:flex;align-items:center;gap:10px;">
              <input type="checkbox" name="air_conditioned" value="1" style="width:16px;height:16px;">
              Air-conditioned (A/C)
            </label>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Max Occupants <span class="req">*</span></label>
            <input type="number" name="max_occupants" class="form-input" value="1" min="1" required>
          </div>
          <div class="form-group">
            <label class="form-label">Monthly Rent (₱) <span class="req">*</span></label>
            <input type="number" name="monthly_rent" class="form-input" step="0.01" required placeholder="5000.00">
          </div>
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Description / Notes</label>
          <textarea name="description" class="form-textarea" placeholder="Optional details about this room" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('addRoomModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Room</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Room Modal -->
<div class="modal-overlay" id="editRoomModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">Edit Room</span>
      <button class="modal-close" onclick="closeModal('editRoomModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/edit-room') ?>" method="POST" id="editRoomForm">
      <div class="modal-body">
        <input type="hidden" name="room_id" id="edit_room_id">
        <div class="form-group">
          <label class="form-label">Room Number <span class="req">*</span></label>
          <input type="text" name="room_number" id="edit_room_number" class="form-input" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Floor</label>
            <input type="number" name="floor" id="edit_floor" class="form-input" min="1">
          </div>
          <div class="form-group">
            <label class="form-label">Type <span class="req">*</span></label>
            <select name="room_type" id="edit_room_type" class="form-select" required>
              <option value="single">Single</option>
              <option value="shared">Shared</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Allowed Gender <span class="req">*</span></label>
            <select name="allowed_gender" id="edit_allowed_gender" class="form-select" required>
              <option value="any">Any / Mixed</option>
              <option value="male">Male only</option>
              <option value="female">Female only</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" style="display:flex;align-items:center;gap:10px;">
              <input type="checkbox" name="air_conditioned" id="edit_air_conditioned" value="1" style="width:16px;height:16px;">
              Air-conditioned (A/C)
            </label>
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" id="edit_status" class="form-select">
              <option value="available">Available</option>
              <option value="occupied">Occupied</option>
              <option value="maintenance">Maintenance</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Max Occupants <span class="req">*</span></label>
            <input type="number" name="max_occupants" id="edit_max_occupants" class="form-input" min="1" required>
          </div>
          <div class="form-group">
            <label class="form-label">Monthly Rent (₱) <span class="req">*</span></label>
            <input type="number" name="monthly_rent" id="edit_monthly_rent" class="form-input" step="0.01" required>
          </div>
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Description / Notes</label>
          <textarea name="description" id="edit_description" class="form-textarea" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('editRoomModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Room</button>
      </div>
    </form>
  </div>
</div>

<!-- Info Modal -->
<div class="modal-overlay" id="infoModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="infoModalTitle">Room Info</span>
      <button class="modal-close" onclick="closeModal('infoModal')">&times;</button>
    </div>
    <div class="modal-body">
      <p id="infoModalBody" style="color:var(--color-text-secondary);margin:0;line-height:1.6;"></p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal('infoModal')">Close</button>
    </div>
  </div>
</div>

<script>
function openModal(id) {
  document.getElementById(id).style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
  document.body.style.overflow = '';
}
function openEditRoomModal(room) {
  document.getElementById('edit_room_id').value = room.id;
  document.getElementById('edit_room_number').value = room.room_number;
  document.getElementById('edit_floor').value = room.floor;
  document.getElementById('edit_room_type').value = room.room_type;
  document.getElementById('edit_allowed_gender').value = room.allowed_gender || 'any';
  document.getElementById('edit_max_occupants').value = room.max_occupants;
  document.getElementById('edit_monthly_rent').value = room.monthly_rent;
  document.getElementById('edit_status').value = room.status;
  document.getElementById('edit_description').value = room.description || '';
  document.getElementById('edit_air_conditioned').checked = !!room.air_conditioned;
  openModal('editRoomModal');
}
function showInfoModal(roomNumber, description) {
  document.getElementById('infoModalTitle').textContent = 'Room ' + roomNumber + ' — Notes';
  document.getElementById('infoModalBody').textContent = description;
  openModal('infoModal');
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay').forEach(function(m) { m.style.display = 'none'; });
    document.body.style.overflow = '';
  }
});
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.style.display = 'none';
    document.body.style.overflow = '';
  }
});
</script>
