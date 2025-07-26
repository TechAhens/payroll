<?php 
$title = 'PF Reports - PF Management';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/pf" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">PF Reports</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Generate comprehensive PF compliance and analysis reports
                </p>
            </div>
        </div>
    </div>

    <!-- Report Types -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Contribution Summary Report -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Contribution Summary</h3>
            </div>
            <div class="p-6">
                <form class="pf-report-form" data-report-type="contribution_summary">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="report_type" value="contribution_summary">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="cs_period_id" class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                            <select name="period_id" id="cs_period_id" class="form-select">
                                <option value="">Select Period</option>
                                <?php foreach ($periods as $period): ?>
                                    <option value="<?php echo $period['id']; ?>">
                                        <?php echo htmlspecialchars($period['period_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="cs_format" class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                            <select name="format" id="cs_format" class="form-select">
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="w-full btn btn-primary">
                            <i class="fas fa-download mr-2"></i>
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Employee-wise Report -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Employee-wise Report</h3>
            </div>
            <div class="p-6">
                <form class="pf-report-form" data-report-type="employee_wise">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="report_type" value="employee_wise">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="ew_period_id" class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                            <select name="period_id" id="ew_period_id" class="form-select">
                                <option value="">Select Period</option>
                                <?php foreach ($periods as $period): ?>
                                    <option value="<?php echo $period['id']; ?>">
                                        <?php echo htmlspecialchars($period['period_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="ew_format" class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                            <select name="format" id="ew_format" class="form-select">
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="w-full btn btn-primary">
                            <i class="fas fa-download mr-2"></i>
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Monthly Summary Report -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Monthly Summary</h3>
            </div>
            <div class="p-6">
                <form class="pf-report-form" data-report-type="monthly_summary">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="report_type" value="monthly_summary">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="ms_financial_year" class="block text-sm font-medium text-gray-700 mb-2">Financial Year</label>
                            <select name="financial_year" id="ms_financial_year" class="form-select">
                                <option value="">Select Financial Year</option>
                                <?php foreach ($financial_years as $fy): ?>
                                    <option value="<?php echo $fy['financial_year']; ?>">
                                        FY <?php echo $fy['financial_year']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="ms_format" class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                            <select name="format" id="ms_format" class="form-select">
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="w-full btn btn-primary">
                            <i class="fas fa-download mr-2"></i>
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Arrears Report -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">PF Arrears Report</h3>
            </div>
            <div class="p-6">
                <form class="pf-report-form" data-report-type="arrears_report">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="report_type" value="arrears_report">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="ar_financial_year" class="block text-sm font-medium text-gray-700 mb-2">Financial Year</label>
                            <select name="financial_year" id="ar_financial_year" class="form-select">
                                <option value="">Select Financial Year</option>
                                <?php foreach ($financial_years as $fy): ?>
                                    <option value="<?php echo $fy['financial_year']; ?>">
                                        FY <?php echo $fy['financial_year']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="ar_format" class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                            <select name="format" id="ar_format" class="form-select">
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="w-full btn btn-primary">
                            <i class="fas fa-download mr-2"></i>
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Report Information -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Report Descriptions</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-2">Contribution Summary</h4>
                    <p class="text-sm text-gray-600 mb-4">
                        Department-wise summary of PF contributions including employee and employer contributions.
                    </p>
                    
                    <h4 class="text-md font-semibold text-gray-900 mb-2">Employee-wise Report</h4>
                    <p class="text-sm text-gray-600">
                        Detailed employee-wise PF contribution report with UAN numbers, basic salary, and contribution breakdowns.
                    </p>
                </div>
                
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-2">Monthly Summary</h4>
                    <p class="text-sm text-gray-600 mb-4">
                        Month-wise PF contribution summary for the entire financial year showing trends and totals.
                    </p>
                    
                    <h4 class="text-md font-semibold text-gray-900 mb-2">Arrears Report</h4>
                    <p class="text-sm text-gray-600">
                        Identifies employees with pending PF contributions and calculates arrear amounts.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportForms = document.querySelectorAll('.pf-report-form');
    
    reportForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const reportType = this.dataset.reportType;
            const data = Object.fromEntries(formData);
            
            // Validate required fields based on report type
            let requiredField = '';
            switch (reportType) {
                case 'contribution_summary':
                case 'employee_wise':
                    requiredField = 'period_id';
                    break;
                case 'monthly_summary':
                case 'arrears_report':
                    requiredField = 'financial_year';
                    break;
            }
            
            if (requiredField && !data[requiredField]) {
                showMessage('Please select the required field', 'error');
                return;
            }
            
            showLoading();
            
            fetch('/pf/reports', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                hideLoading();
                
                if (response.ok) {
                    const contentType = response.headers.get('content-type');
                    
                    if (contentType && (contentType.includes('application/vnd.ms-excel') || contentType.includes('text/csv'))) {
                        // File download
                        return response.blob().then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = `pf_${reportType}_${new Date().toISOString().split('T')[0]}.${data.format}`;
                            a.click();
                            window.URL.revokeObjectURL(url);
                            
                            showMessage('PF report downloaded successfully', 'success');
                        });
                    } else {
                        return response.json().then(data => {
                            if (data.success) {
                                showMessage('Report generated successfully', 'success');
                            } else {
                                showMessage(data.message || 'Failed to generate report', 'error');
                            }
                        });
                    }
                } else {
                    showMessage('Failed to generate report', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showMessage('An error occurred while generating the report', 'error');
            });
        });
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>