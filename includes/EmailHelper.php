<?php
/**
 * EmailHelper - Gestione invio email
 * 
 * Gestisce l'invio di email per attivazione account, reset password, etc.
 */

class EmailHelper {
    
    private $from;
    private $fromName;
    
    public function __construct() {
        $this->from = defined('EMAIL_FROM') ? EMAIL_FROM : 'noreply@musicall.local';
        $this->fromName = defined('APP_NAME') ? APP_NAME : 'MusicAll';
    }
    
    /**
     * Invia email di attivazione account
     * 
     * @param string $toEmail Email destinatario
     * @param string $username Username utente
     * @param string $urlToken Token per URL (hash)
     * @param string $activationCode Codice attivazione a 6 cifre
     * @return bool True se inviata con successo
     */
    public function sendActivationEmail($toEmail, $username, $urlToken, $activationCode) {
        $subject = "[{$this->fromName}] Attivazione Account";
        
        $message = $this->getActivationEmailTemplate($username, $urlToken, $activationCode);
        
        return $this->sendEmail($toEmail, $subject, $message);
    }
    
    /**
     * Invia email di reset password
     * 
     * @param string $toEmail Email destinatario
     * @param string $username Username utente
     * @param string $token Codice reset a 6 cifre
     * @return bool True se inviata con successo
     */
    public function sendPasswordResetEmail($toEmail, $username, $token) {
        $subject = "[{$this->fromName}] Reset Password";
        
        $message = $this->getPasswordResetEmailTemplate($username, $token);
        
        return $this->sendEmail($toEmail, $subject, $message);
    }
    
    /**
     * Template email attivazione
     */
    private function getActivationEmailTemplate($username, $urlToken, $activationCode) {
        $appUrl = $this->getAppUrl();
        $activationUrl = $appUrl . '/activate.php?token=' . $urlToken;
        
        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .content { background: #f8f9fa; padding: 30px; margin: 20px 0; }
        .token { font-size: 32px; font-weight: bold; color: #007bff; text-align: center; 
                 background: white; padding: 20px; margin: 20px 0; border: 2px dashed #007bff; }
        .button { display: inline-block; padding: 12px 30px; background: #007bff; 
                 color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🎵 {$this->fromName}</h1>
            <p>Benvenuto!</p>
        </div>
        
        <div class='content'>
            <h2>Ciao <strong>$username</strong>!</h2>
            
            <p>Il tuo account è stato creato con successo!</p>
            
            <p>Per attivare il tuo account, clicca sul pulsante qui sotto:</p>
            
            <p style='text-align: center;'>
                <a href='$activationUrl' class='button'>Attiva Account</a>
            </p>
            
            <p>Ti verrà richiesto di inserire il seguente codice di attivazione:</p>
            
            <div class='token'>$activationCode</div>
            
            <p><strong>Oppure inserisci manualmente il codice nella pagina di attivazione.</strong></p>
            
            <p style='text-align: center;'>
                <a href='$activationUrl' class='button'>Attiva Account</a>
            </p>
            
            <p><strong>⚠️ Importante:</strong></p>
            <ul>
                <li>Il codice è valido per <strong>24 ore</strong></li>
                <li>Al primo accesso dovrai cambiare la password</li>
                <li>Se non hai richiesto questa registrazione, ignora questa email</li>
            </ul>
            
            <p>Una volta attivato, potrai accedere con:</p>
            <ul>
                <li><strong>Username:</strong> $username</li>
                <li><strong>Password:</strong> quella impostata in fase di creazione</li>
            </ul>
        </div>
        
        <div class='footer'>
            <p>Questa è un'email automatica, non rispondere.</p>
            <p>&copy; " . date('Y') . " {$this->fromName} - Tutti i diritti riservati</p>
        </div>
    </div>
</body>
</html>
";
    }
    
    /**
     * Template email reset password
     */
    private function getPasswordResetEmailTemplate($username, $token) {
        $appUrl = $this->getAppUrl();
        $resetUrl = $appUrl . '/reset_password.php?token=' . $token;
        
        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
        .content { background: #f8f9fa; padding: 30px; margin: 20px 0; }
        .token { font-size: 32px; font-weight: bold; color: #dc3545; text-align: center; 
                 background: white; padding: 20px; margin: 20px 0; border: 2px dashed #dc3545; }
        .button { display: inline-block; padding: 12px 30px; background: #dc3545; 
                 color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔐 Reset Password</h1>
        </div>
        
        <div class='content'>
            <h2>Ciao <strong>$username</strong>,</h2>
            
            <p>Hai richiesto il reset della password.</p>
            
            <p>Utilizza il seguente codice per reimpostare la password:</p>
            
            <div class='token'>$token</div>
            
            <p><strong>Oppure clicca sul pulsante:</strong></p>
            
            <p style='text-align: center;'>
                <a href='$resetUrl' class='button'>Reset Password</a>
            </p>
            
            <p><strong>⚠️ Attenzione:</strong></p>
            <ul>
                <li>Il codice è valido per <strong>15 minuti</strong></li>
                <li>Se non hai richiesto il reset, ignora questa email</li>
                <li>La tua password attuale rimane attiva fino al completamento del reset</li>
            </ul>
        </div>
        
        <div class='footer'>
            <p>Questa è un'email automatica, non rispondere.</p>
            <p>&copy; " . date('Y') . " {$this->fromName}</p>
        </div>
    </div>
</body>
</html>
";
    }
    
    /**
     * Invia email generica
     * 
     * @param string $to Email destinatario
     * @param string $subject Oggetto
     * @param string $message Corpo HTML
     * @return bool
     */
    private function sendEmail($to, $subject, $message) {
        // Headers per email HTML
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            "From: {$this->fromName} <{$this->from}>",
            "Reply-To: {$this->from}",
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Verifica email valida
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("EmailHelper: Email non valida: $to");
            return false;
        }
        
        // Invia email (sopprimi warning per mail() in locale)
        $result = @mail($to, $subject, $message, implode("\r\n", $headers));
        
        if (!$result) {
            error_log("EmailHelper: Errore invio email a $to");
        }
        
        return $result;
    }
    
    /**
     * Ottiene URL base applicazione
     */
    private function getAppUrl() {
        if (defined('APP_URL')) {
            return rtrim(APP_URL, '/');
        }
        
        // Fallback: costruisce URL da $_SERVER
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $port = $_SERVER['SERVER_PORT'] ?? '80';
        
        $url = $protocol . '://' . $host;
        
        // Aggiungi porta solo se non standard
        if (($protocol === 'http' && $port != '80') || ($protocol === 'https' && $port != '443')) {
            $url .= ':' . $port;
        }
        
        return $url;
    }
}