<?php
declare(strict_types=1);
$_flash_msgs  = $session->getMessages();
$_banner_msgs = array_filter($_flash_msgs, fn($m) => $m['type'] !== 'toast');
$_toast_msgs  = array_filter($_flash_msgs, fn($m) => $m['type'] === 'toast');
if (!empty($_banner_msgs)): ?>
<div class="flash-messages" role="alert" aria-live="polite">
    <?php foreach ($_banner_msgs as $_msg): ?>
        <div class="flash-message flash-<?= htmlspecialchars($_msg['type']) ?>">
            <?= htmlspecialchars($_msg['text']) ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif;
if (!empty($_toast_msgs)): ?>
<div class="toast-container" aria-live="polite">
    <?php foreach ($_toast_msgs as $_msg): ?>
        <div class="toast toast--<?= htmlspecialchars($_msg['type'] ?? 'success') ?>">
            <?= htmlspecialchars($_msg['text']) ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
