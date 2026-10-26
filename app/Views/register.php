<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card p-4">
      <h2 class="section-title mb-3">Student registration</h2>
      <form method="post" action="<?= BASE_URL ?>/register">
        <?= Csrf::token() ?>
        <div class="mb-3">
          <label class="form-label">Full name</label>
          <input type="text" class="form-control" name="full_name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" name="password" min="8" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Create account</button>
      </form>
    </div>
  </div>
</div>
