</main>

<!-- Footer -->
<footer class="bg-light mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">
                    <strong><?= APP_NAME ?></strong> v<?= APP_VERSION ?>
                </p>
                <p class="text-muted small mb-0">
                    Sistema di gestione per scuole di musica
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0 text-muted small">
                    © <?= date('Y') ?> - Tutti i diritti riservati
                </p>
                <?php if ($auth->isLoggedIn()): ?>
                <p class="mb-0 text-muted small">
                    Utente: <strong><?= e($_SESSION['username']) ?></strong> 
                    (<?= e($_SESSION['role']) ?>)
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" 
        crossorigin="anonymous"></script>

<!-- Verifica caricamento Bootstrap e inizializza dropdown manualmente se necessario -->
<script>
window.addEventListener('load', function() {
    // Verifica se Bootstrap è caricato
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap JS non caricato! Verifica connessione CDN.');
        // Fallback: inizializza dropdown manualmente con JavaScript vanilla
        document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(dropdownToggle) {
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const menu = this.nextElementSibling;
                if (menu && menu.classList.contains('dropdown-menu')) {
                    // Chiudi altri dropdown aperti
                    document.querySelectorAll('.dropdown-menu.show').forEach(function(m) {
                        if (m !== menu) m.classList.remove('show');
                    });
                    // Toggle questo dropdown
                    menu.classList.toggle('show');
                }
            }, true); // useCapture = true per catturare prima
        });
        
        // Chiudi dropdown al click fuori
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                    menu.classList.remove('show');
                });
            }
        }, true); // useCapture = true
    } else {
        console.log('Bootstrap caricato correttamente');
        
        // Anche con Bootstrap, assicurati che i dropdown siano inizializzati
        setTimeout(function() {
            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(dropdownToggle) {
                if (!dropdownToggle.hasAttribute('data-dropdown-initialized')) {
                    try {
                        new bootstrap.Dropdown(dropdownToggle);
                        dropdownToggle.setAttribute('data-dropdown-initialized', 'true');
                    } catch(e) {
                        console.warn('Errore inizializzazione dropdown:', e);
                    }
                }
            });
        }, 100);
    }
});
</script>

<!-- jQuery (opzionale ma utile) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Custom JS -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>

<?php if (isset($extra_js)): ?>
    <?= $extra_js ?>
<?php endif; ?>

<!-- Modal About -->
<div class="modal fade" id="aboutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle"></i> About <?= APP_NAME ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="<?= BASE_URL ?>/assets/img/logo_musicall.png" 
                     alt="MusicAll Logo" 
                     class="mb-3" 
                     style="max-width: 150px;">
                
                <h4 class="mb-3"><?= APP_NAME ?></h4>
                
                <div class="alert alert-info mb-3">
                    <h5 class="mb-0">
                        <i class="bi bi-tag"></i> Versione <?= APP_VERSION ?>
                    </h5>
                </div>
                
                <p class="text-muted mb-3">
                    Sistema di gestione completo per scuole di musica
                </p>
                
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-star-fill text-warning"></i> Caratteristiche
                        </h6>
                        <ul class="list-unstyled text-start mb-0">
                            <li><i class="bi bi-check-circle text-success"></i> Gestione Allievi e Docenti</li>
                            <li><i class="bi bi-check-circle text-success"></i> Calendario Settimanale Interattivo</li>
                            <li><i class="bi bi-check-circle text-success"></i> Tracciamento Assenze e Recuperi</li>
                            <li><i class="bi bi-check-circle text-success"></i> Statistiche e Report</li>
                            <li><i class="bi bi-check-circle text-success"></i> Sistema Multi-Utente</li>
                        </ul>
                    </div>
                </div>
                
                <p class="text-muted small mb-2">
                    <i class="bi bi-code-slash"></i> Developed by
                </p>
                <p class="fw-bold mb-1">Marco Piras & Cline</p>
                
                <p class="text-muted small mb-0">
                    © <?= date('Y') ?> - Tutti i diritti riservati
                </p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Chiudi
                </button>
            </div>
        </div>
    </div>
</div>

</body>
</html>
