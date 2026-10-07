<?php
$posts = $posts ?? [];
$q = $search ?? ''; $cat = $category ?? '';
$cats = array_fill_keys($categories ?? [], true);
if ($cat !== '') { $cats[$cat] = true; }
ksort($cats);
?>
<main id="main">
<section class="container section">
  <p class="eyebrow">Tech stories / our work</p>
  <h1 class="page-heading xl">The work behind<br><span class="gradient-word">the progress.</span></h1>
  <p class="lead">Every project starts with a real business and a real challenge. Read what we made together, what changed and what the next chapter can look like.</p>
  <form class="filter-bar" method="get" action="<?= e(url('/journal')) ?>" role="search">
    <div class="field"><label for="q">Search stories</label><input id="q" type="search" name="q" value="<?= e($q) ?>" placeholder="Client, topic or service"></div>
    <div class="field"><label for="category">Category</label>
      <select id="category" name="category"><option value="">All categories</option>
        <?php foreach (array_keys($cats) as $c): ?><option value="<?= e($c) ?>"<?= $c === $cat ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
      </select></div>
    <button class="button" type="submit">Filter</button>
    <?php if ($q !== '' || $cat !== ''): ?><a class="button-secondary" href="<?= e(url('/journal')) ?>">Clear</a><?php endif; ?>
  </form>
  <p class="muted" role="status"><?= count($posts) ?> <?= count($posts) === 1 ? 'story' : 'stories' ?><?= $q !== '' ? ' matching “' . e($q) . '”' : '' ?></p>
  <?php if (!$posts): ?>
    <div class="panel empty"><h2>No stories found</h2><p class="muted"><?= ($q !== '' || $cat !== '') ? 'Try a different search or clear the filters.' : 'No stories have been published yet. Check back soon.' ?></p><?php if ($q !== '' || $cat !== ''): ?><a class="button-secondary" href="<?= e(url('/journal')) ?>">Show all stories</a><?php endif; ?></div>
  <?php else: ?>
    <div class="journal-grid"><?php foreach ($posts as $post) { include __DIR__ . '/_card.php'; } ?></div>
  <?php endif; ?>
</section>
</main>
