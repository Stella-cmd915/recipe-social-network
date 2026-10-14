<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    jsonResponse(['ok' => false, 'error' => 'Πρέπει να είστε συνδεδεμένος.'], 401);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'error' => 'Μέθοδος δεν επιτρέπεται.'], 405);
}
if (!verifyCsrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf'] ?? null))) {
    jsonResponse(['ok' => false, 'error' => 'Μη έγκυρο CSRF token.'], 403);
}

$recipeId = (int)($_POST['recipe_id'] ?? 0);
$content  = trim($_POST['content'] ?? '');
if ($recipeId <= 0) jsonResponse(['ok' => false, 'error' => 'Λάθος ID.'], 400);
if (mb_strlen($content) < 1 || mb_strlen($content) > 500) {
    jsonResponse(['ok' => false, 'error' => 'Το σχόλιο πρέπει να είναι 1-500 χαρακτήρες.'], 400);
}

$ins = $pdo->prepare("INSERT INTO comments (user_id, recipe_id, content) VALUES (?, ?, ?)");
$ins->execute([currentUserId(), $recipeId, $content]);
$commentId = (int)$pdo->lastInsertId();

$stmt = $pdo->prepare(
    "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?"
);
$stmt->execute([$commentId]);
$row = $stmt->fetch();

jsonResponse([
    'ok' => true,
    'comment' => [
        'id'         => (int)$row['id'],
        'user_id'    => (int)$row['user_id'],
        'username'   => $row['username'],
        'content'    => $row['content'],
        'created_at' => $row['created_at'],
        'time_ago'   => timeAgo($row['created_at'])
    ]
]);
