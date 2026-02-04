<?php
/**
 * Analizza file Excel e estrae allievi e docenti
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$excelFile = __DIR__ . '/../template/Orario Allievi MusicAll.xlsx';

if (!file_exists($excelFile)) {
    echo "❌ File non trovato: $excelFile\n";
    exit(1);
}

echo "========================================\n";
echo "ANALISI FILE EXCEL\n";
echo "========================================\n";
echo "File: $excelFile\n\n";

try {
    $spreadsheet = IOFactory::load($excelFile);
    $sheet = $spreadsheet->getActiveSheet();
    
    // Array per raccogliere dati
    $allievi = [];
    $docenti = [];
    $lezioni = [];
    
    // Leggi header (prima riga)
    $headers = [];
    $highestColumn = $sheet->getHighestColumn();
    $highestRow = $sheet->getHighestRow();
    
    echo "Righe: $highestRow\n";
    echo "Colonne: $highestColumn\n\n";
    
    // Leggi header dalla riga 1
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $value = $sheet->getCell($col . '1')->getValue();
        if ($value) {
            $headers[$col] = trim($value);
        }
    }
    
    echo "HEADER TROVATI:\n";
    foreach ($headers as $col => $header) {
        echo "  $col: $header\n";
    }
    echo "\n";
    
    // Processa le righe (dalla 2 in poi)
    for ($row = 2; $row <= $highestRow; $row++) {
        $rowData = [];
        
        foreach ($headers as $col => $header) {
            $value = $sheet->getCell($col . $row)->getValue();
            $rowData[$header] = $value;
        }
        
        // Salta righe vuote
        if (empty(array_filter($rowData))) {
            continue;
        }
        
        // Estrai nome allievo (di solito prima colonna significativa)
        $nomeAllievo = null;
        $nomeDocente = null;
        
        // Cerca colonne con nomi comuni
        foreach ($rowData as $key => $value) {
            $keyLower = strtolower($key);
            
            // Cerca allievo
            if (stripos($keyLower, 'allievo') !== false || 
                stripos($keyLower, 'nome') !== false ||
                stripos($keyLower, 'studente') !== false) {
                if ($value && !$nomeAllievo) {
                    $nomeAllievo = trim($value);
                }
            }
            
            // Cerca docente
            if (stripos($keyLower, 'docente') !== false || 
                stripos($keyLower, 'insegnante') !== false ||
                stripos($keyLower, 'maestro') !== false ||
                stripos($keyLower, 'prof') !== false) {
                if ($value) {
                    $nomeDocente = trim($value);
                }
            }
        }
        
        // Aggiungi allievo
        if ($nomeAllievo && !in_array($nomeAllievo, $allievi)) {
            $allievi[] = $nomeAllievo;
        }
        
        // Aggiungi docente
        if ($nomeDocente && !in_array($nomeDocente, $docenti)) {
            $docenti[] = $nomeDocente;
        }
        
        $lezioni[] = $rowData;
    }
    
    // Ordina per nome
    sort($allievi);
    sort($docenti);
    
    echo "========================================\n";
    echo "RISULTATI ESTRAZIONE\n";
    echo "========================================\n\n";
    
    echo "ALLIEVI TROVATI: " . count($allievi) . "\n";
    echo "----------------------------------------\n";
    foreach ($allievi as $i => $allievo) {
        echo ($i + 1) . ". $allievo\n";
    }
    
    echo "\n========================================\n\n";
    
    echo "DOCENTI TROVATI: " . count($docenti) . "\n";
    echo "----------------------------------------\n";
    foreach ($docenti as $i => $docente) {
        echo ($i + 1) . ". $docente\n";
    }
    
    echo "\n========================================\n\n";
    
    echo "LEZIONI/RIGHE TOTALI: " . count($lezioni) . "\n\n";
    
    // Salva i dati in JSON per step successivi
    $output = [
        'allievi' => $allievi,
        'docenti' => $docenti,
        'lezioni' => $lezioni,
        'headers' => array_values($headers)
    ];
    
    file_put_contents(__DIR__ . '/excel_data.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "✅ Dati salvati in: tests/excel_data.json\n";
    
    // Mostra sample delle prime 3 lezioni per debug
    echo "\n========================================\n";
    echo "SAMPLE LEZIONI (prime 3 righe):\n";
    echo "========================================\n";
    
    for ($i = 0; $i < min(3, count($lezioni)); $i++) {
        echo "\nLezione " . ($i + 1) . ":\n";
        foreach ($lezioni[$i] as $key => $value) {
            if ($value) {
                echo "  $key: $value\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}