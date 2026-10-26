<?php
$quiz = $quiz ?? [];
$questions = $questions ?? [];
$attempts = $attempts ?? [];
?>
<div class="card p-4 mb-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
      <div class="small text-muted"><?= htmlspecialchars($quiz['course_title'] ?? 'Course') ?></div>
      <h2 class="section-title mt-1"><?= htmlspecialchars($quiz['title'] ?? 'Course quiz') ?></h2>
      <p class="text-muted mb-0"><?= htmlspecialchars($quiz['description'] ?? '') ?></p>
    </div>
    <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>/course/<?= rawurlencode((string) ($quiz['course_slug'] ?? '')) ?>">Back to course</a>
  </div>
  <div class="small text-muted mt-3">Pass mark: <?= (int) ($quiz['pass_mark'] ?? 0) ?>% · Suggested time: <?= (int) ($quiz['time_limit_minutes'] ?? 0) ?> minutes · Attempts are recorded in your history.</div>
</div>

<?php if (empty($questions)): ?>
  <div class="alert alert-warning">This quiz does not have any questions yet. Please contact the course lecturer.</div>
<?php else: ?>
  <form method="post" action="<?= BASE_URL ?>/quiz/<?= (int) ($quiz['id'] ?? 0) ?>/submit">
    <?= Csrf::token() ?>
    <?php foreach ($questions as $index => $question): ?>
      <fieldset class="card p-4 mb-3">
        <legend class="fs-5 fw-semibold"><?= ($index + 1) ?>. <?= htmlspecialchars($question['question_text']) ?></legend>
        <div class="small text-muted mb-2"><?= (int) $question['points'] ?> point(s)</div>
        <?php foreach ($question['options'] as $option): ?>
          <label class="d-block border rounded p-2 mb-2">
            <input class="form-check-input me-2" type="radio" name="answers[<?= (int) $question['id'] ?>]" value="<?= (int) $option['id'] ?>" required>
            <?= htmlspecialchars($option['option_text']) ?>
          </label>
        <?php endforeach; ?>
      </fieldset>
    <?php endforeach; ?>
    <button class="btn btn-primary" type="submit">Submit quiz</button>
  </form>
<?php endif; ?>

<div class="card p-4 mt-4">
  <h3 class="section-title">Your previous attempts</h3>
  <?php if (empty($attempts)): ?>
    <p class="text-muted mb-0">You have not submitted this quiz yet.</p>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Attempt</th><th>Score</th><th>Result</th><th>Submitted</th></tr></thead>
        <tbody>
          <?php foreach ($attempts as $attempt): ?>
            <tr>
              <td><?= (int) $attempt['attempt_no'] ?></td>
              <td><?= (int) $attempt['score'] ?>%</td>
              <td><?= (int) $attempt['score'] >= (int) ($quiz['pass_mark'] ?? 0) ? 'Passed' : 'Try again' ?></td>
              <td><?= htmlspecialchars($attempt['submitted_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
