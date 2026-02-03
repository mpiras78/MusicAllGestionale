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

<?php if (isset($extra_js)): ?>
    <?= $extra_js ?>
<?php endif; ?>

</body>
</html>