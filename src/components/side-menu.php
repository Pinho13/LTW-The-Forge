<input type="checkbox" id="side-menu-toggle">

<header class="mobile-top-bar">
    <label for="side-menu-toggle" class="logo">
        <img src="/src/assets/images/logo-no-bg.png" alt="The Forge Logo" class="logo__img">
        <span class="logo__text">THE FORGE</span>
    </label>
</header>

<label for="side-menu-toggle" class="side-menu-backdrop"></label>

<nav class="side-menu">
    <?php include __DIR__ . '/logo.php'; ?>

    <div class="side-nav">
        <a href="/src/pages/my-account.php"      <?= $activePage === 'account'      ? 'class="active"' : '' ?>>MY ACCOUNT</a>
        <a href="/src/pages/classes.php"         <?= $activePage === 'classes'      ? 'class="active"' : '' ?>>CLASSES</a>
        <a href="/src/pages/trainers.php"        <?= $activePage === 'trainers'     ? 'class="active"' : '' ?>>TRAINERS</a>
        <a href="/src/pages/my-classes.php"      <?= $activePage === 'my-classes'   ? 'class="active"' : '' ?>>MY CLASSES</a>
        <a href="/src/pages/reservations.php"    <?= $activePage === 'reservations' ? 'class="active"' : '' ?>>RESERVATIONS</a>
        <a href="/src/pages/news.php"            <?= $activePage === 'news'         ? 'class="active"' : '' ?>>NEWS</a>
    </div>

    <?php include __DIR__ . '/sidebar-user-block.php'; ?>
</nav>