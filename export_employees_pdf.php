<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Employee.php';
require_once 'vendor/autoload.php';

session_start();
$auth = new Auth();
$auth->requireLogin();

// Initialize Employee object
$employee_obj = new Employee();

// Get search and status filters from GET
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Get all employees
$employees = $employee_obj->getAllEmployees();

// Filter by search term if provided
if ($search) {
    $employees = array_filter($employees, function ($e) use ($search) {
        return stripos($e['first_name'], $search) !== false ||
            stripos($e['last_name'], $search) !== false ||
            stripos($e['employee_id'], $search) !== false ||
            stripos($e['position'], $search) !== false ||
            stripos($e['email'], $search) !== false;
    });
}

// Filter by status if provided
if ($status) {
    $employees = array_filter($employees, function ($e) use ($status) {
        return $e['status'] === $status;
    });
}

/**
 * Extend TCPDF to create custom Header and Footer
 */
class MYPDF extends TCPDF
{
    //Page header
    public function Header()
    {
        // Logo
        $image_file = 'https://mulewave.com/wp-content/uploads/2025/02/photo_2025-02-03_02-20-31-1.jpg';
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
        $this->Cell(0, 10, 'Employee List Report - Generated on ' . date('F d, Y H:i'), 0, false, 'L', 0, '', 0, false, 'M', 'M');

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
$pdf->SetTitle('Employee Report - ' . date('Y-m-d'));
$pdf->SetSubject('Employee List');
$pdf->SetKeywords('Employee, Report, Mule Wave');

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
    .active { color: #27ae60; font-weight: bold; }
    .inactive { color: #c0392b; font-weight: bold; }
</style>

<h3 style="color:#2c3e50; border-bottom: 1px solid #ecf0f1; padding-bottom: 5px;">Employee List Summary</h3>

<table border="0.5" cellpadding="6" cellspacing="0" width="100%">
    <thead>
        <tr class="table-header">
            <th width="15%">ID</th>
            <th width="30%">Name</th>
            <th width="25%">Position</th>
            <th width="20%">Salary</th>
            <th width="10%">Hire Date</th>
        </tr>
    </thead>
    <tbody>';

$count = 0;
foreach ($employees as $emp) {
    $count++;
    $row_class = ($count % 2 == 0) ? 'even' : 'odd';
    $name = htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']);
    $id = htmlspecialchars($emp['employee_id']);
    $position = htmlspecialchars($emp['position']);
    $salary = number_format($emp['monthly_salary'], 2);
    $hire_date = $emp['hire_date'] ? date('M d, Y', strtotime($emp['hire_date'])) : '-';

    $html .= '
        <tr class="' . $row_class . '">
            <td>' . $id . '</td>
            <td>' . $name . '</td>
            <td>' . $position . '</td>
            <td align="right">' . CURRENCY_SYMBOL . ' ' . $salary . '</td>
            <td>' . $hire_date . '</td>
        </tr>';
}

if ($count === 0) {
    $html .= '<tr><td colspan="5" align="center">No employees found.</td></tr>';
}

$html .= '
    </tbody>
</table>';

// Print the HTML
$pdf->writeHTML($html, true, false, true, false, '');

// Output the PDF
$pdf->Output('MuleWave_Employee_Report_' . date('Ymd_His') . '.pdf', 'D');
