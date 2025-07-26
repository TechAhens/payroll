<?php
/**
 * Provident Fund (PF) Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class PFController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $pfModel = $this->loadModel('PF');
        
        // Get PF summary for current financial year
        $currentFY = $this->getCurrentFinancialYear();
        $pfSummary = $pfModel->getPFSummary($currentFY);
        
        // Get recent PF transactions
        $recentTransactions = $pfModel->getRecentPFTransactions(10);
        
        $this->loadView('pf/index', [
            'pf_summary' => $pfSummary,
            'recent_transactions' => $recentTransactions,
            'current_fy' => $currentFY
        ]);
    }
    
    public function ecrGeneration() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->generateECR();
        } else {
            $this->showECRForm();
        }
    }
    
    public function pfReports() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->generatePFReport();
        } else {
            $this->showPFReportForm();
        }
    }
    
    public function pfSettings() {
        $this->checkAuth();
        $this->checkPermission('settings');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updatePFSettings();
        } else {
            $this->showPFSettings();
        }
    }
    
    public function pfContributions() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $periodId = $_GET['period'] ?? '';
        $departmentId = $_GET['department'] ?? '';
        
        $pfModel = $this->loadModel('PF');
        $contributions = $pfModel->getPFContributions($periodId, $departmentId);
        
        $periods = $this->db->fetchAll("SELECT * FROM payroll_periods ORDER BY start_date DESC LIMIT 12");
        $departments = $this->db->fetchAll("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC");
        
        $this->loadView('pf/contributions', [
            'contributions' => $contributions,
            'periods' => $periods,
            'departments' => $departments,
            'selected_period' => $periodId,
            'selected_department' => $departmentId
        ]);
    }
    
    public function pfReconciliation() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processReconciliation();
        } else {
            $this->showReconciliationForm();
        }
    }
    
    private function generateECR() {
        $data = $this->sanitizeInput($_POST);
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        $rules = [
            'period_id' => ['required' => true, 'type' => 'numeric'],
            'establishment_code' => ['required' => true],
            'return_month' => ['required' => true],
            'return_year' => ['required' => true, 'type' => 'numeric']
        ];
        
        $errors = $this->validateInput($data, $rules);
        
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
            return;
        }
        
        try {
            $pfModel = $this->loadModel('PF');
            $result = $pfModel->generateECRFile($data);
            
            if ($result['success']) {
                $this->logActivity('generate_ecr', 'pf_ecr', $data['period_id']);
                
                // Return file for download
                header('Content-Type: text/plain');
                header('Content-Disposition: attachment; filename="ECR_' . $data['return_month'] . '_' . $data['return_year'] . '.txt"');
                echo $result['ecr_content'];
                exit;
            } else {
                $this->jsonResponse(['success' => false, 'message' => $result['message']], 400);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to generate ECR file'], 500);
        }
    }
    
    private function generatePFReport() {
        $data = $this->sanitizeInput($_POST);
        $reportType = $data['report_type'] ?? 'contribution_summary';
        $periodId = $data['period_id'] ?? '';
        $financialYear = $data['financial_year'] ?? '';
        $format = $data['format'] ?? 'excel';
        
        $pfModel = $this->loadModel('PF');
        
        switch ($reportType) {
            case 'contribution_summary':
                $reportData = $pfModel->getContributionSummaryReport($periodId, $financialYear);
                $filename = 'pf_contribution_summary_' . date('Y-m-d');
                break;
            case 'employee_wise':
                $reportData = $pfModel->getEmployeeWisePFReport($periodId, $financialYear);
                $filename = 'pf_employee_wise_' . date('Y-m-d');
                break;
            case 'monthly_summary':
                $reportData = $pfModel->getMonthlySummaryReport($financialYear);
                $filename = 'pf_monthly_summary_' . $financialYear;
                break;
            case 'arrears_report':
                $reportData = $pfModel->getArrearsReport($financialYear);
                $filename = 'pf_arrears_' . $financialYear;
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid report type'], 400);
                return;
        }
        
        if ($format === 'excel') {
            $this->exportToExcel($reportData, $filename . '.xlsx', 'PF Report');
        } else {
            $this->exportToCSV($reportData, $filename . '.csv');
        }
    }
    
    private function updatePFSettings() {
        $data = $this->sanitizeInput($_POST);
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        try {
            $pfModel = $this->loadModel('PF');
            $result = $pfModel->updatePFSettings($data);
            
            if ($result['success']) {
                $this->logActivity('update_pf_settings', 'pf_settings', null);
                $this->jsonResponse(['success' => true, 'message' => 'PF settings updated successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => $result['message']], 400);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update PF settings'], 500);
        }
    }
    
    private function processReconciliation() {
        $data = $this->sanitizeInput($_POST);
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        try {
            $pfModel = $this->loadModel('PF');
            $result = $pfModel->processReconciliation($data);
            
            if ($result['success']) {
                $this->logActivity('pf_reconciliation', 'pf_reconciliation', null);
                $this->jsonResponse(['success' => true, 'message' => 'PF reconciliation completed successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => $result['message']], 400);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to process reconciliation'], 500);
        }
    }
    
    private function showECRForm() {
        $periods = $this->db->fetchAll("SELECT * FROM payroll_periods ORDER BY start_date DESC LIMIT 12");
        $pfSettings = $this->getPFSettings();
        
        $this->loadView('pf/ecr-generation', [
            'periods' => $periods,
            'pf_settings' => $pfSettings,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showPFReportForm() {
        $periods = $this->db->fetchAll("SELECT * FROM payroll_periods ORDER BY start_date DESC LIMIT 12");
        $financialYears = $this->db->fetchAll("SELECT DISTINCT financial_year FROM payroll_periods ORDER BY financial_year DESC");
        
        $this->loadView('pf/reports', [
            'periods' => $periods,
            'financial_years' => $financialYears,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showPFSettings() {
        $pfSettings = $this->getPFSettings();
        
        $this->loadView('pf/settings', [
            'pf_settings' => $pfSettings,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showReconciliationForm() {
        $periods = $this->db->fetchAll("SELECT * FROM payroll_periods ORDER BY start_date DESC LIMIT 6");
        
        $this->loadView('pf/reconciliation', [
            'periods' => $periods,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function getPFSettings() {
        // In a real implementation, this would fetch from a settings table
        return [
            'establishment_code' => 'DLCPM0026293000',
            'establishment_name' => 'PayrollPro Enterprise',
            'employee_pf_rate' => 12.00,
            'employer_pf_rate' => 12.00,
            'eps_rate' => 8.33,
            'edli_rate' => 0.50,
            'admin_charges_rate' => 0.65,
            'pf_ceiling' => 15000.00,
            'eps_ceiling' => 15000.00
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