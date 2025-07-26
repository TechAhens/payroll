<?php
/**
 * Employee State Insurance (ESI) Model
 */

require_once __DIR__ . '/../core/Model.php';

class ESI extends Model {
    protected $table = 'esi_transactions';
    
    public function getESISummary($financialYear) {
        $sql = "SELECT 
                    COUNT(DISTINCT pt.employee_id) as total_employees,
                    SUM(CASE WHEN sc.code = 'ESI' THEN ABS(pt.amount) ELSE 0 END) as total_employee_contribution,
                    SUM(CASE WHEN sc.code = 'ESI' THEN ABS(pt.amount) * 4.33 ELSE 0 END) as total_employer_contribution,
                    SUM(CASE WHEN sc.code = 'ESI' THEN ABS(pt.amount) * 5.33 ELSE 0 END) as total_esi_contribution
                FROM payroll_transactions pt
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE pp.financial_year = :fy AND sc.code = 'ESI'";
        
        return $this->db->fetch($sql, ['fy' => $financialYear]);
    }
    
    public function getRecentESITransactions($limit = 10) {
        $sql = "SELECT 
                    e.emp_code,
                    e.first_name,
                    e.last_name,
                    pp.period_name,
                    ABS(pt.amount) as esi_amount,
                    pt.created_at
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                JOIN salary_components sc ON pt.component_id = sc.id
                WHERE sc.code = 'ESI'
                ORDER BY pt.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getESIContributions($periodId = null, $departmentId = null) {
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
                    e.esi_number,
                    d.name as department_name,
                    pp.period_name,
                    pp.start_date,
                    pp.end_date,
                    -- Get gross salary for ESI calculation
                    (SELECT SUM(pt2.amount) FROM payroll_transactions pt2 
                     JOIN salary_components sc2 ON pt2.component_id = sc2.id 
                     WHERE pt2.employee_id = e.id AND pt2.period_id = pt.period_id 
                     AND sc2.type = 'earning' AND sc2.is_esi_applicable = 1) as esi_wages,
                    ABS(pt.amount) as employee_esi,
                    ABS(pt.amount) * 4.33 as employer_esi,
                    ABS(pt.amount) * 5.33 as total_esi
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN departments d ON e.department_id = d.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                JOIN salary_components sc ON pt.component_id = sc.id
                {$whereClause}
                AND sc.code = 'ESI'
                AND e.status = 'active'
                ORDER BY e.emp_code ASC";
        
        return $this->db->fetchAll($sql, $params);
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
                    SUM(ABS(pt.amount)) as total_employee_esi,
                    SUM(ABS(pt.amount) * 4.33) as total_employer_esi,
                    SUM(ABS(pt.amount) * 5.33) as total_esi_contribution
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN departments d ON e.department_id = d.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                JOIN salary_components sc ON pt.component_id = sc.id
                {$whereClause}
                AND sc.code = 'ESI'
                GROUP BY d.id, d.name
                ORDER BY d.name";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getEmployeeWiseESIReport($periodId, $financialYear) {
        return $this->getESIContributions($periodId);
    }
    
    public function getMonthlySummaryReport($financialYear) {
        $sql = "SELECT 
                    pp.period_name,
                    pp.start_date,
                    pp.end_date,
                    COUNT(DISTINCT pt.employee_id) as employee_count,
                    SUM(ABS(pt.amount)) as employee_contribution,
                    SUM(ABS(pt.amount) * 4.33) as employer_contribution,
                    SUM(ABS(pt.amount) * 5.33) as total_contribution
                FROM payroll_transactions pt
                JOIN payroll_periods pp ON pt.period_id = pp.id
                JOIN salary_components sc ON pt.component_id = sc.id
                WHERE pp.financial_year = :fy AND sc.code = 'ESI'
                GROUP BY pp.id, pp.period_name, pp.start_date, pp.end_date
                ORDER BY pp.start_date";
        
        return $this->db->fetchAll($sql, ['fy' => $financialYear]);
    }
    
    public function updateESISettings($data) {
        // In a real implementation, this would update a settings table
        $settings = [
            'employee_esi_rate' => floatval($data['employee_esi_rate']),
            'employer_esi_rate' => floatval($data['employer_esi_rate']),
            'esi_threshold' => floatval($data['esi_threshold']),
            'esi_ceiling' => floatval($data['esi_ceiling']),
            'medical_benefit_rate' => floatval($data['medical_benefit_rate'])
        ];
        
        try {
            $settingsFile = __DIR__ . '/../../config/esi_settings.json';
            file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to save ESI settings'];
        }
    }
    
    public function calculateESILiability($periodId) {
        $sql = "SELECT 
                    SUM(ABS(pt.amount)) as employee_esi,
                    SUM(ABS(pt.amount) * 4.33) as employer_esi_contribution,
                    SUM(ABS(pt.amount) * 5.33) as total_esi_liability
                FROM payroll_transactions pt
                JOIN salary_components sc ON pt.component_id = sc.id
                WHERE pt.period_id = :period_id AND sc.code = 'ESI'";
        
        return $this->db->fetch($sql, ['period_id' => $periodId]);
    }
    
    public function getESIChallanData($periodId) {
        $liability = $this->calculateESILiability($periodId);
        $period = $this->db->fetch("SELECT * FROM payroll_periods WHERE id = :id", ['id' => $periodId]);
        
        return [
            'period' => $period,
            'liability' => $liability,
            'due_date' => date('Y-m-21', strtotime($period['end_date'] . ' +1 month')),
            'challan_number' => 'ESI' . $periodId . date('Ymd')
        ];
    }
    
    public function validateESIEligibility($grossSalary, $threshold = 21000) {
        return $grossSalary <= $threshold;
    }
    
    public function calculateESIContribution($grossSalary, $employeeRate = 0.75, $employerRate = 3.25) {
        $employeeESI = ($grossSalary * $employeeRate) / 100;
        $employerESI = ($grossSalary * $employerRate) / 100;
        
        return [
            'employee_esi' => round($employeeESI, 2),
            'employer_esi' => round($employerESI, 2),
            'total_esi' => round($employeeESI + $employerESI, 2)
        ];
    }
}