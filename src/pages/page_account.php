<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/session.php');

$session = new Session();
$session->requireLogin('/src/pages/index.php?open=login');
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
</head>

<body>
    <input type="checkbox" id="side-menu-toggle">

    <header class="mobile-top-bar">
        <label for="side-menu-toggle" class="logo">
            <img src="../assets/images/logo-no-bg.png" alt="The Forge Logo" class="logo__img">
            <span class="logo__text">THE FORGE</span>
        </label>
    </header>

    <label for="side-menu-toggle" class="side-menu-backdrop"></label>

    <?php $activePage = 'profile'; include '../components/side-menu.php'; ?>

    <main>
        <header>
            <h1>Account</h1>
        </header>
    </main>

    <?php include '../components/footer.php'; ?>

</body>
</html>
