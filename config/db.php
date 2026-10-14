<?php
/**
 * Σύνδεση με τη βάση δεδομένων μέσω PDO
 * Παρέχει ασφαλή επικοινωνία με prepared statements.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'recipe_network');
define('DB_USER', 'root');
define('DB_PASS', '');         // Αλλάξτε αν χρειάζεται
define('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die('Σφάλμα σύνδεσης με τη βάση: ' . htmlspecialchars($e->getMessage()));
}

// Ρύθμιση zone & session
date_default_timezone_set('Europe/Athens');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,    // true σε production με HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
