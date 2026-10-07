<?php
$errors = $errors ?? []; $values = $values ?? []; $receipt = $receipt ?? null;
$v = fn($k) => e($values[$k] ?? '');
$svcOpts = ['digital_presence'=>'Digital Presence','business_systems'=>'Business Systems','automation_ai'=>'Automation & AI','digital_growth'=>'Digital Growth','custom_web_app'=>'Custom Web App / Portal','other'=>'Something else'];
$sel = $values['service'] ?? '';
?>
<main id="main">
<section class="container section contact-wrap">
<?php if ($receipt): $ref = $receipt['reference'] ?? $receipt['id'] ?? ''; ?>
  <div class="panel success" role="status">
    <p class="eyebrow">Message received</p>
    <h1 class="page-heading">Thank you. Wale will reply personally.</h1>
    <p class="muted">Your enquiry has been stored privately. We reply manually, and no automatic email has been sent.</p>
    <?php if ($ref !== ''): ?><p>Reference: <strong class="mono"><?= e($ref) ?></strong></p><?php endif; ?>
    <div class="actions"><a class="button" href="<?= e(url('/')) ?>">Back home</a><a class="button-secondary" href="<?= e(url('/journal')) ?>">Read tech stories</a></div>
  </div>
<?php else: ?>
  <p class="eyebrow">Client inquiry form</p>
  <h1 class="page-heading">Start a Conversation with Wale</h1>
  <p class="muted">Tell us what you’re looking to build and your preferred timeline.</p>
  <?php if ($errors): ?>
    <div class="alert" role="alert" tabindex="-1" data-error-summary><strong>Please fix the following:</strong><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" action="<?= e(url('/contact')) ?>" class="form-grid">
    <?= csrf_field() ?>
    <div class="field"><label for="full_name">Your Full Name <span class="req" aria-hidden="true">*</span></label><input id="full_name" name="full_name" required minlength="2" maxlength="120" autocomplete="name" placeholder="e.g. Tunde Adeyemi" value="<?= $v('full_name') ?>"></div>
    <div class="field"><label for="email">Email Address <span class="req" aria-hidden="true">*</span></label><input id="email" type="email" name="email" required maxlength="254" autocomplete="email" placeholder="tunde@company.com" value="<?= $v('email') ?>"></div>
    <div class="field"><label for="phone_number">Phone / WhatsApp Number</label><input id="phone_number" type="tel" name="phone_number" maxlength="40" autocomplete="tel" placeholder="+234 800 000 0000" value="<?= $v('phone_number') ?>"></div>
    <div class="field"><label for="service">Primary Service Needed <span class="req" aria-hidden="true">*</span></label>
      <select id="service" name="service" required><option value="">Choose a service</option>
        <?php foreach ($svcOpts as $k => $l): ?><option value="<?= e($k) ?>"<?= $sel === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
      </select></div>
    <div class="field wide"><label for="budget">Estimated Investment Budget</label><select id="budget" name="budget"><option value="">Select your project scope</option><?php foreach (['Starter website / online presence', 'Growing business / integrations', 'Custom platform / larger system', 'Let’s discuss the scope'] as $scope): ?><option value="<?= e($scope) ?>"<?= ($values['budget'] ?? '') === $scope ? ' selected' : '' ?>><?= e($scope) ?></option><?php endforeach; ?></select></div>
    <p class="note wide">At Wales &amp; Webs, pricing is determined by your requirements, scope and desired outcomes. We’ll prepare a tailored proposal after our initial discussion.</p>
    <div class="field wide"><label for="message">Project Details / Message <span class="req" aria-hidden="true">*</span></label><textarea id="message" name="message" rows="6" required minlength="10" maxlength="5000" placeholder="Tell us briefly about what you need, current bottlenecks, or your goals..."><?= $v('message') ?></textarea></div>
    <div class="field wide check"><input id="privacy_accepted" type="checkbox" name="privacy_accepted" value="1" required><label for="privacy_accepted">I agree that Wales &amp; Webs may store my details privately to reply to this enquiry. Replies are written manually; nothing is sent automatically.</label></div>
    <div class="wide"><button class="button" type="submit">Send enquiry <span aria-hidden="true">→</span></button></div>
  </form>
<?php endif; ?>
</section>
</main>
