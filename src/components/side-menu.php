<?php
$_menu_role = $session->getRole() ?? 'member';
?>
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
        <?php if ($_menu_role === 'member'): ?>
            <a href="/src/pages/my-account.php" <?= $activePage === 'account' ? 'class="active"' : '' ?>>MY ACCOUNT</a>
            <?php if ($session->isFrozen()): ?>
            <a href="/src/pages/frozen.php?page=classes"       class="nav-locked">CLASSES</a>
            <a href="/src/pages/frozen.php?page=my-classes"    class="nav-locked">MY CLASSES</a>
            <a href="/src/pages/frozen.php?page=equipment"     class="nav-locked">EQUIPMENT</a>
            <a href="/src/pages/frozen.php?page=reservations"  class="nav-locked">RESERVATIONS</a>
            <a href="/src/pages/frozen.php?page=trainers"      class="nav-locked">TRAINERS</a>
            <a href="/src/pages/frozen.php?page=news"          class="nav-locked">NEWS</a>
            <?php else: ?>
            <?php if ($session->isPremium()): ?>
            <a href="/src/pages/classes.php"       <?= $activePage === 'classes'      ? 'class="active"' : '' ?>>CLASSES</a>
            <a href="/src/pages/my-classes.php"    <?= $activePage === 'my-classes'   ? 'class="active"' : '' ?>>MY CLASSES</a>
            <?php else: ?>
            <a href="/src/pages/premium-only.php?page=classes"    class="nav-locked">CLASSES</a>
            <a href="/src/pages/premium-only.php?page=my-classes" class="nav-locked">MY CLASSES</a>
            <?php endif; ?>
            <a href="/src/pages/equipment-map.php" <?= $activePage === 'equipment'    ? 'class="active"' : '' ?>>EQUIPMENT</a>
            <a href="/src/pages/reservations.php"  <?= $activePage === 'reservations' ? 'class="active"' : '' ?>>RESERVATIONS</a>
            <a href="/src/pages/trainers.php"      <?= $activePage === 'trainers'     ? 'class="active"' : '' ?>>TRAINERS</a>
            <a href="/src/pages/news.php"          <?= $activePage === 'news'         ? 'class="active"' : '' ?>>NEWS</a>
            <?php endif; ?>

        <?php elseif ($_menu_role === 'trainer'): ?>
            <a href="/src/pages/trainer-dashboard.php" <?= $activePage === 'trainer-dashboard' ? 'class="active"' : '' ?>>DASHBOARD</a>
            <a href="/src/pages/trainer-schedule.php"  <?= $activePage === 'trainer-schedule'  ? 'class="active"' : '' ?>>MY SCHEDULE</a>
            <a href="/src/pages/trainer-profile.php"   <?= $activePage === 'trainer-profile'   ? 'class="active"' : '' ?>>MY PROFILE</a>
            <a href="/src/pages/news.php"              <?= $activePage === 'news'               ? 'class="active"' : '' ?>>NEWS</a>

        <?php elseif ($_menu_role === 'admin'): ?>
            <a href="/src/pages/admin-dashboard.php"  <?= $activePage === 'account'          ? 'class="active"' : '' ?>>DASHBOARD</a>
            <a href="/src/pages/admin-users.php"      <?= $activePage === 'admin-users'      ? 'class="active"' : '' ?>>USERS</a>
            <a href="/src/pages/trainers.php"         <?= $activePage === 'trainers'         ? 'class="active"' : '' ?>>TRAINERS</a>
            <a href="/src/pages/admin-classes.php"    <?= $activePage === 'admin-classes'    ? 'class="active"' : '' ?>>CLASSES</a>
            <a href="/src/pages/admin-analytics.php"  <?= $activePage === 'admin-analytics'  ? 'class="active"' : '' ?>>ANALYTICS</a>
            <a href="/src/pages/equipment-map.php"    <?= $activePage === 'equipment'        ? 'class="active"' : '' ?>>EQUIPMENT</a>
            <a href="/src/pages/news.php"             <?= $activePage === 'news'             ? 'class="active"' : '' ?>>NEWS</a>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/sidebar-user-block.php'; ?>
</nav>
