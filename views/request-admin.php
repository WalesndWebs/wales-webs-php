<main class="desk-layout">
<aside class="desk-sidebar"><div class="sidebar-title"><p class="eyebrow">Request desk</p><h1>Project requests <span class="count"><?= count($requests) ?></span></h1></div>
<?php if (!$requests): ?><p class="muted empty-state">No requests yet. New Usabime requests will appear here.</p><?php endif; ?>
<?php foreach ($requests as $row): ?><a class="desk-row<?= ($request['id'] ?? null) === $row['id'] ? ' active' : '' ?>" href="<?= e(url('/usabime/admin?id=' . $row['id'])) ?>"><span class="row-meta"><?= e($row['reference']) ?><span class="tag"><?= e(str_replace('_', ' ', $row['status'])) ?></span></span><strong><?= e($row['business_name']) ?></strong><small><?= e($row['full_name']) ?></small><small><?= e(substr($row['created_at'], 0, 10)) ?></small></a><?php endforeach; ?>
</aside>
<section class="desk-content">
<?php if (!$request): ?><div class="empty-state"><h2>Select a request to begin.</h2><p class="muted">Client details and proposal tools will appear here.</p></div><?php else: ?>
<div class="desk-heading"><p class="eyebrow">Request · <?= e($request['reference']) ?></p><h2><?= e($request['business_name']) ?></h2><p class="muted"><?= e($request['full_name']) ?> · <?= e(substr($request['created_at'], 0, 16)) ?> UTC</p></div>
<?php require __DIR__ . '/_errors.php'; ?><?php if ($feedback): ?><p class="success" role="status" data-testid="status-admin-feedback"><?= e($feedback) ?></p><?php endif; ?>
<div class="overview-grid"><article class="panel"><h3>Client and business</h3><dl class="details-grid">
<?php foreach (['full_name' => 'Contact name', 'phone_number' => 'Phone', 'email' => 'Email', 'business_type' => 'Business type', 'industry' => 'Industry', 'location' => 'Location', 'public_contact' => 'Public contact', 'brand_colors' => 'Brand colours'] as $key => $label): ?><div><dt><?= e($label) ?></dt><dd><?= e($request[$key] ?: 'Not provided') ?></dd></div><?php endforeach; ?>
<div class="wide"><dt>Business description</dt><dd class="preserve"><?= e($request['business_description']) ?></dd></div></dl></article>
<article class="panel"><h3>Project brief</h3><dl class="details-grid"><div><dt>Support type</dt><dd><?= e(str_replace('_', ' ', $request['service_type'])) ?></dd></div><div><dt>Timeline</dt><dd><?= e($request['preferred_timeline']) ?></dd></div><div><dt>Target date</dt><dd><?= e($request['target_date'] ?: 'Not provided') ?></dd></div><div class="wide"><dt>Additional details</dt><dd class="preserve"><?= e($request['additional_details'] ?: 'None provided') ?></dd></div>
<?php foreach (['website_references' => 'Website references', 'asset_links' => 'Shared assets'] as $key => $label): ?><div class="wide"><dt><?= e($label) ?></dt><dd><?php if (!$request[$key]): ?>None provided<?php else: foreach ($request[$key] as $link): ?><a class="inline-link shared-link" href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer"><?= e($link) ?> ↗</a><?php endforeach; endif; ?></dd></div><?php endforeach; ?>
</dl></article></div>
<form method="post" action="<?= e(url('/usabime/admin?id=' . $request['id'])) ?>" class="panel stack-form"><?= csrf_field() ?><input type="hidden" name="action" value="review"><h3>Internal review</h3><div class="form-grid">
<label class="field"><span>Request status</span><select name="status"><?php foreach (REQUEST_STATUSES as $status): ?><option value="<?= $status ?>"<?= $request['status'] === $status ? ' selected' : '' ?>><?= e($status === 'proposal_sent' ? 'Proposal prepared' : ucfirst(str_replace('_', ' ', $status))) ?></option><?php endforeach; ?></select></label>
<label class="field"><span>Private notes</span><textarea name="admin_notes" maxlength="5000" rows="3"><?= e($request['admin_notes']) ?></textarea></label></div><button type="submit" class="button-secondary">Save review</button></form>
<form method="post" action="<?= e(url('/usabime/admin?id=' . $request['id'])) ?>" class="panel proposal-editor" data-proposal-editor>
<?= csrf_field() ?><div class="editor-heading"><div><p class="eyebrow">Tailored offer</p><h3>Proposal authoring</h3></div><span class="tag"><?= $saved ? 'Saved proposal' : 'New proposal' ?></span></div>
<div class="form-grid">
<label class="field wide"><span>Proposal title</span><input name="title" value="<?= e($proposal['title']) ?>" required minlength="2" maxlength="180"></label>
<label class="field"><span>Currency <small>Type any symbol or label</small></span><input name="currency" value="<?= e($proposal['currency']) ?>" required maxlength="24" placeholder="₦, $, £, NGN, USD…" data-currency></label>
<label class="field"><span>Proposal status</span><select name="status"><?php foreach (['draft', 'sent', 'accepted', 'declined'] as $status): ?><option value="<?= $status ?>"<?= $proposal['status'] === $status ? ' selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></label>
<label class="field"><span>Valid until <small>Optional</small></span><input type="date" name="valid_until" value="<?= e($proposal['valid_until']) ?>"></label>
<?php foreach (['introduction' => ['Introduction', 3000], 'scope' => ['Scope of work', 5000]] as $key => [$label, $max]): ?><label class="field wide"><span><?= e($label) ?></span><textarea name="<?= $key ?>" maxlength="<?= $max ?>" rows="4"><?= e($proposal[$key]) ?></textarea></label><?php endforeach; ?>
</div>
<div class="line-heading"><div><h4>Line items</h4><p class="muted small">Add the work and costs that make up this proposal.</p></div><button type="button" class="button-secondary js-only" data-add-line>+ Add item</button></div>
<div data-line-items>
<?php foreach ($proposal['line_items'] as $index => $line): require __DIR__ . '/_proposal-line.php'; endforeach; ?>
</div>
<template data-line-template><?php $index = '__INDEX__'; $line = ['description' => '', 'quantity' => 1, 'unitPrice' => '']; require __DIR__ . '/_proposal-line.php'; ?></template>
<div class="proposal-total"><span>Proposal total</span><strong data-proposal-total><?php $total = array_reduce($proposal['line_items'], fn($sum, $item) => $sum + (preg_match('/^[0-9]{1,10}(\.[0-9]{1,2})?$/', $item['unitPrice']) ? cents($item['unitPrice']) * $item['quantity'] : 0), 0); ?><?= e($proposal['currency']) ?> <?= format_money($total) ?></strong></div>
<div class="form-grid"><?php foreach (['payment_terms' => ['Payment terms', 2000], 'timeline' => ['Timeline', 500], 'terms' => ['Terms and conditions', 5000]] as $key => [$label, $max]): ?><label class="field<?= $key === 'terms' ? ' wide' : '' ?>"><span><?= $label ?></span><textarea name="<?= $key ?>" maxlength="<?= $max ?>" rows="4"><?= e($proposal[$key]) ?></textarea></label><?php endforeach; ?></div>
<div class="actions"><button type="submit" name="action" value="proposal" class="button">Save proposal</button><button type="submit" name="action" value="print" class="button-secondary">Print / Save PDF</button><p class="muted small">Printing saves first. Choose “Save as PDF” in your browser. Sending is manual.</p></div></form>
<?php if ($saved): $printProposal = $saved; require __DIR__ . '/proposal-print.php'; endif; ?>
<?php endif; ?></section></main>