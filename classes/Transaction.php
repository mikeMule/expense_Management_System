<?php
require_once 'Database.php';

class Transaction
{
    private $db;
    private $location;

    public function __construct()
    {
        $this->db = new Database;
        $this->location = $_SESSION['location'] ?? 'Addis Ababa';
    }

    public function getAllTransactions($limit = null, $offset = 0, $includeSalaries = true)
    {
        $query = 'SELECT t.*, c.name as category_name 
                  FROM transactions t 
                  LEFT JOIN categories c ON t.category_id = c.id
                  WHERE t.location = :location';
        
        if (!$includeSalaries) {
            // Assume salary category names or types if needed, 
            // but usually transactions table doesn't have salaries if they are in salary_payments.
            // Wait, does transactions table include salaries? 
            // In this system, salaries are often added to transactions too.
        }

        $query .= ' ORDER BY t.transaction_date DESC, t.created_at DESC';

        if ($limit !== null) {
            $query .= ' LIMIT ' . intval($limit) . ' OFFSET ' . intval($offset);
        }

        $this->db->query($query);
        $this->db->bind(':location', $this->location);
        return $this->db->resultset();
    }

    public function getTransactionById($id)
    {
        $this->db->query('SELECT t.*, c.name as category_name 
                          FROM transactions t 
                          LEFT JOIN categories c ON t.category_id = c.id 
                          WHERE t.id = :id AND t.location = :location');
        $this->db->bind(':id', $id);
        $this->db->bind(':location', $this->location);
        return $this->db->single();
    }

    public function addTransaction($type, $category_id, $amount, $description, $transaction_date, $notes = '', $attachment_path = null)
    {
        $this->db->query('INSERT INTO transactions (type, category_id, amount, description, transaction_date, notes, attachment_path, location) 
                          VALUES (:type, :category_id, :amount, :description, :transaction_date, :notes, :attachment_path, :location)');

        $this->db->bind(':type', $type);
        $this->db->bind(':category_id', $category_id);
        $this->db->bind(':amount', $amount);
        $this->db->bind(':description', $description);
        $this->db->bind(':transaction_date', $transaction_date);
        $this->db->bind(':notes', $notes);
        $this->db->bind(':attachment_path', $attachment_path);
        $this->db->bind(':location', $this->location);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateTransaction($id, $type, $category_id, $amount, $description, $transaction_date, $notes = '')
    {
        $this->db->query('UPDATE transactions
                          SET type = :type, category_id = :category_id, amount = :amount,
                              description = :description, transaction_date = :transaction_date, notes = :notes
                          WHERE id = :id AND location = :location');

        $this->db->bind(':id', $id);
        $this->db->bind(':type', $type);
        $this->db->bind(':category_id', $category_id);
        $this->db->bind(':amount', $amount);
        $this->db->bind(':description', $description);
        $this->db->bind(':transaction_date', $transaction_date);
        $this->db->bind(':notes', $notes);
        $this->db->bind(':location', $this->location);

        return $this->db->execute();
    }

    public function updateTransactionWithAttachment($id, $type, $category_id, $amount, $description, $transaction_date, $notes = '', $attachment_path = null)
    {
        $this->db->query('UPDATE transactions
                          SET type = :type, category_id = :category_id, amount = :amount,
                              description = :description, transaction_date = :transaction_date,
                              notes = :notes, attachment_path = :attachment_path
                          WHERE id = :id AND location = :location');

        $this->db->bind(':id', $id);
        $this->db->bind(':type', $type);
        $this->db->bind(':category_id', $category_id);
        $this->db->bind(':amount', $amount);
        $this->db->bind(':description', $description);
        $this->db->bind(':transaction_date', $transaction_date);
        $this->db->bind(':notes', $notes);
        $this->db->bind(':attachment_path', $attachment_path);
        $this->db->bind(':location', $this->location);

        return $this->db->execute();
    }

    public function deleteTransaction($id)
    {
        $this->db->query('DELETE FROM transactions WHERE id = :id AND location = :location');
        $this->db->bind(':id', $id);
        $this->db->bind(':location', $this->location);
        return $this->db->execute();
    }

    public function getTransactionsByDateRange($start_date, $end_date, $type = null)
    {
        $query = 'SELECT t.*, c.name as category_name 
                  FROM transactions t 
                  LEFT JOIN categories c ON t.category_id = c.id 
                  WHERE t.location = :location AND t.transaction_date BETWEEN :start_date AND :end_date';

        if ($type !== null) {
            $query .= ' AND t.type = :type';
        }

        $query .= ' ORDER BY t.transaction_date DESC';

        $this->db->query($query);
        $this->db->bind(':location', $this->location);
        $this->db->bind(':start_date', $start_date);
        $this->db->bind(':end_date', $end_date);

        if ($type !== null) {
            $this->db->bind(':type', $type);
        }

        return $this->db->resultset();
    }

    public function getTransactionsByCategory($category_id)
    {
        $query = 'SELECT t.*, c.name as category_name 
                          FROM transactions t 
                          LEFT JOIN categories c ON t.category_id = c.id 
                          WHERE t.location = :location AND t.category_id = :category_id
                          ORDER BY t.transaction_date DESC';

        $this->db->query($query);
        $this->db->bind(':location', $this->location);
        $this->db->bind(':category_id', $category_id);
        return $this->db->resultset();
    }

    public function getTotalIncome($start_date = null, $end_date = null)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'income' AND location = :location";

        if ($start_date && $end_date) {
            $query .= " AND transaction_date BETWEEN :start_date AND :end_date";
        }

        $this->db->query($query);
        $this->db->bind(':location', $this->location);

        if ($start_date && $end_date) {
            $this->db->bind(':start_date', $start_date);
            $this->db->bind(':end_date', $end_date);
        }

        $result = $this->db->single();
        return $result['total'];
    }

    public function getTotalExpenses($start_date = null, $end_date = null)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'expense' AND location = :location";

        if ($start_date && $end_date) {
            $query .= " AND transaction_date BETWEEN :start_date AND :end_date";
        }

        $this->db->query($query);
        $this->db->bind(':location', $this->location);

        if ($start_date && $end_date) {
            $this->db->bind(':start_date', $start_date);
            $this->db->bind(':end_date', $end_date);
        }

        $result = $this->db->single();
        return $result['total'];
    }

    public function getCategories($type = null)
    {
        // Categories are global for now, but we could filter them if needed.
        // Let's keep them global for simplicity.
        $query = 'SELECT * FROM categories';

        if ($type !== null) {
            $query .= ' WHERE type = :type';
        }

        $query .= ' ORDER BY name';

        $this->db->query($query);

        if ($type !== null) {
            $this->db->bind(':type', $type);
        }

        return $this->db->resultset();
    }

    public function getFilteredTransactions($filters = [], $limit = null, $offset = 0)
    {
        $query = 'SELECT t.*, c.name as category_name 
                  FROM transactions t 
                  LEFT JOIN categories c ON t.category_id = c.id 
                  WHERE t.location = :location';
        
        $params = [':location' => $this->location];

        if (!empty($filters['type'])) {
            $query .= ' AND t.type = :type';
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['category_id'])) {
            $query .= ' AND t.category_id = :category_id';
            $params[':category_id'] = $filters['category_id'];
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query .= ' AND t.transaction_date BETWEEN :start_date AND :end_date';
            $params[':start_date'] = $filters['start_date'];
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($filters['search'])) {
            $query .= ' AND (t.description LIKE :search OR t.notes LIKE :search OR c.name LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $query .= ' ORDER BY t.transaction_date DESC, t.created_at DESC';

        if ($limit !== null) {
            $query .= ' LIMIT ' . intval($limit) . ' OFFSET ' . intval($offset);
        }

        $this->db->query($query);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }

        return $this->db->resultset();
    }

    public function countFilteredTransactions($filters = [])
    {
        $query = 'SELECT COUNT(*) as total 
                  FROM transactions t 
                  LEFT JOIN categories c ON t.category_id = c.id 
                  WHERE t.location = :location';
        
        $params = [':location' => $this->location];

        if (!empty($filters['type'])) {
            $query .= ' AND t.type = :type';
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['category_id'])) {
            $query .= ' AND t.category_id = :category_id';
            $params[':category_id'] = $filters['category_id'];
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query .= ' AND t.transaction_date BETWEEN :start_date AND :end_date';
            $params[':start_date'] = $filters['start_date'];
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($filters['search'])) {
            $query .= ' AND (t.description LIKE :search OR t.notes LIKE :search OR c.name LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $this->db->query($query);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }

        $result = $this->db->single();
        return $result['total'];
    }

    public function getRecentTransactions($limit = 5)
    {
        return $this->getFilteredTransactions([], $limit);
    }

    public function getOrCreateCategory($name, $type)
    {
        $this->db->query('SELECT id FROM categories WHERE name = :name AND type = :type');
        $this->db->bind(':name', $name);
        $this->db->bind(':type', $type);
        $result = $this->db->single();

        if ($result) {
            return $result['id'];
        }

        $this->db->query('INSERT INTO categories (name, type) VALUES (:name, :type)');
        $this->db->bind(':name', $name);
        $this->db->bind(':type', $type);
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }
}
