<?php
$role = $role ?? 'student';
?>
<div class="card p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="section-title mb-0">Assignments</h2>
    <?php if ($role === 'student'): ?>
      <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/dashboard">Back to dashboard</a>
    <?php else: ?>
      <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/dashboard">Back to dashboard</a>
    <?php endif; ?>
  </div>

  <?php if (empty($assignments)): ?>
    <div class="alert alert-light border">There are no assignments for your current view.</div>
  <?php else: ?>
    <div class="list-group">
      <?php foreach ($assignments as $assignment): ?>
        <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>/assignment/<?= (int) $assignment['id'] ?>">
          <div class="d-flex justify-content-between gap-3">
            <div>
              <strong><?= htmlspecialchars($assignment['title']) ?></strong>
              <div class="small text-muted"><?= htmlspecialchars($assignment['course_title'] ?? $assignment['course_id']) ?></div>
            </div>
            <div class="text-end">
              <div class="small text-muted">Due: <?= htmlspecialchars($assignment['due_at'] ?? 'No due date') ?></div>
              <?php if (isset($assignment['submission_status'])): ?>
                <span class="badge bg-<?= $assignment['submission_status'] === 'graded' ? 'success' : 'secondary' ?> rounded-pill"><?= htmlspecialchars(strtoupper($assignment['submission_status'] ?? 'Pending')) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
