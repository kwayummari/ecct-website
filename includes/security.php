<?php
/**
 * Security Class - Enhanced Security Features
 * Rate Limiting, CSRF Protection, Session Management, and Input Validation
 */

class Security {
    
    /**
     * Rate Limiting Class
     */
    public static function checkRateLimit($identifier, $max_requests = null, $window = null) {
        $max_requests = $max_requests ?: RATE_LIMIT_REQUESTS;
        $window = $window ?: RATE_LIMIT_WINDOW;
        
        $cache_key = 'rate_limit_' . md5($identifier);
        $cache_file = sys_get_temp_dir() . '/' . $cache_key . '.json';
        
        $current_time = time();
        $requests = [];
        
        // Load existing requests
        if (file_exists($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            if ($data && isset($data['requests'])) {
                $requests = $data['requests'];
            }
        }
        
        // Remove expired requests
        $requests = array_filter($requests, function($timestamp) use ($current_time, $window) {
            return ($current_time - $timestamp) < $window;
        });
        
        // Check if limit exceeded
        if (count($requests) >= $max_requests) {
            return false;
        }
        
        // Add current request
        $requests[] = $current_time;
        
        // Save to cache
        file_put_contents($cache_file, json_encode(['requests' => $requests]));
        
        return true;
    }
    
    /**
     * Enhanced CSRF Protection
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_time'] = time();
        
        return $token;
    }
    
    public static function validateCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_time'])) {
            return false;
        }
        
        // Check token expiry (15 minutes)
        if (time() - $_SESSION['csrf_time'] > 900) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_time']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Enhanced Session Management
     */
    public static function startSecureSession() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        
        // Configure session settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Lax');
        
        session_name(SESSION_NAME);
        session_start();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
        
        // Check session validity
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            session_destroy();
            session_start();
        } else {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }
    }
    
    /**
     * Password Security
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_ROUNDS]);
    }
    
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public static function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Enhanced Input Validation
     */
    public static function sanitizeInput($input, $type = 'string') {
        if (is_array($input)) {
            return array_map(function($item) use ($type) {
                return self::sanitizeInput($item, $type);
            }, $input);
        }
        
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        switch ($type) {
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            
            case 'url':
                return filter_var(trim($input), FILTER_SANITIZE_URL);
            
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            
            case 'html':
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
            
            case 'sql':
                return addslashes(trim($input));
            
            default: // string
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    public static function validateInput($input, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule_set) {
            $value = $input[$field] ?? null;
            
            foreach ($rule_set as $rule => $parameter) {
                switch ($rule) {
                    case 'required':
                        if ($parameter && (empty($value) || trim($value) === '')) {
                            $errors[$field][] = ucfirst($field) . ' is required';
                        }
                        break;
                    
                    case 'min_length':
                        if (!empty($value) && strlen($value) < $parameter) {
                            $errors[$field][] = ucfirst($field) . " must be at least {$parameter} characters";
                        }
                        break;
                    
                    case 'max_length':
                        if (!empty($value) && strlen($value) > $parameter) {
                            $errors[$field][] = ucfirst($field) . " must not exceed {$parameter} characters";
                        }
                        break;
                    
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = ucfirst($field) . ' must be a valid email address';
                        }
                        break;
                    
                    case 'phone':
                        if (!empty($value) && !preg_match('/^[\+]?[0-9\s\-\(\)]{7,15}$/', $value)) {
                            $errors[$field][] = ucfirst($field) . ' must be a valid phone number';
                        }
                        break;
                    
                    case 'url':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                            $errors[$field][] = ucfirst($field) . ' must be a valid URL';
                        }
                        break;
                    
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $errors[$field][] = ucfirst($field) . ' must be a number';
                        }
                        break;
                    
                    case 'date':
                        if (!empty($value) && !strtotime($value)) {
                            $errors[$field][] = ucfirst($field) . ' must be a valid date';
                        }
                        break;
                        
                    case 'in':
                        if (!empty($value) && !in_array($value, $parameter)) {
                            $errors[$field][] = ucfirst($field) . ' contains an invalid value';
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * File Upload Security
     */
    public static function validateFileUpload($file, $allowed_types = null, $max_size = null) {
        $allowed_types = $allowed_types ?: ALLOWED_IMAGE_TYPES;
        $max_size = $max_size ?: MAX_FILE_SIZE;
        
        $errors = [];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = 'File size exceeds the maximum allowed size';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = 'File was only partially uploaded';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errors[] = 'No file was uploaded';
                    break;
                default:
                    $errors[] = 'File upload failed';
            }
            return $errors;
        }
        
        // Check file size
        if ($file['size'] > $max_size) {
            $errors[] = 'File size exceeds ' . round($max_size / 1024 / 1024, 1) . 'MB limit';
        }
        
        // Check file type
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $allowed_types);
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowed_mimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];
        
        if (!in_array($mime_type, $allowed_mimes)) {
            $errors[] = 'Invalid file type detected';
        }
        
        // Check for malicious content (basic)
        $file_content = file_get_contents($file['tmp_name']);
        if (strpos($file_content, '<?php') !== false || strpos($file_content, '<script') !== false) {
            $errors[] = 'File contains potentially malicious content';
        }
        
        return $errors;
    }
    
    /**
     * Login Attempt Tracking
     */
    public static function trackLoginAttempt($identifier, $success = false) {
        $cache_key = 'login_attempts_' . md5($identifier);
        $cache_file = sys_get_temp_dir() . '/' . $cache_key . '.json';
        
        $data = ['attempts' => 0, 'last_attempt' => 0, 'locked_until' => 0];
        
        if (file_exists($cache_file)) {
            $stored_data = json_decode(file_get_contents($cache_file), true);
            if ($stored_data) {
                $data = $stored_data;
            }
        }
        
        if ($success) {
            // Reset on successful login
            unlink($cache_file);
            return true;
        }
        
        $data['attempts']++;
        $data['last_attempt'] = time();
        
        if ($data['attempts'] >= MAX_LOGIN_ATTEMPTS) {
            $data['locked_until'] = time() + LOCKOUT_DURATION;
        }
        
        file_put_contents($cache_file, json_encode($data));
        
        return $data['attempts'] < MAX_LOGIN_ATTEMPTS;
    }
    
    public static function isAccountLocked($identifier) {
        $cache_key = 'login_attempts_' . md5($identifier);
        $cache_file = sys_get_temp_dir() . '/' . $cache_key . '.json';
        
        if (!file_exists($cache_file)) {
            return false;
        }
        
        $data = json_decode(file_get_contents($cache_file), true);
        if (!$data) {
            return false;
        }
        
        if (isset($data['locked_until']) && $data['locked_until'] > time()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate secure random tokens
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Constant time string comparison
     */
    public static function hashEquals($known, $user) {
        return hash_equals($known, $user);
    }
}

// Initialize secure session
Security::startSecureSession();
?>