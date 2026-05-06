<?php
require_once 'Database.php';
require_once 'Transaction.php';
require_once 'Employee.php';

class Report
{
    private $db;
    private $transaction;
    private $employee;
    private $location;

    public function __construct()
    {
        $this->db = new Database;
        $this->transaction = new Transaction;
        $this->employee = new Employee;
        $this->location = $_SESSION['location'] ?? 'Addis Ababa';
    }

    public function getDashboardData()
    {
        $data = [];

        // Current month data
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        // Previous month data
        $prevMonthStart = date('Y-m-01', strtotime('-1 month'));
        $prevMonthEnd = date('Y-m-t', strtotime('-1 month'));

        // Today's data
        $today = date('Y-m-d');

        // Monthly data for the dashboard cards
        $data['monthly_income'] = $this->transaction->getTotalIncome($monthStart, $monthEnd);
        $data['monthly_expenses'] = $this->transaction->getTotalExpenses($monthStart, $monthEnd);
        $data['monthly_balance'] = $data['monthly_income'] - $data['monthly_expenses'];

        // Previous monthly data for comparison
        $prev_income = $this->transaction->getTotalIncome($prevMonthStart, $prevMonthEnd);
        $prev_expenses = $this->transaction->getTotalExpenses($prevMonthStart, $prevMonthEnd);

        // Calculate percentage changes
        $data['income_growth'] = $prev_income > 0 ? (($data['monthly_income'] - $prev_income) / $prev_income) * 100 : 0;
        $data['expense_growth'] = $prev_expenses > 0 ? (($data['monthly_expenses'] - $prev_expenses) / $prev_expenses) * 100 : 0;

        // Overall historical data for other reports
        $data['total_income'] = $this->transaction->getTotalIncome();
        $data['total_expenses'] = $this->transaction->getTotalExpenses();
        $data['balance'] = $data['total_income'] - $data['total_expenses'];

        // Daily data
        $data['daily_income'] = $this->transaction->getTotalIncome($today, $today);
        $data['daily_expenses'] = $this->transaction->getTotalExpenses($today, $today);

        // Recent transactions
        $data['recent_transactions'] = $this->transaction->getRecentTransactions(5);

        // Employee data
        $data['active_employees'] = $this->employee->getEmployeeCount();
        $data['pending_salaries'] = count($this->employee->getPendingSalaries());
        $data['monthly_salary_budget'] = $this->employee->getTotalMonthlySalaries();

        return $data;
    }

    public function getExpensesByCategory($start_date = null, $end_date = null)
    {
        $query = "SELECT c.name, COALESCE(SUM(t.amount), 0) as total 
                  FROM categories c 
                  LEFT JOIN transactions t ON c.id = t.category_id AND t.type = 'expense' AND t.location = :location";

        $where_clauses = ["c.type = 'expense'"];
        if ($start_date && $end_date) {
            $where_clauses[] = "t.transaction_date BETWEEN :start_date AND :end_date";
        }

        $query .= " WHERE " . implode(' AND ', $where_clauses);
        $query .= " GROUP BY c.id, c.name ORDER BY total DESC";

        $this->db->query($query);
        $this->db->bind(':location', $this->location);

        if ($start_date && $end_date) {
            $this->db->bind(':start_date', $start_date);
            $this->db->bind(':end_date', $end_date);
        }

        return $this->db->resultset();
    }

    public function getIncomeByCategory($start_date = null, $end_date = null)
    {
        $query = "SELECT c.name, COALESCE(SUM(t.amount), 0) as total 
                  FROM categories c 
                  LEFT JOIN transactions t ON c.id = t.category_id AND t.type = 'income' AND t.location = :location";

        $where_clauses = ["c.type = 'income'"];
        if ($start_date && $end_date) {
            $where_clauses[] = "t.transaction_date BETWEEN :start_date AND :end_date";
        }

        $query .= " WHERE " . implode(' AND ', $where_clauses);
        $query .= " GROUP BY c.id, c.name ORDER BY total DESC";

        $this->db->query($query);
        $this->db->bind(':location', $this->location);

        if ($start_date && $end_date) {
            $this->db->bind(':start_date', $start_date);
            $this->db->bind(':end_date', $end_date);
        }

        return $this->db->resultset();
    }

    public function getExpenseBreakdownForChart()
    {
        $data = $this->getExpensesByCategory();
        // Limit to top 6 categories for a cleaner chart, group others
        $top_data = array_slice($data, 0, 5);
        $other_total = array_sum(array_column(array_slice($data, 5), 'total'));

        $chart_data = array_column($top_data, 'total', 'name');
        if ($other_total > 0) {
            $chart_data['Other'] = $other_total;
        }
        return $chart_data;
    }

    public function getIncomeExpenseTrendForChart()
    {
        $this->db->query("SELECT
                            DATE(transaction_date) as date,
                            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
                          FROM transactions
                          WHERE location = :location AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                          GROUP BY DATE(transaction_date)
                          ORDER BY date ASC");
        $this->db->bind(':location', $this->location);
        $results = $this->db->resultset();

        $trend = ['labels' => [], 'income' => [], 'expenses' => []];
        $dates = array_column($results, 'date');

        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $trend['labels'][] = date('M d', strtotime($date));
            $key = array_search($date, $dates);
            if ($key !== false) {
                $trend['income'][] = $results[$key]['income'];
                $trend['expenses'][] = $results[$key]['expense'];
            } else {
                $trend['income'][] = 0;
                $trend['expenses'][] = 0;
            }
        }
        return $trend;
    }

    public function getMonthlyCashFlow($year)
    {
        $this->db->query("SELECT 
                            MONTH(transaction_date) as month,
                            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses
                          FROM transactions 
                          WHERE location = :location AND YEAR(transaction_date) = :year 
                          GROUP BY MONTH(transaction_date) 
                          ORDER BY month");

        $this->db->bind(':location', $this->location);
        $this->db->bind(':year', $year);
        return $this->db->resultset();
    }

    public function getDailyReport($date)
    {
        return $this->transaction->getTransactionsByDateRange($date, $date);
    }

    public function getMonthlyReport($month, $year)
    {
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date = date('Y-m-t', strtotime($start_date));

        return [
            'transactions' => $this->transaction->getTransactionsByDateRange($start_date, $end_date),
            'total_income' => $this->transaction->getTotalIncome($start_date, $end_date),
            'total_expenses' => $this->transaction->getTotalExpenses($start_date, $end_date),
            'expenses_by_category' => $this->getExpensesByCategory($start_date, $end_date),
            'income_by_category' => $this->getIncomeByCategory($start_date, $end_date)
        ];
    }

    public function getYearlyReport($year)
    {
        $start_date = $year . '-01-01';
        $end_date = $year . '-12-31';

        return [
            'transactions' => $this->transaction->getTransactionsByDateRange($start_date, $end_date),
            'total_income' => $this->transaction->getTotalIncome($start_date, $end_date),
            'total_expenses' => $this->transaction->getTotalExpenses($start_date, $end_date),
            'monthly_cash_flow' => $this->getMonthlyCashFlow($year),
            'expenses_by_category' => $this->getExpensesByCategory($start_date, $end_date),
            'income_by_category' => $this->getIncomeByCategory($start_date, $end_date)
        ];
    }

    public function getEmployeeSalaryReport($employee_id, $year = null)
    {
        $query = 'SELECT sp.*, e.first_name, e.last_name, e.employee_id as emp_id_str, e.position 
                  FROM salary_payments sp 
                  JOIN employees e ON sp.employee_id = e.id 
                  WHERE sp.employee_id = :employee_id';
        
        if ($year) {
            $query .= ' AND sp.year = :year';
        }
        
        $query .= ' ORDER BY sp.year DESC, sp.month DESC';
        
        $this->db->query($query);
        $this->db->bind(':employee_id', $employee_id);
        if ($year) {
            $this->db->bind(':year', $year);
        }
        
        return $this->db->resultset();
    }
}
