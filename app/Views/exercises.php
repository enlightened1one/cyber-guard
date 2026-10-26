<div class="card p-4 mb-4">
    <h2 class="section-title mb-2">Practical cybersecurity exercises</h2>
    <p class="text-muted mb-0">These safe, browser-based exercises help learners analyze phishing attempts, evaluate password quality, and recognize secure coding practices without exposing real systems.</p>
</div>

<div class="row g-4">
    <?php foreach ($exercises as $exercise): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <span class="badge bg-info-subtle text-info mb-2"><?= htmlspecialchars($exercise['difficulty'] ?? 'Intermediate') ?></span>
                    <h5><?= htmlspecialchars($exercise['title']) ?></h5>
                    <div class="small text-muted mb-3"><?= htmlspecialchars($exercise['course_title'] ?? 'Course lab') ?></div>
                    <p class="text-muted"><?= htmlspecialchars($exercise['instructions'] ?? 'Practice scenario and learning objective.') ?></p>
                    <div class="mt-auto">
                        <a href="<?= BASE_URL ?>/exercise/<?= (int) $exercise['id'] ?>" class="btn btn-primary">Open exercise</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
