<?php
declare(strict_types=1);

$sessionPath = __DIR__ . '/../../utils/session.php';

require_once($sessionPath);

function drawFlashMessages(?Session $session): void
{
    if ($session === null) {
        return;
    }

    $messages = $session->getMessages();
    if ($messages === []) {
        return;
    }
    ?>
    <div class="flash-messages">
        <?php foreach ($messages as $message) { ?>
            <div class="flash-message flash-<?= htmlspecialchars($message['type']) ?>">
                <?= htmlspecialchars($message['text']) ?>
            </div>
        <?php } ?>
    </div>
    <?php
}

function drawHeader(array $extraCss = [], ?Session $session = null): void
{
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
      <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
    <?php } ?>

    <script src="../scripts/index.js" defer></script>
  </head>
  <body>
    <input type="checkbox" id="mobile-menu-toggle">

    <nav id="mobile-menu">
      <label for="mobile-menu-toggle" class="mobile-menu-backdrop"></label>

      <div class="mobile-menu-panel">
        <a href="index.php#about">ABOUT US</a>
        <a href="index.php#facilities">FACILITIES</a>
        <a href="index.php#plans">PLANS</a>

        <?php if ($session !== null && $session->isLoggedIn()) { ?>
          <a href="my-account.php">MY ACCOUNT</a>
        <?php } ?>
      </div>
    </nav>

    <header>
      <nav id="top-nav-bar">
        <label for="mobile-menu-toggle" class="menu-toggle" aria-label="Open navigation menu">☰</label>

        <a href="index.php#about">ABOUT US</a>
        <a href="index.php#facilities">FACILITIES</a>

        <?php include __DIR__ . '/../components/logo.php'; ?>

        <a href="index.php#plans">PLANS</a>

        <div class="login-wrapper">
          <?php if ($session !== null && $session->isLoggedIn()) { ?>
            <a id="account-btn" class="btn-primary" href="my-account.php">MY ACCOUNT</a>
          <?php } else { ?>
            <button id="login-btn" type="button">LOG IN</button>
          <?php } ?>
        </div>
      </nav>
    </header>

    <main>
      <?php drawFlashMessages($session); ?>
<?php
}

function drawPasswordField(string $id, string $name): void
{
?>
  <div class="password-wrapper">
    <input type="password" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>">
    <button type="button" class="toggle-password" aria-label="Toggle password visibility">&#128065;</button>
  </div>
<?php
}

function drawAuthModal(bool $isLogin, ?Session $session = null): void
{
    $form = $isLogin ? 'login' : 'register';
    $error = $session?->popFormError($form);
    $formData = $session?->popFormData($form) ?? [];
    $id = $isLogin ? 'login-modal' : 'register-modal';
    $title = $isLogin ? 'WELCOME BACK' : 'WELCOME';
    $subtitle = $isLogin ? 'Sign in to your account' : 'Register your account';
    $submit = $isLogin ? 'SIGN IN' : 'REGISTER';
?>
  <dialog id="<?= $id ?>" class="auth-modal">
    <button class="btn-ghost modal-close-btn" type="button">&times;</button>
    <h1><?= $title ?></h1>
    <h2><?= $subtitle ?></h2>

    <form method="post" action="<?= $isLogin ? '../actions/action_login.php' : '../actions/action_register.php' ?>">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session?->getCsrfToken() ?? '') ?>">

      <?php if (!$isLogin) { ?>
        <label for="register-name">NAME</label>
        <input
          type="text"
          id="register-name"
          name="name"
          placeholder="Full Name"
          value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
        >
      <?php } ?>

      <label for="<?= $isLogin ? 'login-email' : 'register-email' ?>">EMAIL ADDRESS</label>
      <input
        type="email"
        id="<?= $isLogin ? 'login-email' : 'register-email' ?>"
        name="email"
        placeholder="example@gmail.com"
        value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
      >

      <label for="<?= $isLogin ? 'password' : 'register-password' ?>">PASSWORD</label>
      <?php drawPasswordField($isLogin ? 'password' : 'register-password', 'password'); ?>

      <?php if ($isLogin) { ?>
        <a href="index.php" class="forgot-password">Forgot your password?</a>
      <?php } else { ?>
        <label for="register-confirm-password">CONFIRM PASSWORD</label>
        <?php drawPasswordField('register-confirm-password', 'confirm-password'); ?>
      <?php } ?>

      <button type="submit" class="btn-primary"><?= $submit ?></button>

      <?php if ($error !== null) { ?>
        <p class="form-error"><?= htmlspecialchars($error) ?></p>
      <?php } ?>
    </form>

    <?php if ($isLogin) { ?>
      <p>New member? <a href="#" id="open-register-btn">Register for free</a></p>
    <?php } else { ?>
      <p>Already have an account? <a href="#" id="open-login-btn">Sign In</a></p>
    <?php } ?>
  </dialog>
<?php
}

function drawFooter(?Session $session = null): void
{
?>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <?php drawAuthModal(true, $session); ?>
    <?php drawAuthModal(false, $session); ?>
  </body>
</html>
<?php
}