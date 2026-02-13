<?php
require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== INSERIMENTO TIPOLOGIE PRENOTAZIONI ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Verifica quali tipologie esistono già
    $stmt = $db->query("SELECT codice FROM tipologie_evento WHERE categoria = 'prenotazione'");
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tipologie prenotazione esistenti: " . count($existing) . "\n";
    if (count($existing) > 0) {
        echo "  - " . implode("\n  - ", $existing) . "\n";
    }
    echo "\n";
    
    // Definisci le 3 tipologie di prenotazione
    $tipologie = [
        [
            'categoria' => 'prenotazione',
            'codice' => 'PREN_SALA_ALLIEVI',
            'nome' => 'Prenotazione Sala Allievi',
            'descrizione' => 'Prenotazione sala per allievi iscritti (gratuita)',
            'colore_bg' => '#e8f5e9',
            'colore_border' => '#4caf50',
            'icona' => 'bi-door-open',
            'ordine' => 11
        ],
        [
            'categoria' => 'prenotazione',
            'codice' => 'PREN_DOCENTE',
            'nome' => 'Prenotazione Docente',
            'descrizione' => 'Prenotazione sala da parte di docenti',
            'colore_bg' => '#fff9c4',
            'colore_border' => '#fdd835',
            'icona' => 'bi-person-badge',
            'ordine' => 12
        ],
        [
            'categoria' => 'prenotazione',
            'codice' => 'PREN_ESTERNO',
            'nome' => 'Prenotazione Esterno',
            'descrizione' => 'Prenotazione sala da soci occasionali/esterni',
            'colore_bg' => '#ffebee',
            'colore_border' => '#ef5350',
            'icona' => 'bi-calendar-event',
            'ordine' => 13
        ]
    ];
    
    $inserted = 0;
    $skipped = 0;
    
    foreach ($tipologie as $tip) {
        // Verifica se esiste già
        if (in_array($tip['codice'], $existing)) {
            echo "⏭️  SKIP: {$tip['codice']} già esistente\n";
            $skipped++;
            continue;
        }
        
        // Inserisci
        $sql = "INSERT INTO tipologie_evento (
            categoria, codice, nome, descrizione, 
            colore_bg, colore_border, icona, 
            attiva, ordine_visualizzazione
        ) VALUES (
            :categoria, :codice, :nome, :descrizione,
            :colore_bg, :colore_border, :icona,
            1, :ordine
        )";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':categoria' => $tip['categoria'],
            ':codice' => $tip['codice'],
            ':nome' => $tip['nome'],
            ':descrizione' => $tip['descrizione'],
            ':colore_bg' => $tip['colore_bg'],
            ':colore_border' => $tip['colore_border'],
            ':icona' => $tip['icona'],
            ':ordine' => $tip['ordine']
        ]);
        
        echo "✅ INSERITA: {$tip['codice']} - {$tip['nome']}\n";
        $inserted++;
    }
    
    echo "\n=== RIEPILOGO ===\n";
    echo "Inserite: $inserted\n";
    echo "Saltate (già esistenti): $skipped\n";
    
    // Verifica finale
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM tipologie_evento WHERE categoria = 'prenotazione'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nTotale tipologie prenotazione in DB: {$row['cnt']}\n";
    
    echo "\n✅ Operazione completata con successo!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRORE: " . $e->getMessage() . "\n";
    exit(1);
}