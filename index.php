<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

$params = [];
$where  = '';
if ($search !== '') {
    $where = "WHERE r.title LIKE :s OR r.description LIKE :s OR r.ingredients LIKE :s";
    $params[':s'] = '%' . $search . '%';
}

// Συνολικός αριθμός για pagination
$countSql = "SELECT COUNT(*) FROM recipes r $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

$sql = "SELECT r.*, u.username, u.avatar,
        (SELECT COUNT(*) FROM likes l WHERE l.recipe_id = r.id) AS like_count,
        (SELECT COUNT(*) FROM comments c WHERE c.recipe_id = r.id) AS comment_count
        " . (isLoggedIn() ? ", (SELECT COUNT(*) FROM likes l WHERE l.recipe_id = r.id AND l.user_id = :uid) AS liked_by_me" : "") . "
        FROM recipes r
        JOIN users u ON r.user_id = u.id
        $where
        ORDER BY r.created_at DESC
        LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
if (isLoggedIn()) $params[':uid'] = currentUserId();
$stmt->execute($params);
$recipes = $stmt->fetchAll();

$pageTitle = 'Αρχική';
require 'includes/header.php';
?>
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= e($_SESSION['flash']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<?php if (!isLoggedIn()): ?>
    <div class="bg-gradient p-5 rounded-3 text-white mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <h1 class="fw-bold">🍳 Καλωσήρθες στο RecipeNet</h1>
        <p class="lead">Μοιράσου τις αγαπημένες σου συνταγές. Ανακάλυψε νέες γεύσεις. Αλληλεπίδρασε με την κοινότητα.</p>
        <a href="register.php" class="btn btn-light btn-lg me-2">Εγγραφή</a>
        <a href="login.php" class="btn btn-outline-light btn-lg">Σύνδεση</a>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        <?php if ($search): ?>
            Αποτελέσματα για «<?= e($search) ?>» (<?= $total ?>)
        <?php else: ?>
            <i class="bi bi-fire"></i> Πρόσφατες Συνταγές
        <?php endif; ?>
    </h3>
    <?php if (isLoggedIn()): ?>
        <a href="upload_recipe.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Νέα Συνταγή
        </a>
    <?php endif; ?>
</div>

<?php if (empty($recipes)): ?>
    <div class="alert alert-info">Δεν βρέθηκαν συνταγές.</div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($recipes as $r): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm recipe-card">
                    <?php if (!empty($r['image']) && file_exists(__DIR__ . '/uploads/' . $r['image'])): ?>
                        <a href="recipe.php?id=<?= (int)$r['id'] ?>">
                            <img src="uploads/<?= e($r['image']) ?>" class="card-img-top recipe-img" alt="<?= e($r['title']) ?>">
                        </a>
                    <?php else: ?>
                        <a href="recipe.php?id=<?= (int)$r['id'] ?>">
                            <div class="recipe-img bg-secondary d-flex align-items-center justify-content-center text-white">
                                <i class="bi bi-image" style="font-size: 3rem"></i>
                            </div>
                        </a>
                    <?php endif; ?>
                    <div class="card-body">
                        <?php if (!empty($r['category'])): ?>
                            <span class="badge bg-secondary mb-2"><?= e($r['category']) ?></span>
                        <?php endif; ?>
                        <h5 class="card-title"><a href="recipe.php?id=<?= (int)$r['id'] ?>" class="text-decoration-none"><?= e($r['title']) ?></a></h5>
                        <p class="card-text small text-muted"><?= e(mb_substr($r['description'] ?? '', 0, 100)) ?><?= mb_strlen($r['description'] ?? '') > 100 ? '…' : '' ?></p>
                        <div class="d-flex justify-content-between align-items-center small">
                            <a href="profile.php?id=<?= (int)$r['user_id'] ?>" class="text-decoration-none text-muted">
                                <i class="bi bi-person"></i> <?= e($r['username']) ?>
                            </a>
                            <span class="text-muted"><?= e(timeAgo($r['created_at'])) ?></span>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <?php if (isLoggedIn()): ?>
                                <button class="btn btn-sm btn-link p-0 text-decoration-none like-btn <?= !empty($r['liked_by_me']) ? 'liked' : '' ?>"
                                        data-recipe-id="<?= (int)$r['id'] ?>">
                                    <i class="bi <?= !empty($r['liked_by_me']) ? 'bi-heart-fill text-danger' : 'bi-heart' ?>"></i>
                                    <span class="like-count"><?= (int)$r['like_count'] ?></span>
                                </button>
                            <?php else: ?>
                                <span class="text-muted small"><i class="bi bi-heart"></i> <?= (int)$r['like_count'] ?></span>
                            <?php endif; ?>
                            <span class="ms-2 text-muted small"><i class="bi bi-chat"></i> <?= (int)$r['comment_count'] ?></span>
                        </div>
                        <a href="recipe.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary">Άνοιγμα</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= $search ? '&q=' . urlencode($search) : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
