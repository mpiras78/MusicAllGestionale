<?php
/**
 * Debug: Verifica cosa carica il calendario
 */

require_once 'includes/bootstrap.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'segreteria'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$data_selezionata = $_GET['data'] ?? date('Y-m-d');

// Verifica cosa carica il controller
$eventiCtrl = new EventiController();
$eventi = $eventiCtrl->getEventiPerData($data_selezionata);

// Conta tutti i recuperi per questa data nel DB
$recuperi_query = $db->query("
    SELECT 
        r.id,
        r.data_recupero,
        r.ora_inizio,
        al.cognome || ' ' || al.nome as socio_nome
    FROM recuperi r
    LEFT JOIN soci al ON r.socio_id = al.id
    WHERE r.data_recupero = ?
    AND r.annullato = 0
", [$data_selezionata]);
$recuperi_db = $recuperi_query->fetchAll(PDO::FETCH_ASSOC);

// Conta evento_calendario per questa data
$eventi_cal_query = $db->query("
    SELECT 
        e.id,
        e.data_evento,
        e.ora_inizio,
        t.codice,
        al.cognome || ' ' || al.nome as socio_nome,
        e.attivo,
        e.confermato
    FROM eventi_calendario e
    INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
    LEFT JOIN soci al ON e.socio_id = al.id
    WHERE e.data_evento = ?
    AND t.categoria = 'recupero'
", [$data_selezionata]);
$eventi_cal = $eventi_cal_query->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Recuperi Calendario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light p-4">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col">
                <h1><i class="bi bi-bug"></i> Debug: Recuperi nel Calendario</h1>
                <p class="text-muted">Data selezionata: <strong><?= date('d/m/Y', strtotime($data_selezionata)) ?></strong></p>
            </div>
        </div>

        <!-- Date selector -->
        <div class="row mb-4">
            <div class="col">
                <form method="GET" class="row g-2">
                    <div class="col-auto">
                        <input type="date" name="data" value="<?= $data_selezionata ?>" class="form-control">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Carica</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recuperi nel DB</h5>
                        <p class="card-text display-4"><?= count($recuperi_db) ?></p>
                        <small class="text-muted">Recuperi attivi per questa data</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">evento_calendario</h5>
                        <p class="card-text display-4"><?= count($eventi_cal) ?></p>
                        <small class="text-muted">Recuperi in evento_calendario</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Caricati dal Controller</h5>
                        <p class="card-text display-4"><?= count($eventi) ?></p>
                        <small class="text-muted">Eventi visibili nel calendario</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recuperi nel DB -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Recuperi nel DB (recuperi table)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Orario</th>
                            <th>Socio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recuperi_db) > 0): ?>
                            <?php foreach ($recuperi_db as $r): ?>
                            <tr>
                                <td>#<?= $r['id'] ?></td>
                                <td><?= $r['data_recupero'] ?></td>
                                <td><?= $r['ora_inizio'] ?></td>
                                <td><?= $r['socio_nome'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Nessun recupero per questa data</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- evento_calendario -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">evento_calendario (tipo recupero)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Orario</th>
                            <th>Socio</th>
                            <th>Attivo</th>
                            <th>Confermato</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($eventi_cal) > 0): ?>
                            <?php foreach ($eventi_cal as $e): ?>
                            <tr>
                                <td>#<?= $e['id'] ?></td>
                                <td><?= $e['data_evento'] ?></td>
                                <td><?= $e['ora_inizio'] ?></td>
                                <td><?= $e['socio_nome'] ?></td>
                                <td>
                                    <?php if ($e['attivo']): ?>
                                        <span class="badge bg-success">✓</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">✗</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($e['confermato']): ?>
                                        <span class="badge bg-success">✓</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">✗</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Nessun evento per questa data</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Caricati dal Controller -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Caricati dal Controller (Quello che vede il calendario)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Orario</th>
                            <th>Socio</th>
                            <th>Aula</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($eventi) > 0): ?>
                            <?php foreach ($eventi as $e): ?>
                            <tr>
                                <td>#<?= $e['id'] ?></td>
                                <td><?= $e['tipo'] ?? $e['tipologia_nome'] ?></td>
                                <td><?= $e['ora_inizio'] ?> - <?= $e['ora_fine'] ?></td>
                                <td><?= $e['socio'] ?></td>
                                <td><?= $e['aula'] ?></td>
                                <td><?= substr($e['note'] ?? '', 0, 30) ?>...</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Nessun evento caricato</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Raw Query Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informazioni Query</h5>
            </div>
            <div class="card-body">
                <p><strong>Query EventiController::getEventiPerData():</strong></p>
                <pre><code>SELECT ... FROM eventi_calendario e
WHERE e.data_evento = '<?= $data_selezionata ?>'
AND e.attivo = 1
AND t.attiva = 1</code></pre>
                
                <p class="mt-3"><strong>Query Recuperi nel DB:</strong></p>
                <pre><code>SELECT ... FROM recuperi r
WHERE r.data_recupero = '<?= $data_selezionata ?>'
AND r.annullato = 0</code></pre>

                <p class="mt-3"><strong>Query evento_calendario:</strong></p>
                <pre><code>SELECT ... FROM eventi_calendario e
WHERE e.data_evento = '<?= $data_selezionata ?>'
AND t.categoria = 'recupero'</code></pre>
            </div>
        </div>

        <!-- Cache info -->
        <div class="alert alert-info mt-4">
            <i class="bi bi-info-circle"></i> <strong>Hint:</strong> Se i recuperi apppaiono in evento_calendario ma non nel controller, potrebbe essere:
            <ul class="mb-0 mt-2">
                <li>Tipologia evento non attiva (<code>t.attiva = 1</code> manca)</li>
                <li>evento_calendario con <code>attivo = 0</code></li>
                <li>Cache del browser - prova <strong>Ctrl+Shift+R</strong></li>
            </ul>
        </div>
    </div>
</body>
</html>
