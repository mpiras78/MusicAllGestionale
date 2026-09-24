<?php
/**
 * Debug Semplice: Recuperi vs Calendario
 * Senza dipendenze complesse
 */

require_once 'includes/bootstrap.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'segreteria'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$data_selezionata = $_GET['data'] ?? date('Y-m-d');

try {
    // 1. Recuperi nel DB per questa data
    $recuperi_db = $db->query("
        SELECT 
            r.id,
            r.data_recupero,
            r.ora_inizio,
            r.ora_fine,
            r.socio_id,
            r.aula_id,
            a.cognome || ' ' || a.nome as socio_nome,
            au.nome as aula_nome
        FROM recuperi r
        LEFT JOIN soci a ON r.socio_id = a.id
        LEFT JOIN aule au ON r.aula_id = au.id
        WHERE r.data_recupero = ?
        AND r.annullato = 0
        ORDER BY r.ora_inizio
    ", [$data_selezionata])->fetchAll(PDO::FETCH_ASSOC);

    // 2. evento_calendario di tipo recupero per questa data
    $eventi_cal = $db->query("
        SELECT 
            e.id,
            e.data_evento,
            e.ora_inizio,
            e.ora_fine,
            e.socio_id,
            e.aula_id,
            e.attivo,
            e.confermato,
            t.id as tipologia_id,
            t.codice as tipologia_codice,
            t.attiva as tipologia_attiva,
            a.cognome || ' ' || a.nome as socio_nome,
            au.nome as aula_nome
        FROM eventi_calendario e
        LEFT JOIN tipologie_evento t ON e.tipologia_id = t.id
        LEFT JOIN soci a ON e.socio_id = a.id
        LEFT JOIN aule au ON e.aula_id = au.id
        WHERE e.data_evento = ?
        AND (t.categoria = 'recupero' OR t.codice = 'LEZ_RECUPERO')
        ORDER BY e.ora_inizio
    ", [$data_selezionata])->fetchAll(PDO::FETCH_ASSOC);

    // 3. Verifica tipologia LEZ_RECUPERO
    $tipologia = $db->query("
        SELECT id, codice, nome, categoria, attiva 
        FROM tipologie_evento 
        WHERE codice = 'LEZ_RECUPERO'
    ")->fetch(PDO::FETCH_ASSOC);

    // 4. Query controller - cosa vede il calendario
    $controller_query = $db->prepare("
        SELECT 
            e.id,
            e.ora_inizio,
            e.ora_fine,
            e.aula_id,
            t.codice as tipo,
            t.nome as tipologia_nome,
            a.cognome || ' ' || a.nome as socio,
            au.nome as aula,
            e.note
        FROM eventi_calendario e
        INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
        LEFT JOIN soci a ON e.socio_id = a.id
        LEFT JOIN aule au ON e.aula_id = au.id
        WHERE e.data_evento = ?
        AND e.attivo = 1
        AND t.attiva = 1
        ORDER BY e.ora_inizio
    ");
    $controller_query->execute([$data_selezionata]);
    $controller_eventi = $controller_query->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Recuperi Calendario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .badge-lg { font-size: 1.2rem; padding: 0.5rem 0.8rem; }
        .table-sm td { padding: 0.4rem; }
        .mismatch { background-color: #ffe0e0; }
        .match { background-color: #e0ffe0; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid p-4">
        <div class="row mb-3">
            <div class="col">
                <h1><i class="bi bi-bug"></i> Debug: Recuperi nel Calendario</h1>
            </div>
        </div>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <strong>Errore PHP:</strong> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Date Selector -->
        <div class="row mb-3">
            <div class="col-md-4">
                <form method="GET" class="row g-2">
                    <div class="col">
                        <label class="form-label">Data:</label>
                        <input type="date" name="data" value="<?= $data_selezionata ?>" class="form-control">
                    </div>
                    <div class="col-auto align-self-end">
                        <button type="submit" class="btn btn-primary">Carica</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recuperi DB</h5>
                        <p class="display-6 mb-0">
                            <span class="badge badge-lg bg-primary"><?= count($recuperi_db) ?></span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">evento_calendario</h5>
                        <p class="display-6 mb-0">
                            <span class="badge badge-lg bg-success"><?= count($eventi_cal) ?></span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Controller</h5>
                        <p class="display-6 mb-0">
                            <span class="badge badge-lg bg-info"><?= count($controller_eventi) ?></span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Tipologia</h5>
                        <p class="display-6 mb-0">
                            <?php if ($tipologia): ?>
                                <span class="badge badge-lg <?= $tipologia['attiva'] ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $tipologia['attiva'] ? '✓ Attiva' : '✗ Disattiva' ?>
                                </span>
                            <?php else: ?>
                                <span class="badge badge-lg bg-danger">✗ NON ESISTE</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recuperi nel DB -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📦 Recuperi nel DB (tabella recuperi)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Socio</th>
                            <th>Ora</th>
                            <th>Aula</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recuperi_db) > 0): ?>
                            <?php foreach ($recuperi_db as $r): ?>
                            <tr>
                                <td><?= $r['id'] ?></td>
                                <td><?= $r['socio_nome'] ?? 'N/A' ?></td>
                                <td><?= $r['ora_inizio'] ?> - <?= $r['ora_fine'] ?></td>
                                <td><?= $r['aula_nome'] ?? 'N/A' ?></td>
                                <td>Recupero da assenza</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted">Nessun recupero per questa data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- evento_calendario -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">📅 evento_calendario (tipo recupero)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Socio</th>
                            <th>Ora</th>
                            <th>Aula</th>
                            <th>Attivo</th>
                            <th>Confermato</th>
                            <th>Tipologia Attiva</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($eventi_cal) > 0): ?>
                            <?php foreach ($eventi_cal as $e): ?>
                            <tr class="<?= (!$e['attivo'] || !$e['tipologia_attiva']) ? 'mismatch' : 'match' ?>">
                                <td><?= $e['id'] ?></td>
                                <td><?= $e['socio_nome'] ?? 'N/A' ?></td>
                                <td><?= $e['ora_inizio'] ?> - <?= $e['ora_fine'] ?></td>
                                <td><?= $e['aula_nome'] ?? 'N/A' ?></td>
                                <td><?= $e['attivo'] ? '✓' : '✗' ?></td>
                                <td><?= $e['confermato'] ? '✓' : '✗' ?></td>
                                <td><?= $e['tipologia_attiva'] ? '✓' : '✗' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted">Nessun evento calendario</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted small">
                Righe rosse = Non visibili (attivo=0 o tipologia non attiva)
            </div>
        </div>

        <!-- Visto dal Controller -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">👁️ Quello che Vede il Calendario (Controller)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Socio</th>
                            <th>Ora</th>
                            <th>Aula</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($controller_eventi) > 0): ?>
                            <?php foreach ($controller_eventi as $e): ?>
                            <tr>
                                <td><?= $e['id'] ?></td>
                                <td><?= $e['tipo'] ?></td>
                                <td><?= $e['socio'] ?></td>
                                <td><?= $e['ora_inizio'] ?> - <?= $e['ora_fine'] ?></td>
                                <td><?= $e['aula'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted">Nessun evento visibile</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Diagnostica -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🔍 Diagnostica</h5>
            </div>
            <div class="card-body">
                <h6>Tipologia LEZ_RECUPERO:</h6>
                <?php if ($tipologia): ?>
                    <ul class="mb-3">
                        <li>ID: <?= $tipologia['id'] ?></li>
                        <li>Codice: <?= $tipologia['codice'] ?></li>
                        <li>Nome: <?= $tipologia['nome'] ?></li>
                        <li>Categoria: <?= $tipologia['categoria'] ?></li>
                        <li>Attiva: <strong><?= $tipologia['attiva'] ? '✓ SÌ' : '✗ NO - QUESTO È IL PROBLEMA!' ?></strong></li>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <strong>Tipologia LEZ_RECUPERO non esiste!</strong> Clicca "Auto-Fix Tipologia" su diagnostica_recuperi.php
                    </div>
                <?php endif; ?>

                <h6>Analisi:</h6>
                <?php 
                $db_count = count($recuperi_db);
                $cal_count = count($eventi_cal);
                $ctrl_count = count($controller_eventi);
                ?>
                <ul>
                    <li>Recuperi nel DB: <strong><?= $db_count ?></strong></li>
                    <li>Evento_calendario creati: <strong><?= $cal_count ?></strong></li>
                    <li>Visibili dal controller: <strong><?= $ctrl_count ?></strong></li>
                </ul>

                <?php if ($db_count > 0 && $cal_count == 0): ?>
                <div class="alert alert-warning">
                    ❌ Recuperi nel DB ma NON in evento_calendario!<br>
                    <strong>Soluzione:</strong> Vai a diagnostica_recuperi.php e clicca "Sincronizza Ora"
                </div>
                <?php elseif ($cal_count > 0 && $ctrl_count == 0): ?>
                <div class="alert alert-danger">
                    ❌ Evento_calendario creati ma NON visibili dal controller!<br>
                    <strong>Motivo:</strong> La tipologia LEZ_RECUPERO è disattivata (attiva = 0)<br>
                    <strong>Soluzione:</strong> Vai a diagnostica_recuperi.php e clicca "Auto-Fix Tipologia"
                </div>
                <?php elseif ($db_count == $cal_count && $cal_count == $ctrl_count): ?>
                <div class="alert alert-success">
                    ✅ Tutto sincronizzato e visibile! Ricarica il calendario.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
