<?php
$exercise = $exercise ?? [];
$user = $user ?? null;
$submissions = $submissions ?? [];
$reviewSubmissions = $reviewSubmissions ?? [];
$canGrade = $canGrade ?? false;
?>
<div class="card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="badge bg-primary-subtle text-primary mb-2"><?= htmlspecialchars($exercise['difficulty'] ?? 'Intermediate') ?></div>
            <h2 class="section-title mb-2"><?= htmlspecialchars($exercise['title'] ?? 'Cybersecurity exercise') ?></h2>
        </div>
        <a href="<?= BASE_URL ?>/exercises" class="btn btn-outline-primary">Back to exercises</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <h4 class="section-title">Instructions</h4>
            <p><?= nl2br(htmlspecialchars($exercise['instructions'] ?? 'Review the scenario carefully and determine the best response.')) ?></p>

            <div class="mt-4">
                <h5 class="section-title">Learning outcome</h5>
                <ul>
                    <li>Identify the risk indicators in a simulated cyber scenario.</li>
                    <li>Choose the safest response aligned with cybersecurity best practice.</li>
                    <li>Explain why the selected action is more defensible than the alternatives.</li>
                </ul>
            </div>

            <?php if (!$user): ?>
                <div class="alert alert-info">Sign in as an enrolled student to complete this exercise and save your response.</div>
            <?php elseif (($user['role'] ?? '') === 'student' && !empty($isEnrolled)): ?>
                <div class="card p-4 mt-4">
                    <h4 class="section-title">Complete this exercise</h4>
                    <p class="text-muted">Work through the scenario above. Explain what you would do, why you would do it, and any relevant security considerations. Your response will be saved for review; your score and feedback will appear here after your lecturer grades it.</p>
                    <form method="post" action="<?= BASE_URL ?>/exercise/<?= (int) ($exercise['id'] ?? 0) ?>/submit">
                        <?= Csrf::token() ?>
                        <div class="mb-3">
                            <label for="exercise-answer" class="form-label">Your analysis and response</label>
                            <textarea class="form-control" id="exercise-answer" name="answer" rows="8" minlength="30" maxlength="20000" required placeholder="Describe the steps you would take and explain your reasoning..."><?= htmlspecialchars($submissions[0]['answer_text'] ?? '') ?></textarea>
                            <div class="form-text">Write at least 30 characters. Submitting again creates a new attempt.</div>
                        </div>
                        <button class="btn btn-primary" type="submit">Submit exercise for review</button>
                    </form>
                </div>

                <?php if (!empty($submissions)): ?>
                    <div class="card p-4 mt-4">
                        <h4 class="section-title">Your attempts and feedback</h4>
                        <?php foreach ($submissions as $index => $submission): ?>
                            <div class="border rounded p-3 mt-3">
                                <div class="d-flex justify-content-between flex-wrap gap-2">
                                    <strong>Attempt <?= count($submissions) - $index ?></strong>
                                    <span class="badge bg-<?= $submission['status'] === 'graded' ? 'success' : 'secondary' ?>"><?= htmlspecialchars(ucfirst($submission['status'])) ?></span>
                                </div>
                                <div class="small text-muted mt-1">Submitted <?= htmlspecialchars($submission['submitted_at']) ?></div>
                                <p class="mt-3 mb-2"><?= nl2br(htmlspecialchars($submission['answer_text'])) ?></p>
                                <?php if ($submission['status'] === 'graded'): ?>
                                    <div><strong>Score:</strong> <?= (int) $submission['score'] ?> / <?= (int) ($exercise['max_score'] ?? 100) ?></div>
                                    <?php if (!empty($submission['feedback'])): ?>
                                        <div class="mt-2"><strong>Lecturer feedback:</strong><br><?= nl2br(htmlspecialchars($submission['feedback'])) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-muted">Awaiting lecturer review.</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php elseif ($canGrade): ?>
                <div class="card p-4 mt-4">
                    <h4 class="section-title">Student exercise submissions</h4>
                    <?php if (empty($reviewSubmissions)): ?>
                        <p class="text-muted mb-0">No student responses have been submitted yet.</p>
                    <?php else: ?>
                        <?php foreach ($reviewSubmissions as $submission): ?>
                            <div class="border rounded p-3 mt-3">
                                <div class="d-flex justify-content-between flex-wrap gap-2">
                                    <div>
                                        <strong><?= htmlspecialchars($submission['full_name']) ?></strong>
                                        <div class="small text-muted"><?= htmlspecialchars($submission['email']) ?></div>
                                    </div>
                                    <span class="small text-muted">Submitted <?= htmlspecialchars($submission['submitted_at']) ?></span>
                                </div>
                                <p class="mt-3"><?= nl2br(htmlspecialchars($submission['answer_text'])) ?></p>
                                <form method="post" action="<?= BASE_URL ?>/exercise/<?= (int) ($exercise['id'] ?? 0) ?>/grade">
                                    <?= Csrf::token() ?>
                                    <input type="hidden" name="submission_id" value="<?= (int) $submission['id'] ?>">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-2">
                                            <label class="form-label">Score</label>
                                            <input class="form-control" type="number" name="score" min="0" max="<?= (int) ($exercise['max_score'] ?? 100) ?>" value="<?= (int) ($submission['score'] ?? 0) ?>" required>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Feedback</label>
                                            <textarea class="form-control" name="feedback" rows="2"><?= htmlspecialchars($submission['feedback'] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-success w-100" type="submit">Save grade</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4">
            <h5 class="section-title">Exercise summary</h5>
            <ul class="list-unstyled mb-0">
                <li><strong>Course:</strong> <?= htmlspecialchars($exercise['course_title'] ?? 'General security lab') ?></li>
                <li><strong>Type:</strong> <?= htmlspecialchars($exercise['challenge_type'] ?? 'Scenario analysis') ?></li>
                <li><strong>Max score:</strong> <?= (int) ($exercise['max_score'] ?? 100) ?></li>
            </ul>
        </div>
    </div>
</div>
