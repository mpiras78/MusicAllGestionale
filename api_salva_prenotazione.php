<?php
/**
 * API Salva Prenotazione Rapida
 * Crea un nuovo evento nella tabella eventi_calendario
 */

require_once 'includes/bootstrap.php';

header('Content-Type: application/json');

// Autenticazione richiesta
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

// Verifica CSRF
if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token CSRF non valido']);
    exit;
}

// Rate limiting
$rateLimiter = new RateLimiter();
$rateLimiter->enforce($_SERVER['REMOTE_ADDR'], 'api');

try {
    $db = Database::getInstance()->getConnection();
    
    // Leggi dati POST
    $tipologia_id = $_POST['tipologia_id'] ?? null;
    $aula_id = $_POST['aula_id'] ?? null;
    $ora_inizio = $_POST['ora_inizio'] ?? null;
    $durata = (int)($_POST['durata'] ?? 60);
    $data = $_POST['data'] ?? null;
    $giorno = $_POST['giorno'] ?? null;
    $titolo = $_POST['titolo'] ?? null;
    $note = $_POST['note'] ?? null;
    
    // Validazione input
    $validator = new InputValidator();
    $validator
        ->integer($tipologia_id, 'tipologia_id', 1)
        ->integer($aula_id, 'aula_id', 1)
        ->time($ora_inizio, 'ora_inizio')
        ->integer($durata, 'durata', 15, 240)
        ->date($data, 'data')
        ->inList($giorno, 'giorno', ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato'])
        ->validate();
    
    // Calcola ora_fine in base alla durata
    $ora_fine = date('H:i:s', strtotime($ora_inizio) + ($durata * 60));
    
    // Recupera codice tipologia per determinare chi prenota
    $stmt = $db->prepare("SELECT codice, categoria FROM tipologie_evento WHERE id = ?");
    $stmt->execute([$tipologia_id]);
    $tipologia = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tipologia) {
        throw new Exception('Tipologia non trovata');
    }
    
    // Determina chi prenota in base al codice tipologia
    $allievo_id = null;
    $docente_id = null;
    $socio_occasionale_id = null;
    
    if ($tipologia['codice'] === 'PREN_SALA_ALLIEVI') {
        $allievo_id = $_POST['allievo_id_pren'] ?? $_POST['allievo_id'] ?? null;
        if (!$allievo_id) {
            throw new Exception('Allievo non specificato');
        }
    } elseif ($tipologia['codice'] === 'PREN_DOCENTE') {
        $docente_id = $_POST['docente_id_pren'] ?? $_POST['docente_id'] ?? null;
        if (!$docente_id) {
            throw new Exception('Docente non specificato');
        }
    } elseif ($tipologia['codice'] === 'PREN_ESTERNO') {
        // Prenotazione Esterno: crea o usa socio esistente
        $nome_esterno = trim($_POST['nome_esterno'] ?? '');
        $cognome_esterno = trim($_POST['cognome_esterno'] ?? '');
        $email_esterno = trim($_POST['email_esterno'] ?? '');
        $telefono_esterno = trim($_POST['telefono_esterno'] ?? '');
        
        if (empty($nome_esterno) || empty($cognome_esterno)) {
            throw new Exception('Nome e cognome del socio esterno sono obbligatori');
        }
        
        // Cerca se socio già esiste (per email o nome+cognome)
        if (!empty($email_esterno)) {
            $stmt = $db->prepare("SELECT id FROM soci_esterni WHERE email = ? AND attivo = 1 LIMIT 1");
            $stmt->execute([$email_esterno]);
            $socio_esistente = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($socio_esistente) {
                $socio_occasionale_id = $socio_esistente['id'];
            }
        }
        
        // Se non trovato, crea nuovo socio
        if (!$socio_occasionale_id) {
            $stmt = $db->prepare("
                INSERT INTO soci_esterni (nome, cognome, email, telefono, attivo, created_at)
                VALUES (?, ?, ?, ?, 1, datetime('now', 'localtime'))
            ");
            $stmt->execute([$nome_esterno, $cognome_esterno, $email_esterno ?: null, $telefono_esterno ?: null]);
            $socio_occasionale_id = $db->lastInsertId();
        }
    }
    
    // Verifica conflitti nella stessa aula/orario
    $stmt = $db->prepare("
        SELECT COUNT(*) as cnt 
        FROM eventi_calendario 
        WHERE aula_id = ? 
        AND data_evento = ? 
        AND ora_inizio < ? 
        AND ora_fine > ? 
        AND attivo = 1
    ");
    $stmt->execute([$aula_id, $data, $ora_fine, $ora_inizio]);
    $conflitto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($conflitto['cnt'] > 0) {
        throw new Exception('Slot già occupato in questo orario');
    }
    
    // Inserisci evento
    $sql = "INSERT INTO eventi_calendario (
        tipologia_id,
        ricorrente,
        data_evento,
        ora_inizio,
        ora_fine,
        aula_id,
        allievo_id,
        docente_id,
        socio_occasionale_id,
        titolo,
        note,
        attivo,
        confermato,
        created_by,
        created_at
    ) VALUES (
        ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, ?, datetime('now','localtime')
    )";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $tipologia_id,
        $data,
        $ora_inizio,
        $ora_fine,
        $aula_id,
        $allievo_id,
        $docente_id,
        $socio_occasionale_id,
        $titolo,
        $note,
        $_SESSION['user_id']
    ]);
    
    $evento_id = $db->lastInsertId();
    
    // Se prenotazione a pagamento, crea record pagamento
    if ($tipologia['codice'] === 'PREN_DOCENTE' || $tipologia['codice'] === 'PREN_ESTERNO') {
        // Recupera prezzo orario
        $stmt = $db->prepare("
            SELECT prezzo_orario 
            FROM listini_prezzi 
            WHERE tipologia_id = ? 
            AND attivo = 1 
            AND data_inizio_validita <= ?
            AND (data_fine_validita IS NULL OR data_fine_validita >= ?)
            ORDER BY data_inizio_validita DESC
            LIMIT 1
        ");
        $stmt->execute([$tipologia_id, $data, $data]);
        $listino = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($listino && $listino['prezzo_orario'] > 0) {
            $ore = $durata / 60;
            $importo = $listino['prezzo_orario'] * $ore;
            
            $sql_pag = "INSERT INTO pagamenti (
                tipo,
                evento_id,
                allievo_id,
                socio_occasionale_id,
                importo_totale,
                importo_pagato,
                importo_residuo,
                stato,
                data_emissione,
                created_by,
                created_at
            ) VALUES (
                'prenotazione_sala', ?, ?, ?, ?, 0, ?, 'da_pagare', ?, ?, datetime('now','localtime')
            )";
            
            $stmt_pag = $db->prepare($sql_pag);
            $stmt_pag->execute([
                $evento_id,
                $allievo_id,
                $socio_occasionale_id,
                $importo,
                $importo,
                $data,
                $_SESSION['user_id']
            ]);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Prenotazione creata con successo',
        'evento_id' => $evento_id
    ]);
    
} catch (ValidationException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Errori di validazione',
        'errors' => $e->getErrors()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}