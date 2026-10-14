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
if ($recipeId <= 0) {
    jsonResponse(['ok' => false, 'error' => 'Λάθος ID συνταγής.'], 400);
}

// Επιβεβαίωση ότι η συνταγή υπάρχει
$chk = $pdo->prepare("SELECT id FROM recipes WHERE id = ?");
$chk->execute([$recipeId]);
if (!$chk->fetch()) jsonResponse(['ok' => false, 'error' => 'Η συνταγή δεν υπάρχει.'], 404);

$userId = currentUserId();
// Toggle: αν υπάρχει, διαγραφή· αλλιώς εισαγωγή
$exists = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND recipe_id = ?");
$exists->execute([$userId, $recipeId]);
if ($exists->fetch()) {
    $del = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND recipe_id = ?");
    $del->execute([$userId, $recipeId]);
    $liked = false;
} else {
    $ins = $pdo->prepare("INSERT INTO likes (user_id, recipe_id) VALUES (?, ?)");
    $ins->execute([$userId, $recipeId]);
    $liked = true;
}

// Επιστροφή νέου count
$cnt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE recipe_id = ?");
$cnt->execute([$recipeId]);
jsonResponse([
    'ok'    => true,
    'liked' => $liked,
    'count' => (int)$cnt->fetchColumn()
]);
