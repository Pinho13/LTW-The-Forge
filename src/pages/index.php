<?php
  declare(strict_types = 1);
  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');

  $session = new Session();
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
    <div class="platform-overview__body">
      <p>We built The Forge around one idea: serious training deserves serious tools. From the moment you walk in, everything is designed to get out of your way and let you focus on the work.</p>
      <p>Reserve a machine before you arrive. Book a class with one of our expert trainers. Track your streak, monitor your progress, and manage your membership from anywhere. No queues. No guesswork.</p>
      <p>Whether you are here to lift heavy, move better, or simply show up consistently, The Forge gives you the structure to do it on your terms.</p>
    </div>
  </section>

  <section class="facilities" id="facilities">
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
      <article class="plan-card">
        <h3 class="plan-card__name">Basic</h3>
        <p class="plan-card__price">19.99€ / month</p>
        <p class="plan-card__description">Access to the gym floor and cardio equipment.</p>
        <ul class="plan-card__features">
          <li>Gym access</li>
          <li>Cardio machines</li>
          <li>Basic training support</li>
        </ul>
        <button class="btn-page" id="basic-membership">Be Basic!</button>
      </article>

      <article class="plan-card plan-card--featured">
        <h3 class="plan-card__name">Premium</h3>
        <p class="plan-card__price">39.99€ / month</p>
        <p class="plan-card__description">Unlimited classes and full access to all facilities.</p>
        <ul class="plan-card__features">
          <li>All Basic features</li>
          <li>Unlimited classes</li>
          <li>Full facility access</li>
        </ul>
        <button class="btn-page" id="premium-membership">Be Premium!</button>
      </article>
    </div>
  </section>

  <section class="about" id="about">
    <h2 class="about__title">ABOUT US</h2>

    <div class="about__content">

      <p class="about__intro">
        At <strong>THE FORGE</strong>, we don't just train bodies — we build discipline,
        resilience, and consistency. Our space is designed for those who take
        performance seriously.
      </p>

      <div class="about__grid">

        <div class="about__block">
          <h3 class="about__block-title">FACILITIES</h3>
          <p>
            Over 2,500m² of training space including free weight zones,
            machine circuits, functional training areas, and dedicated
            recovery zones. Every section is optimized for flow and performance.
          </p>
        </div>

        <div class="about__block">
          <h3 class="about__block-title">EQUIPMENT</h3>
          <p>
            Premium-grade machines from leading brands, Olympic lifting platforms,
            calibrated plates, and high-end cardio systems. Built to handle
            both beginners and elite athletes.
          </p>
        </div>

        <div class="about__block">
          <h3 class="about__block-title">FUNCTIONAL TRAINING</h3>
          <p>
            A complete functional zone with sled tracks, battle ropes,
            kettlebells, rigs, and open space for mobility and conditioning.
            Designed for real-world strength and performance.
          </p>
        </div>

        <div class="about__block">
          <h3 class="about__block-title">RECOVERY</h3>
          <p>
            Recovery is part of the process. We provide stretching areas,
            foam rolling stations, and guided mobility zones to keep
            your performance sustainable.
          </p>
        </div>

        <div class="about__block">
          <h3 class="about__block-title">COMMUNITY</h3>
          <p>
            THE FORGE is built around a focused and driven community.
            No distractions — just people committed to improving every day.
          </p>
        </div>

        <div class="about__block">
          <h3 class="about__block-title">COACHING</h3>
          <p>
            Access to experienced trainers, structured programs,
            and performance tracking tools to help you push past limits
            and stay consistent.
          </p>
        </div>

      </div>

    </div>
  </section>

<?php drawFooter(); ?>
