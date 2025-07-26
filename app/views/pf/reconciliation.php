<?php 
$title = 'PF Reconciliation - PF Management';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/pf" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">PF Reconciliation</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Reconcile PF data with EPFO records and identify discrepancies
                </p>
            </div>
        </div>
    </div>

    <!-- Reconciliation Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Reconciliation Parameters</h3>
        </div>
        <div class="p-6">
            <form id="reconciliation-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="period_id" class="block text-sm font-medium text-gray-700 mb-2">Period *</label>
                        <select name="period_id" id="period_id" required class="form-select">
                            <option value="">Select Period</option>
                            <?php foreach ($periods as $period): ?>
                                <option value="<?php echo $period['id']; ?>">
                                    <?php echo htmlspecialchars($period['period_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="reconciliation_file" class="block text-sm font-medium text-gray-700 mb-2">EPFO Data File (Optional)</label>
                        <input type="file" name="reconciliation_file" id="reconciliation_file" 
                               accept=".csv,.xlsx,.xls" class="form-input">
                        <p class="text-xs text-gray-500 mt-1">Upload CSV or Excel file with EPFO data for comparison</p>
                    </div>
                </div>
                
                <div class="border-t pt-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">Reconciliation Options</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="check_uan_validity" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Validate UAN Numbers</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="check_contribution_amounts" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Verify Contribution Amounts</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="identify_missing_members" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Identify Missing Members</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="generate_correction_file" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700">Generate Correction File</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex items-center justify-end space-x-4">
                    <a href="/pf" class="btn btn-outline">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-balance-scale mr-2"></i>
                        Start Reconciliation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reconciliation Results -->
    <div id="reconciliation-results" class="hidden">
        <!-- Reconciliation Summary -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Reconciliation Summary</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600" id="matched-count">0</div>
                        <div class="text-sm text-gray-500">Matched Records</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600" id="unmatched-count">0</div>
                        <div class="text-sm text-gray-500">Unmatched Records</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600" id="discrepancy-count">0</div>
                        <div class="text-sm text-gray-500">Discrepancies</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600" id="total-records">0</div>
                        <div class="text-sm text-gray-500">Total Records</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Discrepancies Table -->
        <div id="discrepancies-section" class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6 hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Discrepancies Found</h3>
            </div>
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Employee
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                UAN Number
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                System Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                EPFO Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Difference
                            </th>
                        </tr>
                    </thead>
                    <tbody id="discrepancies-tbody" class="bg-white divide-y divide-gray-200">
                        <!-- Discrepancies will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-4">
            <button onclick="exportReconciliationReport()" class="btn btn-outline">
                <i class="fas fa-download mr-2"></i>
                Export Report
            </button>
            <button onclick="generateCorrectionFile()" class="btn btn-primary">
                <i class="fas fa-file-medical mr-2"></i>
                Generate Correction File
            </button>
        </div>
    </div>

    <!-- File Format Information -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">File Format Requirements</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p class="mb-2">For reconciliation, upload a CSV or Excel file with the following columns:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li><strong>UAN Number:</strong> 12-digit UAN number</li>
                        <li><strong>Employee Name:</strong> Full name of the employee</li>
                        <li><strong>Employee PF:</strong> Employee PF contribution amount</li>
                        <li><strong>Employer PF:</strong> Employer PF contribution amount</li>
                        <li><strong>EPS:</strong> EPS contribution amount (optional)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('reconciliation-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const periodId = formData.get('period_id');
    
    if (!periodId) {
        showMessage('Please select a period', 'error');
        return;
    }
    
    showLoading();
    
    fetch('/pf/reconciliation', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            displayReconciliationResults(data.result);
            showMessage('Reconciliation completed successfully', 'success');
        } else {
            showMessage(data.message || 'Reconciliation failed', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred during reconciliation', 'error');
    });
});

function displayReconciliationResults(result) {
    // Show results section
    document.getElementById('reconciliation-results').classList.remove('hidden');
    
    // Update summary counts
    document.getElementById('matched-count').textContent = result.matched.length;
    document.getElementById('unmatched-count').textContent = result.unmatched.length;
    document.getElementById('discrepancy-count').textContent = result.discrepancies.length;
    document.getElementById('total-records').textContent = result.total_system_records;
    
    // Show discrepancies if any
    if (result.discrepancies.length > 0) {
        document.getElementById('discrepancies-section').classList.remove('hidden');
        
        const tbody = document.getElementById('discrepancies-tbody');
        tbody.innerHTML = '';
        
        result.discrepancies.forEach(discrepancy => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${discrepancy.employee}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                    ${discrepancy.uan}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ₹${parseFloat(discrepancy.system_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ₹${parseFloat(discrepancy.external_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm ${discrepancy.difference >= 0 ? 'text-green-600' : 'text-red-600'}">
                    ₹${parseFloat(discrepancy.difference).toLocaleString('en-IN', {minimumFractionDigits: 2})}
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    // Store results for export
    window.reconciliationResults = result;
}

function exportReconciliationReport() {
    if (!window.reconciliationResults) {
        showMessage('No reconciliation data to export', 'error');
        return;
    }
    
    // Create CSV content
    let csvContent = "Employee,UAN Number,System Amount,EPFO Amount,Difference,Status\n";
    
    // Add matched records
    window.reconciliationResults.matched.forEach(record => {
        csvContent += `"${record.first_name} ${record.last_name}","${record.uan_number}","${record.employee_pf}","${record.employee_pf}","0","Matched"\n`;
    });
    
    // Add discrepancies
    window.reconciliationResults.discrepancies.forEach(record => {
        csvContent += `"${record.employee}","${record.uan}","${record.system_amount}","${record.external_amount}","${record.difference}","Discrepancy"\n`;
    });
    
    // Add unmatched records
    window.reconciliationResults.unmatched.forEach(record => {
        csvContent += `"${record.first_name} ${record.last_name}","${record.uan_number}","${record.employee_pf}","0","${record.employee_pf}","Unmatched"\n`;
    });
    
    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `pf_reconciliation_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
    
    showMessage('Reconciliation report exported successfully', 'success');
}

function generateCorrectionFile() {
    if (!window.reconciliationResults || window.reconciliationResults.discrepancies.length === 0) {
        showMessage('No discrepancies found to generate correction file', 'info');
        return;
    }
    
    showMessage('Correction file generation feature coming soon', 'info');
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>