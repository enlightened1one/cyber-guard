<div class="row justify-content-center">
  <div class="col-lg-5">
    <div class="card p-4">
      <h2 class="section-title mb-3">Sign in</h2>
      <form method="post" action="<?= BASE_URL ?>/login">
        <?= Csrf::token() ?>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" name="password" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Login</button>
      </form>
      <div class="mt-3 d-flex justify-content-between small">
        <a href="<?= BASE_URL ?>/register">Create account</a>
        <a href="<?= BASE_URL ?>/forgot-password">Forgot password?</a>
      </div>
    </div>
  </div>
</div>
