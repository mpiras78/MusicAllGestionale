<?php
/**
 * Recupera token di attivazione per un utente
 * Usage: php tests/get_activation_token.php username@email.com
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$search = $argv[1] ?? null;

if (!$search) {
    echo "Usage: php tests/get_activation_token.php <username o email>\n";
    echo "Esempio: php tests/get_activation_token.php mario.rossi\n";
    echo "Esempio: php tests/get_activation_token.php mario@test.it\n";
    exit(1);
}

$db = Database::getInstance();

// Cerca per username o email
$sql = "
    SELECT 
        u.id,
        u.username,
        u.email,
        u.active,
        at.url_token,
        at.activation_code,
        at.expires_at,
        at.used,
        at.created_at,
        CASE 
            WHEN at.used = 1 THEN 'USATO'
            WHEN at.expires_at < datetime('now','localtime') THEN 'SCADUTO'
            ELSE 'VALIDO'
        END as stato_token
    FROM users u
    LEFT JOIN activation_tokens at ON u.id = at.user_id
    WHERE u.username = ? OR u.email = ?
    ORDER BY at.id DESC
    LIMIT 1
";

$result = $db->queryOne($sql, [$search, $search]);

if (!$result) {
    echo "❌ Utente non trovato: $search\n";
    exit(1);
}

echo "=== INFORMAZIONI UTENTE ===\n\n";
echo "Username: {$result['username']}\n";
echo "Email: {$result['email']}\n";
echo "Account Attivo: " . ($result['active'] ? '✓ Sì' : '✗ No (da attivare)') . "\n";
echo "\n";

if ($result['activation_code']) {
    echo "=== TOKEN ATTIVAZIONE ===\n\n";
    echo "Codice Attivazione: {$result['activation_code']}\n";
    echo "Stato: {$result['stato_token']}\n";
    echo "Creato: {$result['created_at']}\n";
    echo "Scade: {$result['expires_at']}\n";
    
    if ($result['used']) {
        echo "\n⚠️ Token già utilizzato\n";
    } elseif ($result['stato_token'] === 'SCADUTO') {
        echo "\n⚠️ Token scaduto - contatta l'amministratore per un nuovo token\n";
    } else {
        echo "\n✓ Token valido!\n";
        echo "\nLink di attivazione:\n";
        echo APP_URL . "/activate.php?token={$result['url_token']}\n";
        echo "\nCodice da inserire nella pagina: {$result['activation_code']}\n";
    }
} else {
    echo "⚠️ Nessun token trovato per questo utente\n";
    if ($result['active']) {
        echo "Account già attivo - non serve token\n";
    }
}