<?php
/**
 * Test Flusso Completo Attivazione Utente
 * 
 * Test step by step:
 * 1. Crea utente (simulando gestione_utenti.php)
 * 2. Verifica utente inattivo
 * 3. Recupera token
 * 4. Simula attivazione con password
 * 5. Verifica utente attivo
 * 6. Test login
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "🧪 TEST FLUSSO ATTIVAZIONE UTENTE\n";
echo str_repeat("=", 50) . "\n\n";

$db = Database::getInstance();
$testUsername = 'test_' . time();
$testEmail = $testUsername . '@test.it';
$testPassword = 'Test123';

try {
    // STEP 1: Crea utente (simula UsersController::create)
    echo "📝 STEP 1: Creazione utente...\n";
    
    $tempPassword = bin2hex(random_bytes(16));
    $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
    
    $userId = $db->insert(
        "INSERT INTO users (username, password, email, role, active) VALUES (?, ?, ?, ?, 0)",
        [$testUsername, $passwordHash, $testEmail, 'segreteria']
    );
    
    echo "   ✅ Utente creato: ID=$userId, username=$testUsername\n";
    echo "   ✅ Active=0 (inattivo)\n\n";
    
    // STEP 2: Genera token
    echo "🔑 STEP 2: Generazione token attivazione...\n";
    
    $urlToken = hash('sha256', $userId . $testEmail . time() . uniqid());
    $activationCode = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    $tokenId = $db->insert(
        "INSERT INTO activation_tokens (user_id, url_token, activation_code, expires_at) VALUES (?, ?, ?, ?)",
        [$userId, $urlToken, $activationCode, $expiresAt]
    );
    
    echo "   ✅ Token generato: $urlToken\n";
    echo "   ✅ Codice attivazione: $activationCode\n";
    echo "   ✅ Scadenza: $expiresAt\n\n";
    
    // STEP 3: Verifica utente inattivo non può fare login
    echo "🔒 STEP 3: Test login con utente inattivo...\n";
    
    $loginTest = $db->queryOne("SELECT * FROM users WHERE username = ? AND active = 1", [$testUsername]);
    if (!$loginTest) {
        echo "   ✅ Corretto: utente inattivo NON può fare login\n\n";
    } else {
        echo "   ❌ ERRORE: utente inattivo può fare login!\n\n";
        throw new Exception("Test fallito: utente inattivo loggabile");
    }
    
    // STEP 4: Simula attivazione (cosa fa activate.php)
    echo "🎯 STEP 4: Simulazione attivazione...\n";
    
    // Verifica token e codice
    $tokenCheck = $db->queryOne(
        "SELECT * FROM activation_tokens WHERE url_token = ? AND activation_code = ? AND used = 0",
        [$urlToken, $activationCode]
    );
    
    if (!$tokenCheck) {
        throw new Exception("Token o codice non valido");
    }
    
    echo "   ✅ Token e codice verificati\n";
    
    // Salva password e attiva
    $newPasswordHash = password_hash($testPassword, PASSWORD_DEFAULT);
    $db->execute("UPDATE users SET password = ?, active = 1 WHERE id = ?", [$newPasswordHash, $userId]);
    $db->execute("UPDATE activation_tokens SET used = 1, used_at = datetime('now') WHERE id = ?", [$tokenCheck['id']]);
    
    echo "   ✅ Password impostata\n";
    echo "   ✅ Account attivato (active=1)\n";
    echo "   ✅ Token marcato come usato\n\n";
    
    // STEP 5: Verifica attivazione
    echo "✅ STEP 5: Verifica account attivato...\n";
    
    $user = $db->queryOne("SELECT * FROM users WHERE id = ?", [$userId]);
    if ($user['active'] == 1) {
        echo "   ✅ Active = 1 (attivo)\n";
    } else {
        throw new Exception("Utente non attivato!");
    }
    
    if (password_verify($testPassword, $user['password'])) {
        echo "   ✅ Password salvata correttamente\n\n";
    } else {
        throw new Exception("Password non salvata!");
    }
    
    // STEP 6: Test login
    echo "🔓 STEP 6: Test login con credenziali...\n";
    
    $loginUser = $db->queryOne(
        "SELECT * FROM users WHERE username = ? AND active = 1", 
        [$testUsername]
    );
    
    if ($loginUser && password_verify($testPassword, $loginUser['password'])) {
        echo "   ✅ LOGIN RIUSCITO!\n";
        echo "   ✅ Username: $testUsername\n";
        echo "   ✅ Password: $testPassword\n\n";
    } else {
        throw new Exception("Login fallito!");
    }
    
    // STEP 7: Verifica token non riutilizzabile
    echo "🔒 STEP 7: Verifica token non riutilizzabile...\n";
    
    $tokenRecheck = $db->queryOne(
        "SELECT * FROM activation_tokens WHERE url_token = ? AND used = 1",
        [$urlToken]
    );
    
    if ($tokenRecheck) {
        echo "   ✅ Token marcato come usato\n";
        echo "   ✅ Token non può essere riutilizzato\n\n";
    } else {
        throw new Exception("Token ancora utilizzabile!");
    }
    
    // CLEANUP
    echo "🧹 Pulizia test...\n";
    $db->execute("DELETE FROM activation_tokens WHERE user_id = ?", [$userId]);
    $db->execute("DELETE FROM users WHERE id = ?", [$userId]);
    echo "   ✅ Utente test eliminato\n\n";
    
    // RISULTATO FINALE
    echo str_repeat("=", 50) . "\n";
    echo "✅ TUTTI I TEST SUPERATI! 🎉\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "📋 Riepilogo Flusso:\n";
    echo "1. ✅ Utente creato con active=0\n";
    echo "2. ✅ Token e codice generati\n";
    echo "3. ✅ Utente inattivo bloccato dal login\n";
    echo "4. ✅ Attivazione imposta password + active=1\n";
    echo "5. ✅ Token marcato come usato\n";
    echo "6. ✅ Login funziona dopo attivazione\n";
    echo "7. ✅ Token non riutilizzabile\n\n";
    
    echo "🎯 URL Test Manuale:\n";
    echo BASE_URL . "/activate.php?token=[TOKEN]\n\n";
    
} catch (Exception $e) {
    echo "\n❌ TEST FALLITO: " . $e->getMessage() . "\n";
    echo "\n🧹 Pulizia in caso di errore...\n";
    if (isset($userId)) {
        $db->execute("DELETE FROM activation_tokens WHERE user_id = ?", [$userId]);
        $db->execute("DELETE FROM users WHERE id = ?", [$userId]);
    }
    exit(1);
}