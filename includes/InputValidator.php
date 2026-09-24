<?php
/**
 * Input Validator
 * Validazione centralizzata input utente
 */

class InputValidator {
    private $errors = [];
    
    /**
     * Valida email
     */
    public function email($value, $field = 'email', $required = true) {
        if (empty($value)) {
            if ($required) {
                $this->errors[$field] = 'Email obbligatoria';
            }
            return $this;
        }
        
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Email non valida';
        }
        
        return $this;
    }
    
    /**
     * Valida stringa
     */
    public function string($value, $field, $min = null, $max = null, $required = true) {
        if (empty($value)) {
            if ($required) {
                $this->errors[$field] = ucfirst($field) . ' obbligatorio';
            }
            return $this;
        }
        
        $len = mb_strlen($value);
        
        if ($min !== null && $len < $min) {
            $this->errors[$field] = ucfirst($field) . " deve essere almeno {$min} caratteri";
        }
        
        if ($max !== null && $len > $max) {
            $this->errors[$field] = ucfirst($field) . " non può superare {$max} caratteri";
        }
        
        return $this;
    }
    
    /**
     * Valida numero intero
     */
    public function integer($value, $field, $min = null, $max = null, $required = true) {
        if ($value === null || $value === '') {
            if ($required) {
                $this->errors[$field] = ucfirst($field) . ' obbligatorio';
            }
            return $this;
        }
        
        if (!is_numeric($value) || (int)$value != $value) {
            $this->errors[$field] = ucfirst($field) . ' deve essere un numero intero';
            return $this;
        }
        
        $value = (int)$value;
        
        if ($min !== null && $value < $min) {
            $this->errors[$field] = ucfirst($field) . " deve essere almeno {$min}";
        }
        
        if ($max !== null && $value > $max) {
            $this->errors[$field] = ucfirst($field) . " non può superare {$max}";
        }
        
        return $this;
    }
    
    /**
     * Valida data
     */
    public function date($value, $field, $format = 'Y-m-d', $required = true) {
        if (empty($value)) {
            if ($required) {
                $this->errors[$field] = ucfirst($field) . ' obbligatorio';
            }
            return $this;
        }
        
        $d = DateTime::createFromFormat($format, $value);
        if (!$d || $d->format($format) !== $value) {
            $this->errors[$field] = ucfirst($field) . ' non valido (formato: ' . $format . ')';
        }
        
        return $this;
    }
    
    /**
     * Valida ora
     */
    public function time($value, $field, $required = true) {
        if (empty($value)) {
            if ($required) {
                $this->errors[$field] = ucfirst($field) . ' obbligatorio';
            }
            return $this;
        }
        
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $value)) {
            $this->errors[$field] = ucfirst($field) . ' non valido (formato: HH:MM)';
        }
        
        return $this;
    }
    
    /**
     * Valida telefono italiano
     */
    public function phone($value, $field, $required = true) {
        if (empty($value)) {
            if ($required) {
                $this->errors[$field] = 'Telefono obbligatorio';
            }
            return $this;
        }
        
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        
        if (strlen($cleaned) < 9 || strlen($cleaned) > 13) {
            $this->errors[$field] = 'Telefono non valido';
        }
        
        return $this;
    }
    
    /**
     * Valida range
     */
    public function inRange($value, $field, $min, $max) {
        if ($value < $min || $value > $max) {
            $this->errors[$field] = ucfirst($field) . " deve essere tra {$min} e {$max}";
        }
        
        return $this;
    }
    
    /**
     * Valida enum (valore in lista)
     */
    public function inList($value, $field, $allowed, $required = true) {
        if (empty($value)) {
            if ($required) {
                $this->errors[$field] = ucfirst($field) . ' obbligatorio';
            }
            return $this;
        }
        
        if (!in_array($value, $allowed)) {
            $this->errors[$field] = ucfirst($field) . ' non valido';
        }
        
        return $this;
    }
    
    /**
     * Valida password secondo OWASP 2021 guidelines
     * REASON: PASSWORD_MIN_LENGTH=6 è vulnerabile (crackable in ore su GPU moderna)
     * SEVERITY: HIGH - sec-005
     */
    public function password($value, $field = 'password', $required = true) {
        if (empty($value)) {
            if ($required) {
                $this->errors[$field] = 'Password obbligatoria';
            }
            return $this;
        }

        $minLength = PASSWORD_MIN_LENGTH ?? 12;  // Default OWASP recommended
        $length = mb_strlen($value);

        if ($length < $minLength) {
            $this->errors[$field] = "Password deve essere almeno {$minLength} caratteri";
            return $this;
        }

        // Controlli complessità (se configurati)
        $errors = [];
        
        if (defined('PASSWORD_REQUIRE_UPPERCASE') && PASSWORD_REQUIRE_UPPERCASE) {
            if (!preg_match('/[A-Z]/', $value)) {
                $errors[] = 'maiuscola';
            }
        }
        
        if (defined('PASSWORD_REQUIRE_LOWERCASE') && PASSWORD_REQUIRE_LOWERCASE) {
            if (!preg_match('/[a-z]/', $value)) {
                $errors[] = 'minuscola';
            }
        }
        
        if (defined('PASSWORD_REQUIRE_NUMBERS') && PASSWORD_REQUIRE_NUMBERS) {
            if (!preg_match('/[0-9]/', $value)) {
                $errors[] = 'numero';
            }
        }
        
        if (defined('PASSWORD_REQUIRE_SYMBOLS') && PASSWORD_REQUIRE_SYMBOLS) {
            if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:"\'<>,.?\/]/', $value)) {
                $errors[] = 'carattere speciale';
            }
        }

        if (!empty($errors)) {
            $missing = implode(', ', $errors);
            $this->errors[$field] = "Password deve contenere: {$missing}";
        }

        return $this;
    }
    
    /**
     * Sanitizza stringa per output HTML (sec-007: Input Sanitization)
     * REASON: Previene XSS injection sanitizzando special characters
     * SEVERITY: HIGH - sec-007
     */
    public static function sanitizeOutput($value) {
        if (is_array($value)) {
            return array_map([self::class, 'sanitizeOutput'], $value);
        }
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Sanitizza stringa per database (basic escaping, usa prepared statements in queries)
     */
    public static function sanitizeDatabase($value) {
        if (is_array($value)) {
            return array_map([self::class, 'sanitizeDatabase'], $value);
        }
        return addslashes(strip_tags($value ?? ''));
    }
    
    /**
     * Sanitizza URL per sicurezza
     */
    public static function sanitizeUrl($value) {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        return '';
    }
    
    /**
     * Estrae e sanitizza un valore da $_GET o $_POST
     */
    public static function sanitizeInput($key, $default = '', $sanitize = 'output') {
        $value = $_REQUEST[$key] ?? $default;
        
        if ($sanitize === 'output') {
            return self::sanitizeOutput($value);
        } elseif ($sanitize === 'database') {
            return self::sanitizeDatabase($value);
        } elseif ($sanitize === 'url') {
            return self::sanitizeUrl($value);
        }
        
        return $value;
    }
    
    /**
     * Verifica se ci sono errori
     */


    public function fails() {
        return !empty($this->errors);
    }
    
    /**
     * Ottieni errori
     */
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Lancia eccezione se validazione fallisce
     */
    public function validate() {
        if ($this->fails()) {
            throw new ValidationException($this->errors);
        }
    }
    
    /**
     * Reset errori
     */
    public function reset() {
        $this->errors = [];
        return $this;
    }
}

/**
 * Eccezione validazione
 */
class ValidationException extends Exception {
    private $errors;
    
    public function __construct($errors) {
        $this->errors = $errors;
        parent::__construct('Errori di validazione');
    }
    
    public function getErrors() {
        return $this->errors;
    }
}
