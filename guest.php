<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$stmt = $pdo->query(
    "SELECT r.*, u.username,
        (SELECT COUNT(*) FROM likes l WHERE l.recipe_id = r.id) AS like_count
     FROM recipes r JOIN users u ON r.user_id = u.id
     ORDER BY r.created_at DESC LIMIT 12"
);
$recipes = $stmt->fetchAll();
$pageTitle = 'Επισκέπτης';
require 'includes/header.php';
?>
<div class="alert alert-warning">
    <i class="bi bi-eye"></i> Περιηγείσαι ως <strong>επισκέπτης</strong>. Για να σχολιάσεις, να κάνεις like ή να ανεβάσεις συνταγή
    <a href="register.php">κάνε εγγραφή</a> ή <a href="login.php">σύνδεση</a>.
</div>

<h3 class="mb-3">Συνταγές της Κοινότητας</h3>
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
                    <h5 class="card-title"><a href="recipe.php?id=<?= (int)$r['id'] ?>" class="text-decoration-none"><?= e($r['title']) ?></a></h5>
                    <small class="text-muted">από <?= e($r['username']) ?> · <i class="bi bi-heart-fill text-danger"></i> <?= (int)$r['like_count'] ?></small>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php require 'includes/footer.php'; ?>
