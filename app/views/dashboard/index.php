<?php require APP_PATH . '/views/layouts/header.php'; ?>

<?php
  // Safety net — this view should never be reached without a valid $user.
  // DashboardController::index() always passes it, but just in case:
  if (empty($user)) {
      header('Location: ' . BASE_URL . '/index.php?url=auth/login');
      exit;
  }

  $firstName = explode(' ', trim($user['name']))[0];
  $isLandlord = ($role ?? '') === 'landlord';
?>

<!-- ── TOPBAR ─────────────────────────────────────────────────── -->
<nav class="bg-white border-b border-slate-200 px-6 py-4
            flex items-center justify-between sticky top-0 z-50 shadow-sm">

  <!-- Logo -->
  <a href="<?= BASE_URL ?>/index.php?url=dashboard/index"
     class="font-heading font-extrabold text-lg text-brand-700
            border-2 border-brand-700 px-3 py-1 rounded-md">
    Board<span class="text-blue-600">Track</span>
  </a>

  <!-- Right: user info + logout -->
  <div class="flex items-center gap-4">

    <!-- Role badge -->
    <span class="hidden sm:inline-flex items-center gap-1.5
                 text-xs font-bold uppercase tracking-wider px-3 py-1.5 rounded-full
                 <?= $isLandlord
                   ? 'bg-blue-50 text-blue-600 border border-blue-200'
                   : 'bg-blue-50  text-brand-600 border border-blue-200' ?>">
      <i class="fa-solid <?= $isLandlord ? 'fa-user-tie' : 'fa-person' ?> text-[0.65rem]"></i>
      <?= $isLandlord ? 'Landlord' : 'Tenant' ?>
    </span>

    <!-- Avatar + name -->
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-full flex items-center justify-center
                  font-heading font-extrabold text-sm text-white
                  <?= $isLandlord
                    ? 'bg-amber-500'
                    : 'bg-blue-600' ?>">
        <?= strtoupper(substr($user['name'], 0, 1)) ?>
      </div>
      <span class="hidden sm:block text-sm font-semibold text-brand-900">
        <?= htmlspecialchars($user['name']) ?>
      </span>
    </div>

    <!-- Logout -->
    <a href="<?= BASE_URL ?>/index.php?url=auth/logout"
       class="confirm-logout flex items-center gap-1.5 text-sm font-semibold
              text-slate-500 hover:text-red-500
              transition-colors duration-150 px-2 py-1 rounded-lg
              hover:bg-red-50"
       data-message="Are you sure you want to log out?"
       title="Logout">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span class="hidden sm:inline">Logout</span>
    </a>

  </div>
</nav>

<!-- ── MAIN CONTENT ───────────────────────────────────────────── -->
<main class="max-w-5xl mx-auto px-6 py-14">

  <!-- Welcome block -->
  <div class="mb-10">
    <p class="text-brand-600 text-sm font-semibold uppercase
              tracking-widest mb-2">
      <?= date('l, F j, Y') ?>
    </p>
    <h1 class="font-heading font-extrabold text-brand-900 leading-tight"
        style="font-size: clamp(1.8rem, 4vw, 2.8rem);">
      Welcome back,
      <span class="text-brand-600">
        <?= htmlspecialchars($firstName) ?>
      </span> 👋
    </h1>
    <p class="text-slate-500 mt-2 text-[0.96rem]">
      <?php if ($isLandlord): ?>
        You have full control over your boarding house operations.
      <?php else: ?>
        Here&rsquo;s an overview of your boarding house status.
      <?php endif; ?>
    </p>
  </div>

  <!-- Account info card -->
  <div class="bg-white rounded-2xl border border-slate-200
              shadow-sm p-7 mb-8">
    <h2 class="font-heading font-bold text-brand-900 text-lg mb-5">
      Account Details
    </h2>

    <div class="grid sm:grid-cols-2 gap-4">

      <?php
        $fields = [
          ['fa-user',              'Full Name',  $user['name']],
          ['fa-envelope',          'Email',      $user['email']],
          ['fa-shield-halved',     'Role',       ucfirst($user['role'])],
          ['fa-circle-dot',        'Status',     ucfirst(str_replace('_', ' ', $user['status'] ?? 'active'))],
        ];
        foreach ($fields as [$icon, $label, $value]):
      ?>
        <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-50
                    border border-slate-100">
          <div class="w-9 h-9 rounded-lg flex-shrink-0
                      flex items-center justify-center
                      bg-slate-100 text-slate-600 text-sm">
            <i class="fa-solid <?= $icon ?>"></i>
          </div>
          <div>
            <p class="text-xs text-slate-400 font-medium uppercase
                      tracking-wider mb-0.5">
              <?= $label ?>
            </p>
            <p class="text-sm font-semibold text-brand-900">
              <?= htmlspecialchars($value) ?>
            </p>
          </div>
        </div>
      <?php endforeach; ?>

    </div>
  </div>

  <!-- Module placeholder cards -->
  <h2 class="font-heading font-bold text-brand-900 text-lg mb-5">
    <?= $isLandlord ? 'Management Modules' : 'Quick Access' ?>
  </h2>

  <?php if ($isLandlord): ?>
    <?php
      $modules = [
        ['fa-users',               'bg-slate-100 text-slate-600',   'Tenants',       'Review registrations, approve, assign rooms.'],
        ['fa-door-open',           'bg-slate-100 text-slate-600',   'Rooms',         'Manage room records and occupancy status.'],
        ['fa-file-invoice-dollar', 'bg-slate-100 text-slate-600',   'Billing',       'Issue and track rent bills per tenant.'],
        ['fa-money-check-dollar',  'bg-slate-100 text-slate-600',   'Payments',      'Verify uploaded payment proof screenshots.'],
        ['fa-circle-exclamation',  'bg-slate-100 text-slate-600',   'Complaints',    'Manage and respond to tenant complaints.'],
        ['fa-bullhorn',            'bg-slate-100 text-slate-600',   'Announcements', 'Post notices to all active tenants.'],
      ];
    ?>
  <?php else: ?>
    <?php
      $modules = [
        ['fa-door-open',           'bg-slate-100 text-slate-600',  'My Room',        'View your assigned room and roommate details.'],
        ['fa-file-invoice-dollar', 'bg-slate-100 text-slate-600',  'My Bills',       'View and pay rent bills.'],
        ['fa-circle-exclamation',  'bg-slate-100 text-slate-600',  'Complaints',     'Submit and track your complaints.'],
        ['fa-bullhorn',            'bg-slate-100 text-slate-600',  'Announcements',  'Read notices posted by your landlord.'],
      ];
    ?>
  <?php endif; ?>

  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($modules as [$icon, $iconClass, $title, $desc]): ?>
      <div class="bg-white rounded-2xl border border-slate-200
                  shadow-sm p-6 relative
                  cursor-default">

        <div class="w-11 h-11 rounded-xl flex items-center justify-center
                    text-lg mb-4 <?= $iconClass ?>">
          <i class="fa-solid <?= $icon ?>"></i>
        </div>
        <h4 class="font-heading font-bold text-brand-900 text-[0.95rem] mb-1">
          <?= $title ?>
        </h4>
        <p class="text-slate-500 text-[0.83rem] leading-relaxed">
          <?= $desc ?>
        </p>

        <!-- Coming soon tag -->
        <span class="absolute top-4 right-4 text-[0.65rem] font-bold
                     uppercase tracking-wider text-slate-400
                     bg-slate-100 px-2 py-1 rounded-full">
          Phase 2
        </span>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Auth debug strip (remove in production) -->
  <?php if (defined('APP_ENV') && APP_ENV === 'development'): ?>
    <div class="mt-12 bg-slate-900 text-green-400 font-mono text-xs
                rounded-xl p-5 leading-relaxed">
      <p class="text-slate-500 mb-2 font-sans text-[0.7rem] uppercase tracking-wider">
        Session Debug — remove in production
      </p>
      <p><span class="text-slate-500">user_id   :</span> <?= $_SESSION['user_id']   ?? '—' ?></p>
      <p><span class="text-slate-500">user_name :</span> <?= $_SESSION['user_name'] ?? '—' ?></p>
      <p><span class="text-slate-500">user_role :</span> <?= $_SESSION['user_role'] ?? '—' ?></p>
    </div>
  <?php endif; ?>

</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= Router::asset('js/confirm-actions.js') ?>"></script>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
```

---

**Here is the complete request → response flow from form to dashboard:**
```
── REGISTER ──────────────────────────────────────────────────────

  register.php  (form action → auth/registerPost)
       ↓
  AuthController::registerPost()
       ↓ validates → hashes → User::createUser()
       ↓
  redirect → auth/login   (with flash_success message)

── LOGIN ─────────────────────────────────────────────────────────

  login.php  (form action → auth/loginPost)
       ↓
  AuthController::loginPost()
       ↓ validates → User::findByEmail() → password_verify()
       ↓ session_regenerate_id()
       ↓ $_SESSION['user_id', 'user_name', 'user_role'] set
       ↓
  redirect → dashboard/index

── DASHBOARD ─────────────────────────────────────────────────────

  DashboardController::index()
       ↓ requireAuth()  ← redirects to auth/login if no session
       ↓ User::findById($_SESSION['user_id'])
       ↓
  view('dashboard/index', ['user' => $user, 'role' => $role])
       ↓
  header.php + index.php (view) + footer.php rendered

── LOGOUT ────────────────────────────────────────────────────────

  navbar logout link → auth/logout
       ↓
  AuthController::logout()
       ↓ $_SESSION = [] → session_destroy()
       ↓
  redirect → auth/login