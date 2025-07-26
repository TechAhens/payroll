<?php
/**
 * Employee State Insurance (ESI) Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class ESIController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $esiModel = $this->loadModel('ESI');
        
        // Get ESI summary for current financial year
        $currentFY = $this->getCurrentFinancialYear();
        $esiSummary = $esiModel->getESISummary($currentFY);
        
        // Get recent ESI transactions
        $recentTransactions = $esiModel->getRecentESITransactions(10);
        
        $this->loadView('esi/index', [
            'esi_summary' => $esiSummary,
            'recent_transactions' => $recentTransactions,
            'current_fy' => $currentFY
        ]);
    }
    
    public function esiReports() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->generateESIReport();
        } else {
            $this->showESIReportForm();
        }
    }
    
    public function esiSettings() {
        $this->checkAuth();
        $this->checkPermission('settings');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateESISettings();
        } else {
            $this->showESISettings();
        }
    }
    
    public function esiContributions() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $periodId = $_GET['period'] ?? '';
        $departmentId = $_GET['department'] ?? '';
        
        $esiModel = $this->loadModel('ESI');
        $contributions = $esiModel->getESIContributions($periodId, $departmentId);
        
        $periods = $this->db->fetchAll("SELECT * FROM payroll_periods ORDER BY start_date DESC LIMIT 12");
        $departments = $this->db->fetchAll("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC");
        
        $this->loadView('esi/contributions', [
            'contributions' => $contributions,
            'periods' => $periods,
            'departments' => $departments,
            'selected_period' => $periodId,
            'selected_department' => $departmentId
        ]);
    }
    
    private function generateESIReport() {
        $data = $this->sanitizeInput($_POST);
        $reportType = $data['report_type'] ?? 'contribution_summary';
        $periodId = $data['period_id'] ?? '';
        $financialYear = $data['financial_year'] ?? '';
        $format = $data['format'] ?? 'excel';
        
        $esiModel = $this->loadModel('ESI');
        
        switch ($reportType) {
            case 'contribution_summary':
                $reportData = $esiModel->getContributionSummaryReport($periodId, $financialYear);
                $filename = 'esi_contribution_summary_' . date('Y-m-d');
                break;
            case 'employee_wise':
                $reportData = $esiModel->getEmployeeWiseESIReport($periodId, $financialYear);
                $filename = 'esi_employee_wise_' . date('Y-m-d');
                break;
            case 'monthly_summary':
                $reportData = $esiModel->getMonthlySummaryReport($financialYear);
                $filename = 'esi_monthly_summary_' . $financialYear;
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid report type'], 400);
                return;
        }
        
        if ($format === 'excel') {
            $this->exportToExcel($reportData, $filename . '.xlsx', 'ESI Report');
        } else {
            $this->exportToCSV($reportData, $filename . '.csv');
        }
    }
    
    private function updateESISettings() {
        $data = $this->sanitizeInput($_POST);
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        try {
            $esiModel = $this->loadModel('ESI');
            $result = $esiModel->updateESISettings($data);
            
            if ($result['success']) {
                $this->logActivity('update_esi_settings', 'esi_settings', null);
                $this->jsonResponse(['success' => true, 'message' => 'ESI settings updated successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => $result['message']], 400);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update ESI settings'], 500);
        }
    }
    
    private function showESIReportForm() {
        $periods = $this->db->fetchAll("SELECT * FROM payroll_periods ORDER BY start_date DESC LIMIT 12");
        $financialYears = $this->db->fetchAll("SELECT DISTINCT financial_year FROM payroll_periods ORDER BY financial_year DESC");
        
        $this->loadView('esi/reports', [
            'periods' => $periods,
            'financial_years' => $financialYears,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showESISettings() {
        $esiSettings = $this->getESISettings();
        
        $this->loadView('esi/settings', [
            'esi_settings' => $esiSettings,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function getESISettings() {
        // In a real implementation, this would fetch from a settings table
        return [
            'employee_esi_rate' => 0.75,
            'employer_esi_rate' => 3.25,
            'esi_threshold' => 21000.00,
            'esi_ceiling' => 25000.00,
            'medical_benefit_rate' => 4.00
        ];
    }
    
    private function getCurrentFinancialYear() {
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        if ($currentMonth >= 4) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }
    
    private function exportToExcel($data, $filename, $title = 'Report') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo "<table border='1'>";
        echo "<tr><th colspan='" . count($data[0] ?? []) . "'><h2>$title</h2></th></tr>";
        
        if (!empty($data)) {
            // Headers
            echo "<tr>";
            foreach (array_keys($data[0]) as $header) {
                echo "<th>" . ucwords(str_replace('_', ' ', $header)) . "</th>";
            }
            echo "</tr>";
            
            // Data
            foreach ($data as $row) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell) . "</td>";
                }
                echo "</tr>";
            }
        }
        echo "</table>";
        exit;
    }
    
    private function exportToCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            // Headers
            fputcsv($output, array_keys($data[0]));
            
            // Data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
}