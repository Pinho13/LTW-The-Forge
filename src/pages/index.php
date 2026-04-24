<?php
  declare(strict_types = 1);
  require_once(__DIR__ . '/../../utils/session.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');

  $session = new Session();
  drawHeader(['../style/index.css'], $session);
?>

  <div id="hero-content">
    <section class="hero">
      <h1>FORGE YOUR LIMITS</h1>
      <h2>
        <span>Every workout. Every session. Every day.</span>
      </h2>
      <a href="#plans"><button class="btn-page">EXPLORE PLANS</button></a>
    </section>
  </div>

  <section id="facilities" class="photos-container">
    <div class="image left">
      <img src="../assets/images/main-page/left-image.png" alt="Gym Equipment">
    </div>

    <div class="splitter">
      <div class="border-left"></div>
      <div class="slant">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
      </div>
      <div class="border-right"></div>
    </div>

    <div class="image right">
      <img src="../assets/images/main-page/right-image.png" alt="People Training">
    </div>
  </section>

  <section id="plans" class="plans-section">
    <h2>MEMBERSHIP PLANS</h2>

    <div class="plans-container">
      <article class="plan-card">
        <h3>Basic</h3>
        <p class="price">19.99€ / month</p>
        <p class="description">Access to the gym floor and cardio equipment.</p>
        <ul>
          <li>Gym access</li>
          <li>Cardio machines</li>
          <li>Basic training support</li>
        </ul>
        <a class="plan-button" href="#"><button class="btn-page">Be Basic!</button></a>
      </article>

      <article class="plan-card">
        <h3>Premium</h3>
        <p class="price">39.99€ / month</p>
        <p class="description">Unlimited classes and full access to all facilities.</p>
        <ul>
          <li>All Basic features</li>
          <li>Unlimited classes</li>
          <li>Full facility access</li>
        </ul>
        <a class="plan-button" href="#"><button class="btn-page">Be Premium!</button></a>
      </article>
    </div>
  </section>

  <section id="about" class="about-section">
  <h2>ABOUT US</h2>

  <div class="about-content">

    <p class="about-intro">
      At <strong>THE FORGE</strong>, we don’t just train bodies — we build discipline,
      resilience, and consistency. Our space is designed for those who take
      performance seriously.
    </p>

    <div class="about-grid">

      <div class="about-block">
        <h3>FACILITIES</h3>
        <p>
          Over 2,500m² of training space including free weight zones,
          machine circuits, functional training areas, and dedicated
          recovery zones. Every section is optimized for flow and performance.
        </p>
      </div>

      <div class="about-block">
        <h3>EQUIPMENT</h3>
        <p>
          Premium-grade machines from leading brands, Olympic lifting platforms,
          calibrated plates, and high-end cardio systems. Built to handle
          both beginners and elite athletes.
        </p>
      </div>

      <div class="about-block">
        <h3>FUNCTIONAL TRAINING</h3>
        <p>
          A complete functional zone with sled tracks, battle ropes,
          kettlebells, rigs, and open space for mobility and conditioning.
          Designed for real-world strength and performance.
        </p>
      </div>

      <div class="about-block">
        <h3>RECOVERY</h3>
        <p>
          Recovery is part of the process. We provide stretching areas,
          foam rolling stations, and guided mobility zones to keep
          your performance sustainable.
        </p>
      </div>

      <div class="about-block">
        <h3>COMMUNITY</h3>
        <p>
          THE FORGE is built around a focused and driven community.
          No distractions — just people committed to improving every day.
        </p>
      </div>

      <div class="about-block">
        <h3>COACHING</h3>
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
