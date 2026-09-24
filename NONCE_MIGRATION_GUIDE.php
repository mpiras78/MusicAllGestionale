<?php
/**
 * IMPORTANTE: Come usare il nonce negli script inline per CSP compliance
 * 
 * PRIMA (Non funziona):
 * <script>
 *   console.log("hello");
 * </script>
 * 
 * DOPO (Funziona con CSP):
 * <script nonce="<?= $_SESSION['csp_nonce'] ?>">
 *   console.log("hello");
 * </script>
 * 
 * OPPURE usa la funzione helper:
 * <?= html_script('console.log("hello");') ?>
 * 
 * Per tutti i file PHP con script inline, aggiungi:
 * nonce="<?= $_SESSION['csp_nonce'] ?>"
 * 
 * all'interno di ogni tag <script>
 */

// Questa è una guida per aggiornare i file
// Eseguire questa conversione manualmente o con uno script regex:

/*
Regex per trovare e sostituire in VSCode:
Find:  <script>
Replace: <script nonce="<?= $_SESSION['csp_nonce'] ?>">

ATTENZIONE: Non sostituire:
- <script src="...">  (script esterni)
- <script type="..."> (script con type attribute)
*/
?>
