<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('index.php');

$stmt = $pdo->prepare("SELECT id, username, full_name, bio, avatar, created_at FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    http_response_code(404);
    $pageTitle = 'Δεν βρέθηκε';
    require 'includes/header.php';
    echo '<div class="alert alert-warning">Ο χρήστης δεν βρέθηκε.</div>';
    require 'includes/footer.php';
    exit;
}

$stmt = $pdo->prepare(
    "SELECT r.*, (SELECT COUNT(*) FROM likes l WHERE l.recipe_id = r.id) AS like_count
     FROM recipes r WHERE r.user_id = ? ORDER BY r.created_at DESC"
);
$stmt->execute([$id]);
$recipes = $stmt->fetchAll();

$stats = $pdo->prepare(
    "SELECT (SELECT COUNT(*) FROM recipes WHERE user_id = :uid) AS total_recipes,
            (SELECT COUNT(*) FROM likes l JOIN recipes r ON l.recipe_id = r.id WHERE r.user_id = :uid) AS total_likes,
            (SELECT COUNT(*) FROM comments c JOIN recipes r ON c.recipe_id = r.id WHERE r.user_id = :uid) AS total_comments"
);
$stats->execute([':uid' => $id]);
$s = $stats->fetch();

$pageTitle = $user['username'];
require 'includes/header.php';
?>
<div class="card shadow-sm mb-4">
    <div class="card-body p-4 d-flex flex-column flex-md-row align-items-center gap-4">
        <div class="profile-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
             style="width:120px;height:120px;font-size:3rem;">
            <?= strtoupper(mb_substr($user['username'], 0, 1)) ?>
        </div>
        <div class="flex-grow-1 text-center text-md-start">
            <h2 class="mb-1"><?= e($user['full_name'] ?: $user['username']) ?></h2>
            <p class="text-muted mb-2">@<?= e($user['username']) ?> · Μέλος από <?= date('M Y', strtotime($user['created_at'])) ?></p>
            <?php if (!empty($user['bio'])): ?>
                <p class="mb-0"><?= e($user['bio']) ?></p>
            <?php endif; ?>
        </div>
        <div class="text-center">
            <div class="row text-center g-3">
                <div class="col-4">
                    <div class="fs-3 fw-bold text-primary"><?= (int)$s['total_recipes'] ?></div>
                    <small class="text-muted">Συνταγές</small>
                </div>
                <div class="col-4">
                    <div class="fs-3 fw-bold text-danger"><?= (int)$s['total_likes'] ?></div>
                    <small class="text-muted">Likes</small>
                </div>
                <div class="col-4">
                    <div class="fs-3 fw-bold text-success"><?= (int)$s['total_comments'] ?></div>
                    <small class="text-muted">Σχόλια</small>
                </div>
            </div>
        </div>
    </div>
</div>

<h3 class="mb-3">Οι συνταγές του χρήστη</h3>
<?php if (empty($recipes)): ?>
    <div class="alert alert-info">Δεν υπάρχουν δημοσιεύσεις ακόμη.</div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($recipes as $r): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($r['image']) && file_exists(__DIR__ . '/uploads/' . $r['image'])): ?>
                        <a href="recipe.php?id=<?= (int)$r['id'] ?>">
                            <img src="uploads/<?= e($r['image']) ?>" class="card-img-top recipe-img" alt="<?= e($r['title']) ?>">
                        </a>
                    <?php endif; ?>
                    <div class="card-body">
                        <h6 class="card-title"><a href="recipe.php?id=<?= (int)$r['id'] ?>" class="text-decoration-none"><?= e($r['title']) ?></a></h6>
                        <p class="card-text small text-muted mb-0">
                            <i class="bi bi-heart-fill text-danger"></i> <?= (int)$r['like_count'] ?>
                            · <?= e(timeAgo($r['created_at'])) ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
