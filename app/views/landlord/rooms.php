<?php
/**
 * BoardTrack — Landlord: Rooms
 * app/views/landlord/rooms.php
 * Layout: landlord.php
 */
$rooms      = $rooms      ?? [];
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

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-door-open" style="margin-right:4px;"></i> Total Rooms</div>
    <div class="stat-value"><?= $statistics['total_rooms'] ?? 0 ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-circle-check" style="margin-right:4px;"></i> Available</div>
    <div class="stat-value"><?= $statistics['available'] ?? 0 ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-user" style="margin-right:4px;"></i> Occupied</div>
    <div class="stat-value"><?= $statistics['occupied'] ?? 0 ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-wrench" style="margin-right:4px;"></i> Maintenance</div>
    <div class="stat-value"><?= $statistics['maintenance'] ?? 0 ?></div>
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
            <th>Occupancy</th>
            <th>Monthly Rent</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rooms as $room): ?>
            <tr>
              <td>
                <span style="font-weight:700;font-size:0.95rem;color:var(--gray-900);"><?= htmlspecialchars($room['room_number']) ?></span>
              </td>
              <td style="color:var(--gray-500);">Floor <?= (int)$room['floor'] ?></td>
              <td>
                <span class="badge <?= $room['room_type'] === 'single' ? 'badge-single' : 'badge-shared' ?>">
                  <?= ucfirst($room['room_type']) ?>
                </span>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="flex:1;background:var(--gray-200);border-radius:99px;height:6px;min-width:60px;">
                    <div style="background:var(--primary);height:6px;border-radius:99px;width:<?= $room['max_occupants'] > 0 ? min(100, round(($room['actual_occupants'] / $room['max_occupants']) * 100)) : 0 ?>%;"></div>
                  </div>
                  <span style="font-size:0.82rem;color:var(--gray-500);white-space:nowrap;"><?= (int)$room['actual_occupants'] ?>/<?= (int)$room['max_occupants'] ?></span>
                </div>
              </td>
              <td style="font-weight:600;color:var(--gray-800);">₱<?= number_format($room['monthly_rent'], 2) ?></td>
              <td>
                <span class="badge <?= match($room['status']) {
                  'available'   => 'badge-available',
                  'occupied'    => 'badge-occupied',
                  'maintenance' => 'badge-maintenance',
                  default       => 'badge-normal'
                } ?>">
                  <?= ucfirst($room['status']) ?>
                </span>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:4px;">
                  <a href="<?= Router::url('landlord/view-room/' . $room['id']) ?>" class="btn btn-secondary btn-sm btn-icon" title="View Details">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                  <?php if (!empty($room['description'])): ?>
                    <button type="button" class="btn btn-secondary btn-sm btn-icon" title="Info" onclick="showInfoModal('<?= htmlspecialchars(addslashes($room['room_number'])) ?>', '<?= htmlspecialchars(addslashes($room['description'])) ?>')">
                      <i class="fa-solid fa-circle-info"></i>
                    </button>
                  <?php endif; ?>
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

<!-- Info Modal -->
<div class="modal-overlay" id="infoModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="infoModalTitle">Room Info</span>
      <button class="modal-close" onclick="closeModal('infoModal')">&times;</button>
    </div>
    <div class="modal-body">
      <p id="infoModalBody" style="color:var(--gray-600);margin:0;line-height:1.6;"></p>
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
