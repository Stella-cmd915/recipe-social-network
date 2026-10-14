<?php
/**
 * Κοινές βοηθητικές συναρτήσεις
 */

/** Ελέγχει αν ο χρήστης είναι συνδεδεμένος */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/** Επιστρέφει το ID του τρέχοντος χρήστη ή null */
function currentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/** Ανακατευθύνει τον χρήστη */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/** Ασφαλής έξοδος HTML (XSS prevention) */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** Δημιουργία/ανάκτηση CSRF token */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Επαλήθευση CSRF token */
function verifyCsrf(?string $token): bool {
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/** Φιλικός χρόνος (πχ "πριν 5 λεπτά") */
function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return "πριν λίγο";
    if ($diff < 3600) return "πριν " . floor($diff / 60) . " λεπτά";
    if ($diff < 86400) return "πριν " . floor($diff / 3600) . " ώρες";
    if ($diff < 2592000) return "πριν " . floor($diff / 86400) . " ημέρες";
    return date('d/m/Y', $time);
}

/** Απαιτεί σύνδεση, αλλιώς ανακατεύθυνση */
function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('login.php?msg=login_required');
    }
}

/** Επιστρέφει JSON απάντηση */
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Ασφαλές uploaded image filename */
function generateImageFilename(string $originalName): string {
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    return bin2hex(random_bytes(16)) . '.' . $ext;
}

/** Επιτρεπόμενοι τύποι εικόνας */
function isValidImageType(string $mime): bool {
    return in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true);
}
