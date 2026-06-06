<?php
/**
 * BoardTrack — Landlord: Reviews/Testimonials Management
 * app/views/landlord/reviews.php
 */
$reviews = $reviews ?? [];
$sortOrder = $_GET['sort'] ?? 'latest';

// Sort reviews
if ($sortOrder === 'oldest') {
    usort($reviews, function($a, $b) {
        return strtotime($a['created_at']) - strtotime($b['created_at']);
    });
} else {
    // Latest first (default)
    usort($reviews, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
}

/**
 * Render star rating as Font Awesome icons
 */
function renderStars(int $rating): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating 
            ? '<i class="fa-solid fa-star" style="color:#f59e0b;"></i>'
            : '<i class="fa-regular fa-star" style="color:#d1d5db;"></i>';
    }
    return $html;
}
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Tenant Reviews</h1>
      <p class="page-subtitle">View and manage tenant testimonials and ratings</p>
    </div>
    
    <!-- Sort Filter — small inline buttons top-right -->
    <div style="display:flex;gap:6px;align-items:center;margin-left:auto;">
      <a href="<?= Router::url('landlord/reviews?sort=latest') ?>"
         class="btn btn-xs <?= $sortOrder === 'latest' ? 'btn-primary' : 'btn-outline' ?>">
        <i class="fa-solid fa-arrow-down-wide-short"></i> Latest
      </a>
      <a href="<?= Router::url('landlord/reviews?sort=oldest') ?>"
         class="btn btn-xs <?= $sortOrder === 'oldest' ? 'btn-primary' : 'btn-outline' ?>">
        Oldest
      </a>
    </div>
  </div>
</div>

<?php if (empty($reviews)): ?>
  <div class="empty-state-card">
    <i class="fa-solid fa-star"></i>
    <h3>No Reviews Yet</h3>
    <p>Tenant reviews will appear here once they submit feedback.</p>
  </div>
<?php else: ?>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <?php foreach ($reviews as $review): ?>
      <div class="card p-6 hover:shadow-lg transition-shadow">
        <!-- Header -->
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-lg">
              <?= strtoupper(substr($review['tenant_name'] ?? 'T', 0, 1)) ?>
            </div>
            <div>
              <div class="font-bold text-gray-900"><?= htmlspecialchars($review['tenant_name'] ?? 'Unknown') ?></div>
              <div class="text-sm text-gray-500"><?= date('M j, Y', strtotime($review['created_at'])) ?></div>
            </div>
          </div>
          
          <!-- Status Badge -->
          <?php if ((int) $review['is_approved'] === 1): ?>
            <span class="badge badge-success">
              <i class="fa-solid fa-check"></i> Approved
            </span>
          <?php else: ?>
            <span class="badge badge-warning">
              <i class="fa-solid fa-clock"></i> Pending
            </span>
          <?php endif; ?>
        </div>

        <!-- Rating -->
        <div class="mb-4">
          <div class="flex items-center gap-2">
            <?= renderStars((int) ($review['rating'] ?? 0)) ?>
            <span class="text-lg font-bold text-gray-900 ml-2"><?= (int) ($review['rating'] ?? 0) ?>.0</span>
          </div>
        </div>

        <!-- Review Text -->
        <div class="mb-4">
          <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($review['review_text'] ?? '')) ?></p>
        </div>

        <!-- Actions -->
        <div class="flex gap-2 pt-4 border-t border-gray-200">
          <?php if ((int) $review['is_approved'] === 0): ?>
            <form method="POST" action="<?= Router::url('landlord/approve-review/' . $review['id']) ?>" class="inline">
              <button type="submit" class="btn btn-success btn-sm">
                <i class="fa-solid fa-check"></i> Approve
              </button>
            </form>
          <?php endif; ?>
          
          <form method="POST" action="<?= Router::url('landlord/delete-review/' . $review['id']) ?>" 
                class="inline" 
                onsubmit="return confirm('Delete this review? This action cannot be undone.');">
            <button type="submit" class="btn btn-danger btn-sm">
              <i class="fa-solid fa-trash"></i> Delete
            </button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<style>
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.badge-success {
  background: #d1fae5;
  color: #065f46;
}
.badge-warning {
  background: #fef3c7;
  color: #92400e;
}

/* Override global FAB styles for reviews page sort buttons */
@media (max-width: 767px) {
  .page-header-row .btn-xs {
    position: static !important;
    width: auto !important;
    height: auto !important;
    border-radius: 6px !important;
    padding: 6px 12px !important;
    box-shadow: none !important;
    font-size: 0.75rem !important;
    display: inline-flex !important;
  }
  
  .page-header-row .btn-xs i {
    font-size: 0.875rem !important;
    margin-right: 4px !important;
  }
  
  .page-header-row .btn-xs span {
    display: inline !important;
  }
}

/* Mobile responsiveness for review cards */
@media (max-width: 768px) {
  .grid {
    grid-template-columns: 1fr !important;
  }
  
  .card {
    padding: 1rem !important;
  }
  
  .flex.items-start.justify-between {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .w-12.h-12 {
    width: 2.5rem;
    height: 2.5rem;
    font-size: 1rem;
  }
  
  .flex.gap-2.pt-4 {
    flex-wrap: wrap;
  }
  
  .btn-sm {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
  }
}

/* Star icons sizing */
.fa-star {
  font-size: 1.25rem;
}

@media (max-width: 480px) {
  .fa-star {
    font-size: 1rem;
  }
  
  .page-header-row {
    display: flex !important;
    flex-direction: row !important;
    justify-content: space-between !important;
    align-items: flex-start !important;
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  
  .page-header-row > div:first-child {
    flex: 1 1 100%;
  }
  
  /* Keep sort buttons at top right on mobile */
  .page-header-row > div[style*="display:flex"] {
    margin-left: auto !important;
    margin-top: 0 !important;
    flex-shrink: 0;
  }
  
  .page-title {
    font-size: 1.5rem !important;
  }
  
  .page-subtitle {
    font-size: 0.875rem !important;
  }
}
</style>
