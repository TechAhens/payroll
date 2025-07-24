<?php
/**
 * Front Controller - Entry point for the application
 */

session_start();

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Model.php';

class Router {
    private $routes = [];
    
    public function addRoute($method, $pattern, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    public function dispatch($requestUri, $requestMethod) {
        // Remove query string from URI
        $uri = parse_url($requestUri, PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod && $route['method'] !== 'ANY') {
                continue;
            }
            
            // Convert route pattern to regex
            $pattern = preg_replace('/\{(\w+)\}/', '(\w+)', $route['pattern']);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                
                // Load and instantiate controller
                $controllerName = $route['controller'] . 'Controller';
                $controllerFile = __DIR__ . "/../app/controllers/{$controllerName}.php";
                
                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                    $controller = new $controllerName();
                    
                    $action = $route['action'];
                    if (method_exists($controller, $action)) {
                        call_user_func_array([$controller, $action], $matches);
                        return;
                    }
                }
            }
        }
        
        // 404 Not Found
        $this->show404();
    }
    
    private function show404() {
        http_response_code(404);
        include __DIR__ . '/../app/views/errors/404.php';
    }
}

// Initialize router
$router = new Router();

// Define routes
$router->addRoute('GET', '/', 'Dashboard', 'index');
$router->addRoute('GET', '/dashboard', 'Dashboard', 'index');

// Authentication routes
$router->addRoute('GET', '/login', 'Auth', 'login');
$router->addRoute('POST', '/login', 'Auth', 'login');
$router->addRoute('GET', '/logout', 'Auth', 'logout');
$router->addRoute('GET', '/profile', 'Auth', 'profile');
$router->addRoute('POST', '/profile', 'Auth', 'profile');
$router->addRoute('GET', '/change-password', 'Auth', 'changePassword');
$router->addRoute('POST', '/change-password', 'Auth', 'changePassword');

// Employee routes
$router->addRoute('GET', '/employees', 'Employee', 'index');
$router->addRoute('GET', '/employees/create', 'Employee', 'create');
$router->addRoute('POST', '/employees/create', 'Employee', 'create');
$router->addRoute('GET', '/employees/{id}', 'Employee', 'view');
$router->addRoute('GET', '/employees/{id}/edit', 'Employee', 'edit');
$router->addRoute('POST', '/employees/{id}/edit', 'Employee', 'edit');
$router->addRoute('POST', '/employees/{id}/delete', 'Employee', 'delete');
$router->addRoute('GET', '/employees/{id}/salary-structure', 'Employee', 'salaryStructure');
$router->addRoute('POST', '/employees/{id}/salary-structure', 'Employee', 'salaryStructure');
$router->addRoute('POST', '/employees/{id}/upload-document', 'Employee', 'uploadDocument');
$router->addRoute('GET', '/employees/export', 'Employee', 'export');

// Payroll routes
$router->addRoute('GET', '/payroll', 'Payroll', 'index');
$router->addRoute('GET', '/payroll/periods', 'Payroll', 'periods');
$router->addRoute('POST', '/payroll/periods', 'Payroll', 'periods');
$router->addRoute('GET', '/payroll/process', 'Payroll', 'process');
$router->addRoute('POST', '/payroll/process', 'Payroll', 'process');
$router->addRoute('POST', '/payroll/lock-period', 'Payroll', 'lockPeriod');
$router->addRoute('GET', '/payroll/payslip/{employeeId}/{periodId}', 'Payroll', 'payslip');

$router->addRoute('GET', '/cost-centers', 'CostCenter', 'index');
$router->addRoute('POST', '/cost-centers', 'CostCenter', 'index');

// Settings routes
$router->addRoute('GET', '/settings', 'Settings', 'index');
$router->addRoute('POST', '/settings/general', 'Settings', 'updateGeneral');
$router->addRoute('POST', '/settings/payroll', 'Settings', 'updatePayroll');
$router->addRoute('POST', '/settings/email', 'Settings', 'updateEmail');
$router->addRoute('POST', '/settings/test-email', 'Settings', 'testEmail');
$router->addRoute('GET', '/settings/backup', 'Settings', 'backup');
$router->addRoute('POST', '/settings/backup', 'Settings', 'backup');

// Payslip management routes
$router->addRoute('GET', '/payroll/payslips', 'Payroll', 'payslips');
$router->addRoute('POST', '/payroll/email-payslip/{employeeId}/{periodId}', 'Payroll', 'emailPayslip');

// Tax slab routes
$router->addRoute('GET', '/tax-slabs', 'TaxSlab', 'index');
$router->addRoute('POST', '/tax-slabs', 'TaxSlab', 'index');

// Master data routes
$router->addRoute('GET', '/departments', 'Department', 'index');
$router->addRoute('POST', '/departments', 'Department', 'index');
$router->addRoute('GET', '/designations', 'Designation', 'index');
$router->addRoute('POST', '/designations', 'Designation', 'index');
$router->addRoute('GET', '/salary-components', 'SalaryComponent', 'index');
$router->addRoute('POST', '/salary-components', 'SalaryComponent', 'index');
$router->addRoute('GET', '/loan-types', 'LoanType', 'index');
$router->addRoute('POST', '/loan-types', 'LoanType', 'index');
$router->addRoute('GET', '/leave-types', 'LeaveType', 'index');
$router->addRoute('POST', '/leave-types', 'LeaveType', 'index');
$router->addRoute('GET', '/holidays', 'Holiday', 'index');
$router->addRoute('POST', '/holidays', 'Holiday', 'index');

$router->addRoute('GET', '/pf', 'PF', 'index');
$router->addRoute('GET', '/pf/ecr-generation', 'PF', 'ecrGeneration');
$router->addRoute('POST', '/pf/ecr-generation', 'PF', 'ecrGeneration');
$router->addRoute('GET', '/pf/reports', 'PF', 'pfReports');
$router->addRoute('POST', '/pf/reports', 'PF', 'pfReports');
$router->addRoute('GET', '/pf/contributions', 'PF', 'pfContributions');
$router->addRoute('GET', '/pf/reconciliation', 'PF', 'pfReconciliation');
$router->addRoute('POST', '/pf/reconciliation', 'PF', 'pfReconciliation');
$router->addRoute('GET', '/pf/settings', 'PF', 'pfSettings');
$router->addRoute('POST', '/pf/settings', 'PF', 'pfSettings');

// Report routes
$router->addRoute('GET', '/reports', 'Report', 'index');
$router->addRoute('GET', '/reports/salary-register', 'Report', 'salaryRegister');
$router->addRoute('POST', '/reports/salary-register', 'Report', 'salaryRegister');
$router->addRoute('GET', '/reports/component-report', 'Report', 'componentReport');
$router->addRoute('POST', '/reports/component-report', 'Report', 'componentReport');
$router->addRoute('GET', '/reports/bank-transfer', 'Report', 'bankTransfer');
$router->addRoute('POST', '/reports/bank-transfer', 'Report', 'bankTransfer');
$router->addRoute('GET', '/reports/payslip', 'Report', 'payslip');

// Attendance routes
$router->addRoute('GET', '/attendance', 'Attendance', 'index');
$router->addRoute('GET', '/attendance/mark', 'Attendance', 'mark');
$router->addRoute('POST', '/attendance/mark', 'Attendance', 'mark');
$router->addRoute('POST', '/attendance/bulk-mark', 'Attendance', 'bulkMark');
$router->addRoute('GET', '/attendance/report', 'Attendance', 'report');
$router->addRoute('POST', '/attendance/report', 'Attendance', 'report');

// Loan routes
$router->addRoute('GET', '/loans', 'Loan', 'index');
$router->addRoute('GET', '/loans/create', 'Loan', 'create');
$router->addRoute('POST', '/loans/create', 'Loan', 'create');
$router->addRoute('GET', '/loans/{id}', 'Loan', 'view');
$router->addRoute('GET', '/loans/{id}/payment', 'Loan', 'payment');
$router->addRoute('POST', '/loans/{id}/payment', 'Loan', 'payment');

// User management routes
$router->addRoute('GET', '/users', 'User', 'index');
$router->addRoute('GET', '/users/create', 'User', 'create');
$router->addRoute('POST', '/users/create', 'User', 'create');
$router->addRoute('GET', '/users/{id}/edit', 'User', 'edit');
$router->addRoute('POST', '/users/{id}/edit', 'User', 'edit');

// API routes for AJAX calls
$router->addRoute('GET', '/api/dashboard-widgets', 'Dashboard', 'getWidgetData');
$router->addRoute('GET', '/api/attendance-summary', 'Attendance', 'getSummary');
$router->addRoute('GET', '/api/current-period', 'Payroll', 'getCurrentPeriod');
$router->addRoute('GET', '/api/attendance-summary', 'Api', 'attendanceSummary');
$router->addRoute('GET', '/api/current-period', 'Api', 'currentPeriod');
$router->addRoute('GET', '/api/employee-search', 'Api', 'employeeSearch');
$router->addRoute('GET', '/api/salary-calculator', 'Api', 'salaryCalculator');

// Notification routes
$router->addRoute('GET', '/notifications', 'Notification', 'index');
$router->addRoute('POST', '/notifications/mark-read', 'Notification', 'markRead');
$router->addRoute('POST', '/notifications/mark-all-read', 'Notification', 'markAllRead');
$router->addRoute('POST', '/notifications/delete', 'Notification', 'delete');
$router->addRoute('POST', '/notifications/clear-all', 'Notification', 'clearAll');
$router->addRoute('GET', '/api/notifications/unread-count', 'Notification', 'getUnreadCount');

// Backup routes
$router->addRoute('GET', '/settings/backups', 'Backup', 'index');
$router->addRoute('POST', '/settings/backups', 'Backup', 'index');

// Additional API routes
$router->addRoute('GET', '/api/generate-employee-code', 'Employee', 'generateEmployeeCode');
$router->addRoute('POST', '/employees/bulk-update-salary', 'Employee', 'bulkUpdateSalary');
$router->addRoute('GET', '/api/period-details', 'Payroll', 'getCurrentPeriod');

// Static file serving (for development)
$requestUri = $_SERVER['REQUEST_URI'];
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|ico|svg)$/', $requestUri)) {
    $filePath = __DIR__ . $requestUri;
    if (file_exists($filePath)) {
        $mimeType = mime_content_type($filePath);
        header('Content-Type: ' . $mimeType);
        readfile($filePath);
        exit;
    }
}

// Redirect root to dashboard if logged in, otherwise to login
if ($requestUri === '/') {
    if (isset($_SESSION['user_id'])) {
        header('Location: /dashboard');
    } else {
        header('Location: /login');
    }
    exit;
}

// Dispatch the request
try {
    $router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
} catch (Exception $e) {
    // Log error and show 500 page
    error_log("Application error: " . $e->getMessage());
    http_response_code(500);
    echo "Internal Server Error";
}