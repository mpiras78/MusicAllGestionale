<?php
require_once 'includes/bootstrap.php';

use MusicAll\Models\TipologiaEvento;

// Simula login
$_SESSION['user_id'] = 1;

echo "=== TEST API TIPOLOGIE ===\n\n";

try {
    $tipologie = TipologiaEvento::where('attivo', true)
        ->orderBy('categoria')
        ->orderBy('nome')
        ->get();
    
    echo "Trovate " . count($tipologie) . " tipologie attive:\n\n";
    
    foreach ($tipologie as $tip) {
        echo "ID: {$tip->id}\n";
        echo "Codice: {$tip->codice}\n";
        echo "Nome: {$tip->nome}\n";
        echo "Categoria: {$tip->categoria}\n";
        echo "Attiva: {$tip->attiva}\n";
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "ERRORE: " . $e->getMessage() . "\n";
}