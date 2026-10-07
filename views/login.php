<main class="login-wrap">
  <section class="panel login-panel"><span class="login-symbol" aria-hidden="true">✦</span><p class="eyebrow">Private workspace</p><h1 class="page-heading">Admin sign in</h1>
    <p class="muted">Enter your existing admin password. Your session expires after 30 minutes of inactivity.</p>
    <?php require __DIR__ . '/_errors.php'; ?>
    <form method="post" action="<?= e(url('/admin/login')) ?>" class="stack-form">
      <?= csrf_field() ?><input type="hidden" name="next" value="<?= e($next) ?>">
      <label class="field"><span>Admin password</span><input type="password" name="password" required autocomplete="current-password" data-testid="input-admin-password"></label>
      <button type="submit" class="button" data-testid="button-admin-sign-in">Continue →</button>
    </form><p class="muted small">Protected access. Your password is never stored in the page or export.</p>
  </section>
</main>