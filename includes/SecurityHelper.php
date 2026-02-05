<?php
/**
 * SecurityHelper - Funzioni di sicurezza
 * 
 * Gestisce validazione password, criptazione email, etc.
 */

class SecurityHelper {
    
    /**
     * Valida password forte
     * - Minimo 6 caratteri
     * - Almeno 1 numero
     * - Almeno 1 maiuscola
     * 
     * @param string $password
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public static function validateStrongPassword($password) {
        if (strlen($password) < 6) {
            return [
                'valid' => false,
                'error' => 'La password deve contenere almeno 6 caratteri'
            ];
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return [
                'valid' => false,
                'error' => 'La password deve contenere almeno un numero'
            ];
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return [
                'valid' => false,
                'error' => 'La password deve contenere almeno una lettera maiuscola'
            ];
        }
        
        return ['valid' => true, 'error' => null];
    }
    
    /**
     * Cripta un'email usando AES-256-CBC
     * 
     * @param string $email
     * @return string Email criptata (base64)
     */
    public static function encryptEmail($email) {
        if (empty($email)) {
            return null;
        }
        
        $key = self::getEncryptionKey();
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        
        $encrypted = openssl_encrypt(
            $email,
            'aes-256-cbc',
            $key,
            0,
            $iv
        );
        
        // Combina IV + encrypted e codifica in base64
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decripta un'email
     * 
     * @param string $encryptedEmail Email criptata (base64)
     * @return string Email in chiaro
     */
    public static function decryptEmail($encryptedEmail) {
        if (empty($encryptedEmail)) {
            return null;
        }
        
        $key = self::getEncryptionKey();
        $data = base64_decode($encryptedEmail);
        
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        return openssl_decrypt(
            $encrypted,
            'aes-256-cbc',
            $key,
            0,
            $iv
        );
    }
    
    /**
     * Ottiene la chiave di criptazione
     * IMPORTANTE: In produzione, salvare in variabile d'ambiente
     * 
     * @return string
     */
    private static function getEncryptionKey() {
        // Usa chiave da config o genera una di default
        // IMPORTANTE: Cambiare in produzione e salvare in .env
        if (defined('ENCRYPTION_KEY')) {
            return ENCRYPTION_KEY;
        }
        
        // Fallback (NON sicuro in produzione!)
        return hash('sha256', 'musicall_encryption_key_' . APP_NAME, true);
    }
    
    /**
     * Verifica se l'ultimo admin attivo sta per essere disabilitato
     * 
     * @param Database $db
     * @param int $userId ID utente da controllare
     * @return bool true se è l'ultimo admin attivo
     */
    public static function isLastActiveAdmin($db, $userId) {
        // Conta admin attivi
        $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND active = 1";
        $result = $db->queryOne($sql);
        $activeAdminsCount = $result['count'] ?? 0;
        
        // Verifica se questo user è admin
        $user = $db->queryOne("SELECT role, active FROM users WHERE id = ?", [$userId]);
        
        // È l'ultimo admin attivo se:
        // - C'è solo 1 admin attivo E
        // - Questo utente è admin E
        // - Questo utente è attivo
        return ($activeAdminsCount <= 1 && 
                $user && 
                $user['role'] === 'admin' && 
                $user['active'] == 1);
    }
    
    /**
     * Genera un token casuale alfanumerico
     * 
     * @param int $length Lunghezza del token
     * @return string Token generato
     */
    public static function generateToken($length = 6) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = '';
        
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $token;
    }
    
    /**
     * Valida formato email
     * 
     * @param string $email
     * @return bool
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}