<?php
/**
 * Environment Configuration Loader
 * Loads environment variables from .env file
 */

class EnvLoader {
    private static $loaded = false;
    private static $env = [];

    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }

        $envFile = $path ?: __DIR__ . '/../.env';
        
        if (!file_exists($envFile)) {
            // Fallback to default values or throw error
            self::loadDefaults();
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Skip comments
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                $value = $matches[2];
            }

            // Convert boolean strings
            if (strtolower($value) === 'true') {
                $value = true;
            } elseif (strtolower($value) === 'false') {
                $value = false;
            } elseif (is_numeric($value)) {
                $value = is_float($value) ? (float)$value : (int)$value;
            }

            self::$env[$name] = $value;
            $_ENV[$name] = $value;
            
            // Also set as $_SERVER variable
            if (!array_key_exists($name, $_SERVER)) {
                $_SERVER[$name] = $value;
            }
        }

        self::$loaded = true;
    }

    private static function loadDefaults() {
        $defaults = [
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'ecct_db',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'SITE_URL' => 'http://localhost',
            'SITE_NAME' => 'ECCT',
            'DEBUG_MODE' => true,
            'SECRET_KEY' => 'default_secret_key_change_in_production',
            'CSRF_SECRET' => 'default_csrf_secret_change_in_production',
            'SESSION_LIFETIME' => 7200,
            'PASSWORD_MIN_LENGTH' => 8,
            'MAX_LOGIN_ATTEMPTS' => 5,
            'LOCKOUT_DURATION' => 1800,
            'BCRYPT_ROUNDS' => 12,
            'RATE_LIMIT_REQUESTS' => 100,
            'RATE_LIMIT_WINDOW' => 3600,
            'MAX_FILE_SIZE' => 5242880,
            'ALLOWED_IMAGE_TYPES' => 'jpg,jpeg,png,gif,webp',
            'UPLOAD_PATH' => 'assets/uploads'
        ];

        foreach ($defaults as $key => $value) {
            self::$env[$key] = $value;
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        self::$loaded = true;
    }

    public static function get($key, $default = null) {
        self::load();
        return self::$env[$key] ?? $default;
    }

    public static function set($key, $value) {
        self::$env[$key] = $value;
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    public static function all() {
        self::load();
        return self::$env;
    }
}

// Helper function
function env($key, $default = null) {
    return EnvLoader::get($key, $default);
}

// Load environment variables
EnvLoader::load();
?>