<?php
$user = $user ?? null;
$section = $section ?? 'student';
?>
<div class="card p-4 mb-4">
  <h2 class="section-title mb-0">Welcome, <?= htmlspecialchars($user['full_name'] ?? 'User') ?>.</h2>
  <p class="text-muted mb-0">Role: <?= htmlspecialchars(strtoupper($user['role'])) ?></p>
</div>

<?php if ($section === 'student'): ?>
<div class="row g-4">
  <div class="col-md-6">
    <div class="card p-4 h-100">
      <h4 class="section-title">Enrolled courses</h4>
      <?php foreach (($courses ?? []) as $course): ?>
        <div class="border rounded p-3 mb-2">
          <strong><?= htmlspecialchars($course['title']) ?></strong>
          <div class="small text-muted"><?= htmlspecialchars($course['difficulty']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-4 h-100">
      <h4 class="section-title">Latest announcements</h4>
      <?php foreach (($announcements ?? []) as $a): ?>
        <div class="mb-2 border rounded p-2">
          <strong><?= htmlspecialchars($a['title']) ?></strong>
          <div class="small text-muted"><?= htmlspecialchars($a['content']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<div class="card p-4 mt-4">
  <h4 class="section-title">Assignments</h4>
  <?php foreach (($assignments ?? []) as $assignment): ?>
    <div class="border rounded p-3 mb-2 d-flex justify-content-between align-items-center">
      <div>
        <strong><?= htmlspecialchars($assignment['title']) ?></strong>
        <div class="small text-muted"><?= htmlspecialchars($assignment['course_title']) ?></div>
      </div>
      <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/assignment/<?= (int) $assignment['id'] ?>">Open</a>
    </div>
  <?php endforeach; ?>
</div>
<?php elseif ($section === 'lecturer'): ?>
<div class="card p-4 mb-4"><h4 class="section-title">Courses taught</h4>
<?php foreach (($courses ?? []) as $course): ?>
  <div class="border rounded p-3 mb-2"><?= htmlspecialchars($course['title']) ?> <span class="text-muted small">(<?= htmlspecialchars($course['status']) ?>)</span></div>
<?php endforeach; ?>
</div>
<div class="card p-4">
  <h4 class="section-title">Assignments in review</h4>
  <?php foreach (($assignments ?? []) as $assignment): ?>
    <div class="border rounded p-3 mb-2 d-flex justify-content-between align-items-center">
      <div>
        <strong><?= htmlspecialchars($assignment['title']) ?></strong>
        <div class="small text-muted"><?= htmlspecialchars($assignment['course_title']) ?></div>
      </div>
      <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/assignment/<?= (int) $assignment['id'] ?>">Review</a>
    </div>
  <?php endforeach; ?>
</div>
<?php elseif ($section === 'admin'): ?>
<div class="card p-4">
  <h4 class="section-title">System overview</h4>
  <div class="row g-3 mt-2">
    <div class="col-md-4"><div class="border rounded p-3"><strong><?= count($users ?? []) ?></strong><div class="small text-muted">Users</div></div></div>
    <div class="col-md-4"><div class="border rounded p-3"><strong><?= count($courses ?? []) ?></strong><div class="small text-muted">Courses</div></div></div>
    <div class="col-md-4"><div class="border rounded p-3"><strong>3</strong><div class="small text-muted">Institutions</div></div></div>
  </div>
</div>
<div class="card p-4 mt-4">
  <h4 class="section-title">Assignments</h4>
  <?php foreach (($assignments ?? []) as $assignment): ?>
    <div class="border rounded p-3 mb-2 d-flex justify-content-between align-items-center">
      <div>
        <strong><?= htmlspecialchars($assignment['title']) ?></strong>
        <div class="small text-muted"><?= htmlspecialchars($assignment['course_title']) ?></div>
      </div>
      <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/assignment/<?= (int) $assignment['id'] ?>">Open</a>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
