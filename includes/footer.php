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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (opzionale ma utile) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Custom JS -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>

<!-- Script globale per rendere tutti gli alert dismissible -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trova tutti gli alert che non hanno già un pulsante di chiusura
    const alerts = document.querySelectorAll('.alert:not(.alert-dismissible)');
    
    alerts.forEach(alert => {
        // Aggiungi classe dismissible
        alert.classList.add('alert-dismissible', 'fade', 'show');
        
        // Aggiungi pulsante di chiusura
        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'btn-close';
        closeButton.setAttribute('data-bs-dismiss', 'alert');
        closeButton.setAttribute('aria-label', 'Close');
        
        // Inserisci il pulsante come primo elemento dell'alert
        alert.insertBefore(closeButton, alert.firstChild);
    });
    
    // Per alert già dismissible ma senza pulsante
    const dismissibleAlerts = document.querySelectorAll('.alert-dismissible:not(:has(.btn-close))');
    
    dismissibleAlerts.forEach(alert => {
        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'btn-close';
        closeButton.setAttribute('data-bs-dismiss', 'alert');
        closeButton.setAttribute('aria-label', 'Close');
        
        alert.insertBefore(closeButton, alert.firstChild);
    });
});
</script>

<?php if (isset($extra_js)): ?>
    <?= $extra_js ?>
<?php endif; ?>

</body>
</html>