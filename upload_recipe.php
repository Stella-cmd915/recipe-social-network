<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
requireLogin();

$errors = [];
$old = ['title' => '', 'description' => '', 'ingredients' => '', 'steps' => '', 'category' => '', 'prep_time' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? null)) {
        $errors[] = 'Μη έγκυρο CSRF token.';
    } else {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $ingredients = trim($_POST['ingredients'] ?? '');
        $steps       = trim($_POST['steps'] ?? '');
        $category    = trim($_POST['category'] ?? '');
        $prep_time   = (int)($_POST['prep_time'] ?? 0);
        $old = compact('title', 'description', 'ingredients', 'steps', 'category', 'prep_time');

        if (mb_strlen($title) < 3 || mb_strlen($title) > 150) {
            $errors[] = 'Ο τίτλος πρέπει να έχει 3-150 χαρακτήρες.';
        }
        if (empty($ingredients)) $errors[] = 'Συμπληρώστε τα υλικά.';
        if (empty($steps))       $errors[] = 'Συμπληρώστε τα βήματα.';
        if ($prep_time < 0 || $prep_time > 1440) $errors[] = 'Μη έγκυρος χρόνος προετοιμασίας.';

        // Διαχείριση εικόνας
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            if ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Η εικόνα ξεπερνά τα 5 MB.';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($file['tmp_name']);
                if (!isValidImageType($mime)) {
                    $errors[] = 'Επιτρέπονται μόνο εικόνες JPEG/PNG/GIF/WebP.';
                } else {
                    $imageName = generateImageFilename($file['name']);
                    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/uploads/' . $imageName)) {
                        $errors[] = 'Αποτυχία αποθήκευσης εικόνας.';
                    }
                }
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                "INSERT INTO recipes (user_id, title, description, ingredients, steps, image, category, prep_time)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                currentUserId(), $title, $description, $ingredients, $steps,
                $imageName, $category, $prep_time
            ]);
            $_SESSION['flash'] = 'Η συνταγή σου δημοσιεύτηκε!';
            redirect('recipe.php?id=' . $pdo->lastInsertId());
        }
    }
}
$pageTitle = 'Νέα Συνταγή';
require 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="mb-4"><i class="bi bi-plus-circle"></i> Δημιουργία Συνταγής</h2>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0"><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" id="recipeForm" novalidate>
                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">

                    <div class="mb-3">
                        <label class="form-label">Τίτλος συνταγής *</label>
                        <input type="text" name="title" class="form-control" required minlength="3" maxlength="150"
                               value="<?= e($old['title']) ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Κατηγορία</label>
                            <select name="category" class="form-select">
                                <option value="">-- επιλέξτε --</option>
                                <?php foreach (['Ορεκτικό', 'Σαλάτα', 'Κυρίως πιάτο', 'Σούπα', 'Γλυκό', 'Ποτό', 'Vegan', 'Άλλο'] as $cat): ?>
                                    <option <?= $old['category'] === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Χρόνος προετοιμασίας (λεπτά)</label>
                            <input type="number" name="prep_time" class="form-control" min="0" max="1440"
                                   value="<?= e((string)$old['prep_time']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Σύντομη περιγραφή</label>
                        <textarea name="description" class="form-control" rows="2"><?= e($old['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Υλικά * <small class="text-muted">(ένα ανά γραμμή)</small></label>
                        <textarea name="ingredients" class="form-control" rows="6" required><?= e($old['ingredients']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Βήματα εκτέλεσης * <small class="text-muted">(ένα ανά γραμμή)</small></label>
                        <textarea name="steps" class="form-control" rows="8" required><?= e($old['steps']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Εικόνα συνταγής</label>
                        <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
                        <div class="form-text">JPEG/PNG/GIF/WebP, έως 5 MB.</div>
                        <img id="imagePreview" class="img-fluid rounded mt-2 d-none" alt="Preview">
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-cloud-upload"></i> Δημοσίευση
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
