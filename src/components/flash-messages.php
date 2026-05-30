<?php
declare(strict_types=1);
$_flash_msgs = $session->getMessages();
if (!empty($_flash_msgs)): ?>
<div class="toast-container" aria-live="polite">
    <?php foreach ($_flash_msgs as $_msg):
        $type = $_msg['type'] === 'toast' ? 'success' : $_msg['type'];
    ?>
        <div class="toast toast--<?= htmlspecialchars($type) ?>">
            <?= htmlspecialchars($_msg['text']) ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
