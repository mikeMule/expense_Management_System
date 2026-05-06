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

        // Reference Dates
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $today = date('Y-m-d');

        // Check if there is data for the current month
        $currentMonthCount = $this->transaction->getTotalIncome($monthStart, $monthEnd) + $this->transaction->getTotalExpenses($monthStart, $monthEnd);

        if ($currentMonthCount <= 0) {
            $this->db->query("SELECT MAX(transaction_date) as latest FROM transactions WHERE location = :location");
            $this->db->bind(':location', $this->location);
            $latest = $this->db->single();
            if ($latest && $latest['latest']) {
                $refDate = $latest['latest'];
                $monthStart = date('Y-m-01', strtotime($refDate));
                $monthEnd = date('Y-m-t', strtotime($refDate));
                $today = $refDate;
            }
        }

        $prevMonthStart = date('Y-m-01', strtotime($monthStart . ' -1 month'));
        $prevMonthEnd = date('Y-m-t', strtotime($monthStart . ' -1 month'));

        // Optimized Query: Get all dashboard stats in one go
        $this->db->query("SELECT 
            SUM(CASE WHEN transaction_date BETWEEN :monthStart1 AND :monthEnd1 AND type = 'income' THEN amount ELSE 0 END) as monthly_income,
            SUM(CASE WHEN transaction_date BETWEEN :monthStart2 AND :monthEnd2 AND type = 'expense' THEN amount ELSE 0 END) as monthly_expenses,
            SUM(CASE WHEN transaction_date BETWEEN :prevMonthStart1 AND :prevMonthEnd1 AND type = 'income' THEN amount ELSE 0 END) as prev_income,
            SUM(CASE WHEN transaction_date BETWEEN :prevMonthStart2 AND :prevMonthEnd2 AND type = 'expense' THEN amount ELSE 0 END) as prev_expenses,
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses,
            SUM(CASE WHEN transaction_date = :today1 AND type = 'income' THEN amount ELSE 0 END) as daily_income,
            SUM(CASE WHEN transaction_date = :today2 AND type = 'expense' THEN amount ELSE 0 END) as daily_expenses
            FROM transactions 
            WHERE location = :location");

        $this->db->bind(':location', $this->location);
        $this->db->bind(':monthStart1', $monthStart);
        $this->db->bind(':monthEnd1', $monthEnd);
        $this->db->bind(':monthStart2', $monthStart);
        $this->db->bind(':monthEnd2', $monthEnd);
        $this->db->bind(':prevMonthStart1', $prevMonthStart);
        $this->db->bind(':prevMonthEnd1', $prevMonthEnd);
        $this->db->bind(':prevMonthStart2', $prevMonthStart);
        $this->db->bind(':prevMonthEnd2', $prevMonthEnd);
        $this->db->bind(':today1', $today);
        $this->db->bind(':today2', $today);

        $stats = $this->db->single();

        if (!$stats) {
            $stats = [
                'monthly_income' => 0, 'monthly_expenses' => 0, 
                'prev_income' => 0, 'prev_expenses' => 0,
                'total_income' => 0, 'total_expenses' => 0,
                'daily_income' => 0, 'daily_expenses' => 0
            ];
        }

        $data['monthly_income'] = (float)($stats['monthly_income'] ?? 0);
        $data['monthly_expenses'] = (float)($stats['monthly_expenses'] ?? 0);
        $data['monthly_balance'] = $data['monthly_income'] - $data['monthly_expenses'];

        $prev_income = (float)($stats['prev_income'] ?? 0);
        $prev_expenses = (float)($stats['prev_expenses'] ?? 0);

        $data['income_growth'] = $prev_income > 0 ? (($data['monthly_income'] - $prev_income) / $prev_income) * 100 : 0;
        $data['expense_growth'] = $prev_expenses > 0 ? (($data['monthly_expenses'] - $prev_expenses) / $prev_expenses) * 100 : 0;

        $data['total_income'] = (float)($stats['total_income'] ?? 0);
        $data['total_expenses'] = (float)($stats['total_expenses'] ?? 0);
        $data['balance'] = $data['total_income'] - $data['total_expenses'];

        $data['daily_income'] = (float)($stats['daily_income'] ?? 0);
        $data['daily_expenses'] = (float)($stats['daily_expenses'] ?? 0);

        // Recent transactions and employee data still need separate queries as they hit different tables/logic
        $data['recent_transactions'] = $this->transaction->getRecentTransactions(5) ?: [];

        // Employee data
        $data['active_employees'] = $this->employee->getEmployeeCount() ?: 0;
        $pending = $this->employee->getPendingSalaries();
        $data['pending_salaries'] = is_array($pending) ? count($pending) : 0;
        $data['monthly_salary_budget'] = $this->employee->getTotalMonthlySalaries() ?: 0;

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
        // Find latest date for trend reference
        $this->db->query("SELECT MAX(transaction_date) as latest FROM transactions WHERE location = :location");
        $this->db->bind(':location', $this->location);
        $latestRes = $this->db->single();
        $refDate = ($latestRes && $latestRes['latest']) ? $latestRes['latest'] : date('Y-m-d');

        $this->db->query("SELECT
                            DATE(transaction_date) as date,
                            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
                          FROM transactions
                          WHERE location = :location AND transaction_date >= DATE_SUB(:ref_date, INTERVAL 30 DAY)
                          GROUP BY DATE(transaction_date)
                          ORDER BY date ASC");
        $this->db->bind(':location', $this->location);
        $this->db->bind(':ref_date', $refDate);
        $results = $this->db->resultset();

        $trend = ['labels' => [], 'income' => [], 'expenses' => []];
        $dates = array_column($results, 'date');

        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime($refDate . " -$i days"));
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

    public function getEmployeeSalaryReport($employee_id, $year = null, $month = null)
    {
        $query = 'SELECT sp.*, e.first_name, e.last_name, e.employee_id as emp_id_str, e.position 
                  FROM salary_payments sp 
                  JOIN employees e ON sp.employee_id = e.id 
                  WHERE sp.employee_id = :employee_id';
        
        if ($year) {
            $query .= ' AND sp.year = :year';
        }

        if ($month) {
            $query .= ' AND sp.month = :month';
        }
        
        $query .= ' ORDER BY sp.year DESC, sp.month DESC';
        
        $this->db->query($query);
        $this->db->bind(':employee_id', $employee_id);
        if ($year) {
            $this->db->bind(':year', $year);
        }
        if ($month) {
            $this->db->bind(':month', $month);
        }
        
        return $this->db->resultset();
    }
}
