<?php
$posts = $posts ?? [];
$slides = [
 ['For Nigerian founders','Turn your idea into a business people can trust.','Launch a sharp digital presence, tell your story clearly and start taking your business seriously online.','walesnweb_1789827187005.jpg','Lagos · Build in public','01 / Presence'],
 ['For growing teams','Let your systems do more of the heavy lifting.','We connect the tools behind your business so leads, content, payments and customer support move with less stress.','wales_anime_systems.png','Abuja · Work smarter','02 / Systems'],
 ['For ambitious brands','Show up consistently. Grow beyond referrals.','From social media content to campaigns and conversion-ready pages, we help your brand stay visible and memorable.','wales_anime_social.png','Nigeria · Scale online','03 / Growth'],
];
$services = [
 ['digital-presence','01','Digital Presence','Modern, fast and responsive websites, e-commerce stores and landing pages that convert.','violet'],
 ['business-systems','02','Business Systems','Connect your tools, manage your data and simplify your workflows with custom automation and integrations.','green'],
 ['automation-ai','03','Automation & AI','Remove repetitive work, save time and let smart systems handle the busy work.','violet'],
 ['digital-growth','04','Digital Growth','Social media management, SEO, content strategies that bring real customers to your business.','green'],
];
$steps = [['01','Understand','We learn about your business, goals and challenges.'],['02','Plan','We define the right solution for your needs.'],['03','Build','We design, develop and test with a focus on quality.'],['04','Grow','We support, improve and help you get more value.']];
$quotes = [
 ['Wales & Webs gave us a modern website and booking system that is easy to navigate and our customers absolutely love.','Caroline M.','CEO, Caroline’s Place'],
 ['Their team is professional, responsive and always delivers. Our client portal has made our operations so much easier.','Tunde A.','MD, Prodigy Group'],
 ['From design to support, Wales & Webs understood our vision and brought it to life. They are very happy to have.','Edima T.','Founder, Taste by Edima'],
];
function initials_of($n){ $o=''; foreach (preg_split('/\s+/', trim($n)) as $p) { $o .= mb_substr($p,0,1); } return strtoupper($o); }
?>
<main id="main">
<section class="hero" id="home">
  <div class="hero-grid" aria-hidden="true"></div>
  <div class="container hero-inner">
    <div class="hero-copy">
      <p class="eyebrow reveal">Your digital partner</p>
      <h1 class="reveal">Build your business <span class="gradient-word">for the digital world.</span></h1>
      <p class="lead reveal">We build websites, automate your workflows, manage your social media and help you grow — with smart digital solutions built around how your business actually works.</p>
      <div class="actions reveal">
        <a class="button" href="<?= e(url('/contact')) ?>">Start Your Digital Journey <span aria-hidden="true">→</span></a>
        <a class="button-secondary" href="#services">Explore Our Services</a>
      </div>
      <ul class="chips reveal"><li>Websites</li><li>Automation</li><li>Social Media</li><li>Client Support</li></ul>
    </div>
    <div class="carousel" data-carousel role="region" aria-roledescription="carousel" aria-label="Hero stories">
      <div class="slides">
        <?php foreach ($slides as $i => $s): ?>
        <article class="slide<?= $i === 0 ? ' is-active' : '' ?>" role="group" aria-roledescription="slide" aria-label="<?= $i+1 ?> of 3"<?= $i ? ' aria-hidden="true"' : '' ?>>
          <div class="slide-img" style="background-image:linear-gradient(110deg,rgba(6,10,9,.96) 8%,rgba(6,10,9,.4) 55%,rgba(44,255,143,.16)),url('<?= e(url('/assets/' . $s[3])) ?>')"></div>
          <div class="slide-body">
            <div class="slide-top"><span class="tag"><?= e($s[4]) ?></span><span class="mono-badge"><?= e($s[5]) ?></span></div>
            <div><p class="eyebrow"><?= e($s[0]) ?></p><h2><?= e($s[1]) ?></h2><p><?= e($s[2]) ?></p></div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <div class="carousel-controls">
        <?php foreach ($slides as $i => $s): ?><button type="button" class="dot<?= $i === 0 ? ' is-active' : '' ?>" aria-label="Show <?= e($s[0]) ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>"><span><?= e($s[5]) ?></span></button><?php endforeach; ?>
        <button type="button" class="dot-pause" data-pause aria-pressed="false">Pause</button>
      </div>
    </div>
  </div>
</section>

<section id="services" class="section">
  <div class="container">
    <div class="split-head">
      <div><p class="eyebrow">What we help you build</p><h2 class="page-heading">Complete digital solutions<br>for modern businesses.</h2></div>
      <p class="muted narrow">From a simple website to a full digital ecosystem, we give you the tools, systems and support to work smarter, reach more customers and grow faster.</p>
    </div>
    <div class="service-grid">
      <?php foreach ($services as $s): ?>
      <article class="panel service tone-<?= e($s[4]) ?>" id="service-<?= e($s[0]) ?>" tabindex="-1">
        <span class="num"><?= e($s[1]) ?></span>
        <h3><?= e($s[2]) ?></h3>
        <p class="muted"><?= e($s[3]) ?></p>
        <a class="text-link" href="<?= e(url('/contact')) ?>">Learn more <span aria-hidden="true">→</span></a>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="story" class="section approach">
  <div class="container">
    <p class="eyebrow">Our approach</p>
    <h2 class="page-heading">Simple steps.<br><span class="accent">Real results.</span></h2>
    <ol class="steps">
      <?php foreach ($steps as $s): ?><li><span class="step-n"><?= e($s[0]) ?></span><h3><?= e($s[1]) ?></h3><p class="muted"><?= e($s[2]) ?></p></li><?php endforeach; ?>
    </ol>
  </div>
</section>

<section id="usabime" class="section">
  <div class="container">
    <div class="panel platform">
      <div class="platform-grid">
        <div>
          <p class="eyebrow">Meet Usabime</p>
          <h2 class="page-heading">Start small.<br><span class="accent">Show up. Scale up.</span></h2>
          <p class="muted">Usabime helps new business owners get online before they are ready for the big website. Create a simple portfolio, organise your social media and learn what your business needs next.</p>
          <div class="actions"><a class="button" href="<?= e(url('/usabime/request')) ?>">Get onboarded <span aria-hidden="true">→</span></a><a class="button-secondary" href="<?= e(url('/usabime/request')) ?>">See how it works</a></div>
        </div>
        <ul class="checklist">
          <?php foreach (['Tell us what you sell','Build your first portfolio','Plan your social content','Get guided support','Move into your full website'] as $i => $t): ?><li><span>0<?= $i+1 ?></span><?= e($t) ?></li><?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<section id="work" class="section">
  <div class="container">
    <div class="split-head">
      <div><p class="eyebrow">Our work / field notes</p><h2 class="page-heading">The thinking behind<br><span class="accent">the builds.</span></h2></div>
      <a class="text-link" href="<?= e(url('/journal')) ?>">Read the journal <span aria-hidden="true">→</span></a>
    </div>
    <p class="muted narrow">Real notes from real client work: the decisions, systems and small details that turn a digital presence into a useful business asset.</p>
    <?php $latest = array_slice($posts, 0, 4); if (!$latest): ?>
      <div class="panel empty">New field notes will appear here soon.</div>
    <?php else: ?>
      <div class="journal-grid four"><?php foreach ($latest as $post) { include __DIR__ . '/_card.php'; } ?></div>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <p class="eyebrow">What our clients say</p>
    <h2 class="page-heading">Trusted by businesses<br>across different industries.</h2>
    <div class="quote-grid">
      <?php foreach ($quotes as $q): ?>
      <figure class="panel quote"><blockquote>“<?= e($q[0]) ?>”</blockquote><figcaption><span class="avatar" aria-hidden="true"><?= e(initials_of($q[1])) ?></span><span><strong><?= e($q[1]) ?></strong><small><?= e($q[2]) ?></small></span></figcaption></figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="cta-band">
  <div class="container cta-inner">
    <div><p class="eyebrow">Let’s build together</p><h2 class="page-heading">Ready to take your business<br><span class="accent">to the next level?</span></h2></div>
    <a class="button" href="<?= e(url('/contact')) ?>">Start your journey <span aria-hidden="true">→</span></a>
  </div>
</section>
</main>
