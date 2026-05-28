<?php
/**
 * BoardTrack — Tenant Pending View
 * app/views/tenant/pending.php
 * Layout: tenant.php
 */
$user   = $user   ?? [];
$tenant = $tenant ?? [];
$status = $user['status'] ?? 'pending';

// Determine current step (1-6)
$currentStep = 1;
$isEmailVerified = !empty($user['email_verified_at']) || $status !== 'unverified'; // If they reach here, email is usually verified
$isIdUploaded = !empty($tenant['id_document_path']);
$isQuizDone = !empty($tenant['personality_completed']);
$isApproved = ($status === 'approved');
$isRoomAssigned = !empty($tenant['room_id']);

if ($isEmailVerified) $currentStep = 2;
if ($isIdUploaded) $currentStep = 3;
if ($isQuizDone) $currentStep = 4;
if ($isApproved) $currentStep = 5;
if ($isRoomAssigned) $currentStep = 6;

$steps = [
    1 => ['title' => 'Register', 'desc' => 'Account creation & email verification', 'icon' => 'fa-user-plus'],
    2 => ['title' => 'Upload ID', 'desc' => 'Identity verification', 'icon' => 'fa-id-card'],
    3 => ['title' => 'Personality Test', 'desc' => '10-question compatibility quiz', 'icon' => 'fa-brain'],
    4 => ['title' => 'Await Approval', 'desc' => 'Landlord review of application', 'icon' => 'fa-user-check'],
    5 => ['title' => 'Room Assignment', 'desc' => 'Finalizing your room & roommates', 'icon' => 'fa-door-open'],
    6 => ['title' => 'Get Started', 'desc' => 'Full access to your dashboard', 'icon' => 'fa-rocket'],
];
?>

<div class="page-header mb-6">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Registration Progress</h1>
      <p class="page-subtitle">Complete these steps to activate your account</p>
    </div>
  </div>
</div>

<!-- PROGRESS TRACKER -->
<div class="card mb-6 p-6">
  <!-- Desktop Horizontal View (Hidden on Mobile) -->
  <div class="hidden md:flex items-center justify-between relative pt-2 pb-6">
    <!-- Connecting Line -->
    <div class="absolute top-7 left-0 w-full h-0.5 bg-gray-200 -z-0"></div>
    <div class="absolute top-7 left-0 h-0.5 bg-brand-500 transition-all duration-500 -z-0" style="width: <?= (($currentStep - 1) / 5) * 100 ?>%;"></div>

    <?php foreach ($steps as $num => $s): 
      $isDone = $num < $currentStep;
      $isCurrent = $num === $currentStep;
    ?>
      <div class="flex flex-col items-center text-center relative z-10" style="width: 16.66%;">
        <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 <?= $isDone ? 'bg-brand-500 border-brand-500 text-white' : ($isCurrent ? 'bg-white border-brand-500 text-brand-500' : 'bg-white border-gray-300 text-gray-400') ?>">
          <?php if ($isDone): ?>
            <i class="fa-solid fa-check text-sm"></i>
          <?php else: ?>
            <span class="text-xs font-black"><?= $num ?></span>
          <?php endif; ?>
        </div>
        <div class="mt-3">
          <div class="text-[0.65rem] font-black uppercase tracking-widest <?= $isCurrent ? 'text-brand-600' : ($isDone ? 'text-gray-900' : 'text-gray-400') ?>"><?= $s['title'] ?></div>
          <div class="text-[0.6rem] text-gray-400 font-medium leading-tight max-w-[100px] mx-auto mt-0.5"><?= $s['desc'] ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Mobile Vertical View (Hidden on Desktop) -->
  <div class="md:hidden space-y-4">
    <?php foreach ($steps as $num => $s): 
      $isDone = $num < $currentStep;
      $isCurrent = $num === $currentStep;
    ?>
      <div class="flex items-start gap-4 p-3 rounded-xl transition-colors <?= $isCurrent ? 'bg-brand-50 border border-brand-100' : '' ?>">
        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all duration-300 <?= $isDone ? 'bg-brand-500 border-brand-500 text-white' : ($isCurrent ? 'bg-white border-brand-500 text-brand-500' : 'bg-white border-gray-200 text-gray-300') ?>">
          <?php if ($isDone): ?>
            <i class="fa-solid fa-check text-xs"></i>
          <?php else: ?>
            <span class="text-[10px] font-black"><?= $num ?></span>
          <?php endif; ?>
        </div>
        <div class="flex-grow">
          <div class="flex items-center gap-2">
            <span class="text-[0.7rem] font-black uppercase tracking-widest <?= $isCurrent ? 'text-brand-600' : ($isDone ? 'text-gray-900' : 'text-gray-400') ?>">
              <?= $s['title'] ?>
            </span>
            <?php if ($isCurrent): ?>
              <span class="px-1.5 py-0.5 bg-brand-500 text-white text-[8px] font-black rounded-full uppercase tracking-tighter">Current</span>
            <?php endif; ?>
          </div>
          <p class="text-[0.65rem] <?= $isCurrent ? 'text-brand-700' : 'text-gray-400' ?> leading-tight mt-0.5"><?= $s['desc'] ?></p>
        </div>
      </div>
      <?php if ($num < 6): ?>
        <div class="ml-4 h-4 w-0.5 bg-gray-100"></div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
  
  <!-- Current Action Card -->
  <div class="lg:col-span-8">
    <div class="card p-8">
      <?php if ($currentStep === 3): ?>
        <div class="text-center">
          <div class="w-20 h-20 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center text-3xl mx-auto mb-6">
            <i class="fa-solid fa-brain"></i>
          </div>
          <h2 class="text-2xl font-black text-gray-900 mb-2">Step 3: Compatibility Questionnaire</h2>
          <p class="text-gray-500 mb-8 max-w-md mx-auto">To provide you with the best possible living experience, please complete our 10-question personality test. This helps us match you with compatible roommates.</p>
          <a href="<?= Router::url('personality/personality') ?>" class="btn btn-primary btn-lg px-10 shadow-lg">
            <i class="fa-solid fa-clipboard-list"></i> Start Questionnaire
          </a>
        </div>

      <?php elseif ($currentStep === 4): ?>
        <div class="text-center">
          <div class="w-20 h-20 rounded-full bg-warning-50 text-warning-600 flex items-center justify-center text-3xl mx-auto mb-6">
            <i class="fa-solid fa-hourglass-half"></i>
          </div>
          <h2 class="text-2xl font-black text-gray-900 mb-2">Step 4: Application Under Review</h2>
          <p class="text-gray-500 mb-6 max-w-md mx-auto">Your account is currently under review by the landlord. Access will be available once approved.</p>
          <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 rounded-full border text-xs font-bold text-gray-500">
            <span class="w-2 h-2 rounded-full bg-warning-500 animate-pulse"></span>
            STATUS: PENDING LANDLORD APPROVAL
          </div>
        </div>

      <?php elseif ($currentStep === 5): ?>
        <div class="text-center">
          <div class="w-20 h-20 rounded-full bg-success-50 text-success-600 flex items-center justify-center text-3xl mx-auto mb-6">
            <i class="fa-solid fa-circle-check"></i>
          </div>
          <h2 class="text-2xl font-black text-gray-900 mb-2">Step 5: Account Approved!</h2>
          <p class="text-gray-500 mb-6 max-w-md mx-auto">Great news! Your application has been approved. We are now matching you with the best available room and roommates based on your preferences.</p>
          <div class="p-4 bg-info-50 rounded-xl border border-info-100 text-info-700 text-sm font-medium">
            <i class="fa-solid fa-circle-info mr-1"></i> You will receive a notification as soon as a room is assigned to you.
          </div>
        </div>

      <?php else: ?>
        <!-- Should not really happen if logic is tight -->
        <div class="text-center py-10">
          <i class="fa-solid fa-circle-info text-4xl text-gray-200 mb-4"></i>
          <p class="text-gray-500">Please complete the current registration step to proceed.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Checklist / Info Sidebar -->
  <div class="lg:col-span-4">
    <div class="card p-6">
      <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">Your Checklist</h3>
      <div class="space-y-4">
        <div class="flex items-center gap-3">
          <div class="w-6 h-6 rounded-full bg-success-500 text-white flex items-center justify-center text-[10px]">
            <i class="fa-solid fa-check"></i>
          </div>
          <span class="text-sm font-bold text-gray-900">Email Verified</span>
        </div>
        <div class="flex items-center gap-3">
          <div class="w-6 h-6 rounded-full bg-success-500 text-white flex items-center justify-center text-[10px]">
            <i class="fa-solid fa-check"></i>
          </div>
          <span class="text-sm font-bold text-gray-900">Government ID Uploaded</span>
        </div>
        <div class="flex items-center gap-3">
          <div class="w-6 h-6 rounded-full <?= $isQuizDone ? 'bg-success-500 text-white' : 'bg-gray-100 text-gray-300' ?> flex items-center justify-center text-[10px]">
            <i class="fa-solid <?= $isQuizDone ? 'fa-check' : 'fa-circle' ?>"></i>
          </div>
          <span class="text-sm font-bold <?= $isQuizDone ? 'text-gray-900' : 'text-gray-400' ?>">Personality Test</span>
        </div>
        <div class="flex items-center gap-3">
          <div class="w-6 h-6 rounded-full <?= $isApproved ? 'bg-success-500 text-white' : 'bg-gray-100 text-gray-300' ?> flex items-center justify-center text-[10px]">
            <i class="fa-solid <?= $isApproved ? 'fa-check' : 'fa-circle' ?>"></i>
          </div>
          <span class="text-sm font-bold <?= $isApproved ? 'text-gray-900' : 'text-gray-400' ?>">Landlord Approval</span>
        </div>
        <div class="flex items-center gap-3">
          <div class="w-6 h-6 rounded-full <?= $isRoomAssigned ? 'bg-success-500 text-white' : 'bg-gray-100 text-gray-300' ?> flex items-center justify-center text-[10px]">
            <i class="fa-solid <?= $isRoomAssigned ? 'fa-check' : 'fa-circle' ?>"></i>
          </div>
          <span class="text-sm font-bold <?= $isRoomAssigned ? 'text-gray-900' : 'text-gray-400' ?>">Room Assigned</span>
        </div>
      </div>

      <div class="mt-8 pt-6 border-t border-gray-100">
        <h4 class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-3">Need Help?</h4>
        <p class="text-[0.7rem] text-gray-500 leading-relaxed mb-4">If you're having trouble with the registration process, please contact support.</p>
        <a href="mailto:support@boardtrack.com" class="text-xs font-black text-brand-600 hover:underline flex items-center gap-1.5">
          <i class="fa-solid fa-envelope"></i> Contact Support
        </a>
      </div>
    </div>
  </div>

</div>

