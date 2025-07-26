<?php
/**
 * Provident Fund (PF) Model
 */

require_once __DIR__ . '/../core/Model.php';

class PF extends Model {
    protected $table = 'pf_transactions';
    
    public function getPFSummary($financialYear) {
        $sql = "SELECT 
                    COUNT(DISTINCT pt.employee_id) as total_employees,
                    SUM(CASE WHEN sc.code = 'PF' THEN ABS(pt.amount) ELSE 0 END) as total_employee_contribution,
                    SUM(CASE WHEN sc.code = 'PF' THEN ABS(pt.amount) ELSE 0 END) as total_employer_contribution,
                    SUM(CASE WHEN sc.code = 'PF' THEN ABS(pt.amount) * 2 ELSE 0 END) as total_pf_contribution
                FROM payroll_transactions pt
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE pp.financial_year = :fy AND sc.code = 'PF'";
        
        return $this->db->fetch($sql, ['fy' => $financialYear]);
    }
    
    public function getRecentPFTransactions($limit = 10) {
        $sql = "SELECT 
                    e.emp_code,
                    e.first_name,
                    e.last_name,
                    pp.period_name,
                    ABS(pt.amount) as pf_amount,
                    pt.created_at
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                JOIN salary_components sc ON pt.component_id = sc.id
                WHERE sc.code = 'PF'
                ORDER BY pt.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getPFContributions($periodId = null, $departmentId = null) {
        $conditions = [];
        $params = [];
        
        if ($periodId) {
            $conditions[] = "pt.period_id = :period_id";
            $params['period_id'] = $periodId;
        }
        
        if ($departmentId) {
            $conditions[] = "e.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT 
                    e.emp_code,
                    e.first_name,
                    e.last_name,
                    e.uan_number,
                    e.pf_number,
                    d.name as department_name,
                    pp.period_name,
                    pp.start_date,
                    pp.end_date,
                    -- Get basic salary for PF calculation
                    (SELECT pt2.amount FROM payroll_transactions pt2 
                     JOIN salary_components sc2 ON pt2.component_id = sc2.id 
                     WHERE pt2.employee_id = e.id AND pt2.period_id = pt.period_id 
                     AND sc2.code = 'BASIC') as basic_salary,
                    ABS(pt.amount) as employee_pf,
                    ABS(pt.amount) as employer_pf,
                    ABS(pt.amount) * 2 as total_pf,
                    -- Calculate EPS (8.33% of basic or 15000, whichever is lower)
                    CASE 
                        WHEN (SELECT pt2.amount FROM payroll_transactions pt2 
                              JOIN salary_components sc2 ON pt2.component_id = sc2.id 
                              WHERE pt2.employee_id = e.id AND pt2.period_id = pt.period_id 
                              AND sc2.code = 'BASIC') > 15000 
                        THEN 15000 * 0.0833
                        ELSE (SELECT pt2.amount FROM payroll_transactions pt2 
                              JOIN salary_components sc2 ON pt2.component_id = sc2.id 
                              WHERE pt2.employee_id = e.id AND pt2.period_id = pt.period_id 
                              AND sc2.code = 'BASIC') * 0.0833
                    END as eps_amount
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN departments d ON e.department_id = d.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                JOIN salary_components sc ON pt.component_id = sc.id
                {$whereClause}
                AND sc.code = 'PF'
                AND e.status = 'active'
                ORDER BY e.emp_code ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function generateECRFile($data) {
        $periodId = $data['period_id'];
        $establishmentCode = $data['establishment_code'];
        $returnMonth = str_pad($data['return_month'], 2, '0', STR_PAD_LEFT);
        $returnYear = $data['return_year'];
        
        // Get PF contributions for the period
        $contributions = $this->getPFContributions($periodId);
        
        if (empty($contributions)) {
            return ['success' => false, 'message' => 'No PF contributions found for the selected period'];
        }
        
        // Generate ECR content
        $ecrContent = $this->buildECRContent($contributions, $establishmentCode, $returnMonth, $returnYear);
        
        return ['success' => true, 'ecr_content' => $ecrContent];
    }
    
    private function buildECRContent($contributions, $establishmentCode, $returnMonth, $returnYear) {
        $ecr = "";
        
        // Header record
        $ecr .= "#Header\n";
        $ecr .= "ECR~{$establishmentCode}~{$returnMonth}~{$returnYear}~" . count($contributions) . "\n";
        
        // Member records
        $ecr .= "#Member\n";
        
        foreach ($contributions as $contrib) {
            $uanNumber = str_pad($contrib['uan_number'] ?: '000000000000', 12, '0', STR_PAD_LEFT);
            $memberName = strtoupper($contrib['first_name'] . ' ' . $contrib['last_name']);
            $basicSalary = number_format($contrib['basic_salary'], 0, '', '');
            $employeePF = number_format($contrib['employee_pf'], 0, '', '');
            $employerPF = number_format($contrib['employer_pf'], 0, '', '');
            $epsAmount = number_format($contrib['eps_amount'], 0, '', '');
            
            // ECR member record format
            $ecr .= "{$uanNumber}~{$memberName}~{$basicSalary}~{$employeePF}~{$employerPF}~{$epsAmount}~0~0\n";
        }
        
        // Footer record
        $totalEmployeePF = array_sum(array_column($contributions, 'employee_pf'));
        $totalEmployerPF = array_sum(array_column($contributions, 'employer_pf'));
        $totalEPS = array_sum(array_column($contributions, 'eps_amount'));
        
        $ecr .= "#Footer\n";
        $ecr .= "TOTAL~" . count($contributions) . "~" . 
                number_format($totalEmployeePF, 0, '', '') . "~" .
                number_format($totalEmployerPF, 0, '', '') . "~" .
                number_format($totalEPS, 0, '', '') . "~0~0\n";
        
        return $ecr;
    }
    
    public function getContributionSummaryReport($periodId, $financialYear) {
        $conditions = [];
        $params = [];
        
        if ($periodId) {
            $conditions[] = "pt.period_id = :period_id";
            $params['period_id'] = $periodId;
        } elseif ($financialYear) {
            $conditions[] = "pp.financial_year = :fy";
            $params['fy'] = $financialYear;
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT 
                    d.name as department_name,
                    COUNT(DISTINCT e.id) as employee_count,
                    SUM(ABS(pt.amount)) as total_employee_pf,
                    SUM(ABS(pt.amount)) as total_employer_pf,
                    SUM(ABS(pt.amount) * 2) as total_pf_contribution
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN departments d ON e.department_id = d.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                JOIN salary_components sc ON pt.component_id = sc.id
                {$whereClause}
                AND sc.code = 'PF'
                GROUP BY d.id, d.name
                ORDER BY d.name";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getEmployeeWisePFReport($periodId, $financialYear) {
        return $this->getPFContributions($periodId);
    }
    
    public function getMonthlySummaryReport($financialYear) {
        $sql = "SELECT 
                    pp.period_name,
                    pp.start_date,
                    pp.end_date,
                    COUNT(DISTINCT pt.employee_id) as employee_count,
                    SUM(ABS(pt.amount)) as employee_contribution,
                    SUM(ABS(pt.amount)) as employer_contribution,
                    SUM(ABS(pt.amount) * 2) as total_contribution
                FROM payroll_transactions pt
                JOIN payroll_periods pp ON pt.period_id = pp.id
                JOIN salary_components sc ON pt.component_id = sc.id
                WHERE pp.financial_year = :fy AND sc.code = 'PF'
                GROUP BY pp.id, pp.period_name, pp.start_date, pp.end_date
                ORDER BY pp.start_date";
        
        return $this->db->fetchAll($sql, ['fy' => $financialYear]);
    }
    
    public function getArrearsReport($financialYear) {
        // This would identify employees with PF arrears
        $sql = "SELECT 
                    e.emp_code,
                    e.first_name,
                    e.last_name,
                    e.uan_number,
                    COUNT(pp.id) as total_periods,
                    COUNT(pt.id) as paid_periods,
                    (COUNT(pp.id) - COUNT(pt.id)) as arrear_periods,
                    SUM(CASE WHEN pt.id IS NULL THEN 
                        (SELECT amount FROM salary_structures ss 
                         JOIN salary_components sc ON ss.component_id = sc.id 
                         WHERE ss.employee_id = e.id AND sc.code = 'BASIC' 
                         AND ss.effective_date <= pp.end_date 
                         ORDER BY ss.effective_date DESC LIMIT 1) * 0.12 
                        ELSE 0 END) as arrear_amount
                FROM employees e
                CROSS JOIN payroll_periods pp
                LEFT JOIN payroll_transactions pt ON e.id = pt.employee_id 
                    AND pp.id = pt.period_id 
                    AND pt.component_id = (SELECT id FROM salary_components WHERE code = 'PF')
                WHERE pp.financial_year = :fy
                AND e.join_date <= pp.end_date
                AND (e.leave_date IS NULL OR e.leave_date >= pp.start_date)
                AND e.status = 'active'
                GROUP BY e.id
                HAVING arrear_periods > 0
                ORDER BY arrear_amount DESC";
        
        return $this->db->fetchAll($sql, ['fy' => $financialYear]);
    }
    
    public function updatePFSettings($data) {
        // In a real implementation, this would update a settings table
        // For now, we'll simulate saving to a configuration file
        
        $settings = [
            'establishment_code' => $data['establishment_code'],
            'establishment_name' => $data['establishment_name'],
            'employee_pf_rate' => floatval($data['employee_pf_rate']),
            'employer_pf_rate' => floatval($data['employer_pf_rate']),
            'eps_rate' => floatval($data['eps_rate']),
            'edli_rate' => floatval($data['edli_rate']),
            'admin_charges_rate' => floatval($data['admin_charges_rate']),
            'pf_ceiling' => floatval($data['pf_ceiling']),
            'eps_ceiling' => floatval($data['eps_ceiling'])
        ];
        
        try {
            $settingsFile = __DIR__ . '/../../config/pf_settings.json';
            file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to save PF settings'];
        }
    }
    
    public function processReconciliation($data) {
        $periodId = $data['period_id'];
        $uploadedFile = $data['uploaded_file'] ?? null;
        
        try {
            $this->beginTransaction();
            
            // Get current PF data from system
            $systemData = $this->getPFContributions($periodId);
            
            // Parse uploaded reconciliation file (if provided)
            $externalData = [];
            if ($uploadedFile) {
                $externalData = $this->parseReconciliationFile($uploadedFile);
            }
            
            // Perform reconciliation
            $reconciliationResult = $this->performReconciliation($systemData, $externalData);
            
            // Log reconciliation results
            $this->logReconciliation($periodId, $reconciliationResult);
            
            $this->commit();
            
            return ['success' => true, 'result' => $reconciliationResult];
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => 'Reconciliation failed: ' . $e->getMessage()];
        }
    }
    
    private function parseReconciliationFile($filePath) {
        // Parse CSV/Excel file with external PF data
        // This is a simplified implementation
        $data = [];
        
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $header = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== FALSE) {
                $data[] = array_combine($header, $row);
            }
            
            fclose($handle);
        }
        
        return $data;
    }
    
    private function performReconciliation($systemData, $externalData) {
        $matched = [];
        $unmatched = [];
        $discrepancies = [];
        
        foreach ($systemData as $sysRecord) {
            $found = false;
            
            foreach ($externalData as $extRecord) {
                if ($sysRecord['uan_number'] === $extRecord['uan_number']) {
                    $found = true;
                    
                    // Check for discrepancies
                    if (abs($sysRecord['employee_pf'] - $extRecord['employee_pf']) > 0.01) {
                        $discrepancies[] = [
                            'employee' => $sysRecord['first_name'] . ' ' . $sysRecord['last_name'],
                            'uan' => $sysRecord['uan_number'],
                            'system_amount' => $sysRecord['employee_pf'],
                            'external_amount' => $extRecord['employee_pf'],
                            'difference' => $sysRecord['employee_pf'] - $extRecord['employee_pf']
                        ];
                    } else {
                        $matched[] = $sysRecord;
                    }
                    break;
                }
            }
            
            if (!$found) {
                $unmatched[] = $sysRecord;
            }
        }
        
        return [
            'matched' => $matched,
            'unmatched' => $unmatched,
            'discrepancies' => $discrepancies,
            'total_system_records' => count($systemData),
            'total_external_records' => count($externalData)
        ];
    }
    
    private function logReconciliation($periodId, $result) {
        $logData = [
            'period_id' => $periodId,
            'matched_count' => count($result['matched']),
            'unmatched_count' => count($result['unmatched']),
            'discrepancy_count' => count($result['discrepancies']),
            'reconciliation_date' => date('Y-m-d H:i:s'),
            'status' => count($result['discrepancies']) > 0 ? 'discrepancies_found' : 'reconciled'
        ];
        
        // In a real implementation, save to pf_reconciliation table
        // For now, just log to audit trail
    }
    
    public function calculatePFLiability($periodId) {
        $sql = "SELECT 
                    SUM(ABS(pt.amount)) as employee_pf,
                    SUM(ABS(pt.amount)) as employer_pf_contribution,
                    SUM(ABS(pt.amount) * 0.0833 / 0.12) as eps_contribution,
                    SUM(ABS(pt.amount) * 0.005 / 0.12) as edli_contribution,
                    SUM(ABS(pt.amount) * 0.0065 / 0.12) as admin_charges
                FROM payroll_transactions pt
                JOIN salary_components sc ON pt.component_id = sc.id
                WHERE pt.period_id = :period_id AND sc.code = 'PF'";
        
        return $this->db->fetch($sql, ['period_id' => $periodId]);
    }
    
    public function getPFChallanData($periodId) {
        $liability = $this->calculatePFLiability($periodId);
        $period = $this->db->fetch("SELECT * FROM payroll_periods WHERE id = :id", ['id' => $periodId]);
        
        return [
            'period' => $period,
            'liability' => $liability,
            'due_date' => date('Y-m-15', strtotime($period['end_date'] . ' +1 month')),
            'challan_number' => 'PF' . $periodId . date('Ymd')
        ];
    }
}