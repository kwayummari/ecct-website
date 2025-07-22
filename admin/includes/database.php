<?php

/**
 * Database Class for ECCT Website
 * Simple PDO wrapper for MySQL operations
 */

if (!defined('ECCT_ROOT')) {
    die('Direct access not allowed');
}

class Database
{
    private $pdo;
    private $error;

    public function __construct()
    {
        $this->connect();
    }

    /**
     * Connect to database
     */
    private function connect()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            die("Database connection failed: " . $this->error);
        }
    }

    /**
     * Execute a prepared statement
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database query error: " . $this->error . " | SQL: " . $sql);
            return false;
        }
    }

    /**
     * Select records from table
     */
    public function select($table, $conditions = [], $options = [])
    {
        $sql = "SELECT ";
        $sql .= isset($options['columns']) ? implode(', ', $options['columns']) : '*';
        $sql .= " FROM {$table}";

        $params = [];

        if (!empty($conditions)) {
            $where_clauses = [];
            foreach ($conditions as $key => $value) {
                if (is_array($value)) {
                    $placeholders = str_repeat('?,', count($value) - 1) . '?';
                    $where_clauses[] = "{$key} IN ({$placeholders})";
                    $params = array_merge($params, $value);
                } else {
                    $where_clauses[] = "{$key} = ?";
                    $params[] = $value;
                }
            }
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }

        if (isset($options['order_by'])) {
            $sql .= " ORDER BY " . $options['order_by'];
        }

        if (isset($options['limit'])) {
            $sql .= " LIMIT " . $options['limit'];
            if (isset($options['offset'])) {
                $sql .= " OFFSET " . $options['offset'];
            }
        }

        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : false;
    }

    /**
     * Select a single record
     */
    public function selectOne($table, $conditions = [], $columns = '*')
    {
        $options = ['columns' => is_array($columns) ? $columns : [$columns], 'limit' => 1];
        $results = $this->select($table, $conditions, $options);
        return $results ? $results[0] : false;
    }

    /**
     * Insert a record
     */
    public function insert($table, $data)
    {
        $columns = array_keys($data);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';

        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";

        $stmt = $this->query($sql, array_values($data));
        return $stmt ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Update records
     */
    public function update($table, $data, $conditions = [])
    {
        $set_clauses = [];
        $params = [];

        foreach ($data as $key => $value) {
            $set_clauses[] = "{$key} = ?";
            $params[] = $value;
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $set_clauses);

        if (!empty($conditions)) {
            $where_clauses = [];
            foreach ($conditions as $key => $value) {
                $where_clauses[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }

        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->rowCount() : false;
    }

    /**
     * Delete records
     */
    public function delete($table, $conditions = [])
    {
        if (empty($conditions)) {
            return false; // Prevent accidental deletion of all records
        }

        $where_clauses = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            $where_clauses[] = "{$key} = ?";
            $params[] = $value;
        }

        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $where_clauses);

        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->rowCount() : false;
    }

    /**
     * Count records
     */
    public function count($table, $conditions = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        $params = [];

        if (!empty($conditions)) {
            $where_clauses = [];
            foreach ($conditions as $key => $value) {
                $where_clauses[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }

        $stmt = $this->query($sql, $params);
        $result = $stmt ? $stmt->fetch() : false;
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Check if record exists
     */
    public function exists($table, $conditions = [])
    {
        return $this->count($table, $conditions) > 0;
    }

    /**
     * Get paginated results
     */
    public function paginate($table, $page = 1, $perPage = 10, $conditions = [], $options = [])
    {
        $offset = ($page - 1) * $perPage;
        $options['limit'] = $perPage;
        $options['offset'] = $offset;

        $data = $this->select($table, $conditions, $options);
        $total = $this->count($table, $conditions);
        $totalPages = ceil($total / $perPage);

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
    }

    /**
     * Execute raw SQL query
     */
    public function raw($sql, $params = [])
    {
        return $this->query($sql, $params);
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->pdo->rollback();
    }

    /**
     * Get site settings
     */
    public function getSetting($key, $default = null)
    {
        $result = $this->selectOne('site_settings', ['setting_key' => $key]);
        return $result ? $result['setting_value'] : $default;
    }

    /**
     * Update site setting
     */
    public function updateSetting($key, $value)
    {
        if ($this->exists('site_settings', ['setting_key' => $key])) {
            return $this->update('site_settings', ['setting_value' => $value], ['setting_key' => $key]);
        } else {
            return $this->insert('site_settings', [
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_type' => 'text'
            ]);
        }
    }

    /**
     * Get published content
     */
    public function getPublishedContent($table, $options = [])
    {
        $conditions = ['is_published' => 1];

        if (isset($options['conditions'])) {
            $conditions = array_merge($conditions, $options['conditions']);
        }

        if (!isset($options['order_by'])) {
            $options['order_by'] = 'created_at DESC';
        }

        return $this->select($table, $conditions, $options);
    }

    /**
     * Search across multiple columns
     */
    public function search($table, $searchTerm, $columns, $conditions = [], $options = [])
    {
        $sql = "SELECT ";
        $sql .= isset($options['select']) ? implode(', ', $options['select']) : '*';
        $sql .= " FROM {$table} WHERE (";

        $search_clauses = [];
        $params = [];

        foreach ($columns as $column) {
            $search_clauses[] = "{$column} LIKE ?";
            $params[] = "%{$searchTerm}%";
        }

        $sql .= implode(' OR ', $search_clauses) . ")";

        if (!empty($conditions)) {
            $where_clauses = [];
            foreach ($conditions as $key => $value) {
                $where_clauses[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " AND " . implode(' AND ', $where_clauses);
        }

        if (isset($options['order_by'])) {
            $sql .= " ORDER BY " . $options['order_by'];
        }

        if (isset($options['limit'])) {
            $sql .= " LIMIT " . $options['limit'];
        }

        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : false;
    }

    /**
     * Get last error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Close connection
     */
    public function close()
    {
        $this->pdo = null;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }
}
