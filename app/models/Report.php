<?php
/**
 * Report Model
 */

require_once __DIR__ . '/../core/Model.php';

class Report extends Model {
    protected $table = 'reports';
    
    public function generateSalaryRegister($periodId, $departmentId = null, $options = []) {
        $conditions = "pt.period_id = :period_id";
        $params = ['period_id' => $periodId];
        
        if ($departmentId) {
            $conditions .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }
        
        $sql = "SELECT 
                    e.emp_code,
                    e.first_name,
                    e.last_name,
                    d.name as department_name,
                    des.name as designation_name,
                    pp.period_name,
                    pp.start_date,
                    pp.end_date,
                    -- Basic salary
                    MAX(CASE WHEN sc.code = 'BASIC' THEN pt.amount ELSE 0 END) as basic_salary,
                    -- HRA
                    MAX(CASE WHEN sc.code = 'HRA' THEN pt.amount ELSE 0 END) as hra,
                    -- Other allowances
                    SUM(CASE WHEN sc.type = 'earning' AND sc.code NOT IN ('BASIC', 'HRA') THEN pt.amount ELSE 0 END) as other_allowances,
                    -- Total earnings
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE 0 END) as total_earnings,
                    -- PF deduction
                    MAX(CASE WHEN sc.code = 'PF' THEN ABS(pt.amount) ELSE 0 END) as pf_deduction,
                    -- TDS deduction
                    MAX(CASE WHEN sc.code = 'TDS' THEN ABS(pt.amount) ELSE 0 END) as tds_deduction,
                    -- Other deductions
                    SUM(CASE WHEN sc.type = 'deduction' AND sc.code NOT IN ('PF', 'TDS') THEN ABS(pt.amount) ELSE 0 END) as other_deductions,
                    -- Total deductions
                    SUM(CASE WHEN sc.type = 'deduction' THEN ABS(pt.amount) ELSE 0 END) as total_deductions,
                    -- Net pay
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE -ABS(pt.amount) END) as net_pay
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN departments d ON e.department_id = d.id
                JOIN designations des ON e.designation_id = des.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE {$conditions}
                GROUP BY pt.employee_id, pt.period_id
                ORDER BY e.emp_code ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function generateComponentReport($periodId, $componentId = null) {
        $conditions = "pt.period_id = :period_id";
        $params = ['period_id' => $periodId];
        
        if ($componentId) {
            $conditions .= " AND pt.component_id = :comp_id";
            $params['comp_id'] = $componentId;
        }
        
        $sql = "SELECT 
                    e.emp_code,
                    e.first_name,
                    e.last_name,
                    d.name as department_name,
                    sc.name as component_name,
                    sc.code as component_code,
                    sc.type as component_type,
                    pt.amount,
                    pt.calculated_amount,
                    pt.is_manual_override,
                    pp.period_name
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN departments d ON e.department_id = d.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE {$conditions}
                ORDER BY e.emp_code ASC, sc.display_order ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function generateBankTransferFile($periodId, $bankFormat = 'generic') {
        $sql = "SELECT 
                    e.emp_code,
                    e.first_name,
                    e.last_name,
                    e.bank_account_number,
                    e.bank_name,
                    e.bank_ifsc,
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE -ABS(pt.amount) END) as net_pay
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                WHERE pt.period_id = :period_id
                AND e.bank_account_number IS NOT NULL
                AND e.bank_ifsc IS NOT NULL
                GROUP BY pt.employee_id
                HAVING net_pay > 0
                ORDER BY e.emp_code ASC";
        
        $data = $this->db->fetchAll($sql, ['period_id' => $periodId]);
        
        return $this->formatBankFile($data, $bankFormat);
    }
    
    public function generateTaxReport($type, $periodId = null, $financialYear = null) {
        switch ($type) {
            case 'tds':
                return $this->generateTDSReport($periodId, $financialYear);
            case 'pf':
                return $this->generatePFTaxReport($periodId, $financialYear);
            case 'esi':
                return $this->generateESIReport($periodId, $financialYear);
            default:
                return [];
        }
    }
    
    public function generateAttendanceReport($startDate, $endDate, $departmentId = null) {
        $conditions = "a.attendance_date BETWEEN :start_date AND :end_date";
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        
        if ($departmentId) {
            $conditions .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }
        
        $sql = "SELECT 
                    e.emp_code,
                    e.first_name,
                    e.last_name,
                    d.name as department_name,
                    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_days,
                    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_days,
                    COUNT(CASE WHEN a.status = 'half_day' THEN 1 END) as half_days,
                    COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_days,
                    COUNT(a.id) as total_marked_days,
                    ROUND(AVG(a.total_hours), 2) as avg_hours_per_day
                FROM employees e
                JOIN departments d ON e.department_id = d.id
                LEFT JOIN attendance a ON e.id = a.employee_id AND {$conditions}
                WHERE e.status = 'active'
                GROUP BY e.id
                ORDER BY e.emp_code ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function generateLoanReport($asOfDate, $loanTypeId = null, $options = []) {
        $conditions = "el.disbursed_date <= :as_of_date";
        $params = ['as_of_date' => $asOfDate];
        
        if ($loanTypeId) {
            $conditions .= " AND el.loan_type_id = :loan_type_id";
            $params['loan_type_id'] = $loanTypeId;
        }
        
        if (!isset($options['include_closed']) || !$options['include_closed']) {
            $conditions .= " AND el.status = 'active'";
        }
        
        $sql = "SELECT 
                    e.emp_code,
                    e.first_name,
                    e.last_name,
                    d.name as department_name,
                    lt.name as loan_type,
                    el.loan_amount,
                    el.interest_rate,
                    el.tenure_months,
                    el.emi_amount,
                    el.outstanding_amount,
                    el.disbursed_date,
                    el.first_emi_date,
                    el.status,
                    CEIL(el.outstanding_amount / el.emi_amount) as remaining_emis
                FROM employee_loans el
                JOIN employees e ON el.employee_id = e.id
                JOIN departments d ON e.department_id = d.id
                JOIN loan_types lt ON el.loan_type_id = lt.id
                WHERE {$conditions}
                ORDER BY e.emp_code ASC, el.disbursed_date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    private function generateTDSReport($periodId, $financialYear) {
        $conditions = $periodId ? "pt.period_id = :period_id" : "pp.financial_year = :fy";
        $params = $periodId ? ['period_id' => $periodId] : ['fy' => $financialYear];
        
        $sql = "SELECT 
                    e.emp_code,
                    e.first_name,
                    e.last_name,
                    e.pan_number,
                    d.name as department_name,
                    pp.period_name,
                    pp.financial_year,
                    SUM(CASE WHEN sc.type = 'earning' AND sc.is_taxable = 1 THEN pt.amount ELSE 0 END) as taxable_income,
                    SUM(CASE WHEN sc.code = 'TDS' THEN ABS(pt.amount) ELSE 0 END) as tds_deducted
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN departments d ON e.department_id = d.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE {$conditions}
                GROUP BY e.id" . ($periodId ? "" : ", pp.id") . "
                HAVING tds_deducted > 0
                ORDER BY e.emp_code ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    private function generatePFTaxReport($periodId, $financialYear) {
        $conditions = $periodId ? "pt.period_id = :period_id" : "pp.financial_year = :fy";
        $params = $periodId ? ['period_id' => $periodId] : ['fy' => $financialYear];
        
        $sql = "SELECT 
                    e.emp_code,
                    e.first_name,
                    e.last_name,
                    e.pf_number,
                    e.uan_number,
                    d.name as department_name,
                    pp.period_name,
                    pp.financial_year,
                    MAX(CASE WHEN sc.code = 'BASIC' THEN pt.amount ELSE 0 END) as basic_salary,
                    SUM(CASE WHEN sc.code = 'PF' THEN ABS(pt.amount) ELSE 0 END) as pf_deducted
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN departments d ON e.department_id = d.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE {$conditions}
                GROUP BY e.id" . ($periodId ? "" : ", pp.id") . "
                HAVING pf_deducted > 0
                ORDER BY e.emp_code ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    private function generateESIReport($periodId, $financialYear) {
        $conditions = $periodId ? "pt.period_id = :period_id" : "pp.financial_year = :fy";
        $params = $periodId ? ['period_id' => $periodId] : ['fy' => $financialYear];
        
        $sql = "SELECT 
                    e.emp_code,
                    e.first_name,
                    e.last_name,
                    e.esi_number,
                    d.name as department_name,
                    pp.period_name,
                    pp.financial_year,
                    SUM(CASE WHEN sc.type = 'earning' AND sc.is_esi_applicable = 1 THEN pt.amount ELSE 0 END) as esi_wages,
                    SUM(CASE WHEN sc.code = 'ESI' THEN ABS(pt.amount) ELSE 0 END) as esi_deducted
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN departments d ON e.department_id = d.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE {$conditions}
                GROUP BY e.id" . ($periodId ? "" : ", pp.id") . "
                HAVING esi_deducted > 0
                ORDER BY e.emp_code ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    private function formatBankFile($data, $format) {
        switch ($format) {
            case 'sbi':
                return $this->formatSBIFile($data);
            case 'hdfc':
                return $this->formatHDFCFile($data);
            case 'icici':
                return $this->formatICICIFile($data);
            case 'axis':
                return $this->formatAxisFile($data);
            default:
                return $this->formatGenericFile($data);
        }
    }
    
    private function formatGenericFile($data) {
        $content = "Employee Code,Employee Name,Account Number,IFSC Code,Bank Name,Net Amount\n";
        
        foreach ($data as $row) {
            $content .= sprintf(
                '"%s","%s","%s","%s","%s","%.2f"' . "\n",
                $row['emp_code'],
                $row['first_name'] . ' ' . $row['last_name'],
                $row['bank_account_number'],
                $row['bank_ifsc'],
                $row['bank_name'],
                $row['net_pay']
            );
        }
        
        return $content;
    }
    
    private function formatSBIFile($data) {
        // SBI specific format - fixed width
        $content = "";
        
        foreach ($data as $row) {
            $content .= sprintf(
                "%-20s%-30s%-20s%-11s%12.2f\n",
                substr($row['emp_code'], 0, 20),
                substr($row['first_name'] . ' ' . $row['last_name'], 0, 30),
                substr($row['bank_account_number'], 0, 20),
                substr($row['bank_ifsc'], 0, 11),
                $row['net_pay']
            );
        }
        
        return $content;
    }
    
    private function formatHDFCFile($data) {
        // HDFC specific format
        $content = "Sr No,Employee Code,Employee Name,Account Number,Amount,IFSC Code\n";
        
        $srNo = 1;
        foreach ($data as $row) {
            $content .= sprintf(
                '%d,"%s","%s","%s","%.2f","%s"' . "\n",
                $srNo++,
                $row['emp_code'],
                $row['first_name'] . ' ' . $row['last_name'],
                $row['bank_account_number'],
                $row['net_pay'],
                $row['bank_ifsc']
            );
        }
        
        return $content;
    }
    
    private function formatICICIFile($data) {
        // ICICI specific format
        $content = "Account Number,Amount,Beneficiary Name,IFSC Code,Employee Code\n";
        
        foreach ($data as $row) {
            $content .= sprintf(
                '"%s","%.2f","%s","%s","%s"' . "\n",
                $row['bank_account_number'],
                $row['net_pay'],
                $row['first_name'] . ' ' . $row['last_name'],
                $row['bank_ifsc'],
                $row['emp_code']
            );
        }
        
        return $content;
    }
    
    private function formatAxisFile($data) {
        // Axis Bank specific format - tab separated
        $content = "Employee Code\tEmployee Name\tAccount Number\tIFSC Code\tAmount\n";
        
        foreach ($data as $row) {
            $content .= sprintf(
                "%s\t%s\t%s\t%s\t%.2f\n",
                $row['emp_code'],
                $row['first_name'] . ' ' . $row['last_name'],
                $row['bank_account_number'],
                $row['bank_ifsc'],
                $row['net_pay']
            );
        }
        
        return $content;
    }
    
    public function getReportTemplates() {
        return [
            'salary_register' => [
                'name' => 'Salary Register',
                'description' => 'Complete salary breakdown by period',
                'parameters' => ['period_id', 'department_id'],
                'formats' => ['excel', 'csv', 'pdf']
            ],
            'component_report' => [
                'name' => 'Component Report',
                'description' => 'Component-wise salary analysis',
                'parameters' => ['period_id', 'component_id'],
                'formats' => ['excel', 'csv']
            ],
            'bank_transfer' => [
                'name' => 'Bank Transfer File',
                'description' => 'Bank-ready salary transfer file',
                'parameters' => ['period_id', 'bank_format'],
                'formats' => ['csv', 'txt']
            ],
            'attendance_report' => [
                'name' => 'Attendance Report',
                'description' => 'Employee attendance summary',
                'parameters' => ['start_date', 'end_date', 'department_id'],
                'formats' => ['excel', 'csv']
            ],
            'loan_report' => [
                'name' => 'Loan Report',
                'description' => 'Outstanding loans and EMI details',
                'parameters' => ['as_of_date', 'loan_type_id'],
                'formats' => ['excel', 'csv']
            ]
        ];
    }
    
    public function getReportStats() {
        $stats = [];
        
        // Most generated reports
        $sql = "SELECT 
                    'salary_register' as report_type,
                    COUNT(*) as generation_count
                FROM audit_logs 
                WHERE action = 'generate_salary_register'
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                
                UNION ALL
                
                SELECT 
                    'component_report' as report_type,
                    COUNT(*) as generation_count
                FROM audit_logs 
                WHERE action = 'generate_component_report'
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                
                ORDER BY generation_count DESC";
        
        $stats['popular_reports'] = $this->db->fetchAll($sql);
        
        return $stats;
    }
}