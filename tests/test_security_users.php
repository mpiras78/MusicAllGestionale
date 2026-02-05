<?php
/**
 * Test Sicurezza Utenti
 * 
 * Test delle funzionalità di sicurezza:
 * - Validazione password forte
 * - Blocco disabilitazione ultimo admin
 * - Creazione/Modifica utenti
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/controllers/UsersController.php';

// Colori output
define('GREEN', "\033[0;32m");
define('RED', "\033[0;31m");
define('YELLOW', "\033[1;33m");
define('BLUE', "\033[0;34m");
define('NC', "\033[0m"); // No Color

class SecurityUserTest {
    private $db;
    private $controller;
    private $testResults = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->controller = new UsersController($this->db);
    }
    
    /**
     * Esegue tutti i test
     */
    public function runAllTests() {
        echo BLUE . "\n========================================\n" . NC;
        echo BLUE . "   TEST SICUREZZA UTENTI\n" . NC;
        echo BLUE . "========================================\n\n" . NC;
        
        // Test validazione password
        $this->testPasswordTooShort();
        $this->testPasswordNoNumber();
        $this->testPasswordNoUppercase();
        $this->testPasswordValid();
        
        // Test creazione utenti
        $this->testCreateUserWithWeakPassword();
        $this->testCreateUserWithStrongPassword();
        
        // Test modifica utenti
        $this->testUpdateUserWithWeakPassword();
        $this->testUpdateUserWithStrongPassword();
        
        // Test protezione admin
        $this->testDisableLastAdmin();
        $this->testDisableAdminWithOtherAdminExists();
        
        // Riepilogo
        $this->printSummary();
    }
    
    /**
     * Test: Password troppo corta
     */
    private function testPasswordTooShort() {
        echo "Test 1: Password troppo corta (< 6 caratteri)... ";
        
        $result = SecurityHelper::validateStrongPassword("Test1");
        
        if (!$result['valid'] && str_contains($result['error'], 'almeno 6 caratteri')) {
            $this->pass("Password rifiutata correttamente");
        } else {
            $this->fail("Password debole accettata!");
        }
    }
    
    /**
     * Test: Password senza numeri
     */
    private function testPasswordNoNumber() {
        echo "Test 2: Password senza numeri... ";
        
        $result = SecurityHelper::validateStrongPassword("TestAbc");
        
        if (!$result['valid'] && str_contains($result['error'], 'almeno un numero')) {
            $this->pass("Password rifiutata correttamente");
        } else {
            $this->fail("Password senza numeri accettata!");
        }
    }
    
    /**
     * Test: Password senza maiuscole
     */
    private function testPasswordNoUppercase() {
        echo "Test 3: Password senza maiuscole... ";
        
        $result = SecurityHelper::validateStrongPassword("test123");
        
        if (!$result['valid'] && str_contains($result['error'], 'lettera maiuscola')) {
            $this->pass("Password rifiutata correttamente");
        } else {
            $this->fail("Password senza maiuscole accettata!");
        }
    }
    
    /**
     * Test: Password valida
     */
    private function testPasswordValid() {
        echo "Test 4: Password valida (Test123)... ";
        
        $result = SecurityHelper::validateStrongPassword("Test123");
        
        if ($result['valid']) {
            $this->pass("Password forte accettata");
        } else {
            $this->fail("Password forte rifiutata: " . $result['error']);
        }
    }
    
    /**
     * Test: Creazione utente con password debole
     */
    private function testCreateUserWithWeakPassword() {
        echo "Test 5: Creazione utente con password debole... ";
        
        $data = [
            'username' => 'test_weak_' . time(),
            'password' => 'weak',
            'role' => 'segreteria',
            'email' => 'test@test.it'
        ];
        
        $result = $this->controller->create($data);
        
        if (!$result['success'] && str_contains($result['error'], 'caratteri')) {
            $this->pass("Creazione bloccata correttamente");
        } else {
            $this->fail("Utente con password debole creato!");
        }
    }
    
    /**
     * Test: Creazione utente con password forte
     */
    private function testCreateUserWithStrongPassword() {
        echo "Test 6: Creazione utente con password forte... ";
        
        $username = 'test_strong_' . time();
        $data = [
            'username' => $username,
            'password' => 'Strong123',
            'role' => 'segreteria',
            'email' => 'strong@test.it'
        ];
        
        $result = $this->controller->create($data);
        
        if ($result['success']) {
            $this->pass("Utente creato con successo");
            
            // Cleanup: elimina utente test
            $this->db->execute("DELETE FROM users WHERE username = ?", [$username]);
        } else {
            $this->fail("Creazione fallita: " . $result['error']);
        }
    }
    
    /**
     * Test: Modifica utente con password debole
     */
    private function testUpdateUserWithWeakPassword() {
        echo "Test 7: Modifica password con password debole... ";
        
        // Crea utente temporaneo
        $username = 'test_update_' . time();
        $this->db->insert(
            "INSERT INTO users (username, password, email, role, active) VALUES (?, ?, ?, ?, ?)",
            [$username, password_hash('Old123', PASSWORD_DEFAULT), 'update@test.it', 'segreteria', 1]
        );
        
        $user = $this->db->queryOne("SELECT id FROM users WHERE username = ?", [$username]);
        
        $data = [
            'username' => $username,
            'password' => 'weak',
            'role' => 'segreteria'
        ];
        
        $result = $this->controller->update($user['id'], $data);
        
        if (!$result['success'] && str_contains($result['error'], 'password')) {
            $this->pass("Modifica bloccata correttamente");
        } else {
            $this->fail("Password debole accettata!");
        }
        
        // Cleanup
        $this->db->execute("DELETE FROM users WHERE username = ?", [$username]);
    }
    
    /**
     * Test: Modifica utente con password forte
     */
    private function testUpdateUserWithStrongPassword() {
        echo "Test 8: Modifica password con password forte... ";
        
        // Crea utente temporaneo
        $username = 'test_update2_' . time();
        $this->db->insert(
            "INSERT INTO users (username, password, email, role, active) VALUES (?, ?, ?, ?, ?)",
            [$username, password_hash('Old123', PASSWORD_DEFAULT), 'update2@test.it', 'segreteria', 1]
        );
        
        $user = $this->db->queryOne("SELECT id FROM users WHERE username = ?", [$username]);
        
        $data = [
            'username' => $username,
            'password' => 'NewStrong123',
            'role' => 'segreteria'
        ];
        
        $result = $this->controller->update($user['id'], $data);
        
        if ($result['success']) {
            $this->pass("Password modificata con successo");
        } else {
            $this->fail("Modifica fallita: " . $result['error']);
        }
        
        // Cleanup
        $this->db->execute("DELETE FROM users WHERE username = ?", [$username]);
    }
    
    /**
     * Test: Disabilitazione ultimo admin
     */
    private function testDisableLastAdmin() {
        echo "Test 9: Tentativo disabilitazione ultimo admin... ";
        
        // Conta admin attivi
        $admins = $this->db->queryOne("SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND active = 1");
        $adminCount = $admins['count'];
        
        if ($adminCount == 1) {
            // C'è solo 1 admin, prova a disabilitarlo
            $admin = $this->db->queryOne("SELECT id FROM users WHERE role = 'admin' AND active = 1");
            $result = $this->controller->toggleActive($admin['id']);
            
            if (!$result['success'] && str_contains($result['error'], 'ultimo amministratore')) {
                $this->pass("Disabilitazione bloccata correttamente");
            } else {
                $this->fail("Ultimo admin disabilitato!");
            }
        } else {
            echo YELLOW . "SKIP (ci sono $adminCount admin)\n" . NC;
        }
    }
    
    /**
     * Test: Disabilitazione admin quando ne esistono altri
     */
    private function testDisableAdminWithOtherAdminExists() {
        echo "Test 10: Disabilitazione admin con altri admin presenti... ";
        
        // Crea admin temporaneo
        $username = 'test_admin_' . time();
        $userId = $this->db->insert(
            "INSERT INTO users (username, password, email, role, active) VALUES (?, ?, ?, ?, ?)",
            [$username, password_hash('Admin123', PASSWORD_DEFAULT), 'admin@test.it', 'admin', 1]
        );
        
        // Ora ci sono 2 admin, prova a disabilitare quello test
        $result = $this->controller->toggleActive($userId);
        
        if ($result['success']) {
            $this->pass("Disabilitazione consentita (esistono altri admin)");
        } else {
            $this->fail("Disabilitazione bloccata erroneamente: " . $result['error']);
        }
        
        // Cleanup
        $this->db->execute("DELETE FROM users WHERE username = ?", [$username]);
    }
    
    /**
     * Registra test passato
     */
    private function pass($message) {
        echo GREEN . "✓ PASS" . NC . " - $message\n";
        $this->testResults[] = ['status' => 'pass', 'message' => $message];
    }
    
    /**
     * Registra test fallito
     */
    private function fail($message) {
        echo RED . "✗ FAIL" . NC . " - $message\n";
        $this->testResults[] = ['status' => 'fail', 'message' => $message];
    }
    
    /**
     * Stampa riepilogo
     */
    private function printSummary() {
        $passed = count(array_filter($this->testResults, fn($r) => $r['status'] === 'pass'));
        $failed = count(array_filter($this->testResults, fn($r) => $r['status'] === 'fail'));
        $total = $passed + $failed;
        
        echo "\n" . BLUE . "========================================\n" . NC;
        echo BLUE . "   RIEPILOGO TEST\n" . NC;
        echo BLUE . "========================================\n" . NC;
        echo "Totali: $total\n";
        echo GREEN . "Passati: $passed\n" . NC;
        
        if ($failed > 0) {
            echo RED . "Falliti: $failed\n" . NC;
        } else {
            echo "Falliti: 0\n";
        }
        
        $percentage = $total > 0 ? round(($passed / $total) * 100, 2) : 0;
        echo "\nSuccesso: {$percentage}%\n";
        
        if ($failed == 0) {
            echo GREEN . "\n✓ TUTTI I TEST SUPERATI!\n\n" . NC;
        } else {
            echo RED . "\n✗ ALCUNI TEST FALLITI\n\n" . NC;
        }
    }
}

// Esegui test
try {
    $tester = new SecurityUserTest();
    $tester->runAllTests();
} catch (Exception $e) {
    echo RED . "\nERRORE: " . $e->getMessage() . "\n" . NC;
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}