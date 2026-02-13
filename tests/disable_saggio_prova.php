<?php
require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== DISABILITA TIPOLOGIE SAGGIO E PROVA ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Disabilita SAGGIO
    $stmt = $db->prepare("UPDATE tipologie_evento SET attiva = 0 WHERE codice = 'SAGGIO'");
    $stmt->execute();
    $rows1 = $stmt->rowCount();
    echo $rows1 > 0 ? "✅ SAGGIO disabilitato\n" : "⚠️  SAGGIO non trovato\n";
    
    // Disabilita PROVA
    $stmt = $db->prepare("UPDATE tipologie_evento SET attiva = 0 WHERE codice = 'PROVA'");
    $stmt->execute();
    $rows2 = $stmt->rowCount();
    echo $rows2 > 0 ? "✅ PROVA disabilitato\n" : "⚠️  PROVA non trovato\n";
    
    // Verifica tipologie attive
    echo "\n=== TIPOLOGIE ATTIVE RIMANENTI ===\n";
    $stmt = $db->query("SELECT codice, nome, categoria FROM tipologie_evento WHERE attiva = 1 ORDER BY categoria, nome");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("  %s - %s (%s)\n", $row['codice'], $row['nome'], $row['categoria']);
    }
    
    echo "\n✅ Operazione completata!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRORE: " . $e->getMessage() . "\n";
    exit(1);
}