<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$errors = [];
$msg = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'login_required') {
    $msg = 'Πρέπει να συνδεθείτε για να συνεχίσετε.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? null)) {
        $errors[] = 'Μη έγκυρο CSRF token.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            $errors[] = 'Συμπληρώστε όλα τα πεδία.';
        } else {
            $stmt = $pdo->prepare(
                "SELECT id, password_hash FROM users WHERE username = ? OR email = ? LIMIT 1"
            );
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['flash']   = 'Καλώς ήρθατε πίσω!';
                redirect('index.php');
            } else {
                $errors[] = 'Λάθος στοιχεία σύνδεσης.';
            }
        }
    }
}
$pageTitle = 'Σύνδεση';
require 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4"><i class="bi bi-box-arrow-in-right"></i> Σύνδεση</h2>

                <?php if ($msg): ?>
                    <div class="alert alert-info"><?= e($msg) ?></div>
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0"><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="post" id="loginForm" novalidate>
                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                    <div class="mb-3">
                        <label class="form-label">Username ή Email</label>
                        <input type="text" name="identifier" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Κωδικός</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Σύνδεση</button>
                </form>
                <p class="text-center mt-3 mb-0 small">Δεν έχεις λογαριασμό; <a href="register.php">Εγγραφή</a></p>
                <p class="text-center small text-muted mt-2">ή <a href="guest.php">περιήγηση ως επισκέπτης</a></p>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
