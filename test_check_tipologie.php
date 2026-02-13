<?php
require_once 'includes/bootstrap.php';

echo "=== CHECK TABELLA TIPOLOGIE ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Conta totale
    $stmt = $db->query('SELECT COUNT(*) as cnt FROM tipologie_evento');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Totale tipologie in DB: " . $row['cnt'] . "\n\n";
    
    // Lista tutte
    $stmt = $db->query('SELECT id, codice, nome, categoria, attiva FROM tipologie_evento ORDER BY categoria, nome');
    echo "Elenco completo:\n";
    while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf(
            "ID:%d | Codice:%-25s | Nome:%-30s | Cat:%-15s | Attiva:%d\n",
            $r['id'],
            $r['codice'],
            $r['nome'],
            $r['categoria'],
            $r['attiva']
        );
    }
    
    // Se la tabella è vuota, verifica se lo schema è stato eseguito
    if ($row['cnt'] == 0) {
        echo "\n⚠️ ATTENZIONE: La tabella tipologie_evento è vuota!\n";
        echo "Verifica che lo schema database/schema_sqlite.sql sia stato eseguito.\n";
        echo "Lo schema include INSERT per 7 tipologie predefinite.\n";
    }
    
} catch (Exception $e) {
    echo "ERRORE: " . $e->getMessage() . "\n";
}