<?php
/**
 * Diagnostica: Stato sincronizzazione recuperi
 * Mostra report su recuperi vs evento_calendario
 */

require_once 'includes/bootstrap.php';

// Verificazione autenticazione
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'segreteria'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// 1. Conta recuperi totali
$result_recuperi = $db->query("SELECT COUNT(*) as count FROM recuperi WHERE annullato = 0");
$total_recuperi = $result_recuperi->fetch(PDO::FETCH_ASSOC)['count'];

// 2. Conta eventi_calendario per recuperi
$result_eventi = $db->query("
    SELECT COUNT(DISTINCT e.id) as count FROM eventi_calendario e
    INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
    WHERE t.codice = 'LEZ_RECUPERO'
");
$total_eventi = $result_eventi->fetch(PDO::FETCH_ASSOC)['count'];

// 3. Conta recuperi senza evento_calendario
$result_mancanti = $db->query("
    SELECT COUNT(*) as count FROM recuperi r
    WHERE r.annullato = 0 
    AND NOT EXISTS (
        SELECT 1 FROM eventi_calendario e
        WHERE e.data_evento = r.data_recupero
        AND e.ora_inizio = r.ora_inizio
        AND e.socio_id = r.socio_id
    )
");
$mancanti = $result_mancanti->fetch(PDO::FETCH_ASSOC)['count'];

$sync_status = $mancanti == 0 ? 'success' : 'warning';
$sync_message = $mancanti == 0 ? '✅ Sincronizzato' : "❌ $mancanti recuperi non sincronizzati";

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostica Recuperi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col">
                <h1><i class="bi bi-activity"></i> Diagnostica Sincronizzazione Recuperi</h1>
                <p class="text-muted">Verifica lo stato della sincronizzazione tra recuperi e calendario</p>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Recuperi Totali</h5>
                        <p class="card-text display-4"><?= $total_recuperi ?></p>
                        <p class="small">Recuperi attivi nel database</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">In Calendario</h5>
                        <p class="card-text display-4"><?= $total_eventi ?></p>
                        <p class="small">Recuperi visibili nel calendario</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-<?= $mancanti > 0 ? 'danger' : 'success' ?>">
                    <div class="card-body">
                        <h5 class="card-title">Mancanti</h5>
                        <p class="card-text display-4"><?= $mancanti ?></p>
                        <p class="small">Recuperi non sincronizzati</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Percentuale</h5>
                        <p class="card-text display-4"><?= $total_recuperi > 0 ? round(($total_eventi / $total_recuperi) * 100) : 0 ?>%</p>
                        <p class="small">Copertura calendario</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Alert -->
        <div class="alert alert-<?= $sync_status ?>" role="alert">
            <h4 class="alert-heading">Stato Sincronizzazione</h4>
            <p><?= $sync_message ?></p>
            <?php if ($mancanti > 0): ?>
                <hr>
                <p class="mb-0">Clicca il pulsante "Sincronizza Ora" per aggiungere i recuperi mancanti al calendario.</p>
            <?php endif; ?>
        </div>

        <!-- Sync Button -->
        <?php if ($mancanti > 0): ?>
        <div class="row mb-4">
            <div class="col">
                <button class="btn btn-lg btn-primary" id="btnSincronizza">
                    <i class="bi bi-arrow-clockwise"></i> Sincronizza Ora
                </button>
                <button class="btn btn-lg btn-warning ms-2" id="btnFixTipologia">
                    <i class="bi bi-wrench"></i> Auto-Fix Tipologia
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Result Container -->
        <div id="resultContainer" class="d-none">
            <div class="alert" id="resultAlert"></div>
        </div>

        <!-- Sample Recuperi -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Sample Recuperi Mancanti (Primi 10)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Socio</th>
                            <th>Data Recupero</th>
                            <th>Orario</th>
                            <th>Aula</th>
                            <th>Stato</th>
                        </tr>
                    </thead>
                    <tbody id="recuperiList">
                        <tr>
                            <td colspan="6" class="text-center text-muted">Caricamento...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script nonce="<?= $_SESSION['csp_nonce'] ?>">
    // Event listeners setup
    document.addEventListener('DOMContentLoaded', function() {
        const btnSincronizza = document.getElementById('btnSincronizza');
        if (btnSincronizza) {
            btnSincronizza.addEventListener('click', sincronizzaRecuperi);
        }
        const btnFixTipologia = document.getElementById('btnFixTipologia');
        if (btnFixTipologia) {
            btnFixTipologia.addEventListener('click', fixTipologia);
        }
        loadMancanti();
    });

    function sincronizzaRecuperi(event) {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sincronizzazione...';

        fetch('<?= BASE_URL ?>/api/api_sync_recuperi_calendario.php', {
            method: 'POST'
        })
        .then(r => r.json())
        .then(result => {
            const resultContainer = document.getElementById('resultContainer');
            const resultAlert = document.getElementById('resultAlert');
            
            if (result.success) {
                resultAlert.className = 'alert alert-success';
                resultAlert.innerHTML = `
                    <h4>✅ Sincronizzazione Completata</h4>
                    <p>${result.message}</p>
                    <p>Recuperi sincronizzati: <strong>${result.recuperi_sincronizzati}</strong></p>
                `;
            } else {
                resultAlert.className = 'alert alert-danger';
                resultAlert.innerHTML = `
                    <h4>❌ Errore</h4>
                    <p>${result.error || 'Errore sconosciuto'}</p>
                `;
            }
            
            resultContainer.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = originalText;
            
            // Ricarica pagina dopo 3 secondi
            setTimeout(() => location.reload(), 3000);
        })
        .catch(error => {
            const resultContainer = document.getElementById('resultContainer');
            const resultAlert = document.getElementById('resultAlert');
            resultAlert.className = 'alert alert-danger';
            resultAlert.innerHTML = `<h4>❌ Errore</h4><p>${error.message}</p>`;
            resultContainer.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    // Carica lista recuperi mancanti
    async function loadMancanti() {
        try {
            const response = await fetch('<?= BASE_URL ?>/api/api_get_recuperi_mancanti.php');
            const data = await response.json();
            
            const tbody = document.getElementById('recuperiList');
            if (data.success && data.data && data.data.length > 0) {
                tbody.innerHTML = data.data.map(r => `
                    <tr>
                        <td>#${r.id}</td>
                        <td>${r.socio_nome || 'N/A'}</td>
                        <td>${new Date(r.data_recupero).toLocaleDateString('it-IT')}</td>
                        <td>${r.ora_inizio} - ${r.ora_fine}</td>
                        <td>${r.aula_nome || 'N/A'}</td>
                        <td><span class="badge bg-warning">Non sincronizzato</span></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Nessun recupero mancante</td></tr>';
            }
        } catch (error) {
            console.error('Errore caricamento:', error);
        }
    }

    // Fix tipologia LEZ_RECUPERO
    async function fixTipologia(event) {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Fixing...';

        try {
            const response = await fetch('<?= BASE_URL ?>/api/api_fix_tipologia_recupero.php', {
                method: 'POST'
            });
            const result = await response.json();
            
            const resultContainer = document.getElementById('resultContainer');
            const resultAlert = document.getElementById('resultAlert');
            
            if (result.success) {
                resultAlert.className = 'alert alert-success';
                resultAlert.innerHTML = `
                    <h4>✅ Fix Completato</h4>
                    <p>${result.message}</p>
                    <p>Azione: <strong>${result.action}</strong></p>
                    <p class="mt-2">La pagina verrà ricaricata...</p>
                `;
                resultContainer.classList.remove('d-none');
                setTimeout(() => location.reload(), 2000);
            } else {
                resultAlert.className = 'alert alert-danger';
                resultAlert.innerHTML = `
                    <h4>❌ Errore</h4>
                    <p>${result.error || 'Errore sconosciuto'}</p>
                `;
                resultContainer.classList.remove('d-none');
            }
        } catch (error) {
            const resultContainer = document.getElementById('resultContainer');
            const resultAlert = document.getElementById('resultAlert');
            resultAlert.className = 'alert alert-danger';
            resultAlert.innerHTML = `<h4>❌ Errore</h4><p>${error.message}</p>`;
            resultContainer.classList.remove('d-none');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
    </script>
</body>
</html>
