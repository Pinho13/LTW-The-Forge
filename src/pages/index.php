<?php
declare(strict_types=1);

$sessionPath = __DIR__ . '/../../utils/session.php';
$templatePath = __DIR__ . '/../templates/common.tpl.php';

require_once($sessionPath);
require_once($templatePath);

$session = new Session();

drawHeader(['../style/index.css'], $session);
?>

<div class="hero-content">
  <section class="hero">
    <h1>FORGE YOUR LIMITS</h1>
    <h2>
      <span>Every workout. Every session. Every day.</span>
    </h2>

    <a class="primary-button" href="#plans">EXPLORE PLANS</a>
  </section>
</div>

<section class="platform-overview">
  <h2>&gt; system_overview</h2>

  <p class="terminal-text" data-text="A complete gym management platform where members can explore classes, follow their progress, and manage their account, while trainers and administrators handle scheduling and operations."></p>
</section>

<section class="photos-container">
  <div class="image left">
    <img src="../assets/images/main-page/left-image.png" alt="Image of the interior of The Forge gym with heavy machinery">
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
    <img src="../assets/images/main-page/right-image.webp" alt="Image of the interior of The Forge gym with many people">
  </div>
</section>

<section id="plans" class="plans-section">
  <h2>&gt; membership_plans</h2>

  <div class="plans-container">

    <article class="plan-card">
      <h3>Basic</h3>
      <p class="price">19.99€ / month</p>
      <p class="description">
        Access to gym floor and cardio equipment.
      </p>
      <ul>
        <li>Gym access</li>
        <li>Cardio machines</li>
      </ul>
      <a class="plan-button" href="#">Select Plan</a>
    </article>

    <article class="plan-card featured">
      <h3>Premium</h3>
      <p class="price">39.99€ / month</p>
      <p class="description">
        Unlimited classes and full access to facilities.
      </p>
      <ul>
        <li>All Basic features</li>
        <li>Unlimited classes</li>
        <li>Facility access</li>
      </ul>
      <a class="plan-button" href="#">Select Plan</a>
    </article>

  </div>
</section>

<?php drawFooter(); ?>
