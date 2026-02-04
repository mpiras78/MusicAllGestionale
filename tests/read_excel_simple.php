<?php
/**
 * Legge file Excel usando ZipArchive (già disponibile in PHP)
 */

$excelFile = __DIR__ . '/../template/Orario Allievi MusicAll.xlsx';

if (!file_exists($excelFile)) {
    echo "❌ File non trovato: $excelFile\n";
    exit(1);
}

echo "========================================\n";
echo "ANALISI FILE EXCEL (metodo alternativo)\n";
echo "========================================\n";
echo "File: $excelFile\n\n";

// Verifica se è possibile aprire come ZIP
if (!class_exists('ZipArchive')) {
    echo "❌ ZipArchive non disponibile\n";
    echo "Provo metodo manuale...\n\n";
    
    // Leggi il file come testo e cerca pattern
    $content = file_get_contents($excelFile);
    
    // Cerca nomi (pattern comuni)
    $allievi = [];
    $docenti = [];
    
    // Pattern per nomi italiani (Cognome Nome)
    preg_match_all('/\b([A-Z][a-zàèéìòù]+)\s+([A-Z][a-zàèéìòù]+)\b/', $content, $matches);
    
    if (!empty($matches[0])) {
        echo "Nomi trovati nel file (potrebbero includere allievi e docenti):\n";
        $nomiTrovati = array_unique($matches[0]);
        sort($nomiTrovati);
        
        foreach ($nomiTrovati as $i => $nome) {
            echo ($i + 1) . ". $nome\n";
        }
        
        echo "\n⚠️  Nota: Questi sono tutti i nomi trovati nel file.\n";
        echo "Non posso distinguere automaticamente allievi da docenti senza leggere la struttura.\n\n";
    }
    
    echo "========================================\n";
    echo "SOLUZIONE ALTERNATIVA\n";
    echo "========================================\n\n";
    echo "Per procedere, ho bisogno che tu mi fornisca:\n\n";
    echo "1. L'elenco dei DOCENTI del file Excel\n";
    echo "2. Oppure una descrizione della struttura del file\n";
    echo "   (es. quali colonne contengono allievi, docenti, materie, ecc.)\n\n";
    
    exit(0);
}

try {
    $zip = new ZipArchive();
    
    if ($zip->open($excelFile) !== TRUE) {
        echo "❌ Impossibile aprire il file come ZIP\n";
        exit(1);
    }
    
    echo "✅ File Excel aperto come ZIP\n";
    echo "File contenuti: " . $zip->numFiles . "\n\n";
    
    // Cerca il file sharedStrings.xml che contiene tutti i testi
    $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
    
    if (!$sharedStringsXml) {
        echo "❌ sharedStrings.xml non trovato\n";
        exit(1);
    }
    
    // Parse XML
    $xml = simplexml_load_string($sharedStringsXml);
    
    if (!$xml) {
        echo "❌ Errore nel parsing XML\n";
        exit(1);
    }
    
    echo "✅ Trovate " . count($xml->si) . " stringhe nel file\n\n";
    
    // Estrai tutte le stringhe
    $stringhe = [];
    foreach ($xml->si as $si) {
        $text = (string)$si->t;
        if ($text) {
            $stringhe[] = trim($text);
        }
    }
    
    // Analizza le stringhe per trovare nomi
    $possibiliNomi = [];
    $possibiliDocenti = [];
    $altreStringhe = [];
    
    foreach ($stringhe as $str) {
        // Pattern nome italiano (Cognome Nome o Nome Cognome)
        if (preg_match('/^[A-ZÀÈÉÌÒÙ][a-zàèéìòù]+\s+[A-ZÀÈÉÌÒÙ][a-zàèéìòù]+$/', $str)) {
            $possibiliNomi[] = $str;
        }
        // Cerca indicatori docente
        else if (stripos($str, 'maestr') !== false || 
                 stripos($str, 'prof') !== false ||
                 stripos($str, 'docente') !== false) {
            $possibiliDocenti[] = $str;
        }
        else if (strlen($str) > 2 && strlen($str) < 50) {
            $altreStringhe[] = $str;
        }
    }
    
    echo "========================================\n";
    echo "ANALISI STRINGHE\n";
    echo "========================================\n\n";
    
    echo "POSSIBILI NOMI (pattern Cognome Nome): " . count($possibiliNomi) . "\n";
    echo "----------------------------------------\n";
    $possibiliNomi = array_unique($possibiliNomi);
    sort($possibiliNomi);
    foreach ($possibiliNomi as $i => $nome) {
        echo ($i + 1) . ". $nome\n";
    }
    
    echo "\n========================================\n\n";
    
    echo "ALTRE STRINGHE RILEVANTI (" . min(20, count($altreStringhe)) . " di " . count($altreStringhe) . "):\n";
    echo "----------------------------------------\n";
    $altreStringhe = array_unique($altreStringhe);
    sort($altreStringhe);
    foreach (array_slice($altreStringhe, 0, 20) as $i => $str) {
        echo ($i + 1) . ". $str\n";
    }
    
    // Salva dati grezzi
    $output = [
        'possibili_nomi' => $possibiliNomi,
        'altre_stringhe' => $altreStringhe,
        'totale_stringhe' => count($stringhe)
    ];
    
    file_put_contents(__DIR__ . '/excel_strings.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo "\n✅ Dati salvati in: tests/excel_strings.json\n";
    
    $zip->close();
    
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage() . "\n";
    exit(1);
}