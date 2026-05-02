<?php
/**
 * BoardTrack — Tenant: Personality Questionnaire
 * app/views/tenant/personality.php
 * Layout: tenant.php
 */
$questions = $questions ?? [];
$totalQuestions = count($questions);
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Personality Questionnaire</h1>
      <p class="page-subtitle">Help us find your ideal roommate match.</p>
    </div>
  </div>
</div>

<div class="card" style="max-width:720px;">
  <!-- Progress -->
  <div style="margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
      <span style="font-size:0.82rem;font-weight:600;color:var(--gray-700);" id="progressLabel">Question 1 of <?= $totalQuestions ?></span>
      <span style="font-size:0.75rem;color:var(--gray-400);" id="progressPercent">0%</span>
    </div>
    <div class="score-bar" style="height:6px;">
      <div class="score-fill" id="progressFill" style="width:0%;background:var(--primary);"></div>
    </div>
  </div>

  <!-- Timer notice -->
  <div id="timerNotice" style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:var(--warning-light);border-radius:var(--radius);margin-bottom:16px;font-size:0.82rem;color:#92400e;">
    <i class="fa-solid fa-clock"></i>
    <span>Please take a moment to read carefully... (<span id="timerCountdown">5</span>s)</span>
  </div>

  <!-- Intro -->
  <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;background:var(--primary-light);border:1px solid var(--primary-border);border-radius:var(--radius);margin-bottom:20px;">
    <i class="fa-solid fa-user-group" style="font-size:1.2rem;color:var(--primary);flex-shrink:0;"></i>
    <p style="margin:0;font-size:0.85rem;color:var(--gray-700);">This questionnaire helps the landlord match you with compatible roommates. Please answer honestly — there are no right or wrong answers!</p>
  </div>

  <form action="<?= Router::url('tenant/submit-personality') ?>" method="POST" id="personalityForm">
    <?php foreach ($questions as $index => $question): ?>
      <div class="question-slide" data-index="<?= $index ?>" style="display:<?= $index === 0 ? 'block' : 'none' ?>;">
        <!-- Category tag -->
        <div style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;background:var(--gray-100);border-radius:20px;font-size:0.75rem;color:var(--gray-500);font-weight:500;margin-bottom:12px;">
          <i class="fa-solid <?= match($question['category'] ?? '') {
            'sleep_schedule'     => 'fa-moon',
            'cleanliness'        => 'fa-broom',
            'noise_tolerance'    => 'fa-volume-high',
            'study_habits'       => 'fa-book',
            'social_preference'  => 'fa-users',
            default              => 'fa-circle',
          } ?>"></i>
          <?= ucfirst(str_replace('_', ' ', $question['category'] ?? '')) ?>
        </div>

        <!-- Question text -->
        <h3 style="font-family:var(--font-heading);font-size:1.05rem;font-weight:600;color:var(--gray-900);margin:0 0 16px;line-height:1.5;">
          <?= htmlspecialchars($question['question_text']) ?>
        </h3>

        <!-- Options (fixed 1-5 Likert scale) -->
        <?php
          $scaleOptions = [
            1 => 'Strongly Disagree',
            2 => 'Disagree',
            3 => 'Neutral',
            4 => 'Agree',
            5 => 'Strongly Agree',
          ];
        ?>
        <div style="display:flex;flex-direction:column;gap:8px;">
          <?php foreach ($scaleOptions as $value => $label): ?>
            <label style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#fff;border:2px solid var(--gray-200);border-radius:var(--radius);cursor:pointer;transition:border-color 0.12s,background 0.12s;">
              <input type="radio"
                     name="answers[<?= $question['id'] ?>]"
                     value="<?= $value ?>"
                     required
                     style="width:18px;height:18px;accent-color:var(--primary);flex-shrink:0;"
                     onchange="onAnswerSelect()">
              <span style="font-size:0.88rem;color:var(--gray-700);"><?= htmlspecialchars($label) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Navigation -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:24px;padding-top:16px;border-top:1px solid var(--gray-200);">
      <button type="button" class="btn btn-secondary" id="prevBtn" onclick="changeQuestion(-1)" style="visibility:hidden;">
        <i class="fa-solid fa-arrow-left"></i> Previous
      </button>
      <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeQuestion(1)" disabled>
        Next <i class="fa-solid fa-arrow-right"></i>
      </button>
      <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;" disabled>
        <i class="fa-solid fa-check"></i> Submit Questionnaire
      </button>
    </div>
  </form>
</div>

<style>
.question-slide label:has(input:checked) {
  border-color: var(--primary);
  background: var(--primary-light);
}
</style>

<script>
var currentQ = 0;
var totalQ = <?= $totalQuestions ?>;
var timerInterval = null;
var canAdvance = false;
var TIMER_SECONDS = 5;

function startTimer() {
  canAdvance = false;
  var notice = document.getElementById('timerNotice');
  var countdown = document.getElementById('timerCountdown');
  var remaining = TIMER_SECONDS;
  notice.style.display = 'flex';
  countdown.textContent = remaining;

  // Disable next/submit while timer is active
  updateNavButtons();

  clearInterval(timerInterval);
  timerInterval = setInterval(function() {
    remaining--;
    countdown.textContent = remaining;
    if (remaining <= 0) {
      clearInterval(timerInterval);
      notice.style.display = 'none';
      canAdvance = true;
      updateNavButtons();
    }
  }, 1000);
}

function updateProgress() {
  var pct = Math.round(((currentQ + 1) / totalQ) * 100);
  document.getElementById('progressFill').style.width = pct + '%';
  document.getElementById('progressLabel').textContent = 'Question ' + (currentQ + 1) + ' of ' + totalQ;
  document.getElementById('progressPercent').textContent = pct + '%';
}

function isCurrentAnswered() {
  var slide = document.querySelector('.question-slide[data-index="' + currentQ + '"]');
  return slide && slide.querySelector('input[type="radio"]:checked');
}

function updateNavButtons() {
  document.getElementById('prevBtn').style.visibility = currentQ === 0 ? 'hidden' : 'visible';
  var isLast = currentQ === totalQ - 1;
  var answered = isCurrentAnswered();

  if (isLast) {
    document.getElementById('nextBtn').style.display = 'none';
    document.getElementById('submitBtn').style.display = 'inline-flex';
    document.getElementById('submitBtn').disabled = !canAdvance || !answered;
  } else {
    document.getElementById('nextBtn').style.display = 'inline-flex';
    document.getElementById('submitBtn').style.display = 'none';
    document.getElementById('nextBtn').disabled = !canAdvance || !answered;
  }
}

function onAnswerSelect() {
  updateNavButtons();
}

function changeQuestion(dir) {
  if (dir === 1 && !isCurrentAnswered()) return;

  var cur = document.querySelector('.question-slide[data-index="' + currentQ + '"]');
  var next = currentQ + dir;
  if (next < 0 || next >= totalQ) return;

  cur.style.display = 'none';
  currentQ = next;
  document.querySelector('.question-slide[data-index="' + currentQ + '"]').style.display = 'block';

  updateProgress();
  startTimer();
}

// Init
updateProgress();
startTimer();
</script>
