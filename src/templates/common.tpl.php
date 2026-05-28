<?php
declare(strict_types = 1);
require_once(__DIR__ . '/../utils/session.php');
?>

<?php function drawHeader(array $extraCss = [], ?Session $session = null) {
  $GLOBALS['_tpl_session'] = $session;
?>
<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <?php foreach ($extraCss as $css) { ?>
    <link rel="stylesheet" href="<?=$css?>">
    <?php } ?>
    <script type="module" src="../scripts/index.js"></script>
  </head>
  <body>
    <input type="checkbox" id="mobile-menu-toggle">
    <nav id="mobile-menu">
      <label for="mobile-menu-toggle" class="mobile-menu__backdrop"></label>
      <div class="mobile-menu__panel">
        <a href="#about">ABOUT US</a>
        <a href="#facilities">FACILITIES</a>
        <a href="#plans">PLANS</a>
      </div>
    </nav>
    <header class="site-header">
      <nav class="top-nav" id="top-nav-bar">
        <a class="top-nav__link" href="#overview">ABOUT US</a>
        <a class="top-nav__link" href="#facilities">FACILITIES</a>

        <?php include __DIR__ . '/../components/logo.php'; ?>

        <a class="top-nav__link" href="#plans">PLANS</a>
        <div class="top-nav__actions">
          <?php if ($session?->isLoggedIn()): ?>
            <a href="/src/pages/my-account.php"><button class="top-nav__btn top-nav__btn--account">MY ACCOUNT</button></a>
          <?php else: ?>
            <button class="top-nav__btn" id="login-btn">LOG IN</button>
          <?php endif; ?>
        </div>
      </nav>
    </header>

    <main>
    <?php
    $__flash = $session?->getMessages() ?? [];
    if (!empty($__flash)): ?>
    <div class="flash-messages" role="alert" aria-live="polite">
        <?php foreach ($__flash as $__msg): ?>
            <div class="flash-message flash-<?= htmlspecialchars($__msg['type']) ?>">
                <?= htmlspecialchars($__msg['text']) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
<?php } ?>

<?php function drawPasswordField(string $id, string $name, string $label, string $autocomplete = 'current-password') { ?>
  <div class="form__password-wrapper">
    <label for="<?=$id?>"><?=$label?></label>
    <div class="form__input-row">
      <input type="password" id="<?=$id?>" name="<?=$name?>" autocomplete="<?=$autocomplete?>">
      <button type="button" class="form__toggle-password">&#128065;</button>
    </div>
  </div>
<?php } ?>

<?php function drawAuthModal(bool $isLogin) {
  $session  = $GLOBALS['_tpl_session'] ?? null;
  $form     = $isLogin ? 'login' : 'register';
  $error    = $session?->popFormError($form);
  $formData = $session?->popFormData($form) ?? [];
  $id       = $isLogin ? 'login-modal'            : 'register-modal';
  $title    = $isLogin ? 'WELCOME BACK'            : 'WELCOME';
  $subtitle = $isLogin ? 'Sign in to your account' : 'Register your account';
  $submit   = $isLogin ? 'SIGN IN'                 : 'REGISTER';
?>
  <dialog id="<?=$id?>" class="auth-modal">
    <button class="btn-ghost auth-modal__close">&times;</button>
    <h1 class="auth-modal__title"><?=$title?></h1>
    <h2 class="auth-modal__subtitle"><?=$subtitle?></h2>
    <form class="auth-modal__form" method="post" action="<?= $isLogin ? '../actions/action_login.php' : '../actions/action_register.php' ?>">

      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session?->generateCsrfToken() ?? '') ?>">

      <?php if (!$isLogin) { ?>
        <label for="register-name">NAME</label>
        <input type="text" id="register-name" name="name" placeholder="Full Name"
               value="<?= htmlspecialchars($formData['name'] ?? '') ?>">
      <?php } ?>

      <label for="<?=$isLogin ? 'login-email' : 'register-email'?>">EMAIL ADDRESS</label>
      <input type="email" id="<?=$isLogin ? 'login-email' : 'register-email'?>" name="email" placeholder="example@gmail.com"
             autocomplete="email"
             value="<?= htmlspecialchars($formData['email'] ?? '') ?>">

      <?php drawPasswordField($isLogin ? 'password' : 'register-password', 'password', 'PASSWORD', $isLogin ? 'current-password' : 'new-password'); ?>

      <?php if (!$isLogin): ?>
      <p class="pw-hint" id="pw-hint"></p>
      <?php endif; ?>

      <?php if ($isLogin) { ?>
        <a href="index.php" class="form__forgot">Forgot your password?</a>
      <?php } else { ?>
        <?php drawPasswordField('register-confirm-password', 'confirm-password', 'CONFIRM PASSWORD', 'new-password'); ?>
        <fieldset class="auth-modal__membership">
          <legend class="auth-modal__membership-legend">Choose a membership tier</legend>

          <input type="hidden" name="membership" id="membership-input" value="basic">

          <button type="button" class="auth-modal__membership-option" data-value="basic">Basic</button>
          <button type="button" class="auth-modal__membership-option btn-outline" data-value="premium">Premium</button>
        </fieldset>
      <?php } ?>

      <button type="submit" class="btn-primary"><?=$submit?></button>
      <?php if ($error): ?>
        <p class="auth-modal__error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
    </form>
    <?php if ($isLogin) { ?>
      <p class="auth-modal__switch">New member? <a href="#" id="open-register-btn">Register for free</a></p>
    <?php } else { ?>
      <p class="auth-modal__switch">Already have an account? <a href="#" id="open-login-btn">Sign In</a></p>
    <?php } ?>
  </dialog>
<?php } ?>

<?php function drawFooter() { ?>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <div class="modal-backdrop" id="modal-backdrop"></div>
    <?php drawAuthModal(true); ?>
    <?php drawAuthModal(false); ?>

  </body>
</html>
<?php } ?>
