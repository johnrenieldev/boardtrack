<?php
/**
 * BoardTrack — Landing Page
 * app/views/home/landing.php
 * Layout: main.php
 */
?>

<!-- NAVBAR -->
<nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
  <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
    <a href="<?= Router::url('home/index') ?>" class="font-heading font-bold text-lg text-gray-900">
      Board<span class="text-brand-600">Track</span>
    </a>
    <div class="flex items-center gap-3">
      <a href="<?= Router::url('auth/login') ?>"
         class="text-sm font-medium text-gray-600 hover:text-gray-900 px-3 py-1.5">
        Log In
      </a>
      <a href="<?= Router::url('auth/register') ?>"
         class="text-sm font-semibold bg-brand-600 text-white px-4 py-2 rounded-md hover:bg-brand-700 transition-colors">
        Register
      </a>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="bg-white border-b border-gray-100">
  <div class="max-w-6xl mx-auto px-6 py-20 grid md:grid-cols-2 gap-16 items-center">
    <div>
      <p class="text-brand-600 text-sm font-semibold uppercase tracking-wider mb-3">
        Boarding House Management System
      </p>
      <h1 class="text-4xl font-bold text-gray-900 leading-tight mb-5">
        Manage your boarding house, organized and paperless.
      </h1>
      <p class="text-gray-500 text-lg leading-relaxed mb-8">
        BoardTrack consolidates tenant registration, room assignment, billing, complaints,
        and announcements into one secure platform — for both landlords and tenants.
      </p>
      <div class="flex gap-3 flex-wrap">
        <a href="<?= Router::url('auth/register') ?>"
           class="bg-brand-600 text-white font-semibold px-6 py-3 rounded-md hover:bg-brand-700 transition-colors flex items-center gap-2">
          <i class="fa-solid fa-user-plus text-sm"></i> Register as Tenant
        </a>
        <a href="<?= Router::url('auth/login') ?>"
           class="border border-gray-300 text-gray-700 font-semibold px-6 py-3 rounded-md hover:bg-gray-50 transition-colors flex items-center gap-2">
          <i class="fa-solid fa-right-to-bracket text-sm"></i> Landlord Login
        </a>
      </div>
    </div>
    <div class="hidden md:block">
      <div class="bg-gray-50 border border-gray-200 rounded-xl p-8">
        <div class="grid grid-cols-2 gap-4">
          <?php
            $metrics = [
              ['Modules', '8', 'Complete modules built-in'],
              ['Users', '2', 'Landlord & Tenant roles'],
              ['Secure', 'Yes', 'PDO + bcrypt hashing'],
              ['Web', '100%', 'No mobile app needed'],
            ];
            foreach ($metrics as [$label, $val, $sub]):
          ?>
          <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="text-2xl font-bold text-gray-900 font-heading"><?= $val ?></div>
            <div class="text-sm font-semibold text-gray-700 mt-0.5"><?= $label ?></div>
            <div class="text-xs text-gray-400 mt-1"><?= $sub ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="py-20 bg-gray-50">
  <div class="max-w-6xl mx-auto px-6">
    <div class="text-center mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-3">Everything you need to run a boarding house</h2>
      <p class="text-gray-500 max-w-xl mx-auto">One platform for landlords and tenants. No spreadsheets, no paperwork.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
      <?php
        $features = [
          ['fa-user-check',  'Tenant Registration',   'Government ID upload, email verification, and landlord approval workflow with full account lifecycle.'],
          ['fa-brain',       'Personality Matching',   'Questionnaire-based compatibility scoring helps landlords assign compatible roommates to shared rooms.'],
          ['fa-door-open',   'Room Management',        'Create and manage rooms with type, capacity, and occupancy tracking. Enforce capacity limits automatically.'],
          ['fa-file-invoice','Billing & Payments',     'Issue digital bills per tenant. Tenants upload payment proof. Landlord manually verifies all payments.'],
          ['fa-flag',        'Complaint Tracking',     'Categorized complaints with status updates. Optional anonymity for roommate conflict complaints.'],
          ['fa-scroll',      'Audit Log',              'Full read-only log of all critical actions — approvals, assignments, billing events, and logins.'],
        ];
        foreach ($features as [$icon, $title, $desc]):
      ?>
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="w-9 h-9 bg-brand-50 rounded-md flex items-center justify-center mb-4">
          <i class="fa-solid <?= $icon ?> text-brand-600 text-sm"></i>
        </div>
        <h3 class="font-semibold text-gray-900 mb-2"><?= $title ?></h3>
        <p class="text-sm text-gray-500 leading-relaxed"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="py-20 bg-white border-t border-gray-100">
  <div class="max-w-6xl mx-auto px-6">
    <div class="text-center mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-3">How it works</h2>
      <p class="text-gray-500">Simple steps to get tenants registered and settled in.</p>
    </div>
    <div class="grid md:grid-cols-5 gap-6 text-center">
      <?php
        $steps = [
          ['1', 'Register', 'Submit details and government ID'],
          ['2', 'Personality Quiz', 'Complete compatibility questionnaire'],
          ['3', 'Landlord Review', 'Landlord approves or rejects'],
          ['4', 'Room Assigned', 'Based on availability and personality'],
          ['5', 'Dashboard Access', 'Manage bills, complaints, announcements'],
        ];
        foreach ($steps as [$num, $title, $desc]):
      ?>
      <div>
        <div class="w-10 h-10 bg-brand-600 text-white rounded-full flex items-center justify-center font-bold text-sm mx-auto mb-3">
          <?= $num ?>
        </div>
        <div class="font-semibold text-gray-900 text-sm mb-1"><?= $title ?></div>
        <div class="text-xs text-gray-400"><?= $desc ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="bg-gray-900 text-gray-400 py-10">
  <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
    <div>
      <div class="font-heading font-bold text-white text-lg mb-1">
        Board<span class="text-brand-500">Track</span>
      </div>
      <div class="text-xs">Boarding House Management System — For Academic Use Only</div>
    </div>
    <div class="flex gap-4 text-sm">
      <a href="<?= Router::url('auth/login') ?>"    class="hover:text-white transition-colors">Log In</a>
      <a href="<?= Router::url('auth/register') ?>" class="hover:text-white transition-colors">Register</a>
    </div>
  </div>
</footer>