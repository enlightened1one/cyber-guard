<div class="card p-4 mb-4">
  <h2 class="section-title">Administrative management</h2>
  <div class="row g-3 mt-2">
    <div class="col-md-4"><div class="border rounded p-3"><strong><?= (int) ($stats['users'] ?? 0) ?></strong><div class="small text-muted">Users</div></div></div>
    <div class="col-md-4"><div class="border rounded p-3"><strong><?= (int) ($stats['courses'] ?? 0) ?></strong><div class="small text-muted">Courses</div></div></div>
    <div class="col-md-4"><div class="border rounded p-3"><strong><?= (int) ($stats['lecturers'] ?? 0) ?></strong><div class="small text-muted">Lecturers</div></div></div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card p-4">
      <h3 class="section-title">Course catalog management</h3>
      <form method="post" action="<?= BASE_URL ?>/admin/course/save">
        <?= Csrf::token() ?>
        <div class="row g-3 mt-1">
          <div class="col-md-6">
            <label class="form-label">Course title</label>
            <input class="form-control" name="title" placeholder="e.g. Mobile Security Lab" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Slug</label>
            <input class="form-control" name="slug" placeholder="mobile-security-lab">
          </div>
          <div class="col-md-6">
            <label class="form-label">Category</label>
            <input class="form-control" name="category" value="General">
          </div>
          <div class="col-md-6">
            <label class="form-label">Difficulty</label>
            <select class="form-select" name="difficulty">
              <option value="Beginner">Beginner</option>
              <option value="Intermediate">Intermediate</option>
              <option value="Advanced">Advanced</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Instructor</label>
            <select class="form-select" name="instructor_id">
              <option value="0">Unassigned</option>
              <?php foreach ($lecturers as $lecturer): ?>
                <option value="<?= (int) $lecturer['id'] ?>"><?= htmlspecialchars($lecturer['full_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Duration (weeks)</label>
            <input class="form-control" type="number" name="duration_weeks" min="1" value="6">
          </div>
          <div class="col-md-12">
            <label class="form-label">Short description</label>
            <textarea class="form-control" name="description" rows="3"></textarea>
          </div>
          <div class="col-md-12">
            <label class="form-label">Learning objectives</label>
            <textarea class="form-control" name="learning_objectives" rows="3"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="published">Published</option>
              <option value="draft">Draft</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Institution</label>
            <input class="form-control" type="number" name="institution_id" value="1">
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Create course</button>
      </form>

      <div class="mt-4">
        <h4 class="section-title">Existing courses</h4>
        <div class="list-group mt-3">
          <?php foreach ($courses as $course): ?>
            <div class="list-group-item">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                  <strong><?= htmlspecialchars($course['title']) ?></strong>
                  <div class="small text-muted"><?= htmlspecialchars($course['category'] ?? 'General') ?> · <?= htmlspecialchars($course['difficulty'] ?? 'Beginner') ?> · <?= htmlspecialchars($course['status'] ?? 'published') ?></div>
                </div>
                <div class="d-flex gap-2">
                  <form method="post" action="<?= BASE_URL ?>/admin/course/delete" onsubmit="return confirm('Remove this course?');">
                    <?= Csrf::token() ?>
                    <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card p-4">
      <h3 class="section-title">User access overview</h3>
      <table class="table table-striped mt-3">
        <thead>
          <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['full_name']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($u['role']) ?></td>
              <td><?= htmlspecialchars($u['status']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

