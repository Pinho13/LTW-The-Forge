<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Announcement.class.php');

[$session, $db] = requireAuthenticatedPage();

$posts = Announcement::getAll($db, 50, 0);
$hero  = !empty($posts) ? $posts[0] : null;
$cards = array_slice($posts, 1);

$TYPES = ['Gym News', 'Event', 'Coach Note', 'Maintenance', 'Member Story'];
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/news.css">
</head>

<body>
    <?php $activePage = 'news'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <header class="news-header">
            <h1>News</h1>
            <?php if ($session->isAdmin()): ?>
                <button type="button" class="btn-primary btn-sm" id="new-post-btn">+ New Post</button>
            <?php endif; ?>
        </header>

        <div class="news-body" id="news-list">

        <?php if (empty($posts)): ?>
            <p class="empty-state">No announcements yet.</p>
        <?php else: ?>

            <?php if ($hero): ?>
            <article class="news-hero" data-announcement-id="<?= (int)$hero['id'] ?>">
                <a class="news-hero__image" href="news-article.php?id=<?= (int)$hero['id'] ?>">
                    <img src="/src/assets/images/main-page/left-image.png" alt="The Forge gym floor">
                </a>
                <div class="news-hero__content">
                    <p class="news-card__eyebrow">
                        <span class="news-card__type"><?= htmlspecialchars($hero['type']) ?></span>
                        <span class="news-card__dot">&middot;</span>
                        <span class="news-card__date"><?= (new DateTimeImmutable($hero['created_at']))->format('M j, Y') ?></span>
                        <span class="news-card__dot">&middot;</span>
                        <span class="news-card__read"><?= (int)$hero['read_time'] ?> min</span>
                    </p>
                    <a href="news-article.php?id=<?= (int)$hero['id'] ?>" class="news-hero__title-link">
                        <h2 class="news-hero__title"><?= htmlspecialchars($hero['title']) ?></h2>
                    </a>
                    <p class="news-hero__body"><?= htmlspecialchars(mb_strimwidth($hero['body'], 0, 200, '…')) ?></p>
                    <a href="news-article.php?id=<?= (int)$hero['id'] ?>" class="news-hero__cta">Read Story</a>
                    <?php if ($session->isAdmin()): ?>
                    <div class="news-card__admin">
                        <form method="POST" action="../actions/action_toggle_pin.php">
                            <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
                            <input type="hidden" name="announcement_id" value="<?= (int)$hero['id'] ?>">
                            <button type="submit" class="btn-ghost btn-sm"><?= $hero['pinned'] ? 'Unpin' : 'Pin' ?></button>
                        </form>
                        <form method="POST" action="../actions/action_delete_announcement.php">
                            <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
                            <input type="hidden" name="announcement_id" value="<?= (int)$hero['id'] ?>">
                            <button type="submit" class="btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endif; ?>

            <?php if (!empty($cards)): ?>
            <ul class="news-grid">
                <?php foreach ($cards as $post): ?>
                <li class="news-card <?= $post['pinned'] ? 'news-card--pinned' : '' ?>" data-announcement-id="<?= (int)$post['id'] ?>">
                    <a href="news-article.php?id=<?= (int)$post['id'] ?>" class="news-card__link">
                        <p class="news-card__eyebrow">
                            <span class="news-card__type"><?= htmlspecialchars($post['type']) ?></span>
                            <span class="news-card__dot">&middot;</span>
                            <span class="news-card__date"><?= (new DateTimeImmutable($post['created_at']))->format('M j, Y') ?></span>
                        </p>
                        <h2 class="news-card__title"><?= htmlspecialchars($post['title']) ?></h2>
                        <p class="news-card__body"><?= htmlspecialchars(mb_strimwidth($post['body'], 0, 120, '…')) ?></p>
                        <p class="news-card__footer">
                            <span><?= (int)$post['read_time'] ?> min</span>
                            <span class="news-card__dot">&middot;</span>
                            <span class="news-card__read-link">Read &rarr;</span>
                        </p>
                    </a>
                    <?php if ($session->isAdmin()): ?>
                    <div class="news-card__admin">
                        <form method="POST" action="../actions/action_toggle_pin.php">
                            <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
                            <input type="hidden" name="announcement_id" value="<?= (int)$post['id'] ?>">
                            <button type="submit" class="btn-ghost btn-sm"><?= $post['pinned'] ? 'Unpin' : 'Pin' ?></button>
                        </form>
                        <form method="POST" action="../actions/action_delete_announcement.php">
                            <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
                            <input type="hidden" name="announcement_id" value="<?= (int)$post['id'] ?>">
                            <button type="submit" class="btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

        <?php endif; ?>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <?php if ($session->isAdmin()): ?>
    <div class="modal-backdrop" id="page-backdrop"></div>
    <dialog id="post-modal" class="auth-modal">
        <button type="button" class="btn-ghost auth-modal__close" id="post-close">&times;</button>
        <h2 class="auth-modal__title">New Announcement</h2>
        <form method="POST" action="../actions/action_create_announcement.php" class="auth-modal__form">
            <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">

            <label for="post-title">Title</label>
            <input type="text" id="post-title" name="title" required maxlength="150" placeholder="Announcement title">

            <label for="post-type">Category</label>
            <select id="post-type" name="type">
                <?php foreach ($TYPES as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="post-read-time">Read time (minutes)</label>
            <input type="number" id="post-read-time" name="read_time" min="1" max="30" value="1">

            <label for="post-body">Body</label>
            <textarea id="post-body" name="body" rows="5" required maxlength="2000" placeholder="Write your announcement…"></textarea>

            <label class="checkbox-label">
                <input type="checkbox" name="pinned" value="1"> Pin this announcement
            </label>

            <button type="submit" class="btn-primary modal-action-btn">Publish</button>
        </form>
    </dialog>
    <script>
        const CSRF_TOKEN = <?= json_encode($session->getCsrfToken()) ?>;
    </script>
    <script src="../scripts/news.js"></script>
    <?php endif; ?>
</body>
</html>
