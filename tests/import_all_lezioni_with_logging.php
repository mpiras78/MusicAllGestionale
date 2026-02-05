<?php
/**
 * Import tutte le lezioni con logging separato per giorno
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance();

// Pulisco tabella
echo "🗑️  Cancello lezioni esistenti...\n";
$db->execute('DELETE FROM lezioni');

// Carico file SQL
$sqlFile = __DIR__ . '/../database/insert_lezioni_ALL_DAYS.sql';
if (!file_exists($sqlFile)) {
    die("❌ File non trovato: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);
$statements = explode(';', $sql);

$stats = [
    'lunedi' => ['ok' => 0, 'fail' => [], 'total' => 0],
    'martedi' => ['ok' => 0, 'fail' => [], 'total' => 0],
    'mercoledi' => ['ok' => 0, 'fail' => [], 'total' => 0],
    'giovedi' => ['ok' => 0, 'fail' => [], 'total' => 0],
    'venerdi' => ['ok' => 0, 'fail' => [], 'total' => 0],
    'sabato' => ['ok' => 0, 'fail' => [], 'total' => 0],
];

$currentDay = null;

foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    
    if (empty($stmt) || strpos($stmt, 'INSERT') === false) {
        // Detect giorno nei commenti
        if (preg_match('/LUNEDÌ|lunedi/i', $stmt)) $currentDay = 'lunedi';
        elseif (preg_match('/MARTEDÌ|martedi/i', $stmt)) $currentDay = 'martedi';
        elseif (preg_match('/MERCOLEDÌ|mercoledi/i', $stmt)) $currentDay = 'mercoledi';
        elseif (preg_match('/GIOVEDÌ|giovedi/i', $stmt)) $currentDay = 'giovedi';
        elseif (preg_match('/VENERDÌ|venerdi/i', $stmt)) $currentDay = 'venerdi';
        elseif (preg_match('/SABATO|sabato/i', $stmt)) $currentDay = 'sabato';
        continue;
    }
    
    // Detect giorno dalla query
    if (preg_match("/'(lunedi|martedi|mercoledi|giovedi|venerdi|sabato)'/i", $stmt, $m)) {
        $currentDay = strtolower($m[1]);
    }
    
    if (!$currentDay) continue;
    
    $stats[$currentDay]['total']++;
    
    try {
        $db->execute($stmt);
        $stats[$currentDay]['ok']++;
    } catch (Exception $e) {
        $stats[$currentDay]['fail'][] = $stmt;
    }
}

// Report
echo "\n📊 RISULTATI IMPORT:\n";
echo str_repeat('=', 60) . "\n";

$totalOk = 0;
$totalFail = 0;

foreach ($stats as $day => $data) {
    if ($data['total'] == 0) continue;
    
    $totalOk += $data['ok'];
    $totalFail += count($data['fail']);
    
    $icon = count($data['fail']) == 0 ? '✅' : '⚠️';
    echo sprintf(
        "%s %s: %d/%d importate (%.1f%%)\n",
        $icon,
        strtoupper($day),
        $data['ok'],
        $data['total'],
        ($data['ok'] / $data['total']) * 100
    );
    
    // Salva fallite
    if (count($data['fail']) > 0) {
        $failFile = __DIR__ . "/../database/inserimento_lezioni_fallite_$day.sql";
        $content = "-- =============================================\n";
        $content .= "-- LEZIONI FALLITE - " . strtoupper($day) . "\n";
        $content .= "-- Totale: " . count($data['fail']) . "\n";
        $content .= "-- =============================================\n\n";
        
        foreach ($data['fail'] as $failedStmt) {
            $content .= $failedStmt . ";\n\n";
        }
        
        file_put_contents($failFile, $content);
        echo "   📝 Fallite salvate in: inserimento_lezioni_fallite_$day.sql\n";
    }
}

echo str_repeat('=', 60) . "\n";
echo sprintf("✅ TOTALE IMPORTATE: %d\n", $totalOk);
echo sprintf("❌ TOTALE FALLITE: %d\n", $totalFail);
echo sprintf("🎯 TASSO SUCCESSO: %.1f%%\n", ($totalOk / ($totalOk + $totalFail)) * 100);

$dbTotal = $db->count('SELECT COUNT(*) FROM lezioni');
echo sprintf("\n📊 Lezioni nel database: %d\n", $dbTotal);