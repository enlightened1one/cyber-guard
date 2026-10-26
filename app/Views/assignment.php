<?php
$assignment = $assignment ?? [];
$submission = $submission ?? null;
$submissions = $submissions ?? [];
$canGrade = $canGrade ?? false;
?>
<div class="card p-4 mb-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div>
      <h2 class="section-title mb-1"><?= htmlspecialchars($assignment['title'] ?? 'Assignment') ?></h2>
      <div class="text-muted"><?= htmlspecialchars($assignment['course_title'] ?? 'Course') ?></div>
    </div>
    <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>/assignments">Back to assignments</a>
  </div>

  <div class="mb-3">
    <strong>Due date:</strong> <?= htmlspecialchars($assignment['due_at'] ?? 'No due date set') ?>
    <span class="ms-3"><strong>Max marks:</strong> <?= (int) ($assignment['max_marks'] ?? 100) ?></span>
  </div>

  <div class="border rounded p-3 bg-light mb-4">
    <?= nl2br(htmlspecialchars($assignment['description'] ?? 'No description provided.')) ?>
  </div>

  <?php if (!empty($submission)): ?>
    <div class="border rounded p-3 mb-4">
      <h5 class="section-title">Your submission</h5>
      <div class="small text-muted mb-2">Status: <?= htmlspecialchars(strtoupper($submission['status'] ?? 'submitted')) ?></div>
      <p class="mb-2"><?= nl2br(htmlspecialchars($submission['text_answer'] ?? '')) ?></p>
      <?php if (!empty($submission['grade']) || (int) ($submission['grade'] ?? 0) === 0): ?>
        <div><strong>Grade:</strong> <?= (int) ($submission['grade'] ?? 0) ?> / <?= (int) ($assignment['max_marks'] ?? 100) ?></div>
      <?php endif; ?>
      <?php if (!empty($submission['feedback'])): ?>
        <div class="mt-2"><strong>Feedback:</strong><br><?= nl2br(htmlspecialchars($submission['feedback'])) ?></div>
      <?php endif; ?>
      <?php if (!empty($submission['file_path'])): ?>
        <div class="mt-2"><strong>Attached file:</strong> <a href="<?= BASE_URL ?>/assignment-submission/<?= (int) $submission['id'] ?>/download"><?= htmlspecialchars($submission['file_name'] ?? 'Download file') ?></a></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if (($user['role'] ?? '') === 'student'): ?>
    <form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/assignment/<?= (int) ($assignment['id'] ?? 0) ?>/submit">
      <?= Csrf::token() ?>
      <div class="mb-3">
        <label for="answer" class="form-label">Your response</label>
        <textarea class="form-control" id="answer" name="answer" rows="7" placeholder="Submit your assignment response here..."><?= htmlspecialchars($submission['text_answer'] ?? '') ?></textarea>
      </div>
      <div class="mb-3">
        <label for="submission_file" class="form-label">Attach a document (optional)</label>
        <input class="form-control" id="submission_file" type="file" name="submission_file" accept=".pdf,.doc,.docx,.txt">
        <div class="form-text">PDF, DOC, DOCX, or TXT; maximum file size 5 MB. You can submit a file without a written response.</div>
        <?php if (!empty($submission['file_path'])): ?>
          <div class="form-text">Current attachment: <?= htmlspecialchars($submission['file_name'] ?? '') ?>. Upload another file to replace it.</div>
        <?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary">Submit assignment</button>
    </form>
  <?php endif; ?>

  <?php if ($canGrade): ?>
    <div class="mt-5 border-top pt-4">
      <h4 class="section-title">Submission review</h4>
      <?php if (empty($submissions)): ?>
        <div class="alert alert-light border">No submissions have been received yet.</div>
      <?php else: ?>
        <?php foreach ($submissions as $item): ?>
          <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between gap-3 flex-wrap">
              <div>
                <strong><?= htmlspecialchars($item['full_name']) ?></strong>
                <div class="small text-muted"><?= htmlspecialchars($item['email']) ?></div>
              </div>
              <div class="small text-muted">Submitted: <?= htmlspecialchars($item['submitted_at']) ?></div>
            </div>
            <p class="mt-3 mb-2"><?= nl2br(htmlspecialchars($item['text_answer'] ?? '')) ?></p>
            <?php if (!empty($item['file_path'])): ?>
              <div class="mb-3"><strong>Attachment:</strong> <a href="<?= BASE_URL ?>/assignment-submission/<?= (int) $item['id'] ?>/download"><?= htmlspecialchars($item['file_name'] ?? 'Download file') ?></a></div>
            <?php endif; ?>
            <form method="post" action="<?= BASE_URL ?>/assignment/<?= (int) ($assignment['id'] ?? 0) ?>/grade">
              <?= Csrf::token() ?>
              <input type="hidden" name="submission_id" value="<?= (int) $item['id'] ?>">
              <div class="row g-2 align-items-end">
                <div class="col-md-2">
                  <label class="form-label small">Grade</label>
                  <input type="number" class="form-control" name="grade" min="0" max="<?= (int) ($assignment['max_marks'] ?? 100) ?>" value="<?= (int) ($item['grade'] ?? 0) ?>">
                </div>
                <div class="col-md-8">
                  <label class="form-label small">Feedback</label>
                  <textarea class="form-control" name="feedback" rows="2" placeholder="Provide grading feedback"><?= htmlspecialchars($item['feedback'] ?? '') ?></textarea>
                </div>
                <div class="col-md-2 text-end">
                  <button type="submit" class="btn btn-success w-100">Save</button>
                </div>
              </div>
            </form>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
