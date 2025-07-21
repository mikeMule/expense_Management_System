<?php
require_once 'Database.php';

class Transaction
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getAllTransactions($limit = null, $offset = 0)
    {
        $query = 'SELECT t.*, c.name as category_name 
                  FROM transactions t 
                  LEFT JOIN categories c ON t.category_id = c.id 
                  ORDER BY t.transaction_date DESC, t.created_at DESC';

        if ($limit !== null) {
            $query .= ' LIMIT ' . intval($limit) . ' OFFSET ' . intval($offset);
        }

        $this->db->query($query);
        return $this->db->resultset();
    }

    public function getTransactionById($id)
    {
        $this->db->query('SELECT t.*, c.name as category_name 
                          FROM transactions t 
                          LEFT JOIN categories c ON t.category_id = c.id 
                          WHERE t.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addTransaction($type, $category_id, $amount, $description, $transaction_date, $notes = '', $attachment_path = null)
    {
        $this->db->query('INSERT INTO transactions (type, category_id, amount, description, transaction_date, notes, attachment_path) 
                          VALUES (:type, :category_id, :amount, :description, :transaction_date, :notes, :attachment_path)');

        $this->db->bind(':type', $type);
        $this->db->bind(':category_id', $category_id);
        $this->db->bind(':amount', $amount);
        $this->db->bind(':description', $description);
        $this->db->bind(':transaction_date', $transaction_date);
        $this->db->bind(':notes', $notes);
        $this->db->bind(':attachment_path', $attachment_path);

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
                          WHERE id = :id');

        $this->db->bind(':id', $id);
        $this->db->bind(':type', $type);
        $this->db->bind(':category_id', $category_id);
        $this->db->bind(':amount', $amount);
        $this->db->bind(':description', $description);
        $this->db->bind(':transaction_date', $transaction_date);
        $this->db->bind(':notes', $notes);

        return $this->db->execute();
    }

    public function deleteTransaction($id)
    {
        $this->db->query('DELETE FROM transactions WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getTransactionsByDateRange($start_date, $end_date, $type = null)
    {
        $query = 'SELECT t.*, c.name as category_name 
                  FROM transactions t 
                  LEFT JOIN categories c ON t.category_id = c.id 
                  WHERE t.transaction_date BETWEEN :start_date AND :end_date';

        if ($type !== null) {
            $query .= ' AND t.type = :type';
        }

        $query .= ' ORDER BY t.transaction_date DESC';

        $this->db->query($query);
        $this->db->bind(':start_date', $start_date);
        $this->db->bind(':end_date', $end_date);

        if ($type !== null) {
            $this->db->bind(':type', $type);
        }

        return $this->db->resultset();
    }

    public function getTransactionsByCategory($category_id)
    {
        $this->db->query('SELECT t.*, c.name as category_name 
                          FROM transactions t 
                          LEFT JOIN categories c ON t.category_id = c.id 
                          WHERE t.category_id = :category_id 
                          ORDER BY t.transaction_date DESC');
        $this->db->bind(':category_id', $category_id);
        return $this->db->resultset();
    }

    public function getTotalIncome($start_date = null, $end_date = null)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'income'";

        if ($start_date && $end_date) {
            $query .= " AND transaction_date BETWEEN :start_date AND :end_date";
        }

        $this->db->query($query);

        if ($start_date && $end_date) {
            $this->db->bind(':start_date', $start_date);
            $this->db->bind(':end_date', $end_date);
        }

        $result = $this->db->single();
        return $result['total'];
    }

    public function getTotalExpenses($start_date = null, $end_date = null)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'expense'";

        if ($start_date && $end_date) {
            $query .= " AND transaction_date BETWEEN :start_date AND :end_date";
        }

        $this->db->query($query);

        if ($start_date && $end_date) {
            $this->db->bind(':start_date', $start_date);
            $this->db->bind(':end_date', $end_date);
        }

        $result = $this->db->single();
        return $result['total'];
    }

    public function getCategories($type = null)
    {
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

    public function getRecentTransactions($limit = 5)
    {
        $this->db->query('SELECT t.*, c.name as category_name 
                          FROM transactions t 
                          LEFT JOIN categories c ON t.category_id = c.id 
                          ORDER BY t.created_at DESC 
                          LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultset();
    }
}
