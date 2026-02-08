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

</body>
</html>