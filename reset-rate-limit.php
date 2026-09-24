#!/usr/bin/env php
<?php
/**
 * Reset Rate Limiter - Sblocca IP bloccato
 * Pulisce i record di rate limiting nel database
 */

require_once __DIR__ . '/includes/bootstrap.php';

echo "\n";
echo "╔═══════════════════════════════════════════════╗\n";
echo "║     🔓 MUSICALL - Reset Rate Limiter          ║\n";
echo "╚═══════════════════════════════════════════════╝\n";
echo "\n";

try {
    $db = Database::getInstance();
    
    // Verifica se tabella rate_limit_log esiste
    $tableCheck = $db->query(
        "SELECT name FROM sqlite_master WHERE type='table' AND name='rate_limit_log'"
    )->fetch();
    
    if (!$tableCheck) {
        echo "⚠️  Rate limit table doesn't exist yet\n";
        echo "   Skipping reset...\n";
        exit(0);
    }
    
    // Conta record nel rate_limit_log
    $countBefore = $db->query(
        "SELECT COUNT(*) as count FROM rate_limit_log"
    )->fetch();
    
    $countValue = $countBefore['count'] ?? 0;
    
    echo "📊 Rate Limit Status:\n";
    echo "   Total attempts logged: $countValue\n";
    echo "\n";
    
    // Pulisci tutti i record
    $db->execute("DELETE FROM rate_limit_log");
    
    echo "✅ Deleted all rate limit records\n";
    
    // Conta record nel rate_limit_locks se esiste
    $locksCheck = $db->query(
        "SELECT name FROM sqlite_master WHERE type='table' AND name='rate_limit_locks'"
    )->fetch();
    
    if ($locksCheck) {
        $countLocks = $db->query(
            "SELECT COUNT(*) as count FROM rate_limit_locks"
        )->fetch();
        
        $lockCount = $countLocks['count'] ?? 0;
        
        if ($lockCount > 0) {
            echo "✅ Deleted $lockCount IP lock(s)\n";
            $db->execute("DELETE FROM rate_limit_locks");
        }
    }
    
    // Conta record nel rate_limit_attempts se esiste
    $attemptsCheck = $db->query(
        "SELECT name FROM sqlite_master WHERE type='table' AND name='rate_limit_attempts'"
    )->fetch();
    
    if ($attemptsCheck) {
        $countAttempts = $db->query(
            "SELECT COUNT(*) as count FROM rate_limit_attempts"
        )->fetch();
        
        $attemptCount = $countAttempts['count'] ?? 0;
        
        if ($attemptCount > 0) {
            echo "✅ Deleted $attemptCount attempt record(s)\n";
            $db->execute("DELETE FROM rate_limit_attempts");
        }
    }
    
    echo "\n";
    echo "╔═══════════════════════════════════════════════╗\n";
    echo "║      ✅ RATE LIMITER RESET COMPLETE           ║\n";
    echo "╚═══════════════════════════════════════════════╝\n";
    echo "\n";
    echo "🌐 Prova il login ora:\n";
    echo "   http://localhost:8000/login.php\n";
    echo "\n";
    echo "👤 Credenziali:\n";
    echo "   Username: admin\n";
    echo "   Password: admin123\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);
?>
