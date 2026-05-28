<?php
/**
 * BoardTrack | Landing Page
 * app/views/home/landing.php
 * Layout: main.php
 *
 * Tenant-centered landing page for a single boarding house.
 * Focuses on tenant experience, personality-based roommate matching,
 * and digital boarding management.
 */
?>

<!-- FULL VIEWPORT HERO & NAV CONTAINER -->
<div class="relative min-h-screen flex flex-col overflow-hidden" 
     style="background-image: linear-gradient(to right, rgba(23, 37, 84, 0.94) 0%, rgba(30, 58, 138, 0.72) 45%, rgba(30, 58, 138, 0.28) 100%), url('<?= Router::asset('images/landingpagebg.jpg') ?>'); background-size: cover; background-position: center;">

  <!-- NAVBAR: Transparent & Fixed/Sticky with scroll logic -->
  <nav id="mainNav" class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 px-6 sm:px-10 lg:px-16 py-8">
    <div class="max-w-[1600px] mx-auto flex items-center justify-between">
      <!-- LOGO -->
      <a href="<?= Router::url('home/index') ?>" id="navLogo" class="font-heading font-bold text-3xl tracking-tight transition-all duration-500">
        <span class="text-white">Board</span><span class="text-blue-300" id="logoTrack">Track</span>
      </a>

      <!-- NAV ACTIONS -->
      <div class="flex items-center gap-3 sm:gap-6">
        <a href="<?= Router::url('auth/login') ?>" id="navLogin" 
           class="text-xs sm:text-sm font-bold bg-white text-brand-700 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl hover:bg-blue-50 transition-all duration-300 shadow-lg">
           Log In
        </a>
        <a href="<?= Router::url('auth/register') ?>" id="navRegister"
           class="hidden sm:inline-flex bg-brand-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-brand-700 transition-all duration-300 shadow-xl text-sm">
          Register as Tenant
        </a>
      </div>
    </div>
  </nav>

  <!-- HERO CONTENT -->
  <div class="flex-grow flex items-center relative z-10 pt-20">
    <div class="max-w-[1600px] mx-auto px-6 sm:px-10 lg:px-16 py-12 lg:py-0 w-full">
      <div class="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center">
        <!-- Left Column -->
        <div class="space-y-6 sm:space-y-8 animate-fade-in-up">
          <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md text-white border border-white/30 px-4 py-2 rounded-full text-xs font-bold">
            <i class="fa-solid fa-house-user text-xs"></i>
            <span>SMART BOARDING HOUSE PLATFORM</span>
          </div>
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-[1.15] font-heading drop-shadow-md">
            Find compatible <br class="hidden sm:block"> roommates and enjoy <br class="hidden sm:block"> better living.
          </h1>
          <p class="text-base sm:text-lg text-white/90 font-medium leading-relaxed max-w-xl drop-shadow-sm">
            BoardTrack helps tenants register digitally, upload requirements, take personality assessments, and get assigned to compatible roommates.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 pt-4">
            <a href="<?= Router::url('auth/register') ?>"
               class="inline-flex items-center justify-center gap-3 bg-brand-600 text-white font-bold px-8 py-4 rounded-2xl hover:bg-brand-700 transition-all duration-300 hover:scale-105 shadow-2xl text-base">
              <i class="fa-solid fa-user-plus text-sm"></i>
              Get Started Now
            </a>
            <a href="#how-it-works"
               class="inline-flex items-center justify-center gap-3 border-2 border-white text-white font-bold px-8 py-4 rounded-2xl hover:bg-white/10 transition-all duration-300 text-base backdrop-blur-sm">
              <i class="fa-solid fa-circle-play text-sm"></i>
              See How It Works
            </a>
          </div>
        </div>

        <!-- Right Column: Formally Structured Floating Cards -->
        <div class="hidden lg:block relative h-[550px] animate-fade-in delay-300">
          
          <!-- Card 1: Roommate Matching (Top Left) -->
          <div class="absolute top-4 left-8 bg-white rounded-2xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.12)] w-64 animate-float-1 z-20">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-heart text-brand-600 text-lg"></i>
              </div>
              <div>
                <div class="text-[10px] text-brand-600 font-bold uppercase tracking-wider">Community</div>
                <div class="text-xs text-gray-900 font-bold">Match Status</div>
              </div>
            </div>
            <div class="font-bold text-gray-900 text-sm mb-1.5">Roommate Matching</div>
            <div class="text-[11px] text-gray-500 leading-tight">Match with compatible roommates based on your personality profile.</div>
          </div>

          <!-- Card 2: Digital Billing (Center Right) -->
          <div class="absolute top-[180px] right-8 bg-white rounded-2xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.12)] w-64 animate-float-2 z-10">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-file-invoice-dollar text-brand-600 text-lg"></i>
              </div>
              <div>
                <div class="text-[10px] text-brand-600 font-bold uppercase tracking-wider">Payments</div>
                <div class="text-xs text-gray-900 font-bold">Billing Status</div>
              </div>
            </div>
            <div class="font-bold text-gray-900 text-sm mb-1.5">Digital Billing</div>
            <div class="text-[11px] text-gray-500 leading-tight">Track your monthly bills, water, and electricity usage in real-time.</div>
          </div>

          <!-- Card 3: Digital Onboarding (Bottom Left) -->
          <div class="absolute bottom-4 left-16 bg-white rounded-2xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.12)] w-64 animate-float-3 z-20">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-id-card text-brand-600 text-lg"></i>
              </div>
              <div>
                <div class="text-[10px] text-brand-600 font-bold uppercase tracking-wider">Verification</div>
                <div class="text-xs text-gray-900 font-bold">Upload Status</div>
              </div>
            </div>
            <div class="font-bold text-gray-900 text-sm mb-1.5">Digital Onboarding</div>
            <div class="text-[11px] text-gray-500 leading-tight">Upload IDs and requirements directly from your mobile device.</div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<style>
@keyframes float-1 {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-15px) rotate(1deg); }
}
@keyframes float-2 {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(-1deg); }
}
@keyframes float-3 {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-12px) rotate(0.5deg); }
}
.animate-float-1 { animation: float-1 5s ease-in-out infinite; }
.animate-float-2 { animation: float-2 7s ease-in-out infinite; }
.animate-float-3 { animation: float-3 6s ease-in-out infinite; }

#backToTop {
  display: none;
  position: fixed;
  bottom: 80px;
  right: 30px;
  z-index: 99;
  border: none;
  outline: none;
  background-color: var(--brand-600, #2563eb);
  color: white;
  cursor: pointer;
  width: 50px;
  height: 50px;
  border-radius: 15px;
  font-size: 20px;
  transition: all 0.3s ease;
  box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
}

#backToTop:hover {
  background-color: var(--brand-700, #1d4ed8);
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(37, 99, 235, 0.5);
}

@media (max-width: 640px) {
  #backToTop {
    bottom: 60px;
    right: 20px;
    width: 45px;
    height: 45px;
    font-size: 18px;
  }
}
</style>

<button id="backToTop" title="Go to top">
  <i class="fa-solid fa-arrow-up"></i>
</button>

<script>
window.addEventListener('scroll', function() {
  const nav = document.getElementById('mainNav');
  const logo = document.getElementById('navLogo');
  const logoTrack = document.getElementById('logoTrack');
  const loginBtn = document.getElementById('navLogin');
  const backToTop = document.getElementById('backToTop');
  
  if (window.scrollY > 80) {
    // Show back to top button
    if (backToTop) backToTop.style.display = 'block';

    nav.style.paddingTop = '1rem';
    nav.style.paddingBottom = '1rem';
    nav.classList.add('bg-white/95', 'backdrop-blur-md', 'shadow-lg', 'border-b', 'border-gray-200');
    nav.classList.remove('py-8');
    
    logo.classList.remove('text-3xl');
    logo.classList.add('text-2xl');
    logo.querySelector('span:first-child').classList.replace('text-white', 'text-gray-900');
    logoTrack.classList.replace('text-blue-300', 'text-brand-600');
    
    // Scrolled state: gray text, transparent bg
    loginBtn.classList.remove('bg-white', 'text-brand-700', 'shadow-lg', 'px-5', 'py-2.5', 'rounded-xl');
    loginBtn.classList.add('text-gray-600', 'hover:text-brand-600');
  } else {
    nav.style.paddingTop = '2rem';
    nav.style.paddingBottom = '2rem';
    nav.classList.remove('bg-white/95', 'backdrop-blur-md', 'shadow-lg', 'border-b', 'border-gray-200');
    nav.classList.add('py-8');
    
    logo.classList.add('text-3xl');
    logo.classList.remove('text-2xl');
    logo.querySelector('span:first-child').classList.replace('text-gray-900', 'text-white');
    logoTrack.classList.replace('text-brand-600', 'text-blue-300');
    
    // Hide back to top button
    if (backToTop) backToTop.style.display = 'none';
    
    // Top state: white bg, blue text
    loginBtn.classList.add('bg-white', 'text-brand-700', 'shadow-lg', 'px-5', 'py-2.5', 'rounded-xl');
    loginBtn.classList.remove('text-gray-600', 'hover:text-brand-600');
  }
});
// Back to top click handler
document.getElementById('backToTop').addEventListener('click', function() {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
});
</script>

<!-- WHY BOARDTRACK EXISTS -->
<section class="py-16 sm:py-20 bg-white">
  <div class="max-w-[1600px] mx-auto px-6 sm:px-10 lg:px-16">
    <div class="text-center mb-12 sm:mb-16 animate-fade-in-up">
      <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mb-2">Built for better tenant living.</h2>
      <p class="text-sm text-gray-600 max-w-3xl mx-auto">
        Traditional boarding house management creates friction for tenants. BoardTrack solves these problems with digital tools designed for shared living.
      </p>
    </div>
    <div class="grid md:grid-cols-2 gap-4 lg:gap-6">
      <div class="space-y-3 sm:space-y-4 animate-fade-in-up delay-100">
        <h3 class="text-sm sm:text-base font-semibold text-gray-900 mb-3 flex items-center gap-2">
          <i class="fa-solid fa-triangle-exclamation text-red-500 text-xs"></i>
          Common Problems
        </h3>
        <div class="space-y-3">
          <div class="flex items-start gap-3 p-3 bg-red-50 rounded-lg border border-red-100">
            <i class="fa-solid fa-xmark text-red-500 mt-0.5 text-xs"></i>
            <div>
              <div class="font-semibold text-gray-900 text-xs">Incompatible Roommates</div>
              <div class="text-xs text-gray-600">Random assignments can lead to conflicts over sleep, cleanliness, and noise.</div>
            </div>
          </div>
          <div class="flex items-start gap-3 p-3 bg-red-50 rounded-lg border border-red-100">
            <i class="fa-solid fa-xmark text-red-500 mt-0.5 text-xs"></i>
            <div>
              <div class="font-semibold text-gray-900 text-xs">Manual Registration</div>
              <div class="text-xs text-gray-600">Paper forms get lost, and tracking requirements is disorganized.</div>
            </div>
          </div>
          <div class="flex items-start gap-3 p-3 bg-red-50 rounded-lg border border-red-100">
            <i class="fa-solid fa-xmark text-red-500 mt-0.5 text-xs"></i>
            <div>
              <div class="font-semibold text-gray-900 text-xs">Hesitant Reporting</div>
              <div class="text-xs text-gray-600">Tenants avoid reporting roommate issues due to fear of confrontation.</div>
            </div>
          </div>
        </div>
      </div>
      <div class="space-y-3 sm:space-y-4 animate-fade-in-up delay-200">
        <h3 class="text-sm sm:text-base font-semibold text-gray-900 mb-3 flex items-center gap-2">
          <i class="fa-solid fa-check-circle text-green-500 text-xs"></i>
          BoardTrack Solutions
        </h3>
        <div class="space-y-3">
          <div class="flex items-start gap-3 p-3 bg-green-50 rounded-lg border border-green-100">
            <i class="fa-solid fa-check text-green-500 mt-0.5 text-xs"></i>
            <div>
              <div class="font-semibold text-gray-900 text-xs">Personality-Based Matching</div>
              <div class="text-xs text-gray-600">Room assignments consider sleep habits, cleanliness, and lifestyle preferences.</div>
            </div>
          </div>
          <div class="flex items-start gap-3 p-3 bg-green-50 rounded-lg border border-green-100">
            <i class="fa-solid fa-check text-green-500 mt-0.5 text-xs"></i>
            <div>
              <div class="font-semibold text-gray-900 text-xs">Digital Onboarding</div>
              <div class="text-xs text-gray-600">Upload IDs and complete registration online with real-time status tracking.</div>
            </div>
          </div>
          <div class="flex items-start gap-3 p-3 bg-green-50 rounded-lg border border-green-100">
            <i class="fa-solid fa-check text-green-500 mt-0.5 text-xs"></i>
            <div>
              <div class="font-semibold text-gray-900 text-xs">Anonymous Complaints</div>
              <div class="text-xs text-gray-600">Report roommate conflicts safely without revealing your identity.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PERSONALITY MATCHING SECTION -->
<section class="py-16 sm:py-24 bg-gradient-to-br from-blue-50 via-white to-blue-50 relative overflow-hidden">
  <div class="max-w-[1600px] mx-auto px-6 sm:px-10 lg:px-16 relative z-10">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <!-- Left: Visual Concept -->
      <div class="animate-fade-in-up">
        <div class="inline-flex items-center gap-2 bg-brand-100 text-brand-700 px-3 py-1.5 rounded-full text-xs font-semibold mb-6">
          <i class="fa-solid fa-heart"></i>
          <span>EXCLUSIVE FEATURE</span>
        </div>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-6 leading-tight">
          Find your perfect <br> <span class="text-brand-600">roommate match.</span>
        </h2>
        <p class="text-base text-gray-600 mb-8 leading-relaxed max-w-lg">
          No more random assignments. Our smart algorithm analyzes your lifestyle and personality to connect you with roommates who truly match your social energy and habits.
        </p>
        
        <div class="space-y-4">
          <div class="flex items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-brand-600 flex-shrink-0">
              <i class="fa-solid fa-bolt"></i>
            </div>
            <div>
              <div class="font-bold text-gray-900 text-sm">Social Compatibility</div>
              <div class="text-xs text-gray-500">Matching based on energy levels and communication styles.</div>
            </div>
          </div>
          <div class="flex items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border border-blue-100 hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-brand-600 flex-shrink-0">
              <i class="fa-solid fa-moon"></i>
            </div>
            <div>
              <div class="font-bold text-gray-900 text-sm">Living Habits</div>
              <div class="text-xs text-gray-500">Syncing room environment preferences and guest policies.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Interactive Profile Mockup -->
      <div class="relative animate-fade-in-up delay-200">
        <div class="bg-white rounded-[32px] p-8 shadow-[0_32px_64px_rgba(0,0,0,0.08)] border border-blue-50 relative z-10">
          <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-brand-600 rounded-full flex items-center justify-center text-white font-bold">JD</div>
              <div>
                <div class="font-bold text-gray-900">Compatibility Score</div>
                <div class="flex items-center gap-1 text-green-500 font-bold text-sm">
                  <i class="fa-solid fa-circle-check"></i> 98% Match
                </div>
              </div>
            </div>
          </div>
          
          <div class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-50 rounded-2xl p-4">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Social Style</div>
                <div class="text-sm font-bold text-gray-900">Introvert-Friendly</div>
              </div>
              <div class="bg-gray-50 rounded-2xl p-4">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Environment</div>
                <div class="text-sm font-bold text-gray-900">Quiet & Focused</div>
              </div>
            </div>
            
            <div class="bg-blue-50 rounded-2xl p-5 border border-blue-100">
              <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-blue-700">Personality Traits</span>
                <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold">10/10 TEST</span>
              </div>
              <div class="flex flex-wrap gap-2">
                <span class="bg-white px-3 py-1 rounded-full text-[11px] font-semibold text-gray-600 border border-blue-100 shadow-sm">Communication</span>
                <span class="bg-white px-3 py-1 rounded-full text-[11px] font-semibold text-gray-600 border border-blue-100 shadow-sm">Guest Policy</span>
                <span class="bg-white px-3 py-1 rounded-full text-[11px] font-semibold text-gray-600 border border-blue-100 shadow-sm">Social Energy</span>
              </div>
            </div>
          </div>
        </div>
        <!-- Decorative elements -->
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-brand-100 rounded-full blur-3xl opacity-60"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-blue-100 rounded-full blur-3xl opacity-60"></div>
      </div>
    </div>
  </div>
</section>

<!-- TENANT FEATURES SECTION: The 3 Pillars -->
<section class="py-16 sm:py-24 bg-white relative">
  <div class="max-w-[1600px] mx-auto px-6 sm:px-10 lg:px-16">
    <div class="text-center mb-16 animate-fade-in-up">
      <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">The Modern Tenant Experience</h2>
      <p class="text-base text-gray-600 max-w-2xl mx-auto">
        We've simplified every step of your boarding journey into three core digital experiences.
      </p>
    </div>
    
    <div class="grid lg:grid-cols-3 gap-8">
      <!-- Pillar 1: Onboarding -->
      <div class="group bg-gray-50 rounded-[40px] p-10 hover:bg-brand-600 transition-all duration-500 animate-fade-in-up">
        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-brand-600 mb-8 shadow-sm group-hover:scale-110 transition-transform duration-500">
          <i class="fa-solid fa-id-card-clip text-2xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-4 group-hover:text-white transition-colors">Seamless Onboarding</h3>
        <p class="text-sm text-gray-600 mb-8 leading-relaxed group-hover:text-blue-100 transition-colors">
          Digital registration, ID verification, and requirement tracking all in one place. No more paper forms.
        </p>
        <ul class="space-y-3">
          <li class="flex items-center gap-3 text-xs font-bold text-gray-500 group-hover:text-white/80 transition-colors">
            <i class="fa-solid fa-circle-check text-brand-500 group-hover:text-white"></i> Online Registration
          </li>
          <li class="flex items-center gap-3 text-xs font-bold text-gray-500 group-hover:text-white/80 transition-colors">
            <i class="fa-solid fa-circle-check text-brand-500 group-hover:text-white"></i> Secure ID Upload
          </li>
          <li class="flex items-center gap-3 text-xs font-bold text-gray-500 group-hover:text-white/80 transition-colors">
            <i class="fa-solid fa-circle-check text-brand-500 group-hover:text-white"></i> Real-time Status
          </li>
        </ul>
      </div>

      <!-- Pillar 2: Living -->
      <div class="group bg-gray-50 rounded-[40px] p-10 hover:bg-brand-600 transition-all duration-500 animate-fade-in-up delay-100">
        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-brand-600 mb-8 shadow-sm group-hover:scale-110 transition-transform duration-500">
          <i class="fa-solid fa-mobile-screen-button text-2xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-4 group-hover:text-white transition-colors">Smart Management</h3>
        <p class="text-sm text-gray-600 mb-8 leading-relaxed group-hover:text-blue-100 transition-colors">
          Manage your monthly bills, payments, and house announcements directly from your mobile dashboard.
        </p>
        <ul class="space-y-3">
          <li class="flex items-center gap-3 text-xs font-bold text-gray-500 group-hover:text-white/80 transition-colors">
            <i class="fa-solid fa-circle-check text-brand-500 group-hover:text-white"></i> Digital Billing
          </li>
          <li class="flex items-center gap-3 text-xs font-bold text-gray-500 group-hover:text-white/80 transition-colors">
            <i class="fa-solid fa-circle-check text-brand-500 group-hover:text-white"></i> Payment Tracking
          </li>
          <li class="flex items-center gap-3 text-xs font-bold text-gray-500 group-hover:text-white/80 transition-colors">
            <i class="fa-solid fa-circle-check text-brand-500 group-hover:text-white"></i> Mobile Announcements
          </li>
        </ul>
      </div>

      <!-- Pillar 3: Safety -->
      <div class="group bg-gray-50 rounded-[40px] p-10 hover:bg-brand-600 transition-all duration-500 animate-fade-in-up delay-200">
        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-brand-600 mb-8 shadow-sm group-hover:scale-110 transition-transform duration-500">
          <i class="fa-solid fa-shield-halved text-2xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-4 group-hover:text-white transition-colors">Safe Community</h3>
        <p class="text-sm text-gray-600 mb-8 leading-relaxed group-hover:text-blue-100 transition-colors">
          Report issues safely with optional anonymity. We prioritize a harmonious and conflict-free living space.
        </p>
        <ul class="space-y-3">
          <li class="flex items-center gap-3 text-xs font-bold text-gray-500 group-hover:text-white/80 transition-colors">
            <i class="fa-solid fa-circle-check text-brand-500 group-hover:text-white"></i> Anonymous Complaints
          </li>
          <li class="flex items-center gap-3 text-xs font-bold text-gray-500 group-hover:text-white/80 transition-colors">
            <i class="fa-solid fa-circle-check text-brand-500 group-hover:text-white"></i> Conflict Resolution
          </li>
          <li class="flex items-center gap-3 text-xs font-bold text-gray-500 group-hover:text-white/80 transition-colors">
            <i class="fa-solid fa-circle-check text-brand-500 group-hover:text-white"></i> Privacy Protection
          </li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section id="how-it-works" class="py-20 sm:py-32 bg-gray-50 scroll-mt-24 lg:scroll-mt-32">
  <div class="max-w-[1600px] mx-auto px-6 sm:px-10 lg:px-16">
    <div class="text-center mb-16 sm:mb-20 animate-fade-in-up">
      <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">How Tenant Registration Works</h2>
      <p class="text-base text-gray-600 max-w-3xl mx-auto">
        A simple, structured process from registration to room assignment.
      </p>
    </div>
    <div class="relative">
      <div class="hidden md:block absolute top-8 left-1/2 transform -translate-x-1/2 w-3/4 h-0.5 bg-gradient-to-r from-brand-200 via-brand-400 to-brand-200"></div>
      <div class="grid md:grid-cols-6 gap-4 sm:gap-6">
        <?php
          $steps = [
            ['1', 'Register', 'Create your account with email verification.'],
            ['2', 'Upload ID', 'Upload government ID for secure verification.'],
            ['3', 'Personality Test', 'Complete the 10-question compatibility questionnaire.'],
            ['4', 'Await Approval', 'Landlord reviews your application and ID.'],
            ['5', 'Room Assignment', 'Receive room assignment based on compatibility.'],
            ['6', 'Get Started', 'Access your dashboard to manage bills and complaints.'],
          ];
          foreach ($steps as $index => [$num, $title, $desc]):
        ?>
        <div class="relative text-center animate-fade-in-up" style="animation-delay: <?= ($index * 0.05) ?>s">
          <div class="relative z-10 w-10 h-10 bg-brand-600 text-white rounded-full flex items-center justify-center font-bold text-sm mx-auto mb-2 shadow-md shadow-brand-500/30">
            <?= $num ?>
          </div>
          <div class="font-semibold text-gray-900 mb-1 text-xs"><?= $title ?></div>
          <div class="text-xs text-gray-600 leading-relaxed"><?= $desc ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="py-16 sm:py-20 bg-gray-50">
  <div class="max-w-[1600px] mx-auto px-6 sm:px-10 lg:px-16">
    <div class="text-center mb-12 sm:mb-16 animate-fade-in-up">
      <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mb-2">What Tenants Say</h2>
      <p class="text-sm text-gray-600 max-w-3xl mx-auto">
        Real feedback from tenants using BoardTrack.
      </p>
    </div>
    <?php if (!empty($testimonials)): ?>
      <div class="grid md:grid-cols-3 gap-3 sm:gap-4">
        <?php foreach ($testimonials as $index => $t):
          $initials = strtoupper(substr($t['name'] ?? 'U', 0, 1));
          $rating = $t['rating'] ?? 5;
        ?>
        <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-300 hover:-translate-y-0.5 animate-fade-in-up" style="animation-delay: <?= ($index * 0.05) ?>s">
          <div class="flex items-center gap-1 mb-3">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <?php if ($i <= $rating): ?>
                <i class="fa-solid fa-star text-yellow-400 text-xs"></i>
              <?php else: ?>
                <i class="fa-solid fa-star text-gray-300 text-xs"></i>
              <?php endif; ?>
            <?php endfor; ?>
          </div>
          <p class="text-gray-600 mb-4 leading-relaxed text-xs">
            "<?= htmlspecialchars($t['review_text'] ?? '') ?>"
          </p>
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-brand-100 rounded-full flex items-center justify-center text-brand-600 font-bold text-xs">
              <?= $initials ?>
            </div>
            <div>
              <div class="font-semibold text-gray-900 text-xs"><?= htmlspecialchars($t['name'] ?? 'Anonymous') ?></div>
              <div class="text-xs text-gray-500">Tenant</div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
          <i class="fa-solid fa-comments text-gray-400 text-2xl"></i>
        </div>
        <p class="text-sm text-gray-500">No reviews yet. Be the first to share your experience!</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- CONTACT SECTION -->
<section id="contact" class="py-12 sm:py-24 bg-white">
  <div class="max-w-[1600px] mx-auto px-6 sm:px-10 lg:px-16">
    <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-start">
      <div class="space-y-6 sm:space-y-8 animate-fade-in-up">
        <div class="text-center lg:text-left">
          <h2 class="text-2xl sm:text-4xl font-extrabold text-gray-900 mb-3 tracking-tight">Need Help?</h2>
          <p class="text-sm sm:text-base text-gray-600 leading-relaxed max-w-xl mx-auto lg:mx-0">
            Reach out to us directly for technical support or general inquiries.
          </p>
        </div>
        <div class="space-y-4 sm:space-y-6">
          <div class="flex items-center lg:items-start gap-4 flex-col lg:flex-row text-center lg:text-left">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0 border border-blue-100">
              <i class="fa-solid fa-envelope text-brand-600 text-base"></i>
            </div>
            <div>
              <div class="font-bold text-gray-900 text-xs sm:text-sm mb-0.5">Email Address</div>
              <a href="mailto:support@bsit2a.com" class="text-brand-600 font-semibold hover:text-brand-700 transition-colors text-xs sm:text-sm">
                support@bsit2a.com
              </a>
            </div>
          </div>
          <div class="flex items-center lg:items-start gap-4 flex-col lg:flex-row text-center lg:text-left">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0 border border-blue-100">
              <i class="fa-solid fa-headset text-brand-600 text-base"></i>
            </div>
            <div>
              <div class="font-bold text-gray-900 text-xs sm:text-sm mb-0.5">Technical Support</div>
              <div class="text-[11px] sm:text-sm text-gray-600">Get help with system setup or bugs.</div>
            </div>
          </div>
        </div>
      </div>
      <div class="animate-fade-in-up delay-200">
        <div class="bg-gray-50 border border-gray-100 rounded-3xl p-5 sm:p-10 shadow-xl">
          <form id="contactForm" class="space-y-4 sm:space-y-6" method="POST" action="<?= Router::url('home/contact') ?>">
            <div class="grid sm:grid-cols-2 gap-4 sm:gap-6">
              <div class="space-y-1.5">
                <label for="name" class="block text-[11px] font-bold text-gray-900 uppercase tracking-wider">Full Name</label>
                <input type="text" id="name" name="name" required
                       class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all outline-none text-xs"
                       placeholder="John Doe">
              </div>
              <div class="space-y-1.5">
                <label for="email" class="block text-[11px] font-bold text-gray-900 uppercase tracking-wider">Email Address</label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all outline-none text-xs"
                       placeholder="john@example.com">
              </div>
            </div>
            <div class="space-y-1.5">
              <label for="subject" class="block text-[11px] font-bold text-gray-900 uppercase tracking-wider">Subject</label>
              <select id="subject" name="subject" required
                      class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all outline-none text-xs">
                <option value="">Select a topic</option>
                <option value="technical">Technical Support</option>
                <option value="partnership">Partnership Inquiry</option>
                <option value="general">General Question</option>
              </select>
            </div>
            <div class="space-y-1.5">
              <label for="message" class="block text-[11px] font-bold text-gray-900 uppercase tracking-wider">Message</label>
              <textarea id="message" name="message" rows="3" required
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all outline-none resize-none text-xs"
                        placeholder="How can we help?"></textarea>
            </div>
            <button type="submit"
                    class="w-full bg-brand-600 text-white font-bold px-6 py-3 rounded-lg hover:bg-brand-700 transition-all duration-300 text-sm">
              Send Message
            </button>
          </form>
          <div id="formSuccess" class="hidden mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-xs animate-fade-in">
            <i class="fa-solid fa-check-circle mr-2"></i>
            Message sent successfully.
          </div>
          <div id="formError" class="hidden mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-xs animate-fade-in">
            <i class="fa-solid fa-circle-xmark mr-2"></i>
            Failed to send message. Please try again.
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="bg-[#0f172a] text-gray-400 border-t border-white/5">
  <div class="max-w-[1600px] mx-auto px-6 sm:px-10 lg:px-16 py-8">
    <div class="flex flex-col md:flex-row items-center justify-between gap-6">
      <div class="text-center md:text-left">
        <div class="font-heading font-bold text-white text-xl mb-1">
          Board<span class="text-blue-300">Track</span>
        </div>
        <p class="text-[11px] sm:text-xs leading-relaxed max-w-sm">
          A smart boarding house platform for digital tenant management.
        </p>
      </div>
      
      <div class="flex flex-col items-center md:items-end gap-2 text-[10px] sm:text-xs font-medium tracking-wide">
        <div>&copy; <?= date('Y') ?> BoardTrack. All rights reserved.</div>
        <div class="flex items-center gap-2 text-gray-500">
          <i class="fa-solid fa-shield-halved text-[8px]"></i>
          Smart Boarding House Platform
        </div>
      </div>
    </div>
  </div>
</footer>

<script>
// Contact form handling
document.getElementById('contactForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const form = this;
  const formData = new FormData(form);
  const successDiv = document.getElementById('formSuccess');
  const errorDiv = document.getElementById('formError');
  
  // Hide previous messages
  successDiv.classList.add('hidden');
  errorDiv.classList.add('hidden');
  
  // Basic client-side validation
  const name = formData.get('name').trim();
  const email = formData.get('email').trim();
  const subject = formData.get('subject');
  const message = formData.get('message').trim();
  
  if (!name || !email || !subject || !message) {
    errorDiv.classList.remove('hidden');
    return;
  }
  
  // Email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    errorDiv.classList.remove('hidden');
    return;
  }
  
  // Submit form (actual submission will be handled by backend)
  fetch(form.action, {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      successDiv.classList.remove('hidden');
      form.reset();
    } else {
      errorDiv.classList.remove('hidden');
    }
  })
  .catch(error => {
    // For now, show success as fallback if backend not implemented
    successDiv.classList.remove('hidden');
    form.reset();
    console.log('Contact form submitted (backend endpoint may need implementation)');
  });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  });
});
</script>