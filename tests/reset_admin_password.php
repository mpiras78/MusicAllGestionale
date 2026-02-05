<?php
/**
 * Reset password admin a "admin123"
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== RESET PASSWORD ADMIN ===\n\n";

$db = Database::getInstance();

// Nuova password
$newPassword = 'admin123';
$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

// Update password admin
$result = $db->execute(
    "UPDATE users SET password = ?, active = 1 WHERE username = 'admin'",
    [$passwordHash]
);

if ($result) {
    echo "✅ PASSWORD ADMIN RESETTATA CON SUCCESSO!\n\n";
    echo "Credenziali:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n\n";
    echo "Puoi ora effettuare il login.\n";
} else {
    echo "❌ Errore durante il reset della password\n";
}