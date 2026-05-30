<?php
  declare(strict_types = 1);
  require_once(__DIR__ . '/../../utils/session.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../../database/models/ClassCatalog.class.php');
  require_once(__DIR__ . '/../../utils/page_bootstrap.php');

  $session = new Session();
  $db = getDatabaseConnection();
  $featuredClasses  = ClassCatalog::getFeatured($db);
  $featuredTrainers = ClassCatalog::getFeaturedTrainers($db);

  drawHeader(['../style/index.css'], $session);
?>

  <div class="hero" id="hero">
    <section class="hero__content">
      <h1 class="hero__title">FORGE YOUR LIMITS</h1>
      <h2 class="hero__subtitle">
        <span>Every workout. Every session. Every day.</span>
      </h2>
      <a href="#plans"><button class="hero__cta btn-page">EXPLORE PLANS</button></a>
    </section>
  </div>

  <section class="platform-overview" id="overview">
    <h2>WHAT IS THE FORGE</h2>
    <p class="terminal-text" data-text="The Forge is more than a gym. It is a training ground built for those who show up. Discipline is the only membership that matters."></p>
    <div class="about__grid platform-overview__grid">

      <div class="about__block hover-lift">
        <h3 class="about__block-title">FACILITIES</h3>
        <p>Over 2,500m² of training space including free weight zones, machine circuits, functional training areas, and dedicated recovery zones. Every section is optimized for flow and performance.</p>
      </div>

      <div class="about__block hover-lift">
        <h3 class="about__block-title">EQUIPMENT</h3>
        <p>Premium-grade machines from leading brands, Olympic lifting platforms, calibrated plates, and high-end cardio systems. Built to handle both beginners and elite athletes.</p>
      </div>

      <div class="about__block hover-lift">
        <h3 class="about__block-title">FUNCTIONAL TRAINING</h3>
        <p>A complete functional zone with sled tracks, battle ropes, kettlebells, rigs, and open space for mobility and conditioning. Designed for real-world strength and performance.</p>
      </div>

      <div class="about__block hover-lift">
        <h3 class="about__block-title">RECOVERY</h3>
        <p>Recovery is part of the process. We provide stretching areas, foam rolling stations, and guided mobility zones to keep your performance sustainable.</p>
      </div>

      <div class="about__block hover-lift">
        <h3 class="about__block-title">COMMUNITY</h3>
        <p>THE FORGE is built around a focused and driven community. No distractions — just people committed to improving every day.</p>
      </div>

      <div class="about__block hover-lift">
        <h3 class="about__block-title">COACHING</h3>
        <p>Access to experienced trainers, structured programs, and performance tracking tools to help you push past limits and stay consistent.</p>
      </div>

    </div>
  </section>

  <?php if (!empty($featuredClasses) || !empty($featuredTrainers)): ?>
  <section class="featured" id="featured">
    <h2 class="featured__title">FEATURED</h2>

    <?php if (!empty($featuredClasses)): ?>
    <div class="featured__block">
      <h3 class="featured__subtitle">Classes</h3>
      <div class="featured__grid featured__grid--classes">
        <?php foreach ($featuredClasses as $fc):
            $intensity = (int)$fc['intensity'];
        ?>
        <article class="featured-card">
          <div class="featured-card__top">
            <span class="featured-card__type"><?= htmlspecialchars($fc['type_name'] ?? '') ?></span>
            <span class="featured-card__badge">★ Featured</span>
          </div>
          <h4 class="featured-card__name"><?= htmlspecialchars($fc['name']) ?></h4>
          <?php if ($fc['description']): ?>
          <p class="featured-card__desc"><?= htmlspecialchars($fc['description']) ?></p>
          <?php endif; ?>
          <?php if ($fc['next_room']): ?>
          <span class="featured-card__room"><?= htmlspecialchars($fc['next_room']) ?></span>
          <?php endif; ?>
          <div class="featured-card__meta">
            <div class="featured-card__intensity">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="featured-card__dot <?= $i <= $intensity ? 'filled' : '' ?>"></span>
              <?php endfor; ?>
            </div>
            <?php if ($fc['trainer_name']): ?>
            <span class="featured-card__trainer"><?= htmlspecialchars($fc['trainer_name']) ?></span>
            <?php endif; ?>
          </div>
          <a href="/src/pages/classes.php" class="featured-card__cta">View Schedule</a>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($featuredTrainers)): ?>
    <div class="featured__block">
      <h3 class="featured__subtitle">Trainers</h3>
      <div class="featured__grid featured__grid--trainers">
        <?php foreach ($featuredTrainers as $ft):
            $pfpPath = __DIR__ . '/../../database/profile_pictures/' . $ft['user_id'] . '.png';
            $pfpUrl  = file_exists($pfpPath)
                ? '/database/profile_pictures/' . $ft['user_id'] . '.png?v=' . filemtime($pfpPath)
                : null;
            $initials = '';
            foreach (array_slice(array_filter(explode(' ', $ft['name'])), 0, 2) as $w) {
                $initials .= mb_strtoupper(mb_substr($w, 0, 1));
            }
        ?>
        <article class="featured-trainer-card hover-lift">
          <div class="featured-trainer-card__avatar-wrap">
            <?php if ($pfpUrl): ?>
              <img src="<?= htmlspecialchars($pfpUrl) ?>" alt="<?= htmlspecialchars($ft['name']) ?>" class="featured-trainer-card__avatar">
            <?php else: ?>
              <div class="featured-trainer-card__avatar featured-trainer-card__avatar--initials"><?= htmlspecialchars($initials) ?></div>
            <?php endif; ?>
            <span class="featured-trainer-card__badge">★ Featured</span>
          </div>
          <h4 class="featured-trainer-card__name"><?= htmlspecialchars($ft['name']) ?></h4>
          <?php if ($ft['specializations']): ?>
          <p class="featured-trainer-card__spec"><?= htmlspecialchars($ft['specializations']) ?></p>
          <?php endif; ?>
          <a href="<?= $session->isLoggedIn() ? '/src/pages/trainers.php?id=' . (int)$ft['user_id'] : '/src/pages/trainers.php' ?>" class="featured-card__cta">View Profile</a>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <section class="facilities" id="facilities">
    <h2 class="facilities__title">FACILITIES</h2>
    <div class="facilities__image facilities__image--left">
      <img src="../assets/images/main-page/left-image.png" alt="Gym Equipment">
    </div>

    <div class="facilities__splitter">
      <div class="facilities__splitter-border facilities__splitter-border--left"></div>
      <div class="facilities__slant">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
      </div>
      <div class="facilities__splitter-border facilities__splitter-border--right"></div>
    </div>

    <div class="facilities__image facilities__image--right">
      <img src="../assets/images/main-page/right-image.png" alt="People Training">
    </div>
  </section>

  <section class="plans" id="plans">
    <h2 class="plans__title">MEMBERSHIP PLANS</h2>

    <div class="plans__grid">
      <article class="plan-card hover-lift">
        <h3 class="plan-card__name">Basic</h3>
        <p class="plan-card__price">19.99€ / month</p>
        <p class="plan-card__description">Access to the gym floor and cardio equipment.</p>
        <ul class="plan-card__features">
          <li>Gym access</li>
          <li>Cardio machines</li>
          <li>Basic training support</li>
        </ul>
        <a class="plan-card__cta" href="#"><button class="btn-page">Be Basic!</button></a>
      </article>

      <article class="plan-card plan-card--featured hover-lift">
        <h3 class="plan-card__name">Premium</h3>
        <p class="plan-card__price">39.99€ / month</p>
        <p class="plan-card__description">Unlimited classes and full access to all facilities.</p>
        <ul class="plan-card__features">
          <li>All Basic features</li>
          <li>Unlimited classes</li>
          <li>Full facility access</li>
        </ul>
        <a class="plan-card__cta" href="#"><button class="btn-page">Be Premium!</button></a>
      </article>
    </div>
  </section>


<?php drawFooter(); ?>
