<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="section-title">Course catalogue</h2>
  <a href="<?= BASE_URL ?>/" class="btn btn-link">Back home</a>
</div>
<div class="row g-4">
  <?php foreach ($courses as $course): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <span class="badge bg-secondary mb-2"><?= htmlspecialchars($course['difficulty']) ?></span>
          <h5><?= htmlspecialchars($course['title']) ?></h5>
          <p class="text-muted"><?= htmlspecialchars($course['description']) ?></p>
          <div class="small text-muted mb-2">Instructor: <?= htmlspecialchars($course['instructor_name'] ?? 'TBD') ?></div>
          <a href="<?= BASE_URL ?>/course/<?= rawurlencode($course['slug']) ?>" class="btn btn-primary">Open course</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
