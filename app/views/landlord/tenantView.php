<?php
/**
 * BoardTrack — Landlord: View Tenant
 */
$tenant              = $tenant              ?? [];
$personalityAnswers  = $personalityAnswers  ?? [];
$bills               = $bills               ?? [];
$complaints          = $complaints          ?? [];
$isSuspicious        = $isSuspicious        ?? false;
$compatibilityScores = $compatibilityScores ?? [];
$availableRooms      = $availableRooms      ?? [];
?>
<div class="dash-page-header">
  <div class="flex items-center gap-4">
    <a href="<?= Router::url('landlord/tenants') ?>" id="backButton" class="btn btn-sm btn-outline">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>
    <div>
      <h1 class="dash-page-title"><?= htmlspecialchars($tenant['name']) ?></h1>
      <p class="dash-page-sub">Tenant Profile & Activity</p>
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
  <!-- Left Column: Profile -->
  <div class="lg:col-span-4 space-y-6">
    <div class="card p-8 text-center bg-white">
      <div class="w-24 h-24 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-4xl font-black mx-auto mb-6 border-4 border-white shadow-md relative">
        <?= strtoupper(substr($tenant['name'], 0, 1)) ?>
        <div class="absolute bottom-0 right-0 w-6 h-6 rounded-full border-4 border-white shadow-sm <?= match($tenant['user_status']) {
          'approved' => !empty($tenant['room_id']) ? 'bg-success-500' : 'bg-brand-500',
          'pending'  => 'bg-warning-500',
          'rejected' => 'bg-danger-500',
          default    => 'bg-gray-400'
        } ?>"></div>
      </div>
      <h2 class="text-2xl font-black text-gray-900 mb-1 leading-tight"><?= htmlspecialchars($tenant['name']) ?></h2>
      <p class="text-sm text-gray-500 font-medium mb-6"><?= htmlspecialchars($tenant['email']) ?></p>
      
      <div class="inline-flex px-4 py-1.5 rounded-lg text-[0.65rem] font-black uppercase tracking-widest border mb-8 <?= match($tenant['user_status']) {
        'approved' => !empty($tenant['room_id']) ? 'bg-success-50 text-success-600 border-success-200' : 'bg-brand-50 text-brand-600 border-brand-200',
        'pending'  => 'bg-warning-50 text-warning-600 border-warning-200',
        'rejected' => 'bg-danger-50 text-danger-600 border-danger-200',
        default    => 'bg-gray-50 text-gray-600 border-gray-200'
      } ?>">
        <?= match($tenant['user_status']) {
          'approved' => !empty($tenant['room_id']) ? 'Active' : 'Waiting List',
          default    => str_replace('_', ' ', $tenant['user_status'])
        } ?> Account
      </div>

      <div class="grid grid-cols-2 gap-4 pt-6 border-t border-gray-100">
        <div class="text-center">
          <div class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-widest mb-1">Room No.</div>
          <div class="font-black text-gray-900 text-lg"><?= $tenant['room_number'] ? 'R-' . $tenant['room_number'] : 'NONE' ?></div>
        </div>
        <div class="text-center border-l border-gray-100">
          <div class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-widest mb-1">Room Type</div>
          <div class="font-black text-gray-900 text-lg uppercase"><?= htmlspecialchars($tenant['room_type'] ?? 'N/A') ?></div>
        </div>
      </div>
    </div>

    <!-- Contact details -->
    <div class="card overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
        <h3 class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest">Account Details</h3>
      </div>
      <div class="p-6 space-y-5">
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 border border-gray-100">
            <i class="fa-solid fa-venus-mars"></i>
          </div>
          <div>
            <div class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-wider">Gender</div>
            <div class="text-sm font-black text-gray-900"><?= ucfirst(htmlspecialchars($tenant['gender'] ?? 'N/A')) ?></div>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 border border-gray-100">
            <i class="fa-solid fa-calendar"></i>
          </div>
          <div>
            <div class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-wider">Registration Date</div>
            <div class="text-sm font-black text-gray-900"><?= date('F j, Y', strtotime($tenant['registered_at'] ?? $tenant['created_at'])) ?></div>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 border border-gray-100">
            <i class="fa-solid fa-door-open"></i>
          </div>
          <div>
            <div class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-wider">Room Preference</div>
            <div class="text-sm font-black text-gray-900"><?= ucfirst(htmlspecialchars($tenant['room_type_preference'] ?? 'Not specified')) ?></div>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 border border-gray-100">
            <i class="fa-solid fa-snowflake"></i>
          </div>
          <div>
            <div class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-wider">A/C Preference</div>
            <div class="text-sm font-black text-gray-900"><?= !empty($tenant['air_conditioned_preference']) ? 'Yes, Preferred' : 'No Preference' ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Emergency Contact -->
    <div class="card overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
        <h3 class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest">Emergency Contact</h3>
      </div>
      <div class="p-6">
        <div class="p-5 bg-brand-50 rounded-2xl border border-brand-100 relative overflow-hidden">
          <i class="fa-solid fa-shield-heart absolute -right-4 -bottom-4 text-brand-100 text-6xl opacity-50"></i>
          <div class="relative">
            <div class="font-black text-brand-900 text-lg mb-1 leading-tight"><?= htmlspecialchars($tenant['guardian_name'] ?? 'Not provided') ?></div>
            <div class="text-xs text-brand-600 font-bold uppercase tracking-wider mb-4 flex items-center gap-1.5">
              <i class="fa-solid fa-envelope text-[10px]"></i>
              <?= htmlspecialchars($tenant['guardian_email'] ?? 'No email available') ?>
            </div>
            <div class="text-xs text-brand-800 leading-relaxed font-medium bg-white/60 backdrop-blur-sm p-3 rounded-xl border border-brand-100/50">
              <span class="text-[10px] font-black uppercase text-brand-400 block mb-1">Statement of Purpose</span>
              "<?= htmlspecialchars($tenant['guardian_purpose'] ?? 'No purpose stated') ?>"
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Column: Details & Tabs -->
  <div class="lg:col-span-8 space-y-6">
    <!-- Verification Status (Added context) -->
    <div class="flex items-center gap-4 p-4 bg-white rounded-2xl border border-gray-100 shadow-sm">
      <div class="flex-1">
        <div class="text-[0.6rem] font-black text-gray-400 uppercase tracking-widest mb-0.5">Application Status</div>
        <div class="flex items-center gap-2">
          <span class="w-2 h-2 rounded-full <?= match($tenant['user_status']) {
            'approved' => 'bg-success-500',
            'pending'  => 'bg-warning-500',
            'rejected' => 'bg-danger-500',
            default    => 'bg-gray-300'
          } ?>"></span>
          <span class="text-sm font-black text-gray-900 uppercase tracking-tight"><?= str_replace('_', ' ', $tenant['user_status']) ?></span>
        </div>
      </div>
      <div class="h-8 w-px bg-gray-100"></div>
      <div class="flex-1">
        <div class="text-[0.6rem] font-black text-gray-400 uppercase tracking-widest mb-0.5">Verification</div>
        <div class="flex items-center gap-1.5 text-success-600">
          <i class="fa-solid fa-circle-check text-[10px]"></i>
          <span class="text-xs font-black uppercase tracking-tight">Identity Provided</span>
        </div>
      </div>
      <div class="h-8 w-px bg-gray-100"></div>
      <div class="flex-1">
        <div class="text-[0.6rem] font-black text-gray-400 uppercase tracking-widest mb-0.5">Profile Match</div>
        <div class="text-xs font-black text-gray-900 uppercase tracking-tight">
          <?= !empty($personalityAnswers) ? 'Complete' : 'Pending' ?>
        </div>
      </div>
    </div>

    <!-- Registration Info -->
    <div class="card overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 bg-white flex justify-between items-center">
        <h3 class="font-black text-gray-900 flex items-center gap-2 uppercase tracking-widest text-xs">
          <i class="fa-solid fa-file-contract text-brand-600"></i> Verification Documents
        </h3>
      </div>
      <div class="p-8">
        <div class="mb-6">
          <label class="block text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-3">Primary Government ID</label>
          <?php $idDocPath = $tenant['id_document_path'] ?? $tenant['id_file_path'] ?? null; ?>
          <?php if ($idDocPath): ?>
            <div class="group relative rounded-2xl overflow-hidden border border-gray-200 bg-gray-50 shadow-sm transition-all hover:shadow-md max-w-2xl mx-auto">
              <img src="<?= Router::upload('ids', $idDocPath) ?>" class="w-full h-auto object-contain max-h-[450px] transition-transform duration-500 group-hover:scale-[1.02]">
              <div class="absolute inset-0 bg-gray-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-4 backdrop-blur-[2px]">
                <button onclick="window.open('<?= Router::upload('ids', $idDocPath) ?>', '_blank')" class="btn btn-primary px-6 py-2.5 font-black text-xs uppercase tracking-widest rounded-xl shadow-xl">
                  <i class="fa-solid fa-expand mr-2"></i> View Full Screen
                </button>
              </div>
            </div>
          <?php else: ?>
            <div class="p-12 text-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
              <i class="fa-solid fa-id-card-clip text-gray-300 text-5xl mb-4"></i>
              <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">No verification document found</p>
              <p class="text-xs text-gray-400 mt-1">Tenant has not uploaded their ID yet.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Review Actions & Room Assignment (Consolidated for Pending) -->
    <?php if ($tenant['user_status'] === 'pending'): ?>
      <div class="card overflow-hidden border-warning-200 shadow-lg shadow-warning-500/5">
        <div class="px-6 py-4 border-b border-warning-100 bg-warning-50/50 flex items-center justify-between">
          <h3 class="font-black text-warning-900 flex items-center gap-2 uppercase tracking-widest text-xs">
            <i class="fa-solid fa-shield-check"></i> Application Review
          </h3>
          <span class="px-3 py-1 bg-warning-100 text-warning-700 rounded-lg text-[0.65rem] font-black uppercase tracking-widest border border-warning-200">
            Action Required
          </span>
        </div>
        <div class="p-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
              <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1">Decision Summary</div>
              <p class="text-sm text-gray-600 leading-relaxed">
                Review the documents and personality profile carefully before making a decision. Once approved, the tenant can be assigned to a room.
              </p>
              
              <?php if (empty($personalityAnswers)): ?>
                <div class="flex items-center gap-3 p-4 bg-amber-50 rounded-2xl border border-amber-100">
                  <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                  </div>
                  <div>
                    <div class="text-xs font-black text-amber-900 uppercase">Questionnaire Pending</div>
                    <div class="text-[0.65rem] text-amber-700 font-bold">Waiting for tenant to complete profile.</div>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <div class="flex flex-col gap-3 justify-center">
              <?php if (!empty($personalityAnswers)): ?>
                <button type="button" class="btn btn-success py-4 flex items-center justify-center gap-3 text-sm font-black uppercase tracking-widest shadow-lg shadow-success-500/20 rounded-2xl transition-all hover:-translate-y-1 active:translate-y-0" onclick="showApproveModal()">
                  <i class="fa-solid fa-user-check text-lg"></i> Approve & Process
                </button>
              <?php else: ?>
                <button type="button" class="btn btn-secondary py-4 flex items-center justify-center gap-3 text-sm font-black uppercase tracking-widest opacity-50 cursor-not-allowed rounded-2xl" onclick="Swal.fire('Profile Incomplete', 'Tenant must complete the personality questionnaire first.', 'warning')">
                  <i class="fa-solid fa-user-check text-lg"></i> Approve & Process
                </button>
              <?php endif; ?>
              
              <button type="button" class="btn btn-outline-danger py-4 flex items-center justify-center gap-3 text-sm font-black uppercase tracking-widest rounded-2xl transition-all hover:bg-danger-50" onclick="showRejectModal()">
                <i class="fa-solid fa-user-xmark text-lg"></i> Decline Application
              </button>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Room Assignment -->
    <div class="card overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
        <h3 class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest">Room Allocation</h3>
      </div>
      <div class="p-6">
        <?php if ($tenant['room_id'] ?? null): ?>
          <div class="p-4 bg-brand-50 rounded-2xl border border-brand-100 mb-4 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-white border border-brand-100 flex items-center justify-center text-brand-600 text-2xl font-black shadow-sm">
              <?= htmlspecialchars($tenant['room_number'] ?? '—') ?>
            </div>
            <div>
              <div class="text-[0.65rem] font-black text-brand-400 uppercase tracking-widest">Active Placement</div>
              <div class="text-sm font-black text-brand-900">Floor <?= $tenant['floor'] ?? '—' ?> · <?= ucfirst($tenant['room_type'] ?? 'N/A') ?></div>
            </div>
          </div>
          <button type="button" class="btn btn-secondary w-full py-3 font-black text-[0.65rem] uppercase tracking-widest rounded-xl shadow-xs" onclick="showMoveOutModal()">
            <i class="fa-solid fa-right-from-bracket mr-2"></i> Mark as Moved Out
          </button>
        <?php else: ?>
          <div class="p-8 text-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 mb-4">
            <i class="fa-solid fa-door-closed text-gray-200 text-4xl mb-3"></i>
            <p class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest">No Active Placement</p>
          </div>
          <button type="button" class="btn btn-primary w-full py-3 font-black text-[0.65rem] uppercase tracking-widest rounded-xl shadow-md" onclick="showAssignModal()">
            <i class="fa-solid fa-plus mr-2"></i> Assign Room
          </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Match Status -->
    <div class="card overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
        <h3 class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest">Compatibility Analysis</h3>
      </div>
      <div class="p-6">
        <?php if (!empty($currentCompatibility)): ?>
          <?php $comp = $currentCompatibility; ?>
          <div class="flex items-center justify-between mb-6">
            <div class="w-20 h-20 rounded-full border-4 border-gray-50 flex items-center justify-center relative shadow-inner">
              <svg class="absolute inset-0 w-full h-full -rotate-90">
                <circle cx="40" cy="40" r="34" fill="none" stroke="currentColor" stroke-width="6" class="text-gray-100"></circle>
                <circle cx="40" cy="40" r="34" fill="none" stroke="currentColor" stroke-width="6" 
                  stroke-dasharray="213.6" 
                  stroke-dashoffset="<?= 213.6 - (213.6 * ($comp['score'] / 100)) ?>"
                  class="<?= match($comp['color']) {
                    'green' => 'text-success-500',
                    'blue' => 'text-brand-500',
                    'orange' => 'text-warning-500',
                    'red' => 'text-danger-500',
                    default => 'text-gray-300'
                  } ?>"></circle>
              </svg>
              <span class="text-lg font-black text-gray-900"><?= round($comp['score']) ?>%</span>
            </div>
            <div class="text-right">
              <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1">Status</div>
              <div class="text-xs font-black px-3 py-1 rounded-lg uppercase tracking-widest <?= match($comp['color']) {
                'green' => 'bg-success-50 text-success-600 border border-success-100',
                'blue' => 'bg-brand-50 text-brand-600 border border-brand-100',
                'orange' => 'bg-warning-50 text-warning-600 border border-warning-100',
                'red' => 'bg-danger-50 text-danger-600 border border-danger-100',
                default => 'bg-gray-50 text-gray-400 border border-gray-100'
              } ?>"><?= $comp['status'] ?></div>
            </div>
          </div>

          <div class="space-y-4">
            <div>
              <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-3 border-b border-gray-50 pb-1">Primary Match Factors</div>
              <div class="space-y-2">
                <?php foreach ($comp['explanation'] as $reason): ?>
                  <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-xl border border-gray-100/50">
                    <div class="w-5 h-5 rounded-full bg-white border border-gray-200 flex items-center justify-center flex-shrink-0">
                      <i class="fa-solid fa-check text-success-500 text-[10px]"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-700 leading-tight"><?= htmlspecialchars($reason) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="text-center py-8">
            <i class="fa-solid fa-layer-group text-gray-100 text-5xl mb-4"></i>
            <p class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest">No Analysis Available</p>
            <p class="text-xs text-gray-400 mt-2 px-6">Assign a room to generate compatibility metrics with existing roommates.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Questionnaire -->
    <div class="card overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 bg-white flex justify-between items-center">
        <h3 class="font-black text-gray-900 flex items-center gap-2 uppercase tracking-widest text-xs">
          <i class="fa-solid fa-brain text-brand-600"></i> Behavioral Profile
        </h3>
        <?php if ($isSuspicious): ?>
          <span class="bg-danger-50 text-danger-600 border border-danger-200 px-3 py-1 rounded-lg text-[0.65rem] font-black uppercase tracking-widest flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-shield-exclamation"></i> Suspicious Pattern Detected
          </span>
        <?php endif; ?>
      </div>
      <div class="p-8">
        <?php if (empty($personalityAnswers)): ?>
          <div class="text-center py-12 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-100">
            <i class="fa-solid fa-clipboard-question text-gray-200 text-5xl mb-4"></i>
            <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">Questionnaire not yet submitted</p>
          </div>
        <?php else: ?>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($personalityAnswers as $answer): ?>
              <div class="p-5 rounded-2xl bg-white border border-gray-100 shadow-sm transition-all hover:border-brand-200 group">
                <div class="text-[0.6rem] font-black text-gray-400 uppercase tracking-widest mb-2 group-hover:text-brand-500 transition-colors"><?= htmlspecialchars($answer['question_text'] ?? '—') ?></div>
                <div class="text-sm font-black text-gray-900 leading-snug"><?= htmlspecialchars($answer['answer_text'] ?? '—') ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Room Recommendations -->
    <?php if (!$tenant['room_id']): ?>
    <div class="card overflow-hidden">
      <div class="p-6 border-b border-gray-100 bg-white">
        <h3 class="font-bold text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-star text-brand-600"></i> Recommended Rooms
        </h3>
      </div>
      <div class="p-0 overflow-x-auto">
        <?php if (empty($recommendations)): ?>
          <div class="p-12 text-center text-gray-400 italic text-sm">
            No shared rooms available for matching.
          </div>
        <?php else: ?>
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-100">
                <th class="px-6 py-4 text-[0.65rem] font-black text-gray-400 uppercase tracking-widest">Room</th>
                <th class="px-6 py-4 text-[0.65rem] font-black text-gray-400 uppercase tracking-widest">Compatibility</th>
                <th class="px-6 py-4 text-[0.65rem] font-black text-gray-400 uppercase tracking-widest">Why?</th>
                <th class="px-6 py-4 text-[0.65rem] font-black text-gray-400 uppercase tracking-widest text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php foreach ($recommendations as $rec): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center font-black text-gray-900 shadow-xs">
                        <?= htmlspecialchars($rec['room_number']) ?>
                      </div>
                      <div>
                        <div class="text-xs font-bold text-gray-900"><?= $rec['current_occupants'] ?> occupants</div>
                        <div class="text-[0.65rem] text-gray-400 font-medium">Shared Room</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2 mb-1">
                      <span class="text-sm font-black text-gray-900"><?= round($rec['compatibility_score']) ?>%</span>
                      <span class="px-2 py-0.5 rounded-full text-[0.6rem] font-black uppercase tracking-tighter 
                        <?= match($rec['color']) {
                          'green' => 'bg-success-50 text-success-600 border border-success-200',
                          'blue' => 'bg-brand-50 text-brand-600 border border-brand-200',
                          'orange' => 'bg-warning-50 text-warning-600 border border-warning-200',
                          'red' => 'bg-danger-50 text-danger-600 border border-danger-200',
                          default => 'bg-gray-50 text-gray-600 border border-gray-200'
                        } ?>"><?= $rec['status'] ?></span>
                    </div>
                    <div class="w-24 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                      <div class="h-full <?= match($rec['color']) {
                        'green' => 'bg-success-500',
                        'blue' => 'bg-brand-500',
                        'orange' => 'bg-warning-500',
                        'red' => 'bg-danger-500',
                        default => 'bg-gray-300'
                      } ?>" style="width: <?= $rec['compatibility_score'] ?>%"></div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-1">
                      <?php foreach (array_slice($rec['reasons'], 0, 2) as $reason): ?>
                        <div class="text-[0.65rem] font-bold text-gray-600 flex items-center gap-1">
                          <i class="fa-solid fa-circle-check text-success-500 text-[10px]"></i>
                          <?= htmlspecialchars($reason) ?>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <?php if ($rec['compatibility_score'] >= 50): ?>
                      <button type="button" class="btn btn-sm btn-primary shadow-xs font-bold" onclick="setRoomAndShowModal(<?= $rec['room_id'] ?>)">
                        Assign
                      </button>
                    <?php else: ?>
                      <button type="button" class="btn btn-sm btn-outline-danger shadow-xs font-bold" onclick="setRoomAndShowModal(<?= $rec['room_id'] ?>)">
                        Assign Anyway
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function setRoomAndShowModal(roomId) {
  const modalId = '<?= $tenant['user_status'] === 'pending' ? 'approveModal' : 'assignRoomModal' ?>';
  const select = document.querySelector('#' + modalId + ' select[name="room_id"]');
  if (select) {
    select.value = roomId;
  }
  openModal(modalId);
}
</script>

<!-- Approve Modal -->
<div class="modal-overlay" id="approveModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Approve Tenant</span>
      <button class="modal-close" onclick="closeModal('approveModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/approve-tenant') ?>" method="POST">
      <input type="hidden" name="tenant_id" value="<?= $tenant['id'] ?? '' ?>">
      <div class="modal-body">
        <p style="margin:0 0 16px;color:var(--color-text-secondary);">You are about to approve <strong><?= htmlspecialchars($tenant['name'] ?? '') ?></strong>.</p>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Assign Room (Optional)</label>
          <select name="room_id" class="form-select" onchange="previewCompatibility(this.value, 'approveCompPreview')">
            <option value="">— Add to Waiting List —</option>
            <?php foreach ($availableRooms as $r): ?>
              <option value="<?= $r['id'] ?>">Room <?= htmlspecialchars($r['room_number']) ?> (<?= ucfirst($r['room_type']) ?>, <?= $r['max_occupants'] - ($r['actual_occupants'] ?? 0) ?> spots)</option>
            <?php endforeach; ?>
          </select>
          <div id="approveCompPreview" class="mt-3" style="display:none;"></div>
          <span class="form-help">Leave empty to add tenant to waiting list instead.</span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('approveModal')">Cancel</button>
        <button type="submit" class="btn btn-success">Approve Tenant</button>
      </div>
    </form>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal-overlay" id="rejectModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Reject Tenant</span>
      <button class="modal-close" onclick="closeModal('rejectModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/reject-tenant') ?>" method="POST"
          data-confirm="Reject this tenant application? This cannot be undone."
          data-action="Reject tenant" data-color="#dc2626" data-confirm-text="Yes, reject">
      <input type="hidden" name="tenant_id" value="<?= $tenant['id'] ?? '' ?>">
      <div class="modal-body">
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Rejection Reason <span class="req">*</span></label>
          <textarea name="reason" class="form-textarea" rows="3" required placeholder="Explain why this application is being rejected..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('rejectModal')">Cancel</button>
        <button type="submit" class="btn btn-danger">Reject Tenant</button>
      </div>
    </form>
  </div>
</div>

<!-- Assign Room Modal -->
<div class="modal-overlay" id="assignRoomModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Assign Room</span>
      <button class="modal-close" onclick="closeModal('assignRoomModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/approve-tenant') ?>" method="POST">
      <input type="hidden" name="tenant_id" value="<?= $tenant['id'] ?? '' ?>">
      <div class="modal-body">
        <p style="margin:0 0 16px;color:var(--color-text-secondary);">Assign a room to <strong><?= htmlspecialchars($tenant['name'] ?? '') ?></strong>.</p>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Select Room <span class="req">*</span></label>
          <select name="room_id" class="form-select" required onchange="previewCompatibility(this.value, 'assignCompPreview')">
            <option value="">— Select a Room —</option>
            <?php foreach ($availableRooms as $r): ?>
              <option value="<?= $r['id'] ?>">Room <?= htmlspecialchars($r['room_number']) ?> (<?= ucfirst($r['room_type']) ?>, <?= $r['max_occupants'] - ($r['actual_occupants'] ?? 0) ?> spots)</option>
            <?php endforeach; ?>
          </select>
          <div id="assignCompPreview" class="mt-3" style="display:none;"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('assignRoomModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Assign Room</button>
      </div>
    </form>
  </div>
</div>

<!-- Move Out Modal -->
<div class="modal-overlay" id="moveOutModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Mark as Moved Out</span>
      <button class="modal-close" onclick="closeModal('moveOutModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/move-out-tenant') ?>" method="POST"
          data-confirm="Mark this tenant as moved out and remove them from their room?"
          data-action="Confirm move out" data-color="#dc2626" data-confirm-text="Yes, move out">
      <input type="hidden" name="tenant_id" value="<?= $tenant['id'] ?? '' ?>">
      <div class="modal-body">
        <p style="margin:0 0 16px;color:var(--color-text-secondary);">Are you sure you want to mark <strong><?= htmlspecialchars($tenant['name'] ?? '') ?></strong> as moved out? This will remove them from Room <?= htmlspecialchars($tenant['room_number'] ?? '') ?>.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('moveOutModal')">Cancel</button>
        <button type="submit" class="btn btn-danger">Confirm Move Out</button>
      </div>
    </form>
  </div>
</div>

<!-- Modals Script -->
<script>
function openModal(id) {
  document.getElementById(id).style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
  document.body.style.overflow = '';
}
function showApproveModal() { openModal('approveModal'); }
function showRejectModal() { openModal('rejectModal'); }
function showAssignModal() { openModal('assignRoomModal'); }
function showMoveOutModal() { openModal('moveOutModal'); }

function previewCompatibility(roomId, containerId) {
  const container = document.getElementById(containerId);
  if (!roomId) {
    container.style.display = 'none';
    return;
  }

  container.innerHTML = '<div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin text-brand-500"></i><p class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-widest mt-2">Calculating Compatibility...</p></div>';
  container.style.display = 'block';

  fetch('<?= Router::url("landlord/compatibility-preview") ?>&tenant_id=<?= $tenant["id"] ?>&room_id=' + roomId)
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        container.innerHTML = '<div class="p-3 bg-danger-50 text-danger-600 rounded-lg text-xs font-bold">' + data.error + '</div>';
        return;
      }

      let reasonsHtml = '';
      if (data.explanation && data.explanation.length > 0) {
        reasonsHtml = `
          <div class="mt-3 pt-3 border-t border-gray-100">
            <div class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-widest mb-2">Why this match?</div>
            <div class="space-y-1">
              ${data.explanation.slice(0, 3).map(reason => `
                <div class="flex items-center gap-2 text-[0.7rem] font-bold text-gray-700">
                  <i class="fa-solid fa-check text-success-500"></i>
                  ${reason}
                </div>
              `).join('')}
            </div>
          </div>
        `;
      }

      container.innerHTML = `
        <div class="comp-preview-box">
          <div class="flex items-center justify-between mb-2">
            <div class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-widest">Compatibility Preview</div>
            <span class="comp-badge comp-badge-${data.color}">${data.status}</span>
          </div>
          <div class="flex items-center gap-3">
            <div class="text-xl font-black text-gray-900">${Math.round(data.score)}%</div>
            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-${data.color === 'blue' ? 'brand' : (data.color === 'green' ? 'success' : (data.color === 'orange' ? 'warning' : 'danger'))}-500" style="width: ${data.score}%"></div>
            </div>
          </div>
          ${reasonsHtml}
          ${data.score < 50 ? `
            <div class="mt-3 p-2 bg-danger-50 border border-danger-100 rounded text-[0.65rem] text-danger-600 font-bold flex items-start gap-2">
              <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
              <span>Low compatibility detected. This match may increase roommate conflict.</span>
            </div>
          ` : ''}
        </div>
      `;
    })
    .catch(err => {
      container.innerHTML = '<div class="p-3 bg-danger-50 text-danger-600 rounded-lg text-xs font-bold">Failed to load preview</div>';
    });
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
.profile-details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.detail-group label { display: block; font-size: 0.75rem; font-weight: 700; color: var(--color-text-muted); text-transform: uppercase; margin-bottom: 4px; }
.detail-value { font-size: 1rem; color: var(--color-text-primary); font-weight: 500; }
.id-preview-box { display: block; transition: opacity 0.2s; }
.id-preview-box:hover { opacity: 0.8; }
.grid-col-8 { grid-column: span 8; }
.grid-col-4 { grid-column: span 4; }
</style>
