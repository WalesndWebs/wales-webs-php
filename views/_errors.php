<?php if ($errors): ?>
<div class="alert" role="alert" tabindex="-1" data-error-summary><strong>Please check:</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>