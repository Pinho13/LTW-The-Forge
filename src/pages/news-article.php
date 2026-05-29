<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Announcement.class.php');

[$session, $db] = requireAuthenticatedPage();

$id   = (int) ($_GET['id'] ?? 0);
$post = $id > 0 ? Announcement::getById($db, $id) : null;

if (!$post) {
    $session->addMessage('error', 'Announcement not found.');
    header('Location: /src/pages/news.php');
    exit;
}

$date = new DateTimeImmutable($post['created_at']);
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/news-article.css">
</head>

<body>
    <?php $activePage = 'news'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <header>
            <h1>News</h1>
        </header>

        <article class="article-body">

            <a href="news.php" class="article-back">&larr; Back to News</a>

            <div class="article-eyebrow">
                <span class="article-type"><?= htmlspecialchars($post['type']) ?></span>
                <span class="article-dot">&middot;</span>
                <span class="article-date"><?= $date->format('M j, Y') ?></span>
                <span class="article-dot">&middot;</span>
                <span class="article-read"><?= (int)$post['read_time'] ?> min read</span>
            </div>

            <h1 class="article-title"><?= htmlspecialchars($post['title']) ?></h1>

            <p class="article-meta">By <?= htmlspecialchars($post['author_name']) ?></p>

            <div class="article-content">
                <?= nl2br(htmlspecialchars($post['body'])) ?>
            </div>

            <?php if ($session->isAdmin()): ?>
            <div class="article-admin">
                <form method="POST" action="../actions/action_toggle_pin.php">
                    <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
                    <input type="hidden" name="announcement_id" value="<?= $id ?>">
                    <button type="submit" class="btn-ghost btn-sm">
                        <?= $post['pinned'] ? 'Unpin' : 'Pin' ?>
                    </button>
                </form>
                <form method="POST" action="../actions/action_delete_announcement.php"
                      onsubmit="return confirm('Delete this announcement?')">
                    <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
                    <input type="hidden" name="announcement_id" value="<?= $id ?>">
                    <button type="submit" class="btn-danger btn-sm">Delete</button>
                </form>
            </div>
            <?php endif; ?>

        </article>
    </main>

    <?php include '../components/footer.php'; ?>
</body>
</html>
