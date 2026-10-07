<?php
$accent = in_array($post['accent'] ?? '', ['green','violet','amber','pink'], true) ? $post['accent'] : 'green';
$paras = array_filter(preg_split('/\R{2,}/', trim((string)$post['body'])), fn($p) => trim($p) !== '');
$svcs = is_array($post['services'] ?? null) ? $post['services'] : [];
?>
<main id="main">
<article class="container story">
  <a class="text-link back" href="<?= e(url('/journal')) ?>"><span aria-hidden="true">←</span> All tech stories</a>
  <div class="cover big cover-<?= e($accent) ?>">
    <span class="tag"><?= e($post['category']) ?></span>
    <div><p class="mono"><?= e($post['client_name']) ?></p><h1><?= e($post['title']) ?></h1></div>
  </div>
  <div class="story-meta">
    <p class="lead"><?= e($post['excerpt']) ?></p>
    <div class="actions"><button type="button" class="button" data-copy="<?= e(url('/journal/' . $post['slug'])) ?>">Copy link</button></div>
  </div>
  <div class="tags"><?php foreach ($svcs as $s): ?><span class="tag"><?= e($s) ?></span><?php endforeach; ?></div>
  <div class="article-copy"><?php foreach ($paras as $p): ?><p><?= nl2br(e(trim($p))) ?></p><?php endforeach; ?></div>
  <aside class="panel story-cta">
    <p class="eyebrow">Your business could be next</p>
    <h2>Let’s build something useful.</h2>
    <p class="muted">Tell us what your business needs. We will help you find a practical next step.</p>
    <a class="button" href="<?= e(url('/contact')) ?>">Start a conversation <span aria-hidden="true">→</span></a>
  </aside>
</article>
</main>
