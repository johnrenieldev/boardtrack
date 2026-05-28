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
      <span style="font-size:0.82rem;font-weight:600;color:var(--color-text-primary);" id="progressLabel">Question 1 of <?= $totalQuestions ?></span>
      <span style="font-size:0.75rem;color:var(--color-text-muted);" id="progressPercent">0%</span>
    </div>
    <div class="score-bar" style="height:6px;">
      <div class="score-fill" id="progressFill" style="width:0%;background:var(--primary);"></div>
    </div>
  </div>

  <!-- Intro -->
  <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;background:var(--primary-light);border:1px solid var(--primary-border);border-radius:var(--radius);margin-bottom:20px;">
    <i class="fa-solid fa-user-group" style="font-size:1.2rem;color:var(--primary);flex-shrink:0;"></i>
    <p style="margin:0;font-size:0.85rem;color:var(--color-text-primary);">This questionnaire helps the landlord match you with compatible roommates. Please answer honestly — there are no right or wrong answers!</p>
  </div>

  <form action="<?= Router::url('personality/submit-personality') ?>" method="POST" id="personalityForm">
    <?php foreach ($questions as $index => $question): ?>
      <div class="question-slide" data-index="<?= $index ?>" style="display:<?= $index === 0 ? 'block' : 'none' ?>;">
        <!-- Category tag -->
        <div style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;background:var(--gray-100);border-radius:20px;font-size:0.75rem;color:var(--color-text-secondary);font-weight:500;margin-bottom:12px;">
          <i class="fa-solid <?= match($question['category'] ?? '') {
            'sleep_schedule'     => 'fa-moon',
            'cleanliness'        => 'fa-broom',
            'social_preference'  => 'fa-users',
            'study_habits'       => 'fa-book',
            'noise_tolerance'    => 'fa-volume-high',
            'guest_policy'       => 'fa-user-clock',
            'default'            => 'fa-tag'
          } ?> text-xs"></i>
          <?= ucfirst(str_replace('_', ' ', $question['category'] ?? 'General')) ?>
        </div>

        <!-- Question text -->
        <h3 style="font-family:var(--font-heading);font-size:1.05rem;font-weight:600;color:var(--color-text-primary);margin:0 0 16px;line-height:1.5;">
          <?= htmlspecialchars($question['question_text']) ?>
        </h3>

        <!-- Options -->
        <div style="display:flex;flex-direction:column;gap:10px;">
          <?php foreach ($question['options'] ?? [] as $idx => $label): ?>
            <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:var(--gray-50);border:1px solid var(--gray-200);border-radius:var(--radius);cursor:pointer;transition:border-color 0.15s,background 0.15s;">
              <input type="radio"
                     name="answers[<?= $question['id'] ?? '' ?>]"
                     value="<?= $idx ?>"
                     required
                     style="width:18px;height:18px;accent-color:var(--primary);flex-shrink:0;"
                     onchange="onAnswerSelect()">
              <span style="font-size:0.88rem;color:var(--color-text-primary);"><?= htmlspecialchars($label) ?></span>
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
var canAdvance = true;

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
  updateNavButtons();
}

// Init
updateProgress();
updateNavButtons();
</script>
