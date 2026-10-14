<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
$currentUser = null;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT id, username, full_name, avatar FROM users WHERE id = ?");
    $stmt->execute([currentUserId()]);
    $currentUser = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>RecipeNet - Κοινωνικό Δίκτυο Συνταγών</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Custom -->
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-egg-fried"></i> RecipeNet
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house"></i> Αρχική</a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item"><a class="nav-link" href="upload_recipe.php"><i class="bi bi-plus-circle"></i> Νέα Συνταγή</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="guest.php"><i class="bi bi-eye"></i> Περιήγηση ως Επισκέπτης</a></li>
                <?php endif; ?>
            </ul>
            <form class="d-flex me-3" role="search" action="index.php" method="get">
                <input class="form-control form-control-sm" type="search" name="q"
                       placeholder="Αναζήτηση συνταγής..." value="<?= e($_GET['q'] ?? '') ?>">
            </form>
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> <?= e($currentUser['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php?id=<?= (int)$currentUser['id'] ?>"><i class="bi bi-person"></i> Το προφίλ μου</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Αποσύνδεση</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Σύνδεση</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-light text-primary ms-2 px-3" href="register.php">Εγγραφή</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container my-4">
