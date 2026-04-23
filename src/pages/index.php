<?php
  declare(strict_types = 1);
  require_once(__DIR__ . '/../../utils/session.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');

  $session = new Session();
  drawHeader(['../style/index.css'], $session);
?>

  <div class="hero-content">
    <section class="hero">
      <h1>FORGE YOUR LIMITS</h1>
      <h2>
        <span>Every workout. Every session. Every day.</span>
      </h2>
      <button>EXPLORE PLANS</button>
    </section>
  </div>

  <section class="photos-container">
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

<?php drawFooter(); ?>
