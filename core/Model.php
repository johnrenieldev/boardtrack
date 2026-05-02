<?php

class Model
{
    protected PDO    $db;
    protected string $table = '';   // Override in each model

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ─────────────────────────────────────────────────────────
    // CORE QUERY HELPERS
    // ─────────────────────────────────────────────────────────

    /**
     * Fetch all rows from the model's table.
     *
     * @param string $orderBy   Column + direction, e.g. 'created_at DESC'
     * @return array
     */
    public function all(string $orderBy = 'id ASC'): array
    {
        $sql  = "SELECT * FROM `{$this->table}` ORDER BY {$orderBy}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find a single row by primary key.
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find a single row matching a column + value.
     *
     * @param string $column   Column name
     * @param mixed  $value    Value to match
     * @return array|null
     */
    public function findBy(string $column, mixed $value): ?array
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE `{$column}` = :value LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':value' => $value]);
        $row  = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find all rows matching a column + value.
     *
     * @param string $column
     * @param mixed  $value
     * @param string $orderBy
     * @return array
     */
    public function findAllBy(string $column, mixed $value, string $orderBy = 'id ASC'): array
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE `{$column}` = :value ORDER BY {$orderBy}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':value' => $value]);
        return $stmt->fetchAll();
    }

    /**
     * Insert a new row.
     *
     * @param array $data   Associative array of column => value
     * @return int          Last inserted ID
     */
    public function insert(array $data): int
    {
        $columns      = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $this->table,
            implode(', ', array_map(fn($c) => "`{$c}`", $columns)),
            implode(', ', $placeholders)
        );

        $params = [];
        foreach ($data as $col => $val) {
            $params[":{$col}"] = $val;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update rows matching a WHERE condition.
     *
     * @param array  $data        Columns to update
     * @param array  $where       Condition columns ['column' => 'value']
     * @return int                Number of affected rows
     */
    public function update(array $data, array $where): int
    {
        $setClauses   = [];
        $whereClauses = [];
        $params       = [];

        foreach ($data as $col => $val) {
            $setClauses[]       = "`{$col}` = :set_{$col}";
            $params[":set_{$col}"] = $val;
        }

        foreach ($where as $col => $val) {
            $whereClauses[]        = "`{$col}` = :wh_{$col}";
            $params[":wh_{$col}"]  = $val;
        }

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE %s',
            $this->table,
            implode(', ', $setClauses),
            implode(' AND ', $whereClauses)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Delete rows by primary key.
     *
     * @param int $id
     * @return int   Affected rows
     */
    public function delete(int $id): int
    {
        $sql  = "DELETE FROM `{$this->table}` WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    /**
     * Count all rows — optionally filtered.
     *
     * @param string $where   Raw WHERE clause (use with care)
     * @param array  $params  Bound parameters for the WHERE clause
     * @return int
     */
    public function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        if ($where) $sql .= " WHERE {$where}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Check if a row with the given column/value exists.
     *
     * @param string $column
     * @param mixed  $value
     * @return bool
     */
    public function exists(string $column, mixed $value): bool
    {
        $sql  = "SELECT 1 FROM `{$this->table}` WHERE `{$column}` = :value LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':value' => $value]);
        return $stmt->fetchColumn() !== false;
    }

    // ─────────────────────────────────────────────────────────
    // RAW QUERY (for complex JOINs etc.)
    // ─────────────────────────────────────────────────────────

    /**
     * Execute a raw prepared query and return all results.
     * Use ONLY when the helper methods are insufficient.
     *
     * @param string $sql    SQL with named placeholders (:param)
     * @param array  $params Bound parameters
     * @return array
     */
    public function rawQuery(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a raw prepared query and return the first row only.
     *
     * @param string $sql
     * @param array  $params
     * @return array|null
     */
    public function rawQueryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row  = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Execute a raw statement (INSERT / UPDATE / DELETE).
     *
     * @param string $sql
     * @param array  $params
     * @return int   Affected rows
     */
    public function rawExecute(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    // ─────────────────────────────────────────────────────────
    // TRANSACTION HELPERS
    // ─────────────────────────────────────────────────────────

    public function beginTransaction(): void  { $this->db->beginTransaction(); }
    public function commit(): void            { $this->db->commit(); }
    public function rollback(): void          { $this->db->rollBack(); }
}