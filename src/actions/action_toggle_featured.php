<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/AdminLog.class.php');

[$session, $db] = requireAuthenticatedPage();

$isJson = ($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json';

function jsonError(string $msg): never {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

if (!$session->isAdmin()) {
    if ($isJson) jsonError('Forbidden.');
    header('Location: /src/pages/index.php');
    exit;
}

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    if ($isJson) jsonError('Invalid request.');
    $session->addMessage('error', 'Invalid request.');
    header('Location: /src/pages/admin-classes.php');
    exit;
}

$type    = $_POST['type']    ?? '';
$id      = (int)($_POST['id'] ?? 0);
$swapId  = (int)($_POST['swap_id'] ?? 0);
$return  = $_POST['return']  ?? '/src/pages/admin-classes.php';

if (!in_array($type, ['class', 'trainer']) || $id <= 0) {
    if ($isJson) jsonError('Invalid request.');
    $session->addMessage('error', 'Invalid request.');
    header("Location: $return");
    exit;
}

const FEATURED_LIMIT = 4;

if ($type === 'class') {
    $row = $db->prepare("SELECT name, is_featured FROM class WHERE id = :id");
    $row->execute([':id' => $id]);
    $class = $row->fetch(PDO::FETCH_ASSOC);
    if (!$class) {
        if ($isJson) jsonError('Class not found.');
        $session->addMessage('error', 'Class not found.');
        header("Location: $return");
        exit;
    }

    $newVal = $class['is_featured'] ? 0 : 1;

    if ($newVal === 1) {
        $count = (int)$db->query("SELECT COUNT(*) FROM class WHERE is_featured = 1")->fetchColumn();
        if ($count >= FEATURED_LIMIT) {
            if ($swapId > 0) {
                $db->prepare("UPDATE class SET is_featured = 0 WHERE id = :id")->execute([':id' => $swapId]);
            } else {
                if ($isJson) {
                    $featured = $db->query(
                        "SELECT c.id, c.name, ct.name AS type_name FROM class c
                         LEFT JOIN class_type ct ON ct.id = c.type_id
                         WHERE c.is_featured = 1 ORDER BY c.name ASC"
                    )->fetchAll(PDO::FETCH_ASSOC);
                    header('Content-Type: application/json');
                    echo json_encode(['ok' => false, 'limit' => true, 'featured' => $featured]);
                    exit;
                }
                $session->addMessage('error', 'Featured limit reached (max 4). Remove one first.');
                header("Location: $return");
                exit;
            }
        }
    }

    $db->prepare("UPDATE class SET is_featured = :val WHERE id = :id")->execute([':val' => $newVal, ':id' => $id]);
    $action = $newVal ? 'featured' : 'unfeatured';
    AdminLog::write($db, (int)$session->getId(), 'UPDATE', "Marked class {$class['name']} as $action on homepage");

    if ($isJson) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'featured' => (bool)$newVal]);
        exit;
    }
    $session->addMessage('success', "Class " . ($newVal ? 'featured' : 'unfeatured') . " on homepage.");
} else {
    $row = $db->prepare("SELECT u.name, tp.is_featured FROM trainer_profile tp JOIN user u ON u.user_id = tp.user_id WHERE tp.user_id = :id");
    $row->execute([':id' => $id]);
    $trainer = $row->fetch(PDO::FETCH_ASSOC);
    if (!$trainer) {
        if ($isJson) jsonError('Trainer not found.');
        $session->addMessage('error', 'Trainer not found.');
        header("Location: $return");
        exit;
    }

    $newVal = $trainer['is_featured'] ? 0 : 1;

    if ($newVal === 1) {
        $count = (int)$db->query("SELECT COUNT(*) FROM trainer_profile WHERE is_featured = 1")->fetchColumn();
        if ($count >= FEATURED_LIMIT) {
            if ($swapId > 0) {
                $db->prepare("UPDATE trainer_profile SET is_featured = 0 WHERE user_id = :id")->execute([':id' => $swapId]);
            } else {
                if ($isJson) {
                    $featured = $db->query(
                        "SELECT u.user_id AS id, u.name FROM trainer_profile tp
                         JOIN user u ON u.user_id = tp.user_id
                         WHERE tp.is_featured = 1 ORDER BY u.name ASC"
                    )->fetchAll(PDO::FETCH_ASSOC);
                    header('Content-Type: application/json');
                    echo json_encode(['ok' => false, 'limit' => true, 'featured' => $featured]);
                    exit;
                }
                $session->addMessage('error', 'Featured limit reached (max 4). Remove one first.');
                header("Location: $return");
                exit;
            }
        }
    }

    $db->prepare("UPDATE trainer_profile SET is_featured = :val WHERE user_id = :id")->execute([':val' => $newVal, ':id' => $id]);
    $action = $newVal ? 'featured' : 'unfeatured';
    AdminLog::write($db, (int)$session->getId(), 'UPDATE', "Marked trainer {$trainer['name']} as $action on homepage");

    if ($isJson) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'featured' => (bool)$newVal]);
        exit;
    }
    $session->addMessage('success', "Trainer " . ($newVal ? 'featured' : 'unfeatured') . " on homepage.");
}

header("Location: $return");
exit;
