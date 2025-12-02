<?php
require('db.php');
class Model {
    protected $table;
    protected $primaryKey;
    protected $db;

    public function __construct($table, $primaryKey = 'id') {
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->db = $GLOBALS['conn']; // Using the existing connection from the global variable
    }

    public function read_all($limit = null, $orderBy = null) {
        $query = "SELECT * FROM {$this->table}";

        if ($orderBy) {
            $query .= " ORDER BY " . $this->sanitizeField($orderBy);
        }

        if ($limit) {
            $query .= " LIMIT ?";
        }

        $stmt = $this->db->prepare($query);
        
        if ($limit) {
            $stmt->bind_param('i', $limit); // Assuming limit is always an integer
        }

        $stmt->execute();
        return $this->fetchAll($stmt);
    }

    public function read($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param('i', $id); // Assuming primary key is always an integer
        $stmt->execute();
        return $this->fetch($stmt);
    }

    public function query($sql, $params = [], $types = '') {
        $stmt = $this->db->prepare($sql);

        if ($params) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $this->fetchAll($stmt);
    }

    public function create($data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = str_repeat('?,', count($data) - 1) . '?';
        $types = $this->determineTypes($data);

        // Prepare the SQL statement
        $stmt = $this->db->prepare("INSERT INTO {$this->table} ($columns) VALUES ($placeholders)");
        
        if ($stmt === false) {
            // Handle the case where the statement couldn't be prepared
            throw new Exception("Error preparing statement: " . $this->db->error);
        }

        // Bind the parameters
        $stmt->bind_param($types, ...array_values($data));

        // Execute the query and check for success
        if (!$stmt->execute()) {
            // Handle execution error
            throw new Exception("Error executing statement: " . $stmt->error);
        }

        // Return the last inserted ID
        return $this->db->insert_id;
    }

    public function update($id, $data) {
        $set = "";
        $values = [];

        foreach ($data as $key => $value) {
            $set .= "$key = ?, ";
            $values[] = $value;
        }

        $set = rtrim($set, ', ');
        $values[] = $id; // Add the ID as the last parameter
        $types = $this->determineTypes($data) . 'i'; // Assume ID is an integer

        $stmt = $this->db->prepare("UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = ?");
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param('i', $id); // Assuming primary key is always an integer
        return $stmt->execute();
    }

    public function where($conditions, $params = [], $types = '') {
        $conditionString = implode(" AND ", array_map(function ($key) {
            return "$key = ?";
        }, array_keys($conditions)));

        $sql = "SELECT * FROM {$this->table} WHERE $conditionString";
        $stmt = $this->db->prepare($sql);

        if ($params) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $this->fetchAll($stmt);
    }

    protected function fetchAll($stmt) {
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    protected function fetch($stmt) {
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Helper function to determine the parameter types for bind_param
    protected function determineTypes($data) {
        $types = '';
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } elseif (is_string($value)) {
                $types .= 's';
            } else {
                $types .= 'b'; // For blob or unknown types
            }
        }
        return $types;
    }

    // Sanitize fields like table names or column names to prevent SQL injection
    protected function sanitizeField($field) {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $field); // Allow only alphanumeric characters and underscores
    }
}

