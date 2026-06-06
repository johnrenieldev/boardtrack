<?php
/**
 * BoardTrack — Landlord: View Room
 */
?>
<div class="dash-page-header">
  <div class="flex items-center gap-4">
    <a href="<?= Router::url('landlord/rooms') ?>" id="backButton" class="btn btn-sm btn-outline">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>
    <div>
      <h1 class="dash-page-title">Room <?= htmlspecialchars($room['room_number']) ?></h1>
      <p class="dash-page-sub">Room Details & Occupants</p>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var backButton = document.getElementById('backButton');
  if (backButton && sessionStorage.getItem('fromNotifications') === 'true') {
    backButton.href = '<?= Router::url('landlord/notifications') ?>';
    sessionStorage.removeItem('fromNotifications');
  }
});
</script>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
  <!-- Left Column: Room Details -->
  <div class="lg:col-span-7">
    <div class="card mb-6">
      <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-white">
        <h3 class="font-bold text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-door-open text-brand-600"></i> Room Information
        </h3>
        <?php
          $occupancyPercent = ($room['actual_occupants'] / $room['max_occupants']) * 100;
          $statusBadge = $occupancyPercent >= 100 ? 'bg-danger-50 text-danger-600 border-danger-200' : 'bg-success-50 text-success-600 border-success-200';
          $statusLabel = $occupancyPercent >= 100 ? 'OCCUPIED' : 'AVAILABLE';
        ?>
        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border <?= $statusBadge ?>">
          <?= $statusLabel ?>
        </span>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
          <div>
            <label class="block text-[0.65rem] font-bold uppercase text-gray-400 tracking-wider mb-1">Status</label>
            <div class="font-bold <?= $occupancyPercent >= 100 ? 'text-danger-600' : 'text-success-600' ?>">
              <?= $statusLabel ?>
            </div>
          </div>
          <div>
            <label class="block text-[0.65rem] font-bold uppercase text-gray-400 tracking-wider mb-1">Floor</label>
            <div class="font-bold text-gray-900"><?= htmlspecialchars($room['floor']) ?></div>
          </div>
          <div>
            <label class="block text-[0.65rem] font-bold uppercase text-gray-400 tracking-wider mb-1">Type</label>
            <div class="font-bold text-gray-900"><?= ucfirst(htmlspecialchars($room['room_type'])) ?></div>
          </div>
          <div>
            <label class="block text-[0.65rem] font-bold uppercase text-gray-400 tracking-wider mb-1">Allowed Gender</label>
            <div class="font-bold text-gray-900"><?= $room['allowed_gender'] === 'any' ? 'Any / Mixed' : ucfirst(htmlspecialchars($room['allowed_gender'])) ?></div>
          </div>
          <div>
            <label class="block text-[0.65rem] font-bold uppercase text-gray-400 tracking-wider mb-1">Monthly Rent</label>
            <div class="text-xl font-black text-brand-600">₱<?= number_format($room['monthly_rent'], 2) ?></div>
          </div>
          <div>
            <label class="block text-[0.65rem] font-bold uppercase text-gray-400 tracking-wider mb-1">Occupancy</label>
            <div class="flex items-center gap-2">
              <span class="font-bold text-gray-900"><?= $room['actual_occupants'] ?> / <?= $room['max_occupants'] ?></span>
              <div class="w-24 h-2 bg-gray-100 rounded-full overflow-hidden border border-gray-200">
                <div class="h-full <?= $occupancyPercent >= 100 ? 'bg-danger-500' : 'bg-success-500' ?>" style="width: <?= $occupancyPercent ?>%"></div>
              </div>
            </div>
          </div>
        </div>

        <?php if ($room['description']): ?>
          <div class="mt-8">
            <label class="block text-[0.65rem] font-bold uppercase text-gray-400 tracking-wider mb-1">Description</label>
            <div class="text-sm text-gray-600 leading-relaxed italic p-4 bg-gray-50 rounded-lg border border-gray-200">
              "<?= nl2br(htmlspecialchars($room['description'])) ?>"
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Occupants Table -->
    <div class="card" style="padding: 0;">
      <div class="p-6 border-b border-gray-100 bg-white flex justify-between items-center">
        <h3 class="font-bold text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-users text-brand-600"></i> Current Occupants
        </h3>
        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">
          <?= $room['actual_occupants'] ?> Registered
        </span>
      </div>
      <div class="overflow-x-auto" style="width: 100%; padding: 0;">
        <?php if (empty($room['occupants'])): ?>
          <div class="p-12 text-center text-gray-400 italic">
            No occupants currently assigned to this room.
          </div>
        <?php else: ?>
          <table class="bt-table" style="width: 100%; min-width: 100%; margin: 0;">
            <thead>
              <tr>
                <th class="text-left px-6 py-4 bg-gray-50 text-xs font-bold uppercase tracking-wider text-gray-500">Tenant Name</th>
                <th class="text-center px-6 py-4 bg-gray-50 text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                <th class="text-center px-6 py-4 bg-gray-50 text-xs font-bold uppercase tracking-wider text-gray-500">Joined On</th>
                <th class="text-center px-6 py-4 bg-gray-50 text-xs font-bold uppercase tracking-wider text-gray-500">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php foreach ($room['occupants'] as $occupant): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 font-bold text-gray-900 text-sm text-left" data-label="Tenant Name"><?= htmlspecialchars($occupant['name']) ?></td>
                  <td class="px-6 py-4 text-center" data-label="Status">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[0.6rem] font-black uppercase tracking-widest bg-success-50 text-success-600 border border-success-200">
                      <?= htmlspecialchars($occupant['user_status'] ?? 'APPROVED') ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 text-xs text-gray-500 font-medium text-center" data-label="Joined On"><?= date('M j, Y', strtotime($occupant['joined_at'] ?? $occupant['created_at'])) ?></td>
                  <td class="px-6 py-4 text-center" data-label="Actions">
                    <a href="<?= Router::url('landlord/view-tenant/' . $occupant['id']) ?>" class="btn btn-secondary btn-sm inline-flex items-center gap-2">
                      <i class="fa-solid fa-user-circle text-[0.7rem]"></i> Profile
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

  <!-- Right Column: Room Stats / Quick Info -->
  <div class="lg:col-span-5">
    <div class="card p-8 text-center bg-white">
      <div class="w-20 h-20 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center text-3xl font-black mx-auto mb-6 border border-brand-100 shadow-sm">
        <i class="fa-solid fa-door-closed"></i>
      </div>
      <h2 class="text-2xl font-black text-gray-900 mb-1">Room <?= htmlspecialchars($room['room_number']) ?></h2>
      <p class="text-sm text-gray-500 font-medium mb-8">Management & Monitoring</p>
      
      <div class="flex items-center justify-center gap-2 mb-8 p-3 bg-gray-50 rounded-xl border border-gray-200">
        <?php if ($occupancyPercent >= 100): ?>
          <i class="fa-solid fa-circle-xmark text-danger-500"></i>
          <span class="text-xs font-bold text-danger-700 uppercase tracking-widest">Room Full</span>
        <?php else: ?>
          <i class="fa-solid fa-circle-check text-success-500"></i>
          <span class="text-xs font-bold text-success-700 uppercase tracking-widest">Has Available Space</span>
        <?php endif; ?>
      </div>

      <div class="space-y-3">
        <button type="button" class="btn btn-secondary w-full py-3 flex items-center justify-center gap-2 font-bold shadow-xs" onclick="showEditRoomModal()">
          <i class="fa-solid fa-pen-to-square"></i> Edit Room Details
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Room Modal -->
<div class="modal-overlay" id="editRoomModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Edit Room Details</span>
      <button class="modal-close" onclick="closeModal('editRoomModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/update-room') ?>" method="POST">
      <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Room Number <span class="req">*</span></label>
          <input type="text" name="room_number" class="form-input" required value="<?= htmlspecialchars($room['room_number']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Floor <span class="req">*</span></label>
          <input type="number" name="floor" class="form-input" required value="<?= htmlspecialchars($room['floor']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Room Type <span class="req">*</span></label>
          <select name="room_type" class="form-select" required>
            <option value="single" <?= ($room['room_type'] ?? '') === 'single' ? 'selected' : '' ?>>Single</option>
            <option value="shared" <?= ($room['room_type'] ?? '') === 'shared' ? 'selected' : '' ?>>Shared</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Allowed Gender <span class="req">*</span></label>
          <select name="allowed_gender" class="form-select" required>
            <option value="any" <?= ($room['allowed_gender'] ?? 'any') === 'any' ? 'selected' : '' ?>>Any / Mixed</option>
            <option value="male" <?= ($room['allowed_gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male only</option>
            <option value="female" <?= ($room['allowed_gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female only</option>
            <option value="prefer_not_to_say" <?= ($room['allowed_gender'] ?? '') === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer not to say only</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" style="display:flex;align-items:center;gap:10px;">
            <input
              type="checkbox"
              name="air_conditioned"
              value="1"
              <?= !empty($room['air_conditioned']) ? 'checked' : '' ?>
              style="width:16px;height:16px;"
            >
            Air-conditioned (A/C)
          </label>
        </div>
        <div class="form-group">
          <label class="form-label">Monthly Rent (₱) <span class="req">*</span></label>
          <input type="number" name="monthly_rent" class="form-input" required step="0.01" value="<?= htmlspecialchars($room['monthly_rent']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Max Occupants <span class="req">*</span></label>
          <input type="number" name="max_occupants" class="form-input" required min="1" value="<?= htmlspecialchars($room['max_occupants']) ?>">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-textarea" rows="3" placeholder="Optional room description..."><?= htmlspecialchars($room['description'] ?? '') ?></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('editRoomModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
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
function showEditRoomModal() {
  openModal('editRoomModal');
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

<style>
/* Make table with rounded corners */
.bt-table {
  width: 100% !important;
  min-width: 100% !important;
  table-layout: fixed !important;
  border-radius: 8px !important;
  overflow: hidden !important;
}

/* Round the card corners */
.card {
  border-radius: 8px !important;
  overflow: hidden !important;
}

/* Ensure container doesn't restrict width */
.overflow-x-auto {
  overflow-x: hidden !important;
  width: 100% !important;
  border-radius: 0 0 8px 8px !important;
}

/* Distribute columns to fill full width */
.bt-table th:nth-child(1),
.bt-table td:nth-child(1) {
  width: 35% !important;
}

.bt-table th:nth-child(2),
.bt-table td:nth-child(2) {
  width: 20% !important;
}

.bt-table th:nth-child(3),
.bt-table td:nth-child(3) {
  width: 25% !important;
}

.bt-table th:nth-child(4),
.bt-table td:nth-child(4) {
  width: 20% !important;
}

/* Normal padding and font sizes */
.bt-table th {
  padding: 12px 16px !important;
  font-size: 0.75rem !important;
  font-weight: 600 !important;
}

.bt-table td {
  padding: 12px 16px !important;
  font-size: 0.875rem !important;
}

/* Tenant name styling */
.bt-table td:first-child {
  font-size: 0.875rem !important;
  font-weight: 600 !important;
}

/* Status badge normal size */
.bt-table td:nth-child(2) span {
  font-size: 0.6rem !important;
  padding: 4px 8px !important;
}

/* Joined date normal size */
.bt-table td:nth-child(3) {
  font-size: 0.75rem !important;
}

/* Profile button normal size */
.bt-table td:nth-child(4) .btn {
  font-size: 0.75rem !important;
  padding: 6px 12px !important;
}

.bt-table td:nth-child(4) .btn i {
  font-size: 0.7rem !important;
}

/* Mobile responsive */
@media (max-width: 767px) {
  /* Hide table headers */
  .bt-table thead {
    display: none !important;
  }
  
  /* Make each row a card */
  .bt-table tbody tr {
    display: block !important;
    margin-bottom: 16px !important;
    border: 1px solid var(--gray-200) !important;
    border-radius: 8px !important;
    background: white !important;
    padding: 0 !important;
  }
  
  /* First cell (tenant name) - full width grey background */
  .bt-table td:first-child {
    display: block !important;
    width: 100% !important;
    padding: 14px 16px !important;
    font-size: 0.95rem !important;
    font-weight: 700 !important;
    border-bottom: 1px solid var(--gray-100) !important;
    text-align: left !important;
    background: var(--gray-50) !important;
    margin: 0 !important;
  }
  
  /* Other cells - label on left, value on right */
  .bt-table td:not(:first-child) {
    display: flex !important;
    justify-content: flex-start !important;
    align-items: center !important;
    padding: 10px 14px !important;
    border: none !important;
    gap: 12px !important;
  }
  
  /* Add labels before content */
  .bt-table td:not(:first-child)::before {
    content: attr(data-label) !important;
    font-weight: 600 !important;
    color: var(--gray-500) !important;
    font-size: 0.7rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    width: 85px !important;
    min-width: 85px !important;
    flex-shrink: 0 !important;
  }
  
  /* Center the profile button properly - use flexbox */
  .bt-table td:nth-child(4) {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    padding: 14px 16px !important;
    text-align: center !important;
    gap: 0 !important;
    width: 100% !important;
  }
  
  .bt-table td:nth-child(4)::before {
    display: none !important;
  }
  
  .bt-table td:nth-child(4) a {
    display: inline-flex !important;
    margin: 0 !important;
  }
  
  .bt-table td:nth-child(4) .btn {
    width: auto !important;
    display: inline-flex !important;
  }
  
  /* Make status badge and date align properly */
  .bt-table td:nth-child(2),
  .bt-table td:nth-child(3) {
    text-align: left !important;
  }
  
  /* Make status badge wrap tightly around text */
  .bt-table td:nth-child(2) span {
    display: inline-flex !important;
    width: auto !important;
    max-width: fit-content !important;
  }
}
</style>
