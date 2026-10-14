<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('index.php');

$stmt = $pdo->prepare(
    "SELECT r.*, u.username, u.full_name, u.avatar,
        (SELECT COUNT(*) FROM likes l WHERE l.recipe_id = r.id) AS like_count,
        " . (isLoggedIn() ? "(SELECT COUNT(*) FROM likes l WHERE l.recipe_id = r.id AND l.user_id = ?) AS liked_by_me" : "0 AS liked_by_me") . "
     FROM recipes r JOIN users u ON r.user_id = u.id
     WHERE r.id = ?"
);
$stmt->execute(isLoggedIn() ? [currentUserId(), $id] : [$id]);
$recipe = $stmt->fetch();
if (!$recipe) {
    http_response_code(404);
    $pageTitle = 'Δεν βρέθηκε';
    require 'includes/header.php';
    echo '<div class="alert alert-warning">Η συνταγή δεν βρέθηκε.</div>';
    require 'includes/footer.php';
    exit;
}

// Σχόλια
$cs = $pdo->prepare(
    "SELECT c.*, u.username, u.avatar FROM comments c
     JOIN users u ON c.user_id = u.id
     WHERE c.recipe_id = ? ORDER BY c.created_at DESC"
);
$cs->execute([$id]);
$comments = $cs->fetchAll();

$pageTitle = $recipe['title'];
require 'includes/header.php';
?>
<article class="row g-4">
    <div class="col-lg-7">
        <?php if (!empty($recipe['image']) && file_exists(__DIR__ . '/uploads/' . $recipe['image'])): ?>
            <img src="uploads/<?= e($recipe['image']) ?>" class="img-fluid rounded shadow-sm w-100 recipe-hero" alt="<?= e($recipe['title']) ?>">
        <?php endif; ?>
    </div>
    <div class="col-lg-5">
        <?php if (!empty($recipe['category'])): ?>
            <span class="badge bg-primary mb-2"><?= e($recipe['category']) ?></span>
        <?php endif; ?>
        <h1 class="fw-bold"><?= e($recipe['title']) ?></h1>
        <p class="text-muted">
            <a href="profile.php?id=<?= (int)$recipe['user_id'] ?>" class="text-decoration-none">
                <i class="bi bi-person-circle"></i> <?= e($recipe['username']) ?>
            </a>
            · <?= e(timeAgo($recipe['created_at'])) ?>
            <?php if ($recipe['prep_time']): ?>
                · <i class="bi bi-clock"></i> <?= (int)$recipe['prep_time'] ?> λεπτά
            <?php endif; ?>
        </p>

        <?php if (!empty($recipe['description'])): ?>
            <p class="lead"><?= nl2br(e($recipe['description'])) ?></p>
        <?php endif; ?>

        <div class="d-flex align-items-center gap-2 mt-3">
            <?php if (isLoggedIn()): ?>
                <button class="btn <?= $recipe['liked_by_me'] ? 'btn-danger' : 'btn-outline-danger' ?> like-btn"
                        data-recipe-id="<?= (int)$recipe['id'] ?>">
                    <i class="bi <?= $recipe['liked_by_me'] ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                    <span class="like-count"><?= (int)$recipe['like_count'] ?></span>
                </button>
            <?php else: ?>
                <span class="btn btn-outline-secondary disabled">
                    <i class="bi bi-heart"></i> <?= (int)$recipe['like_count'] ?>
                </span>
            <?php endif; ?>
            <span class="text-muted"><i class="bi bi-chat-dots"></i> <span id="commentTotal"><?= count($comments) ?></span> σχόλια</span>
        </div>
    </div>
</article>

<hr class="my-4">

<div class="row g-4">
    <section class="col-md-5">
        <h3><i class="bi bi-basket"></i> Υλικά</h3>
        <ul class="list-group">
            <?php foreach (preg_split('/\r?\n/', $recipe['ingredients']) as $ing): if (trim($ing) === '') continue; ?>
                <li class="list-group-item"><?= e($ing) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <section class="col-md-7">
        <h3><i class="bi bi-list-ol"></i> Εκτέλεση</h3>
        <ol class="list-group list-group-numbered">
            <?php foreach (preg_split('/\r?\n/', $recipe['steps']) as $step):
                $step = preg_replace('/^\d+\.\s*/', '', trim($step));
                if ($step === '') continue;
            ?>
                <li class="list-group-item"><?= e($step) ?></li>
            <?php endforeach; ?>
        </ol>
    </section>
</div>

<hr class="my-4">

<section id="comments">
    <h3><i class="bi bi-chat-text"></i> Σχόλια</h3>
    <?php if (isLoggedIn()): ?>
        <form id="commentForm" class="mb-4" data-recipe-id="<?= (int)$recipe['id'] ?>">
            <div class="input-group">
                <textarea name="content" class="form-control" rows="2" placeholder="Γράψε ένα σχόλιο…" required maxlength="500"></textarea>
                <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i></button>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-info"><a href="login.php">Συνδέσου</a> για να σχολιάσεις.</div>
    <?php endif; ?>

    <div id="commentList">
        <?php foreach ($comments as $c): ?>
            <div class="card mb-2">
                <div class="card-body py-2">
                    <strong><a href="profile.php?id=<?= (int)$c['user_id'] ?>" class="text-decoration-none"><?= e($c['username']) ?></a></strong>
                    <small class="text-muted ms-2"><?= e(timeAgo($c['created_at'])) ?></small>
                    <p class="mb-0"><?= nl2br(e($c['content'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
