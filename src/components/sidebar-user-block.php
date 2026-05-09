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
?>
<a href="/src/pages/page_account.php" class="sidebar-user-block <?= $activePage === 'profile' ? 'sidebar-user-block--active' : '' ?>">
    <div class="sidebar-user-block__avatar">
        <span><?= $_sbu_initials ?></span>
    </div>
    <div class="sidebar-user-block__info">
        <p class="sidebar-user-block__name"><?= $_sbu_name ?></p>
        <p class="sidebar-user-block__role"><?= $_sbu_role_label ?></p>
    </div>
    <span class="sidebar-user-block__chevron">›</span>
</a>
