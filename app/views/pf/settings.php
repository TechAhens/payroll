<?php 
$title = 'PF Settings - PF Management';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/pf" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">PF Settings</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Configure PF rates, establishment details, and calculation parameters
                </p>
            </div>
        </div>
    </div>

    <!-- PF Settings Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">PF Configuration</h3>
        </div>
        <div class="p-6">
            <form id="pf-settings-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <!-- Establishment Details -->
                <div class="mb-8">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">Establishment Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="establishment_code" class="block text-sm font-medium text-gray-700 mb-2">Establishment Code *</label>
                            <input type="text" name="establishment_code" id="establishment_code" required
                                   value="<?php echo htmlspecialchars($pf_settings['establishment_code'] ?? 'DLCPM0026293000'); ?>"
                                   class="form-input" placeholder="e.g., DLCPM0026293000">
                        </div>
                        
                        <div>
                            <label for="establishment_name" class="block text-sm font-medium text-gray-700 mb-2">Establishment Name *</label>
                            <input type="text" name="establishment_name" id="establishment_name" required
                                   value="<?php echo htmlspecialchars($pf_settings['establishment_name'] ?? 'PayrollPro Enterprise'); ?>"
                                   class="form-input">
                        </div>
                    </div>
                </div>

                <!-- PF Rates -->
                <div class="mb-8">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">PF Contribution Rates</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="employee_pf_rate" class="block text-sm font-medium text-gray-700 mb-2">Employee PF Rate (%)</label>
                            <input type="number" name="employee_pf_rate" id="employee_pf_rate"
                                   value="<?php echo $pf_settings['employee_pf_rate'] ?? 12.00; ?>"
                                   class="form-input" step="0.01" min="0" max="100">
                        </div>
                        
                        <div>
                            <label for="employer_pf_rate" class="block text-sm font-medium text-gray-700 mb-2">Employer PF Rate (%)</label>
                            <input type="number" name="employer_pf_rate" id="employer_pf_rate"
                                   value="<?php echo $pf_settings['employer_pf_rate'] ?? 12.00; ?>"
                                   class="form-input" step="0.01" min="0" max="100">
                        </div>
                        
                        <div>
                            <label for="eps_rate" class="block text-sm font-medium text-gray-700 mb-2">EPS Rate (%)</label>
                            <input type="number" name="eps_rate" id="eps_rate"
                                   value="<?php echo $pf_settings['eps_rate'] ?? 8.33; ?>"
                                   class="form-input" step="0.01" min="0" max="100">
                        </div>
                        
                        <div>
                            <label for="edli_rate" class="block text-sm font-medium text-gray-700 mb-2">EDLI Rate (%)</label>
                            <input type="number" name="edli_rate" id="edli_rate"
                                   value="<?php echo $pf_settings['edli_rate'] ?? 0.50; ?>"
                                   class="form-input" step="0.01" min="0" max="100">
                        </div>
                        
                        <div>
                            <label for="admin_charges_rate" class="block text-sm font-medium text-gray-700 mb-2">Admin Charges Rate (%)</label>
                            <input type="number" name="admin_charges_rate" id="admin_charges_rate"
                                   value="<?php echo $pf_settings['admin_charges_rate'] ?? 0.65; ?>"
                                   class="form-input" step="0.01" min="0" max="100">
                        </div>
                    </div>
                </div>

                <!-- Ceiling Amounts -->
                <div class="mb-8">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">Ceiling Amounts</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="pf_ceiling" class="block text-sm font-medium text-gray-700 mb-2">PF Ceiling Amount</label>
                            <input type="number" name="pf_ceiling" id="pf_ceiling"
                                   value="<?php echo $pf_settings['pf_ceiling'] ?? 15000.00; ?>"
                                   class="form-input" step="1" min="0">
                        </div>
                        
                        <div>
                            <label for="eps_ceiling" class="block text-sm font-medium text-gray-700 mb-2">EPS Ceiling Amount</label>
                            <input type="number" name="eps_ceiling" id="eps_ceiling"
                                   value="<?php echo $pf_settings['eps_ceiling'] ?? 15000.00; ?>"
                                   class="form-input" step="1" min="0">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <a href="/pf" class="btn btn-outline">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- PF Calculation Preview -->
    <div class="mt-6 bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">PF Calculation Preview</h3>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label for="preview_basic" class="block text-sm font-medium text-gray-700 mb-2">Basic Salary for Preview</label>
                <input type="number" id="preview_basic" value="30000" class="form-input w-48" step="1" min="0">
            </div>
            
            <div id="pf-calculation-result" class="bg-gray-50 p-4 rounded-lg">
                <!-- Calculation results will be displayed here -->
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('pf-settings-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    showLoading();
    
    fetch('/pf/settings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showMessage('PF settings saved successfully', 'success');
        } else {
            showMessage(data.message || 'Failed to save settings', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while saving settings', 'error');
    });
});

// PF calculation preview
function updatePFPreview() {
    const basicSalary = parseFloat(document.getElementById('preview_basic').value) || 0;
    const employeePFRate = parseFloat(document.getElementById('employee_pf_rate').value) || 12;
    const employerPFRate = parseFloat(document.getElementById('employer_pf_rate').value) || 12;
    const epsRate = parseFloat(document.getElementById('eps_rate').value) || 8.33;
    const edliRate = parseFloat(document.getElementById('edli_rate').value) || 0.50;
    const adminRate = parseFloat(document.getElementById('admin_charges_rate').value) || 0.65;
    const pfCeiling = parseFloat(document.getElementById('pf_ceiling').value) || 15000;
    
    if (basicSalary > 0) {
        const calculation = PFCalculator.calculate(basicSalary, {
            pfRate: employeePFRate,
            epsRate: epsRate,
            edliRate: edliRate,
            adminRate: adminRate,
            ceiling: pfCeiling
        });
        
        PFCalculator.displayCalculation(calculation, 'pf-calculation-result');
    }
}

// Update preview when values change
document.getElementById('preview_basic').addEventListener('input', updatePFPreview);
document.getElementById('employee_pf_rate').addEventListener('input', updatePFPreview);
document.getElementById('employer_pf_rate').addEventListener('input', updatePFPreview);
document.getElementById('eps_rate').addEventListener('input', updatePFPreview);
document.getElementById('edli_rate').addEventListener('input', updatePFPreview);
document.getElementById('admin_charges_rate').addEventListener('input', updatePFPreview);
document.getElementById('pf_ceiling').addEventListener('input', updatePFPreview);

// Initial preview
updatePFPreview();
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>