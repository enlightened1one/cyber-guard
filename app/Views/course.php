<?php
$course = $course ?? [];
$modules = $modules ?? [];
$quizzes = $quizzes ?? [];
?>
<div class="card p-4 mb-4">
  <div class="d-flex justify-content-between flex-wrap gap-3 align-items-center">
    <div>
      <div class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($course['difficulty'] ?? 'Beginner') ?></div>
      <h2 class="mt-2"><?= htmlspecialchars($course['title'] ?? 'Course') ?></h2>
      <p class="text-muted mb-0"><?= htmlspecialchars($course['description'] ?? '') ?></p>
    </div>
    <?php if (Auth::user()): ?>
      <?php if ($isEnrolled): ?>
        <span class="btn btn-success">Enrolled</span>
      <?php else: ?>
        <form method="post" action="<?= BASE_URL ?>/enroll-course">
          <?= Csrf::token() ?>
          <input type="hidden" name="course_id" value="<?= (int) ($course['id'] ?? 0) ?>">
          <button class="btn btn-primary" type="submit">Enroll now</button>
        </form>
      <?php endif; ?>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/login" class="btn btn-primary">Sign in to enroll</a>
    <?php endif; ?>
  </div>
</div>

<div class="card p-4 mb-4">
  <h3 class="section-title">Course quizzes</h3>
  <?php if (!$isEnrolled): ?>
    <p class="text-muted mb-0">Enroll in this course to take its quizzes and review your scores.</p>
  <?php elseif (empty($quizzes)): ?>
    <p class="text-muted mb-0">There are no quizzes available for this course yet.</p>
  <?php else: ?>
    <div class="list-group">
      <?php foreach ($quizzes as $quiz): ?>
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3" href="<?= BASE_URL ?>/quiz/<?= (int) $quiz['id'] ?>">
          <span>
            <strong><?= htmlspecialchars($quiz['title']) ?></strong>
            <span class="d-block small text-muted"><?= htmlspecialchars($quiz['description'] ?? '') ?></span>
          </span>
          <span class="text-end text-nowrap">
            <?php if (isset($quiz['attempt_count'])): ?>
              <span class="d-block small text-muted"><?= (int) $quiz['attempt_count'] ?> attempt(s)</span>
            <?php endif; ?>
            <span class="badge bg-primary rounded-pill">Pass: <?= (int) $quiz['pass_mark'] ?>%</span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<div class="row g-4">
  <?php foreach ($modules as $module): ?>
    <div class="col-12">
      <div class="card p-3">
        <h4><?= htmlspecialchars($module['title']) ?></h4>
        <?php foreach ($module['lessons'] as $lesson): ?>
          <div class="border rounded p-3 mt-2">
            <strong><?= htmlspecialchars($lesson['title']) ?></strong>
            <p class="mb-1 text-muted"><?= htmlspecialchars($lesson['summary'] ?? '') ?></p>
            <small class="text-muted">Estimated time: <?= (int) ($lesson['duration_minutes'] ?? 0) ?> mins</small>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
