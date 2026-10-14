<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$errors = [];
$old = ['username' => '', 'email' => '', 'full_name' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? null)) {
        $errors[] = 'Μη έγκυρο CSRF token.';
    } else {
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');

        $old = compact('username', 'email', 'full_name');

        // Server-side validation
        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $errors[] = 'Το username πρέπει να είναι 3-50 χαρακτήρες (γράμματα/αριθμοί/_).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Μη έγκυρη διεύθυνση email.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Ο κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.';
        }
        if ($password !== $password2) {
            $errors[] = 'Οι κωδικοί δεν ταιριάζουν.';
        }
        if (mb_strlen($full_name) > 100) {
            $errors[] = 'Το ονοματεπώνυμο είναι πολύ μεγάλο.';
        }

        // Έλεγχος μοναδικότητας
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Το username ή το email χρησιμοποιείται ήδη.';
            }
        }

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$username, $email, $hash, $full_name]);

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$pdo->lastInsertId();
            $_SESSION['flash']   = 'Καλωσήρθες! Ο λογαριασμός σου δημιουργήθηκε επιτυχώς.';
            redirect('index.php');
        }
    }
}
$pageTitle = 'Εγγραφή';
require 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4"><i class="bi bi-person-plus"></i> Δημιουργία Λογαριασμού</h2>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0"><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="post" id="registerForm" novalidate>
                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">

                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required
                               pattern="[a-zA-Z0-9_]{3,50}" value="<?= e($old['username']) ?>">
                        <div class="form-text">3-50 χαρακτήρες, γράμματα/αριθμοί/_</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required value="<?= e($old['email']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ονοματεπώνυμο</label>
                        <input type="text" name="full_name" class="form-control" value="<?= e($old['full_name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Κωδικός *</label>
                        <input type="password" name="password" id="password" class="form-control" required minlength="8">
                        <div class="progress mt-2" style="height: 5px;">
                            <div id="pwStrength" class="progress-bar" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Επιβεβαίωση κωδικού *</label>
                        <input type="password" name="password2" id="password2" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Εγγραφή</button>
                </form>
                <p class="text-center mt-3 mb-0 small">Έχεις λογαριασμό; <a href="login.php">Σύνδεση</a></p>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
