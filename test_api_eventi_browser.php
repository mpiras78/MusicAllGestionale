<?php
/**
 * Test API Eventi - Interfaccia Browser
 * Testa l'API eventi in modo visuale
 */
require_once 'includes/bootstrap.php';

// Richiede autenticazione
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Test API Eventi';
include 'includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h2><i class="bi bi-calendar-event"></i> Test API Eventi Calendario</h2>
            <p class="text-muted">Test interattivo per verificare il funzionamento dell'API</p>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Form Test -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">🧪 Test API</h5>
                </div>
                <div class="card-body">
                    <form id="testForm">
                        <div class="mb-3">
                            <label class="form-label">Endpoint</label>
                            <select class="form-select" id="action" name="action">
                                <option value="list">GET - Lista Eventi</option>
                                <option value="get">GET - Singolo Evento</option>
                            </select>
                        </div>

                        <div class="mb-3" id="dateGroup">
                            <label class="form-label">Data</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="mb-3 d-none" id="idGroup">
                            <label class="form-label">ID Evento</label>
                            <input type="number" class="form-control" id="eventId" name="id" 
                                   placeholder="Inserisci ID evento">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Filtri Opzionali</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="number" class="form-control form-control-sm" 
                                           id="aula_id" name="aula_id" placeholder="Aula ID">
                                </div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control form-control-sm" 
                                           id="docente_id" name="docente_id" placeholder="Docente ID">
                                </div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control form-control-sm" 
                                           id="allievo_id" name="allievo_id" placeholder="Allievo ID">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-play-fill"></i> Esegui Test
                        </button>
                    </form>

                    <div class="alert alert-info mt-3">
                        <small>
                            <strong>💡 Tip:</strong> L'API restituisce JSON. 
                            Vedrai il risultato nel pannello a destra.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">📊 Statistiche Database</h6>
                </div>
                <div class="card-body">
                    <?php
                    $db = Database::getInstance()->getConnection();
                    
                    // Conta eventi
                    $stmt = $db->query("SELECT COUNT(*) as count FROM eventi_calendario WHERE attivo=1");
                    $eventiCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    
                    // Conta tipologie
                    $stmt = $db->query("SELECT COUNT(*) as count FROM tipologie_evento WHERE attiva=1");
                    $tipologieCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    
                    // Conta aule
                    $stmt = $db->query("SELECT COUNT(*) as count FROM aule WHERE attiva=1");
                    $auleCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    ?>
                    <ul class="list-unstyled mb-0">
                        <li>✅ <strong><?= $eventiCount ?></strong> eventi attivi</li>
                        <li>🏷️ <strong><?= $tipologieCount ?></strong> tipologie evento</li>
                        <li>🚪 <strong><?= $auleCount ?></strong> aule disponibili</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Risultati -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">📋 Risposta API</h5>
                </div>
                <div class="card-body">
                    <div id="loading" class="text-center d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Caricamento...</span>
                        </div>
                        <p class="mt-2">Chiamata API in corso...</p>
                    </div>

                    <div id="result" class="d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span id="statusBadge"></span>
                            <small class="text-muted" id="responseTime"></small>
                        </div>
                        <pre id="jsonResponse" class="bg-light p-3 rounded" style="max-height: 500px; overflow-y: auto;"></pre>
                    </div>

                    <div id="emptyState" class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">Esegui un test per vedere i risultati</p>
                    </div>
                </div>
            </div>

            <!-- Esempio Output -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">📖 Esempio Output Atteso</h6>
                </div>
                <div class="card-body">
                    <small>
                    <strong>Successo:</strong>
                    <pre class="bg-light p-2 rounded mb-2"><code>{
  "success": true,
  "date": "2026-02-12",
  "count": 5,
  "data": [
    {
      "id": 1,
      "tipologia_nome": "Lezione Regolare",
      "colore_bg": "#fff5f0",
      "ora_inizio": "10:00:00",
      "ora_fine": "10:45:00",
      "aula_nome": "AULA PIANO",
      "docente_nome": "Rossi Mario",
      "partecipante_nome": "Bianchi Luca",
      "materia_nome": "Pianoforte"
    },
    ...
  ]
}</code></pre>

                    <strong>Errore:</strong>
                    <pre class="bg-light p-2 rounded"><code>{
  "success": false,
  "error": "Messaggio di errore"
}</code></pre>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('testForm');
    const actionSelect = document.getElementById('action');
    const dateGroup = document.getElementById('dateGroup');
    const idGroup = document.getElementById('idGroup');
    const loading = document.getElementById('loading');
    const result = document.getElementById('result');
    const emptyState = document.getElementById('emptyState');
    const jsonResponse = document.getElementById('jsonResponse');
    const statusBadge = document.getElementById('statusBadge');
    const responseTime = document.getElementById('responseTime');

    // Mostra/nascondi campi in base all'action
    actionSelect.addEventListener('change', function() {
        if (this.value === 'get') {
            dateGroup.classList.add('d-none');
            idGroup.classList.remove('d-none');
        } else {
            dateGroup.classList.remove('d-none');
            idGroup.classList.add('d-none');
        }
    });

    // Submit form
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Mostra loading
        emptyState.classList.add('d-none');
        result.classList.add('d-none');
        loading.classList.remove('d-none');
        
        // Prepara URL
        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        for (const [key, value] of formData.entries()) {
            if (value) {
                params.append(key, value);
            }
        }
        
        const url = `api_eventi.php?${params.toString()}`;
        const startTime = Date.now();
        
        try {
            const response = await fetch(url);
            const data = await response.json();
            const endTime = Date.now();
            const elapsed = endTime - startTime;
            
            // Nascondi loading
            loading.classList.add('d-none');
            result.classList.remove('d-none');
            
            // Mostra status
            if (data.success) {
                statusBadge.innerHTML = '<span class="badge bg-success">✓ Successo</span>';
            } else {
                statusBadge.innerHTML = '<span class="badge bg-danger">✗ Errore</span>';
            }
            
            responseTime.textContent = `Risposta in ${elapsed}ms`;
            
            // Mostra JSON formattato
            jsonResponse.textContent = JSON.stringify(data, null, 2);
            
            // Syntax highlighting
            jsonResponse.innerHTML = syntaxHighlight(JSON.stringify(data, null, 2));
            
        } catch (error) {
            loading.classList.add('d-none');
            result.classList.remove('d-none');
            statusBadge.innerHTML = '<span class="badge bg-danger">✗ Errore di Rete</span>';
            jsonResponse.textContent = `Errore: ${error.message}`;
        }
    });
    
    // Syntax highlighting per JSON
    function syntaxHighlight(json) {
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
            let cls = 'text-success'; // number
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'text-primary fw-bold'; // key
                } else {
                    cls = 'text-danger'; // string
                }
            } else if (/true|false/.test(match)) {
                cls = 'text-info'; // boolean
            } else if (/null/.test(match)) {
                cls = 'text-secondary'; // null
            }
            return '<span class="' + cls + '">' + match + '</span>';
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>