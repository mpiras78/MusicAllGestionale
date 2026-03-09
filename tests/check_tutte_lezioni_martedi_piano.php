<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "=== TUTTE LE LEZIONI MARTEDÌ - AULA PIANO ===\n\n";

$stmt = $db->prepare("
        SELECT l.*,
            al.cognome || ' ' || al.nome as socio,
            d.cognome || ' ' || d.nome as docente,
            m.nome as materia
        FROM lezioni l
        LEFT JOIN soci s ON l.socio_id = s.id
        LEFT JOIN persone al ON s.persona_id = al.id
        LEFT JOIN docenti d ON l.docente_id = d.id
        LEFT JOIN materie m ON l.materia_id = m.id
    WHERE l.giorno_settimana = 'martedi'
    AND l.aula_id = 2
    ORDER BY l.ora_inizio
");
$stmt->execute();
$lezioni = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Lezioni trovate: " . count($lezioni) . "\n\n";

foreach ($lezioni as $lez) {
    echo "─────────────────────────────────\n";
    echo "ID: {$lez['id']}\n";
    echo "Socio: {$lez['socio']}\n";
    echo "Docente: {$lez['docente']}\n";
    echo "Materia: {$lez['materia']}\n";
    echo "Orario: {$lez['ora_inizio']} - {$lez['ora_fine']}\n";
    
    // Verifica assenza per 10 febbraio
    $stmt_ass = $db->prepare("
        SELECT * FROM assenze 
        WHERE lezione_id = ? 
        AND data_assenza = '2026-02-10'
    ");
    $stmt_ass->execute([$lez['id']]);
    $assenza = $stmt_ass->fetch(PDO::FETCH_ASSOC);
    
    if ($assenza) {
        echo "⚠️  ANNULLATA il 10 febbraio (Attiva: {$assenza['attiva']})\n";
    }
}

echo "─────────────────────────────────\n";
?>