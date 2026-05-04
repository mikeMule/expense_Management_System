<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Transaction.php';

session_start();
$auth = new Auth();
$auth->requireLogin();

$transaction_obj = new Transaction();

// Handle filters (Same as transactions.php)
$filter_type = $_GET['type'] ?? '';
$filter_category = $_GET['category'] ?? '';
$filter_start_date = $_GET['start_date'] ?? '';
$filter_end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

// Get transactions based on filters
$transactions = $transaction_obj->getFilteredTransactions([
    'type' => $filter_type,
    'category_id' => $filter_category,
    'start_date' => $filter_start_date,
    'end_date' => $filter_end_date,
    'search' => $search
]);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=transaction_report_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');
// CSV header
fputcsv($output, ['Date', 'Type', 'Description', 'Category', 'Amount', 'Notes']);

foreach ($transactions as $t) {
    fputcsv($output, [
        date('Y-m-d H:i', strtotime($t['transaction_date'])),
        ucfirst($t['type']),
        $t['description'],
        $t['category_name'] ?? 'N/A',
        $t['amount'],
        $t['notes']
    ]);
}
fclose($output);
exit;
