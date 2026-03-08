<?php
/**
 * Script per generare insert_lezioni.sql dal file Excel
 * Associa automaticamente soci (ex-allievi) a docenti/materie basandosi su:
 * - Giorno della settimana
 * - Orario
 * - Sala
 * - Mappatura orari_docenti.md
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Mappatura giorno italiano -> inglese
$giorni_map = [
    'LUNEDI' => 'lunedi',
    'MARTEDI' => 'martedi',
    'MERCOLEDI' => 'mercoledi',
    'GIOVEDI' => 'giovedi',
    'VENERDI' => 'venerdi',
    'SABATO' => 'sabato'
];

// Mappatura Docenti per Giorno/Sala/Fascia Oraria (da orari_docenti.md)
$orari_docenti = [
    'lunedi' => [
        'AULA MIDI' => [
            ['start' => '11:00', 'end' => '13:00', 'docente' => 'TESSITORE', 'materia' => 'Chitarra'],
            ['start' => '15:00', 'end' => '22:00', 'docente' => 'LORITO', 'materia' => 'Canto Pop']
        ],
        'AULA PIANO' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'CICCARELLI', 'materia' => 'Pianoforte']
        ],
        'AULA MAGNA' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'BUONO', 'materia' => 'Canto Pop']
        ],
        'SALA JAZZ' => [
            ['start' => '16:00', 'end' => '18:15', 'docente' => 'URSINI', 'materia' => 'Chitarra'],
            ['start' => '18:15', 'end' => '22:00', 'docente' => 'PICCININI', 'materia' => 'Chitarra']
        ],
        'SALA POP' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'ALBERINI', 'materia' => 'Batteria']
        ],
        'SALA ROCK' => [
            ['start' => '14:00', 'end' => '21:00', 'docente' => 'SALVUCCI', 'materia' => 'Canto Pop'],
            ['start' => '21:00', 'end' => '22:00', 'docente' => 'PICCININI', 'materia' => 'Musica d\'Insieme', 'lab' => 'SWAPPING']
        ]
    ],
    'martedi' => [
        'AULA MIDI' => [
            ['start' => '09:15', 'end' => '13:00', 'docente' => 'TESSITORE', 'materia' => 'Chitarra'],
            ['start' => '13:00', 'end' => '22:00', 'docente' => 'MARCANTE', 'materia' => 'Chitarra']
        ],
        'AULA PIANO' => [
            ['start' => '09:15', 'end' => '12:15', 'docente' => 'SALVUCCI', 'materia' => 'Canto Pop'],
            ['start' => '16:00', 'end' => '22:00', 'docente' => 'PACCHIAROTTI', 'materia' => 'Piano e Canto']
        ],
        'AULA MAGNA' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'DI CRESCE', 'materia' => 'Canto Pop']
        ],
        'SALA JAZZ' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'ALBERINI', 'materia' => 'Batteria']
        ],
        'SALA POP' => [
            ['start' => '09:15', 'end' => '12:00', 'docente' => 'BUONO', 'materia' => 'Canto Pop'],
            ['start' => '12:00', 'end' => '22:00', 'docente' => 'LORITO', 'materia' => 'Canto Pop']
        ],
        'SALA ROCK' => [
            ['start' => '09:15', 'end' => '18:15', 'docente' => 'DI GIORGIO', 'materia' => 'Canto Metal'],
            ['start' => '18:15', 'end' => '22:00', 'docente' => 'MARCANTE', 'materia' => 'Musica d\'Insieme', 'lab' => 'I fuori tempo']
        ]
    ],
    'mercoledi' => [
        'AULA MIDI' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'PITINI', 'materia' => 'Canto Pop']
        ],
        'AULA PIANO' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'LABANCA', 'materia' => 'Pianoforte']
        ],
        'AULA MAGNA' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'LORITO', 'materia' => 'Canto Pop']
        ],
        'SALA JAZZ' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'URSINI', 'materia' => 'Chitarra']
        ],
        'SALA POP' => [
            ['start' => '15:00', 'end' => '17:30', 'docente' => 'DI GIORGIO', 'materia' => 'Canto Metal'],
            ['start' => '17:30', 'end' => '18:15', 'docente' => 'URSINI', 'materia' => 'Musica d\'Insieme', 'lab' => 'Baby'],
            ['start' => '18:15', 'end' => '22:00', 'docente' => 'NICASTRI', 'materia' => 'Batteria']
        ],
        'SALA ROCK' => [
            ['start' => '09:15', 'end' => '12:30', 'docente' => 'DI GIORGIO', 'materia' => 'Canto Metal'],
            ['start' => '12:30', 'end' => '22:00', 'docente' => 'BONIOLI', 'materia' => 'Batteria']
        ]
    ],
    'giovedi' => [
        'AULA MIDI' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'MARCANTE', 'materia' => 'Chitarra']
        ],
        'AULA PIANO' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'LABANCA', 'materia' => 'Pianoforte']
        ],
        'AULA MAGNA' => [
            ['start' => '09:15', 'end' => '12:15', 'docente' => 'SCARTONI', 'materia' => 'Chitarra'],
            ['start' => '12:15', 'end' => '22:00', 'docente' => 'CICCARELLI', 'materia' => 'Pianoforte']
        ],
        'SALA JAZZ' => [
            ['start' => '14:00', 'end' => '15:00', 'docente' => 'ALIEN', 'materia' => 'Beatbox'],
            ['start' => '16:00', 'end' => '22:00', 'docente' => 'DE CARLI', 'materia' => 'Batteria']
        ],
        'SALA POP' => [
            ['start' => '09:15', 'end' => '12:30', 'docente' => 'BUONO', 'materia' => 'Canto Pop'],
            ['start' => '15:00', 'end' => '22:00', 'docente' => 'SABA', 'materia' => 'Basso']
        ],
        'SALA ROCK' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'SALVUCCI', 'materia' => 'Canto Pop']
        ]
    ],
    'venerdi' => [
        'AULA MIDI' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'PITINI', 'materia' => 'Canto Pop']
        ],
        'AULA PIANO' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'D\'AURIA', 'materia' => 'Violino']
        ],
        'AULA MAGNA' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'CICCARELLI', 'materia' => 'Pianoforte']
        ],
        'SALA JAZZ' => [
            ['start' => '09:15', 'end' => '17:45', 'docente' => 'OMBRES', 'materia' => 'Batteria'],
            ['start' => '17:45', 'end' => '22:00', 'docente' => 'ZINO', 'materia' => 'Canto Pop']
        ],
        'SALA POP' => [
            ['start' => '09:15', 'end' => '13:00', 'docente' => 'BUONO', 'materia' => 'Canto Pop'],
            ['start' => '13:00', 'end' => '17:45', 'docente' => 'ZINO', 'materia' => 'Canto Pop'],
            ['start' => '17:45', 'end' => '22:00', 'docente' => 'URSINI', 'materia' => 'Chitarra']
        ],
        'SALA ROCK' => [
            ['start' => '09:15', 'end' => '17:45', 'docente' => 'URSINI', 'materia' => 'Chitarra'],
            ['start' => '17:45', 'end' => '22:00', 'docente' => 'OMBRES', 'materia' => 'Batteria']
        ]
    ],
    'sabato' => [
        'AULA MIDI' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'PITINI', 'materia' => 'Canto Pop']
        ],
        'AULA PIANO' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'PACCHIAROTTI', 'materia' => 'Piano e Canto']
        ],
        'AULA MAGNA' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'MENCHERINI', 'materia' => 'Rap e Beatmaking']
        ],
        'SALA JAZZ' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'OMBRES', 'materia' => 'Batteria']
        ],
        'SALA POP' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'PICCININI', 'materia' => 'Chitarra']
        ],
        'SALA ROCK' => [
            ['start' => '09:15', 'end' => '22:00', 'docente' => 'FERILLI', 'materia' => 'Basso']
        ]
    ]
];

function findDocente($giorno, $ora, $sala, $orari_docenti) {
    if (!isset($orari_docenti[$giorno][$sala])) {
        return null;
    }
    
    $orario_time = strtotime("2000-01-01 " . $ora);
    
    foreach ($orari_docenti[$giorno][$sala] as $fascia) {
        $start_time = strtotime("2000-01-01 " . $fascia['start']);
        $end_time = strtotime("2000-01-01 " . $fascia['end']);
        
        if ($orario_time >= $start_time && $orario_time < $end_time) {
            return $fascia;
        }
    }
    
    return null;
}

echo "===========================================\n";
echo "  Generazione insert_lezioni.sql\n";
echo "===========================================\n\n";

$file = __DIR__ . '/../template/Orario Soci MusicAll.xlsx';

if (!file_exists($file)) {
    die("ERRORE: File Excel non trovato: $file\n");
}

echo "📄 Caricamento file Excel...\n";
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();

$lezioni = [];
$stats = [
    'totale' => 0,
    'associati' => 0,
    'non_associati' => 0,
    'laboratori' => 0
];

echo "📊 Analisi lezioni...\n\n";

// Analizza ogni riga (skip header)
foreach ($sheet->getRowIterator(2) as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    
    $data = [];
    foreach ($cellIterator as $cell) {
        $data[] = $cell->getValue();
    }
    
    if (empty($data[0])) continue; // Skip righe vuote
    
    $giorno_ita = strtoupper(trim($data[0]));
    $sala = strtoupper(trim($data[1]));
    $orario = trim($data[2]);
    $allievo_raw = trim($data[3]);
    
    if (empty($allievo_raw) || $allievo_raw === '-') continue;
    
    // Parse orario
    if (preg_match('/(\d{2}:\d{2})-(\d{2}:\d{2})/', $orario, $matches)) {
        $ora_inizio = $matches[1];
        $ora_fine = $matches[2];
    } else {
        continue;
    }
    
    // Parse allievo
    $parts = preg_split('/\s+/', $allievo_raw, 2);
    $cognome = $parts[0];
    $nome = isset($parts[1]) ? $parts[1] : '';
    
    // Converti giorno
    $giorno = $giorni_map[$giorno_ita] ?? null;
    if (!$giorno) continue;
    
    // Trova docente/materia
    $docente_info = findDocente($giorno, $ora_inizio, $sala, $orari_docenti);
    
    $stats['totale']++;
    
    if ($docente_info) {
        $stats['associati']++;
        if (isset($docente_info['lab'])) {
            $stats['laboratori']++;
        }
        
        $lezioni[] = [
            'giorno' => $giorno,
            'sala' => $sala,
            'ora_inizio' => $ora_inizio,
            'ora_fine' => $ora_fine,
            'allievo_cognome' => $cognome,
            'allievo_nome' => $nome,
            'docente' => $docente_info['docente'],
            'materia' => $docente_info['materia'],
            'is_lab' => isset($docente_info['lab']),
            'lab_name' => $docente_info['lab'] ?? null
        ];
    } else {
        $stats['non_associati']++;
        echo "⚠️  Non associato: $giorno_ita $sala $orario $allievo_raw\n";
    }
}

echo "\n📊 Statistiche:\n";
echo "  Totale lezioni: {$stats['totale']}\n";
echo "  Associati: {$stats['associati']}\n";
echo "  Laboratori: {$stats['laboratori']}\n";
echo "  Non associati: {$stats['non_associati']}\n\n";

// Genera SQL
echo "📝 Generazione SQL...\n";

$sql = "-- ============================================\n";
$sql .= "-- INSERT LEZIONI - MusicAll\n";
$sql .= "-- Generato automaticamente da Excel\n";
$sql .= "-- Data: " . date('Y-m-d H:i:s') . "\n";
$sql .= "-- ============================================\n\n";
$sql .= "-- NOTA: Eseguire DOPO aver importato soci e docenti\n\n";

foreach ($lezioni as $lez) {
    $tipo = $lez['is_lab'] ? 'laboratorio' : 'regolare';
    $note = $lez['is_lab'] ? "Laboratorio: {$lez['lab_name']}" : '';
    
    $sql .= "INSERT INTO lezioni (allievo_id, docente_id, materia_id, aula_id, giorno_settimana, ora_inizio, ora_fine, tipo, note, attiva)\n";
    $sql .= "SELECT \n";
    $sql .= "    (SELECT s.id FROM soci s JOIN persone p ON s.persona_id = p.id WHERE p.cognome='{$lez['allievo_cognome']}' AND p.nome='{$lez['allievo_nome']}' LIMIT 1),\n";
    $sql .= "    (SELECT id FROM docenti WHERE cognome='{$lez['docente']}' LIMIT 1),\n";
    $sql .= "    (SELECT id FROM materie WHERE nome='{$lez['materia']}' LIMIT 1),\n";
    $sql .= "    (SELECT id FROM aule WHERE nome='{$lez['sala']}' LIMIT 1),\n";
    $sql .= "    '{$lez['giorno']}',\n";
    $sql .= "    '{$lez['ora_inizio']}',\n";
    $sql .= "    '{$lez['ora_fine']}',\n";
    $sql .= "    '$tipo',\n";
    $sql .= "    " . ($note ? "'$note'" : "NULL") . ",\n";
    $sql .= "    1;\n\n";
}

$sql .= "-- ============================================\n";
$sql .= "-- STATISTICHE\n";
$sql .= "-- ============================================\n";
$sql .= "-- Totale lezioni inserite: {$stats['associati']}\n";
$sql .= "-- Laboratori: {$stats['laboratori']}\n";
$sql .= "-- ============================================\n";

$output_file = __DIR__ . '/../database/insert_lezioni.sql';
file_put_contents($output_file, $sql);

echo "✅ File generato: database/insert_lezioni.sql\n";
echo "✅ Lezioni SQL: {$stats['associati']}\n\n";

echo "===========================================\n";
echo "  Completato!\n";
echo "===========================================\n";