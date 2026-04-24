<div class="sidebar-user-block">
    <div class="sidebar-user-avatar">
        <span><?= htmlspecialchars(strtoupper(substr($user->name, 0, 1))) ?></span>
    </div>

    <div class="sidebar-user-info">
        <p class="sidebar-user-name"><?= htmlspecialchars($user->name) ?></p>
        <p class="sidebar-user-role"><?= htmlspecialchars(ucfirst($user->role)) ?></p>
    </div>

    <form method="post" action="../actions/action_logout.php" class="logout-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCsrfToken()) ?>">
        <button type="submit" class="btn-secondary">LOG OUT</button>
    </form>
</div>