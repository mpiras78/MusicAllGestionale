<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/bootstrap.php';

// Simula POST request
$_SERVER['REQUEST_METHOD'] = 'POST';

// Dati di test - modifica con un ID lezione valido dal tuo DB
$test_data = [
    'lezione_id' => 1,  // MODIFICA: usa un ID lezione esistente
    'data_lezione' => '2026-02-10',  // MODIFICA: usa una data valida
    'causale' => 'allievo',
    'note' => 'Test assenza da debug'
];

echo "=== TEST SALVATAGGIO ASSENZA ===\n\n";
echo "Dati in input:\n";
print_r($test_data);
echo "\n";

try {
    // Simula JSON input
    $_POST = $test_data;
    
    // Test 1: Verifica LezioniController
    echo "1. Test LezioniController...\n";
    $lezioniCtrl = new LezioniController();
    $lezione = $lezioniCtrl->getLezioneById($test_data['lezione_id']);
    
    if (!$lezione) {
        die("ERRORE: Lezione ID {$test_data['lezione_id']} non trovata!\n");
    }
    
    echo "   ✓ Lezione trovata:\n";
    print_r($lezione);
    echo "\n";
    
    // Test 2: Verifica AssenzeController
    echo "2. Test AssenzeController...\n";
    $assenzeCtrl = new AssenzeController();
    
    // Verifica se metodo exists
    if (!method_exists($assenzeCtrl, 'creaAssenza')) {
        die("ERRORE: Metodo creaAssenza non esiste!\n");
    }
    echo "   ✓ Metodo creaAssenza esiste\n\n";
    
    // Test 3: Verifica check duplicati
    echo "3. Check duplicati...\n";
    $db = Database::getInstance();
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM assenze 
        WHERE lezione_id = ? AND data_assenza = ?
    ");
    $stmt->execute([$test_data['lezione_id'], $test_data['data_lezione']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        echo "   ! Assenza già esistente (eliminarla per testare?)\n\n";
    } else {
        echo "   ✓ Nessun duplicato trovato\n\n";
    }
    
    // Test 4: Prova creazione assenza
    echo "4. Creazione assenza...\n";
    $assenza_data = [
        'allievo_id' => $lezione['allievo_id'],
        'lezione_id' => $test_data['lezione_id'],
        'data_assenza' => $test_data['data_lezione'],
        'causale' => $test_data['causale'],
        'richiede_recupero' => ($test_data['causale'] === 'docente') ? 1 : 0,
        'note' => $test_data['note']
    ];
    
    echo "   Dati assenza:\n";
    print_r($assenza_data);
    echo "\n";
    
    $assenza_id = $assenzeCtrl->creaAssenza($assenza_data);
    
    if (!$assenza_id) {
        die("ERRORE: creaAssenza ha restituito false/null\n");
    }
    
    echo "   ✓ Assenza creata con ID: $assenza_id\n\n";
    
    // Test 5: Se causata da docente, test recupero
    if ($test_data['causale'] === 'docente') {
        echo "5. Test creazione recupero...\n";
        
        if (!class_exists('RecuperiController')) {
            echo "   ! Classe RecuperiController non esiste\n";
        } else {
            $recuperiCtrl = new RecuperiController();
            
            if (!method_exists($recuperiCtrl, 'creaRecuperoDaAssenza')) {
                echo "   ! Metodo creaRecuperoDaAssenza non esiste\n";
            } else {
                try {
                    $recuperiCtrl->creaRecuperoDaAssenza($assenza_id);
                    echo "   ✓ Recupero creato\n";
                } catch (Exception $e) {
                    echo "   ! Errore creazione recupero: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\n=== TEST COMPLETATO CON SUCCESSO ===\n";
    
} catch (Exception $e) {
    echo "\n=== ERRORE EXCEPTION ===\n";
    echo "Messaggio: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Linea: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "\n=== ERRORE PHP ===\n";
    echo "Messaggio: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Linea: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}