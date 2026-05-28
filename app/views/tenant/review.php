<?php
/**
 * BoardTrack — Tenant Review/Testimonial Submission View
 * app/views/tenant/review.php
 * Layout: tenant.php
 */
$testimonial = $testimonial ?? [];
$hasSubmitted = $hasSubmitted ?? false;
?>

<div class="page-header mb-4">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Submit Review</h1>
      <p class="page-subtitle">Share your experience with BoardTrack</p>
    </div>
  </div>
</div>

<?php if ($hasSubmitted): ?>
  <div class="card">
    <div class="card-header">
      <div class="card-title">Review Status</div>
    </div>
    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
      <div class="flex items-center gap-2 text-green-700">
        <i class="fa-solid fa-check-circle"></i>
        <span class="font-semibold">Your review has been submitted successfully!</span>
      </div>
      <p class="text-sm text-green-600 mt-2">
        Your review is pending approval and will appear on the landing page once approved by the landlord.
      </p>
    </div>
  </div>
<?php else: ?>
  <div class="card">
    <div class="card-header">
      <div class="card-title">Write a Review</div>
    </div>
    <form action="<?= Router::url('tenant/submit-review') ?>" method="POST" class="space-y-4">
      <div>
        <label class="form-label">Rating <span class="req">*</span></label>
        <div class="flex gap-2" id="starRating">
          <button type="button" class="star-btn text-2xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="1">
            <i class="fa-solid fa-star"></i>
          </button>
          <button type="button" class="star-btn text-2xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="2">
            <i class="fa-solid fa-star"></i>
          </button>
          <button type="button" class="star-btn text-2xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="3">
            <i class="fa-solid fa-star"></i>
          </button>
          <button type="button" class="star-btn text-2xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="4">
            <i class="fa-solid fa-star"></i>
          </button>
          <button type="button" class="star-btn text-2xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="5">
            <i class="fa-solid fa-star"></i>
          </button>
        </div>
        <input type="hidden" id="ratingInput" name="rating" value="" required>
        <div id="ratingError" class="form-error hidden">Please select a rating</div>
      </div>

      <div>
        <label for="reviewText" class="form-label">Your Review <span class="req">*</span></label>
        <textarea 
          id="reviewText" 
          name="review_text" 
          rows="5" 
          class="form-textarea" 
          placeholder="Share your experience with BoardTrack. How has it helped you as a tenant? What features do you find most useful?"
          required
        ></textarea>
        <div class="text-xs text-gray-400 mt-1">Minimum 10 characters</div>
      </div>

      <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50 p-3 rounded-lg">
        <i class="fa-solid fa-info-circle text-brand-500"></i>
        <span>Your review will be reviewed by the landlord before appearing on the landing page.</span>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-paper-plane"></i>
        Submit Review
      </button>
    </form>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const starBtns = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('ratingInput');
    const ratingError = document.getElementById('ratingError');
    
    starBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const rating = this.dataset.rating;
        ratingInput.value = rating;
        ratingError.classList.add('hidden');
        
        // Update star colors
        starBtns.forEach((starBtn, index) => {
          if (index < rating) {
            starBtn.classList.remove('text-gray-300');
            starBtn.classList.add('text-yellow-400');
          } else {
            starBtn.classList.remove('text-yellow-400');
            starBtn.classList.add('text-gray-300');
          }
        });
      });
      
      btn.addEventListener('mouseenter', function() {
        const rating = this.dataset.rating;
        starBtns.forEach((starBtn, index) => {
          if (index < rating) {
            starBtn.classList.remove('text-gray-300');
            starBtn.classList.add('text-yellow-400');
          }
        });
      });
      
      btn.addEventListener('mouseleave', function() {
        const currentRating = ratingInput.value;
        starBtns.forEach((starBtn, index) => {
          if (currentRating && index < currentRating) {
            starBtn.classList.remove('text-gray-300');
            starBtn.classList.add('text-yellow-400');
          } else if (!currentRating) {
            starBtn.classList.remove('text-yellow-400');
            starBtn.classList.add('text-gray-300');
          }
        });
      });
    });
    
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
      if (!ratingInput.value) {
        e.preventDefault();
        ratingError.classList.remove('hidden');
      }
    });
  });
  </script>
<?php endif; ?>
