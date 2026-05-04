<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Transaction.php';
require_once 'vendor/autoload.php';

session_start();
$auth = new Auth();
$auth->requireLogin();

// Initialize Transaction object
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

/**
 * Extend TCPDF to create custom Header and Footer michael
 */



class MYPDF extends TCPDF
{
    //Page header
    public function Header()
    {
        // Logo
        $image_file = 'https://mulewave.com/wp-content/uploads/2025/02/photo_2025-02-03_02-20-31-1.jpg';
        // Image( file, x, y, w, h, type, link, align, resize, dpi, palign, ismask, imgmask, border, fitbox, hidden, fitonpage, alt, altimgs)
        $this->Image($image_file, 15, 10, 30, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        // Set font
        $this->SetFont('helvetica', 'B', 24);
        // Company Name
        $this->SetTextColor(41, 128, 185); // Professional Blue
        $this->SetX(50); // Move right to accommodate logo
        $this->Cell(0, 15, 'Mule Wave Technology', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Ln(12);

        // Subtitle
        $this->SetX(50); // Move right to accommodate logo
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(52, 73, 94); // Dark Blue/Grey
        $this->Cell(0, 10, 'EXPENSE MANAGEMENT SYSTEM', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Ln(8);

        // Report Title
        $this->SetX(50); // Move right to accommodate logo
        $this->SetFont('helvetica', 'I', 11);
        $this->SetTextColor(127, 140, 141); // Grey
        $this->Cell(0, 10, 'Financial Transaction Report - Generated on ' . date('F d, Y H:i'), 0, false, 'L', 0, '', 0, false, 'M', 'M');

        // Stylish Horizontal Line
        $this->SetDrawColor(41, 128, 185);
        $this->SetLineWidth(0.5);
        $this->Line(15, 48, 195, 48);
    }

    // Page footer
    public function Footer()
    {
        // Position at 20 mm from bottom
        $this->SetY(-20);

        // Stylish Horizontal Line for footer
        $this->SetDrawColor(189, 195, 199);
        $this->SetLineWidth(0.2);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(2);

        // Set font
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(52, 73, 94);

        // Footer info
        $this->Cell(0, 10, 'Phone: +251 913001494 | +1 204 281 9099 | +251 98 5896868  |  Website: www.mulewave.com  |  Email: wes@mulewave.com', 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->Ln(5);

        // Page Number
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(127, 140, 141);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

// Create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Document information
$pdf->SetCreator('Mule Wave Tech');
$pdf->SetAuthor('Mule Wave Technology');
$pdf->SetTitle('Expense Report - ' . date('Y-m-d'));
$pdf->SetSubject('Financial Transactions');
$pdf->SetKeywords('Expense, Income, Report, Mule Wave');

// Set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// Set header and footer fonts
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins (Top margin is high because of the custom header)
$pdf->SetMargins(15, 55, 15);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 25);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Report content
$pdf->SetFont('helvetica', '', 10);

$html = '
<style>
    .table-header {
        background-color: #2c3e50;
        color: #ffffff;
        font-weight: bold;
        text-align: center;
    }
    .even { background-color: #f8f9fa; }
    .odd { background-color: #ffffff; }
    .income { color: #27ae60; font-weight: bold; }
    .expense { color: #c0392b; font-weight: bold; }
    .summary-table {
        margin-top: 20px;
        border-top: 1px solid #bdc3c7;
    }
    .summary-label {
        font-weight: bold;
        text-align: right;
    }
    .summary-value {
        font-weight: bold;
        text-align: right;
    }
</style>

<h3 style="color:#2c3e50; border-bottom: 1px solid #ecf0f1; padding-bottom: 5px;">Transaction Summary</h3>

<table border="0.5" cellpadding="6" cellspacing="0" width="100%">
    <thead>
        <tr class="table-header">
            <th width="15%">Date</th>
            <th width="10%">Type</th>
            <th width="35%">Description</th>
            <th width="20%">Category</th>
            <th width="20%">Amount</th>
        </tr>
    </thead>
    <tbody>';

$total_income = 0;
$total_expense = 0;
$count = 0;

foreach ($transactions as $t) {
    $count++;
    $row_class = ($count % 2 == 0) ? 'even' : 'odd';
    $date = date('M d, Y', strtotime($t['transaction_date']));
    $type = ucfirst($t['type']);
    $desc = htmlspecialchars($t['description']);
    $cat = htmlspecialchars($t['category_name'] ?? 'N/A');
    $amount = number_format($t['amount'], 2);

    if ($t['type'] == 'income') {
        $total_income += $t['amount'];
        $amount_display = '<span class="income">+' . CURRENCY_SYMBOL . ' ' . $amount . '</span>';
    } else {
        $total_expense += $t['amount'];
        $amount_display = '<span class="expense">-' . CURRENCY_SYMBOL . ' ' . $amount . '</span>';
    }

    $html .= '
        <tr class="' . $row_class . '">
            <td>' . $date . '</td>
            <td align="center">' . $type . '</td>
            <td>' . $desc . '</td>
            <td align="center">' . $cat . '</td>
            <td align="right">' . $amount_display . '</td>
        </tr>';
}

if ($count === 0) {
    $html .= '<tr><td colspan="5" align="center">No transactions found for the selected period.</td></tr>';
}

$net = $total_income - $total_expense;
$net_display = ($net >= 0) ? '<span class="income">+' . CURRENCY_SYMBOL . ' ' . number_format($net, 2) . '</span>' : '<span class="expense">-' . CURRENCY_SYMBOL . ' ' . number_format(abs($net), 2) . '</span>';

$html .= '
    </tbody>
</table>

<br><br>

<table cellpadding="4" cellspacing="0" width="100%" style="border-top: 2px solid #2c3e50;">
    <tr>
        <td width="70%" align="right"><strong>Total Income:</strong></td>
        <td width="30%" align="right"><span class="income">+' . CURRENCY_SYMBOL . ' ' . number_format($total_income, 2) . '</span></td>
    </tr>
    <tr>
        <td width="70%" align="right"><strong>Total Expenses:</strong></td>
        <td width="30%" align="right"><span class="expense">-' . CURRENCY_SYMBOL . ' ' . number_format($total_expense, 2) . '</span></td>
    </tr>
    <tr style="background-color: #ecf0f1;">
        <td width="70%" align="right"><strong>Net Balance:</strong></td>
        <td width="30%" align="right"><strong>' . $net_display . '</strong></td>
    </tr>
</table>';

// Print the HTML
$pdf->writeHTML($html, true, false, true, false, '');

// Output the PDF
$pdf->Output('MuleWave_Expense_Report_' . date('Ymd_His') . '.pdf', 'D');
