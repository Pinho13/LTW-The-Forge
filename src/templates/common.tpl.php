<?php
declare(strict_types = 1);
require_once(__DIR__ . '/../../utils/session.php');
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
    <link rel="stylesheet" href="../style/base.css">
    <link rel="stylesheet" href="../style/components/buttons.css">
    <link rel="stylesheet" href="../style/components/top-nav-bar.css">
    <link rel="stylesheet" href="../style/components/forms.css">
    <link rel="stylesheet" href="../style/components/modals.css">
    <link rel="stylesheet" href="../style/components/footer.css">
    <link rel="stylesheet" href="../style/components/logo.css">
    <?php foreach ($extraCss as $css) { ?>
    <link rel="stylesheet" href="<?=$css?>">
    <?php } ?>
    <script src="../scripts/index.js" defer></script>
  </head>
  <body>
    <header>
      <nav id="top-nav-bar">
        <a href="index.php">ABOUT US</a>
        <a href="index.php">FACILITIES</a>

        <?php include __DIR__ . '/../components/logo.php'; ?>

        <a href="index.php">PLANS</a>
        <div class="login-wrapper">
          <button id="login-btn">LOG IN</button>
        </div>
      </nav>
    </header>

    <main>
<?php } ?>

<?php function drawPasswordField(string $id, string $name) { ?>
  <div class="password-wrapper">
    <input type="password" id="<?=$id?>" name="<?=$name?>">
    <button type="button" class="toggle-password">&#128065;</button>
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
    <button class="btn-ghost modal-close-btn">&times;</button>
    <h1><?=$title?></h1>
    <h2><?=$subtitle?></h2>
    <form method="post" action="<?= $isLogin ? '../actions/action_login.php' : '../actions/action_register.php' ?>">

      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session?->generateCsrfToken() ?? '') ?>">

      <?php if (!$isLogin) { ?>
        <label for="register-name">NAME</label>
        <input type="text" id="register-name" name="name" placeholder="Full Name"
               value="<?= htmlspecialchars($formData['name'] ?? '') ?>">
      <?php } ?>

      <label for="<?=$isLogin ? 'login-email' : 'register-email'?>">EMAIL ADDRESS</label>
      <input type="email" id="<?=$isLogin ? 'login-email' : 'register-email'?>" name="email" placeholder="example@gmail.com"
             value="<?= htmlspecialchars($formData['email'] ?? '') ?>">

      <label for="<?=$isLogin ? 'password' : 'register-password'?>">PASSWORD</label>
      <?php drawPasswordField($isLogin ? 'password' : 'register-password', 'password'); ?>

      <?php if ($isLogin) { ?>
        <a href="index.php" class="forgot-password">Forgot your password?</a>
      <?php } else { ?>
        <label for="register-confirm-password">CONFIRM PASSWORD</label>
        <?php drawPasswordField('register-confirm-password', 'confirm-password'); ?>
      <?php } ?>

      <button type="submit" class="btn-primary"><?=$submit?></button>
      <?php if ($error): ?>
        <p class="form-error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
    </form>
    <?php if ($isLogin) { ?>
      <p>New member? <a href="#" id="open-register-btn">Register for free</a></p>
    <?php } else { ?>
      <p>Already have an account? <a href="#" id="open-login-btn">Sign In</a></p>
    <?php } ?>
  </dialog>
<?php } ?>

<?php function drawFooter() { ?>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <?php drawAuthModal(true); ?>
    <?php drawAuthModal(false); ?>

  </body>
</html>
<?php } ?>
