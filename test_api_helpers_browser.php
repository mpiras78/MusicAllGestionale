<?php
/**
 * Test API Helpers nel Browser
 * Verifica che le API carichino correttamente allievi, docenti, materie, aule
 */

require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

$page_title = 'Test API Helpers';
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2><i class="bi bi-bug"></i> Test API Helpers</h2>
            <p class="text-muted">Verifica caricamento dati da API</p>
            <hr>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Allievi</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary mb-3" onclick="testAllievi()">
                        <i class="bi bi-play-fill"></i> Test Allievi
                    </button>
                    <pre id="resultAllievi" class="bg-light p-3" style="max-height: 300px; overflow-y: auto;"></pre>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-person-badge"></i> Docenti</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-success mb-3" onclick="testDocenti()">
                        <i class="bi bi-play-fill"></i> Test Docenti
                    </button>
                    <pre id="resultDocenti" class="bg-light p-3" style="max-height: 300px; overflow-y: auto;"></pre>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-book"></i> Materie</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-warning mb-3" onclick="testMaterie()">
                        <i class="bi bi-play-fill"></i> Test Materie
                    </button>
                    <pre id="resultMaterie" class="bg-light p-3" style="max-height: 300px; overflow-y: auto;"></pre>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-door-open"></i> Aule</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-info mb-3" onclick="testAule()">
                        <i class="bi bi-play-fill"></i> Test Aule
                    </button>
                    <pre id="resultAule" class="bg-light p-3" style="max-height: 300px; overflow-y: auto;"></pre>
                </div>
            </div>
        </div>
        
        <div class="col-12 mb-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-palette"></i> Tipologie Eventi</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-danger mb-3" onclick="testTipologie()">
                        <i class="bi bi-play-fill"></i> Test Tipologie
                    </button>
                    <pre id="resultTipologie" class="bg-light p-3" style="max-height: 300px; overflow-y: auto;"></pre>
                </div>
            </div>
        </div>
        
        <div class="col-12">
            <div class="card border-dark">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-lightning-fill"></i> Test Tutti</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-dark btn-lg" onclick="testAll()">
                        <i class="bi bi-play-fill"></i> Esegui Tutti i Test
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function testAllievi() {
    const result = document.getElementById('resultAllievi');
    result.textContent = 'Caricamento...';
    
    try {
        const response = await fetch('api_get_helpers.php?type=allievi');
        const data = await response.json();
        result.textContent = JSON.stringify(data, null, 2);
        
        if (data.success) {
            result.classList.remove('text-danger');
            result.classList.add('text-success');
            console.log('✅ Allievi caricati:', data.data.length);
        } else {
            result.classList.add('text-danger');
            console.error('❌ Errore allievi:', data.error);
        }
    } catch (error) {
        result.textContent = 'ERRORE: ' + error.message;
        result.classList.add('text-danger');
        console.error('❌ Errore fetch allievi:', error);
    }
}

async function testDocenti() {
    const result = document.getElementById('resultDocenti');
    result.textContent = 'Caricamento...';
    
    try {
        const response = await fetch('api_get_helpers.php?type=docenti');
        const data = await response.json();
        result.textContent = JSON.stringify(data, null, 2);
        
        if (data.success) {
            result.classList.remove('text-danger');
            result.classList.add('text-success');
            console.log('✅ Docenti caricati:', data.data.length);
        } else {
            result.classList.add('text-danger');
            console.error('❌ Errore docenti:', data.error);
        }
    } catch (error) {
        result.textContent = 'ERRORE: ' + error.message;
        result.classList.add('text-danger');
        console.error('❌ Errore fetch docenti:', error);
    }
}

async function testMaterie() {
    const result = document.getElementById('resultMaterie');
    result.textContent = 'Caricamento...';
    
    try {
        const response = await fetch('api_get_helpers.php?type=materie');
        const data = await response.json();
        result.textContent = JSON.stringify(data, null, 2);
        
        if (data.success) {
            result.classList.remove('text-danger');
            result.classList.add('text-success');
            console.log('✅ Materie caricate:', data.data.length);
        } else {
            result.classList.add('text-danger');
            console.error('❌ Errore materie:', data.error);
        }
    } catch (error) {
        result.textContent = 'ERRORE: ' + error.message;
        result.classList.add('text-danger');
        console.error('❌ Errore fetch materie:', error);
    }
}

async function testAule() {
    const result = document.getElementById('resultAule');
    result.textContent = 'Caricamento...';
    
    try {
        const response = await fetch('api_get_helpers.php?type=aule');
        const data = await response.json();
        result.textContent = JSON.stringify(data, null, 2);
        
        if (data.success) {
            result.classList.remove('text-danger');
            result.classList.add('text-success');
            console.log('✅ Aule caricate:', data.data.length);
        } else {
            result.classList.add('text-danger');
            console.error('❌ Errore aule:', data.error);
        }
    } catch (error) {
        result.textContent = 'ERRORE: ' + error.message;
        result.classList.add('text-danger');
        console.error('❌ Errore fetch aule:', error);
    }
}

async function testTipologie() {
    const result = document.getElementById('resultTipologie');
    result.textContent = 'Caricamento...';
    
    try {
        const response = await fetch('api_get_tipologie.php');
        const data = await response.json();
        result.textContent = JSON.stringify(data, null, 2);
        
        if (data.success) {
            result.classList.remove('text-danger');
            result.classList.add('text-success');
            console.log('✅ Tipologie caricate:', data.data.length);
        } else {
            result.classList.add('text-danger');
            console.error('❌ Errore tipologie:', data.error);
        }
    } catch (error) {
        result.textContent = 'ERRORE: ' + error.message;
        result.classList.add('text-danger');
        console.error('❌ Errore fetch tipologie:', error);
    }
}

async function testAll() {
    console.log('🚀 Esecuzione tutti i test...');
    await testAllievi();
    await new Promise(r => setTimeout(r, 500));
    await testDocenti();
    await new Promise(r => setTimeout(r, 500));
    await testMaterie();
    await new Promise(r => setTimeout(r, 500));
    await testAule();
    await new Promise(r => setTimeout(r, 500));
    await testTipologie();
    console.log('✅ Tutti i test completati');
}

// Auto-esegui all'avvio
window.addEventListener('load', () => {
    console.log('🔍 Pagina caricata, pronto per i test');
    console.log('💡 Apri la console per vedere i dettagli dei test');
});
</script>

<?php include 'includes/footer.php'; ?>