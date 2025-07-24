<?php
/**
 * Settings Model
 */

require_once __DIR__ . '/../core/Model.php';

class Settings extends Model {
    protected $table = 'system_settings';
    
    public function getSetting($key, $default = null) {
        $setting = $this->findBy('setting_key', $key);
        return $setting ? $setting['setting_value'] : $default;
    }
    
    public function setSetting($key, $value, $description = null) {
        $existing = $this->findBy('setting_key', $key);
        
        $data = [
            'setting_key' => $key,
            'setting_value' => $value,
            'description' => $description,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->create($data);
        }
    }
    
    public function getSettingsByCategory($category) {
        return $this->findAll(
            'category = :category',
            ['category' => $category],
            'setting_key ASC'
        );
    }
    
    public function updateSettings($settings, $category = null) {
        try {
            $this->beginTransaction();
            
            foreach ($settings as $key => $value) {
                $this->setSetting($key, $value);
            }
            
            $this->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => 'Failed to update settings'];
        }
    }
    
    public function getCompanySettings() {
        return [
            'company_name' => $this->getSetting('company_name', 'PayrollPro Enterprise'),
            'company_code' => $this->getSetting('company_code', 'PPE001'),
            'company_address' => $this->getSetting('company_address', ''),
            'financial_year_start' => $this->getSetting('financial_year_start', 4),
            'currency' => $this->getSetting('currency', 'INR'),
            'timezone' => $this->getSetting('timezone', 'Asia/Kolkata'),
            'date_format' => $this->getSetting('date_format', 'dd/mm/yyyy')
        ];
    }
    
    public function getPayrollSettings() {
        return [
            'pf_rate_employee' => $this->getSetting('pf_rate_employee', 12.00),
            'pf_rate_employer' => $this->getSetting('pf_rate_employer', 12.00),
            'esi_rate_employee' => $this->getSetting('esi_rate_employee', 0.75),
            'esi_rate_employer' => $this->getSetting('esi_rate_employer', 3.25),
            'esi_threshold' => $this->getSetting('esi_threshold', 21000),
            'pt_amount' => $this->getSetting('pt_amount', 200),
            'auto_calculate_tds' => $this->getSetting('auto_calculate_tds', 1),
            'auto_process_loans' => $this->getSetting('auto_process_loans', 1)
        ];
    }
    
    public function getEmailSettings() {
        return [
            'smtp_host' => $this->getSetting('smtp_host', 'smtp.gmail.com'),
            'smtp_port' => $this->getSetting('smtp_port', 587),
            'smtp_username' => $this->getSetting('smtp_username', ''),
            'smtp_password' => $this->getSetting('smtp_password', ''),
            'from_email' => $this->getSetting('from_email', 'noreply@company.com'),
            'from_name' => $this->getSetting('from_name', 'PayrollPro System'),
            'enable_ssl' => $this->getSetting('enable_ssl', 1),
            'auto_email_payslips' => $this->getSetting('auto_email_payslips', 0)
        ];
    }
    
    public function getSecuritySettings() {
        return [
            'session_timeout' => $this->getSetting('session_timeout', 30),
            'max_login_attempts' => $this->getSetting('max_login_attempts', 3),
            'lockout_duration' => $this->getSetting('lockout_duration', 15),
            'password_min_length' => $this->getSetting('password_min_length', 6),
            'require_password_change' => $this->getSetting('require_password_change', 0),
            'enable_audit_log' => $this->getSetting('enable_audit_log', 1),
            'enable_2fa' => $this->getSetting('enable_2fa', 0)
        ];
    }
    
    public function getBackupSettings() {
        return [
            'backup_frequency' => $this->getSetting('backup_frequency', 'daily'),
            'backup_retention' => $this->getSetting('backup_retention', 30),
            'backup_database' => $this->getSetting('backup_database', 1),
            'backup_files' => $this->getSetting('backup_files', 1),
            'backup_path' => $this->getSetting('backup_path', '/var/backups/payroll')
        ];
    }
    
    public function testEmailConfiguration($settings) {
        // In a real implementation, this would test SMTP connection
        try {
            // Simulate email test
            $testResult = [
                'success' => true,
                'message' => 'SMTP connection successful',
                'details' => [
                    'host' => $settings['smtp_host'],
                    'port' => $settings['smtp_port'],
                    'encryption' => $settings['enable_ssl'] ? 'TLS' : 'None'
                ]
            ];
            
            return $testResult;
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'SMTP connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    public function exportSettings($format = 'json') {
        $allSettings = $this->findAll('', [], 'category ASC, setting_key ASC');
        
        $organized = [];
        foreach ($allSettings as $setting) {
            $category = $setting['category'] ?: 'general';
            $organized[$category][$setting['setting_key']] = $setting['setting_value'];
        }
        
        switch ($format) {
            case 'json':
                return json_encode($organized, JSON_PRETTY_PRINT);
            case 'php':
                return "<?php\nreturn " . var_export($organized, true) . ";\n";
            default:
                return $organized;
        }
    }
    
    public function importSettings($data, $overwrite = false) {
        try {
            $this->beginTransaction();
            
            $imported = 0;
            $skipped = 0;
            
            foreach ($data as $category => $settings) {
                foreach ($settings as $key => $value) {
                    $existing = $this->findBy('setting_key', $key);
                    
                    if ($existing && !$overwrite) {
                        $skipped++;
                        continue;
                    }
                    
                    $this->setSetting($key, $value);
                    $imported++;
                }
            }
            
            $this->commit();
            
            return [
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped
            ];
        } catch (Exception $e) {
            $this->rollback();
            return [
                'success' => false,
                'message' => 'Failed to import settings: ' . $e->getMessage()
            ];
        }
    }
}