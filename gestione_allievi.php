<?php
// Redirect compatibility stub: gestione_soci.php -> gestione_soci.php
// Keeps existing links working; will immediately redirect to the new page.
require_once __DIR__ . '/includes/bootstrap.php';

$auth->requireLogin();

header('Location: ' . BASE_URL . '/gestione_soci.php');
exit;
