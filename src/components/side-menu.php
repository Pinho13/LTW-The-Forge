<aside class="side-menu">
    <?php include __DIR__ . '/logo.php'; ?>
    <nav class="side-nav">
        <a href="account.php"      <?= $activePage === 'account'      ? 'class="active"' : '' ?>>MY ACCOUNT</a>
        <a href="classes.php"      <?= $activePage === 'classes'      ? 'class="active"' : '' ?>>CLASSES</a>
        <a href="trainers.php"     <?= $activePage === 'trainers'     ? 'class="active"' : '' ?>>TRAINERS</a>
        <a href="my-classes.php"   <?= $activePage === 'my-classes'   ? 'class="active"' : '' ?>>MY CLASSES</a>
        <a href="reservations.php" <?= $activePage === 'reservations' ? 'class="active"' : '' ?>>RESERVATIONS</a>
        <a href="news.php"         <?= $activePage === 'news'         ? 'class="active"' : '' ?>>NEWS</a>
    </nav>
</aside>
