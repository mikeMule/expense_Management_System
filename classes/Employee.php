<?php
require_once 'Database.php';

class Employee
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getAllEmployees()
    {
        $this->db->query('SELECT * FROM employees ORDER BY first_name, last_name');
        return $this->db->resultset();
    }

    public function getActiveEmployees()
    {
        $this->db->query("SELECT * FROM employees WHERE status = 'active' ORDER BY first_name, last_name");
        return $this->db->resultset();
    }

    public function getEmployeeById($id)
    {
        $this->db->query('SELECT * FROM employees WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addEmployee($employee_id, $first_name, $last_name, $email, $phone, $position, $monthly_salary, $hire_date)
    {
        $this->db->query('INSERT INTO employees (employee_id, first_name, last_name, email, phone, position, monthly_salary, hire_date) 
                          VALUES (:employee_id, :first_name, :last_name, :email, :phone, :position, :monthly_salary, :hire_date)');

        $this->db->bind(':employee_id', $employee_id);
        $this->db->bind(':first_name', $first_name);
        $this->db->bind(':last_name', $last_name);
        $this->db->bind(':email', $email);
        $this->db->bind(':phone', $phone);
        $this->db->bind(':position', $position);
        $this->db->bind(':monthly_salary', $monthly_salary);
        $this->db->bind(':hire_date', $hire_date);

        return $this->db->execute();
    }

    public function updateEmployee($id, $employee_id, $first_name, $last_name, $email, $phone, $position, $monthly_salary, $hire_date, $status)
    {
        $this->db->query('UPDATE employees 
                          SET employee_id = :employee_id, first_name = :first_name, last_name = :last_name, 
                              email = :email, phone = :phone, position = :position, 
                              monthly_salary = :monthly_salary, hire_date = :hire_date, status = :status 
                          WHERE id = :id');

        $this->db->bind(':id', $id);
        $this->db->bind(':employee_id', $employee_id);
        $this->db->bind(':first_name', $first_name);
        $this->db->bind(':last_name', $last_name);
        $this->db->bind(':email', $email);
        $this->db->bind(':phone', $phone);
        $this->db->bind(':position', $position);
        $this->db->bind(':monthly_salary', $monthly_salary);
        $this->db->bind(':hire_date', $hire_date);
        $this->db->bind(':status', $status);

        return $this->db->execute();
    }

    public function deleteEmployee($id)
    {
        $this->db->query('DELETE FROM employees WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getEmployeeCount()
    {
        $this->db->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'");
        $result = $this->db->single();
        return $result['count'];
    }

    public function getSalaryPayments($month = null, $year = null)
    {
        $query = 'SELECT sp.*, e.first_name, e.last_name, e.employee_id, e.position 
                  FROM salary_payments sp 
                  JOIN employees e ON sp.employee_id = e.id';

        if ($month !== null && $year !== null) {
            $query .= ' WHERE sp.month = :month AND sp.year = :year';
        }

        $query .= ' ORDER BY sp.year DESC, sp.month DESC, e.first_name, e.last_name';

        $this->db->query($query);

        if ($month !== null && $year !== null) {
            $this->db->bind(':month', $month);
            $this->db->bind(':year', $year);
        }

        return $this->db->resultset();
    }

    public function addSalaryPayment($employee_id, $month, $year, $amount, $notes = '')
    {
        $this->db->query('INSERT INTO salary_payments (employee_id, month, year, amount, notes) 
                          VALUES (:employee_id, :month, :year, :amount, :notes)');

        $this->db->bind(':employee_id', $employee_id);
        $this->db->bind(':month', $month);
        $this->db->bind(':year', $year);
        $this->db->bind(':amount', $amount);
        $this->db->bind(':notes', $notes);

        return $this->db->execute();
    }

    public function paySalary($payment_id, $payment_date)
    {
        $this->db->query("UPDATE salary_payments 
                          SET status = 'paid', payment_date = :payment_date 
                          WHERE id = :id");

        $this->db->bind(':id', $payment_id);
        $this->db->bind(':payment_date', $payment_date);

        return $this->db->execute();
    }

    public function getPendingSalaries()
    {
        $this->db->query("SELECT sp.*, e.first_name, e.last_name, e.employee_id 
                          FROM salary_payments sp 
                          JOIN employees e ON sp.employee_id = e.id 
                          WHERE sp.status = 'pending' 
                          ORDER BY sp.year, sp.month, e.first_name");
        return $this->db->resultset();
    }

    public function getTotalMonthlySalaries()
    {
        $this->db->query("SELECT COALESCE(SUM(monthly_salary), 0) as total 
                          FROM employees 
                          WHERE status = 'active'");
        $result = $this->db->single();
        return $result['total'];
    }

    public function generateMonthlySalaries($month, $year)
    {
        $employees = $this->getActiveEmployees();
        $success = 0;

        foreach ($employees as $employee) {
            try {
                $this->addSalaryPayment($employee['id'], $month, $year, $employee['monthly_salary']);
                $success++;
            } catch (Exception $e) {
                // Skip if already exists
                continue;
            }
        }

        return $success;
    }

    public function deleteSalaryPayment($id)
    {
        $this->db->query('DELETE FROM salary_payments WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
