<?php
/**
 * Script Reset Password Utente
 * Resetta password per un utente specifico
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance();

// Username da resettare
$username = 'marco';
$newPassword = 'Marco123'; // Password temporanea

echo "🔐 RESET PASSWORD UTENTE\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Verifica utente esiste
    $user = $db->queryOne("SELECT * FROM users WHERE username = ?", [$username]);
    
    if (!$user) {
        echo "❌ ERRORE: Utente '$username' non trovato!\n\n";
        echo "Utenti disponibili:\n";
        $users = $db->query("SELECT username, role, active FROM users ORDER BY username");
        foreach ($users as $u) {
            $status = $u['active'] ? '✅ Attivo' : '❌ Inattivo';
            echo "  - {$u['username']} ({$u['role']}) - $status\n";
        }
        exit(1);
    }
    
    echo "📋 Utente trovato:\n";
    echo "   Username: {$user['username']}\n";
    echo "   Email: {$user['email']}\n";
    echo "   Ruolo: {$user['role']}\n";
    echo "   Stato: " . ($user['active'] ? '✅ Attivo' : '❌ Inattivo') . "\n\n";
    
    // Hash nuova password
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Aggiorna password
    $db->execute(
        "UPDATE users SET password = ? WHERE username = ?",
        [$passwordHash, $username]
    );
    
    echo "✅ PASSWORD RESETTATA CON SUCCESSO!\n\n";
    echo "📝 Nuove credenziali:\n";
    echo "   Username: $username\n";
    echo "   Password: $newPassword\n\n";
    
    // Se inattivo, suggerisci attivazione
    if (!$user['active']) {
        echo "⚠️  ATTENZIONE: L'utente è INATTIVO!\n";
        echo "   Per attivarlo:\n";
        echo "   UPDATE users SET active = 1 WHERE username = '$username';\n\n";
        
        // Attiva utente
        echo "🔓 Attivo l'utente automaticamente...\n";
        $db->execute("UPDATE users SET active = 1 WHERE username = ?", [$username]);
        echo "✅ Utente attivato!\n\n";
    }
    
    echo "🎯 Ora puoi fare login con:\n";
    echo "   http://localhost:8000/login.php\n";
    echo "   Username: $username\n";
    echo "   Password: $newPassword\n\n";
    
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
    exit(1);
}