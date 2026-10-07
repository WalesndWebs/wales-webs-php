<?php $accent = in_array($post['accent'] ?? '', ['green','violet','amber','pink'], true) ? $post['accent'] : 'green'; $svcs = is_array($post['services'] ?? null) ? array_slice($post['services'], 0, 3) : []; ?>
<article class="journal-card">
  <a href="<?= e(url('/journal/' . $post['slug'])) ?>" aria-label="Read <?= e($post['title']) ?>">
    <div class="cover cover-<?= e($accent) ?>">
      <div class="cover-top"><span class="tag"><?= e($post['category']) ?></span><?php if (!empty($post['featured'])): ?><span class="featured">Featured story</span><?php endif; ?></div>
      <div><p class="mono"><?= e($post['client_name']) ?></p><h3><?= e($post['title']) ?></h3></div>
    </div>
    <div class="card-body">
      <p class="muted"><?= e($post['excerpt']) ?></p>
      <div class="tags"><?php foreach ($svcs as $s): ?><span class="tag"><?= e($s) ?></span><?php endforeach; ?></div>
      <span class="text-link">Read the story <span aria-hidden="true">→</span></span>
    </div>
  </a>
</article>
