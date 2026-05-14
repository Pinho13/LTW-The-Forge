<?php
$_sbu_name = htmlspecialchars($session->getName() ?? '');
$_sbu_role = $session->getRole() ?? 'member';

$_sbu_words = array_values(array_filter(explode(' ', $_sbu_name)));
$_sbu_initials = '';
foreach (array_slice($_sbu_words, 0, 2) as $_sbu_word) {
    $_sbu_initials .= mb_strtoupper(mb_substr($_sbu_word, 0, 1));
}

$_sbu_role_label = match($_sbu_role) {
    'trainer' => 'Trainer',
    'admin'   => 'Administrator',
    default   => 'Member',
};

$_sbu_user_id = $session->getId();
$_sbu_pfp_path = __DIR__ . '/../../database/profile_pictures/' . $_sbu_user_id . '.png';
$_sbu_pfp_url = file_exists($_sbu_pfp_path)
    ? '/database/profile_pictures/' . $_sbu_user_id . '.png?v=' . filemtime($_sbu_pfp_path)
    : null;
?>
<a href="/src/pages/page_account.php" class="sidebar-user-block <?= $activePage === 'profile' ? 'sidebar-user-block--active' : '' ?>">
    <?php if ($_sbu_pfp_url): ?>
        <img src="<?= htmlspecialchars($_sbu_pfp_url) ?>"
             alt="<?= $_sbu_name ?>"
             class="user-avatar user-avatar--circle">
    <?php else: ?>
        <div class="user-avatar user-avatar--circle user-avatar--initials">
            <span><?= $_sbu_initials ?></span>
        </div>
    <?php endif; ?>
    <div class="sidebar-user-block__info">
        <p class="sidebar-user-block__name"><?= $_sbu_name ?></p>
        <p class="sidebar-user-block__role"><?= $_sbu_role_label ?></p>
    </div>
    <span class="sidebar-user-block__chevron">›</span>
</a>
