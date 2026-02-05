<?php
/**
 * UsersController - Gestione Utenti
 * 
 * Gestisce CRUD utenti e ruoli
 */

class UsersController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Lista tutti gli utenti
     */
    public function index() {
        $sql = "
            SELECT u.*, 
                   CASE 
                       WHEN u.role = 'admin' THEN '👑 Amministratore'
                       WHEN u.role = 'segreteria' THEN '📋 Segreteria'
                       WHEN u.role = 'docente' THEN '🎵 Docente'
                   END as role_label,
                   d.cognome as docente_cognome,
                   d.nome as docente_nome
            FROM users u
            LEFT JOIN docenti d ON u.id = d.user_id
            ORDER BY 
                CASE u.role
                    WHEN 'admin' THEN 1
                    WHEN 'segreteria' THEN 2
                    WHEN 'docente' THEN 3
                END,
                u.username
        ";
        return $this->db->query($sql);
    }
    
    /**
     * Ottieni utente per ID
     */
    public function getById($id) {
        $sql = "
            SELECT u.*, d.id as docente_id
            FROM users u
            LEFT JOIN docenti d ON u.id = d.user_id
            WHERE u.id = ?
        ";
        return $this->db->queryOne($sql, [$id]);
    }
    
    /**
     * Crea nuovo utente
     */
    public function create($data) {
        try {
            // Validazione
            if (empty($data['username']) || empty($data['role'])) {
                return ['success' => false, 'error' => 'Username e ruolo sono obbligatori'];
            }
            
            // Validazione email
            if (empty($data['email'])) {
                return ['success' => false, 'error' => 'Email è obbligatoria'];
            }
            
            if (!SecurityHelper::isValidEmail($data['email'])) {
                return ['success' => false, 'error' => 'Email non valida'];
            }
            
            // Verifica username univoco
            $existing = $this->db->queryOne("SELECT id FROM users WHERE username = ?", [$data['username']]);
            if ($existing) {
                return ['success' => false, 'error' => 'Username già esistente'];
            }
            
            // Password temporanea casuale (verrà sostituita durante attivazione)
            $tempPassword = bin2hex(random_bytes(16));
            $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
            
            // Insert utente - SEMPRE disabilitato (active=0) finché non attiva
            $sql = "
                INSERT INTO users (username, password, email, role, active)
                VALUES (?, ?, ?, ?, 0)
            ";
            
            $userId = $this->db->insert($sql, [
                $data['username'],
                $passwordHash,
                $data['email'],
                $data['role']
            ]);
            
            // Se è un docente, collega al docente esistente se specificato
            if ($data['role'] === 'docente' && !empty($data['docente_id'])) {
                $this->db->execute("UPDATE docenti SET user_id = ? WHERE id = ?", [$userId, $data['docente_id']]);
            }
            
            // Genera URL token (hash univoco basato su user_id + email + timestamp)
            $urlToken = hash('sha256', $userId . $data['email'] . time() . uniqid());
            
            // Genera codice attivazione a 6 cifre
            $activationCode = SecurityHelper::generateToken(6);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Salva token nel database
            $this->db->insert(
                "INSERT INTO activation_tokens (user_id, url_token, activation_code, expires_at) VALUES (?, ?, ?, ?)",
                [$userId, $urlToken, $activationCode, $expiresAt]
            );
            
            // Invia email (non bloccare se fallisce in locale)
            try {
                $emailHelper = new EmailHelper();
                $emailHelper->sendActivationEmail($data['email'], $data['username'], $urlToken, $activationCode);
            } catch (Exception $e) {
                // Log errore ma non bloccare creazione utente
                error_log("Errore invio email attivazione: " . $e->getMessage());
            }
            
            return [
                'success' => true, 
                'id' => $userId,
                'message' => 'Utente creato. Email di attivazione inviata a ' . $data['email']
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Aggiorna utente
     */
    public function update($id, $data) {
        try {
            // Verifica che l'utente esista
            $user = $this->getById($id);
            if (!$user) {
                return ['success' => false, 'error' => 'Utente non trovato'];
            }
            
            // Validazione
            if (empty($data['username']) || empty($data['role'])) {
                return ['success' => false, 'error' => 'Username e ruolo sono obbligatori'];
            }
            
            // Validazione password se viene cambiata
            if (!empty($data['password'])) {
                $passwordValidation = SecurityHelper::validateStrongPassword($data['password']);
                if (!$passwordValidation['valid']) {
                    return ['success' => false, 'error' => $passwordValidation['error']];
                }
            }
            
            // Verifica username univoco (escluso se stesso)
            $existing = $this->db->queryOne("SELECT id FROM users WHERE username = ? AND id != ?", [$data['username'], $id]);
            if ($existing) {
                return ['success' => false, 'error' => 'Username già esistente'];
            }
            
            // Update base
            if (!empty($data['password'])) {
                // Aggiorna con nuova password
                $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
                $sql = "
                    UPDATE users 
                    SET username = ?, password = ?, email = ?, role = ?, active = ?
                    WHERE id = ?
                ";
                $this->db->execute($sql, [
                    $data['username'],
                    $passwordHash,
                    $data['email'] ?? null,
                    $data['role'],
                    isset($data['active']) ? (int)$data['active'] : 1,
                    $id
                ]);
            } else {
                // Aggiorna senza cambiare password
                $sql = "
                    UPDATE users 
                    SET username = ?, email = ?, role = ?, active = ?
                    WHERE id = ?
                ";
                $this->db->execute($sql, [
                    $data['username'],
                    $data['email'] ?? null,
                    $data['role'],
                    isset($data['active']) ? (int)$data['active'] : 1,
                    $id
                ]);
            }
            
            // Gestione collegamento docente
            if ($data['role'] === 'docente') {
                if (!empty($data['docente_id'])) {
                    // Scollega eventuali altri utenti da questo docente
                    $this->db->execute("UPDATE docenti SET user_id = NULL WHERE user_id = ?", [$id]);
                    
                    // Collega questo utente al docente
                    $this->db->execute("UPDATE docenti SET user_id = ? WHERE id = ?", [$id, $data['docente_id']]);
                }
            } else {
                // Se non è più docente, scollega
                $this->db->execute("UPDATE docenti SET user_id = NULL WHERE user_id = ?", [$id]);
            }
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Elimina utente
     */
    public function delete($id) {
        try {
            // Non permettere eliminazione dell'admin principale
            $user = $this->getById($id);
            if ($user && $user['username'] === 'admin') {
                return ['success' => false, 'error' => 'Non è possibile eliminare l\'utente admin principale'];
            }
            
            // Scollega docenti se collegati
            $this->db->execute("UPDATE docenti SET user_id = NULL WHERE user_id = ?", [$id]);
            
            // Elimina utente
            $this->db->execute("DELETE FROM users WHERE id = ?", [$id]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Lista docenti disponibili (senza user_id)
     */
    public function getDocentiDisponibili() {
        $sql = "
            SELECT id, cognome, nome 
            FROM docenti 
            WHERE user_id IS NULL AND attivo = 1
            ORDER BY cognome, nome
        ";
        return $this->db->query($sql);
    }
    
    /**
     * Tutti i docenti per select
     */
    public function getAllDocenti() {
        $sql = "
            SELECT id, cognome, nome, user_id
            FROM docenti 
            WHERE attivo = 1
            ORDER BY cognome, nome
        ";
        return $this->db->query($sql);
    }
    
    /**
     * Toggle attivo/disattivo
     */
    public function toggleActive($id) {
        try {
            $user = $this->getById($id);
            if (!$user) {
                return ['success' => false, 'error' => 'Utente non trovato'];
            }
            
            // Blocca se è l'ultimo admin attivo
            if ($user['active'] == 1 && SecurityHelper::isLastActiveAdmin($this->db, $id)) {
                return [
                    'success' => false, 
                    'error' => 'Non puoi disabilitare l\'ultimo amministratore attivo. Crea prima un altro admin.'
                ];
            }
            
            $newStatus = $user['active'] ? 0 : 1;
            $this->db->execute("UPDATE users SET active = ? WHERE id = ?", [$newStatus, $id]);
            
            return ['success' => true, 'active' => $newStatus];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}