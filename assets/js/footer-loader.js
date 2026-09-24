/**
 * Footer Loader - Verifica Bootstrap e inizializza dropdown
 * Estratto da includes/footer.php per CSP compliance
 */

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
