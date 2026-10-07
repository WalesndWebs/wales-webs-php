<?php $v = fn($key) => e($values[$key] ?? ''); ?>
<main id="main" class="container request-wrap">
<?php if ($receipt): ?>
<section class="panel success"><p class="eyebrow">Request received</p><h1 class="page-heading">Thank you, <?= e(explode(' ', $receipt['name'])[0]) ?>.</h1><p>Our team will review your request and prepare a tailored proposal. We will contact you personally using the details you shared.</p><p class="receipt-reference">Your reference <strong><?= e($receipt['reference']) ?></strong></p><p class="muted">No account needed. No automatic email has been sent.</p><a class="button" href="<?= e(url('/')) ?>">Return home →</a></section>
<?php else: ?>
<div class="request-heading"><p class="eyebrow">Usabime · Project request</p><h1 class="page-heading">Tell us what your business needs.</h1><p class="muted">A clear starting point is enough. Share your business, goals and timeline. We will prepare the right next steps with you.</p></div>
<?php require __DIR__ . '/_errors.php'; ?>
<form method="post" action="<?= e(url('/usabime/request')) ?>" class="panel request-wizard" data-request-wizard<?= $errors ? ' data-has-errors="1"' : '' ?>>
<?= csrf_field() ?>
<div class="request-stepper" aria-label="Request progress"><span data-step-label="0">01 · About you</span><span data-step-label="1">02 · Your project</span><span data-step-label="2">03 · Review</span></div>
<section data-step="0"><h2>First, the essentials</h2><p class="muted">How can we reach you, and what should we know about your business?</p><div class="form-grid">
<?php foreach ([
 'full_name' => ['Your full name', 2, 120, 'e.g. Amina Bello', 'name'],
 'phone_number' => ['Phone number', 5, 40, 'Include your country code', 'tel'],
 'business_name' => ['Business name', 2, 160, 'The name customers know', 'organization'],
 'business_type' => ['What kind of business is it?', 2, 120, 'e.g. Catering company', 'off'],
 'industry' => ['Industry', 2, 120, 'Food, retail, professional services…', 'off'],
 'location' => ['Business location', 2, 200, 'City, region or country', 'off'],
] as $key => [$label, $min, $max, $placeholder, $auto]): ?>
<label class="field"><span><?= e($label) ?> <b class="req">*</b></span><input name="<?= e($key) ?>" value="<?= $v($key) ?>" required minlength="<?= $min ?>" maxlength="<?= $max ?>" placeholder="<?= e($placeholder) ?>" autocomplete="<?= e($auto) ?>" data-testid="input-request-<?= e($key) ?>"></label>
<?php endforeach; ?>
<label class="field"><span>Email address <small>Optional — phone is enough</small></span><input type="email" name="email" value="<?= $v('email') ?>" maxlength="254" autocomplete="email" placeholder="you@example.com"></label>
</div></section>
<section data-step="1"><h2>What would help most?</h2><p class="muted">Tell us about the support you need. Links are optional; no uploads or account needed.</p><div class="form-grid">
<label class="field wide"><span>Support type</span><select name="service_type" required>
<?php foreach (['business_profile' => 'Business profile', 'digital_presence' => 'Digital presence', 'business_blog' => 'Business blog'] as $key => $label): ?><option value="<?= e($key) ?>"<?= ($values['service_type'] ?? '') === $key ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
<label class="field wide"><span>Describe your business and what you offer <b class="req">*</b></span><textarea name="business_description" required minlength="10" maxlength="3000" rows="5" placeholder="What do customers come to you for?"><?= $v('business_description') ?></textarea></label>
<label class="field"><span>Public contact details <small>Optional</small></span><textarea name="public_contact" maxlength="1000" rows="3" placeholder="Business phone, public email or social handle"><?= $v('public_contact') ?></textarea></label>
<label class="field"><span>Brand colours <small>Optional</small></span><input name="brand_colors" maxlength="300" value="<?= $v('brand_colors') ?>" placeholder="e.g. Deep green and cream"></label>
<label class="field"><span>Preferred timeline <b class="req">*</b></span><input name="preferred_timeline" required minlength="2" maxlength="120" value="<?= $v('preferred_timeline') ?>" placeholder="e.g. Within the next two months"></label>
<label class="field"><span>Target date <small>Optional</small></span><input type="date" name="target_date" value="<?= $v('target_date') ?>"></label>
<?php foreach (['website_references' => ['Websites you like or already use', 'Public website or inspiration links'], 'asset_links' => ['Links to your brand assets', 'Public cloud-folder links to logos or brand material']] as $key => [$label, $hint]): $links = $values[$key] ?? []; ?>
<label class="field wide"><span><?= e($label) ?> <small>Optional · one link per line, maximum 10</small></span><textarea name="<?= e($key) ?>" rows="3" maxlength="20010" placeholder="https://"><?= e(is_array($links) ? implode("\n", $links) : $links) ?></textarea><small class="muted"><?= e($hint) ?>. Use http or https links; no direct uploads.</small></label>
<?php endforeach; ?>
<label class="field wide"><span>Anything else we should know? <small>Optional</small></span><textarea name="additional_details" maxlength="3000" rows="4" placeholder="Goals, challenges or questions…"><?= $v('additional_details') ?></textarea><small class="muted">Do not include passwords or sensitive account details.</small></label>
</div></section>
<section data-step="2"><h2>Review before sending</h2><p class="muted">Check your details. You can go back to change anything.</p><dl class="request-review" data-request-review></dl>
<label class="consent"><input type="checkbox" name="privacy_accepted" value="1" required><span>I agree that Wales &amp; Webs may store these details privately to review my request and contact me about this project. Do not share passwords or sensitive account information.</span></label></section>
<div class="request-actions"><button type="button" class="button-secondary wizard-only" data-step-back>← Back</button><p class="muted small">Your information is used only to review this request.</p><button type="button" class="button wizard-only" data-step-next>Continue →</button><button type="submit" class="button" data-request-submit>Send request →</button></div>
</form><p class="muted small request-footnote">A member of the team will review your request. Proposals and replies are handled personally.</p>
<script src="<?= e(asset_url('/assets/request.js')) ?>" defer></script>
<?php endif; ?>
</main>