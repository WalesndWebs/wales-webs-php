<main class="desk-layout">
<aside class="desk-sidebar"><div class="sidebar-title"><p class="eyebrow">Messages</p><h1>Contact inbox <span class="count"><?= count($inquiries) ?></span></h1></div>
<?php if (!$inquiries): ?><p class="muted empty-state">No inquiries yet. New contact form messages will appear here.</p><?php endif; ?>
<?php foreach ($inquiries as $row): ?><a class="desk-row<?= ($inquiry['id'] ?? null) === $row['id'] ? ' active' : '' ?>" href="<?= e(url('/admin/contact?id=' . $row['id'])) ?>"><span class="row-meta"><?= e($row['reference']) ?><span class="tag"><?= e($row['status']) ?></span></span><strong><?= e($row['full_name']) ?></strong><small><?= e($row['email']) ?></small><small><?= e(substr($row['created_at'], 0, 10)) ?></small></a><?php endforeach; ?>
</aside>
<section class="desk-content">
<?php if (!$inquiry): ?><div class="empty-state"><h2>Your contact desk is ready.</h2><p class="muted">Customer messages will be saved here, not sent by email.</p></div><?php else: ?>
<div class="desk-heading"><p class="eyebrow">Inquiry · <?= e($inquiry['reference']) ?></p><h2><?= e($inquiry['full_name']) ?></h2><p class="muted">Received <?= e(substr($inquiry['created_at'], 0, 16)) ?> UTC</p></div>
<?php require __DIR__ . '/_errors.php'; ?><?php if ($feedback): ?><p class="success" role="status"><?= e($feedback) ?></p><?php endif; ?>
<article class="panel"><h3>Contact and project details</h3><dl class="details-grid">
<div><dt>Email</dt><dd><a class="inline-link" href="mailto:<?= e($inquiry['email']) ?>"><?= e($inquiry['email']) ?></a></dd></div>
<div><dt>Phone / WhatsApp</dt><dd><?= e($inquiry['phone_number'] ?: 'Not provided') ?></dd></div>
<div><dt>Primary service</dt><dd><?= e(SERVICE_LABELS[$inquiry['service']] ?? $inquiry['service']) ?></dd></div>
<div><dt>Project scope / budget</dt><dd><?= e($inquiry['budget'] ?: 'To be discussed') ?></dd></div>
<div class="wide"><dt>Message</dt><dd class="preserve"><?= e($inquiry['message']) ?></dd></div></dl>
<a class="button-secondary" href="mailto:<?= e($inquiry['email']) ?>?subject=<?= e(rawurlencode('Your Wales & Webs inquiry ' . $inquiry['reference'])) ?>">Open an email draft →</a><p class="muted small">This opens your own email app. Nothing is sent automatically. Mark “Replied” only after you have replied.</p></article>
<form method="post" action="<?= e(url('/admin/contact?id=' . $inquiry['id'])) ?>" class="panel stack-form"><?= csrf_field() ?><h3>Internal review</h3>
<label class="field"><span>Status</span><select name="status"><?php foreach (CONTACT_STATUSES as $status): ?><option value="<?= $status ?>"<?= $inquiry['status'] === $status ? ' selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></label>
<label class="field"><span>Private notes</span><textarea name="admin_notes" maxlength="5000" rows="5"><?= e($inquiry['admin_notes']) ?></textarea></label>
<button type="submit" class="button">Save review</button></form>
<?php endif; ?></section></main>