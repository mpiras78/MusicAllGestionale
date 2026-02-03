<?php
/**
 * Bootstrap File
 * Carica configurazione e classi necessarie
 */

// Carica configurazione
require_once __DIR__ . '/../config/config.php';

// Carica classi
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

// Funzioni helper
require_once __DIR__ . '/helpers.php';

// Inizializza oggetti globali
$db = Database::getInstance();
$auth = new Auth();