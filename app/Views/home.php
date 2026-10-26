<section class="hero rounded-4 p-4 p-lg-5 mb-4">
  <div class="row align-items-center g-4">
    <div class="col-lg-7">
      <span class="badge badge-soft rounded-pill px-3 py-2 mb-3">Nigeria Cybersecurity Learning Platform</span>
      <h1 class="display-5 fw-bold">Build practical cybersecurity skills for the next generation.</h1>
      <p class="lead mt-3">CyberGuard LMS helps universities and students explore cyber hygiene, secure coding, malware awareness, ethical hacking, and incident response through guided coursework and practical exercises.</p>
      <div class="d-flex gap-3 flex-wrap mt-4">
        <a href="<?= BASE_URL ?>/catalog" class="btn btn-primary btn-lg">Explore courses</a>
        <a href="<?= BASE_URL ?>/register" class="btn btn-outline-light btn-lg">Join as student</a>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card text-dark p-4">
        <h5 class="fw-bold mb-3">Institutional priorities</h5>
        <ul class="list-group list-group-flush">
          <li class="list-group-item">Cyber awareness and safe digital culture</li>
          <li class="list-group-item">Secure coding and OWASP awareness</li>
          <li class="list-group-item">Ethical hacking and risk management</li>
          <li class="list-group-item">Incident response readiness</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<div class="row g-4 mb-4">
  <?php foreach ($courses as $course): ?>
    <div class="col-md-6 col-xl-4">
      <div class="card h-100">
        <div class="card-body d-flex flex-column">
          <span class="badge bg-primary-subtle text-primary mb-2"><?= htmlspecialchars($course['difficulty']) ?></span>
          <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
          <p class="text-muted"><?= htmlspecialchars(substr($course['description'], 0, 120)) ?>...</p>
          <div class="mt-auto">
            <small class="text-muted d-block mb-3"><?= htmlspecialchars($course['institution_name'] ?? 'Demo institution') ?></small>
            <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/course/<?= rawurlencode($course['slug']) ?>">View details</a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card p-4">
  <h3 class="section-title mb-3">Latest updates</h3>
  <?php foreach ($announcements as $announcement): ?>
    <div class="border rounded p-3 mb-2">
      <strong><?= htmlspecialchars($announcement['title']) ?></strong>
      <p class="mb-0 text-muted"><?= htmlspecialchars($announcement['content']) ?></p>
    </div>
  <?php endforeach; ?>
</div>
