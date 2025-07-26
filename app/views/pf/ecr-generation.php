<?php 
$title = 'ECR Generation - PF Management';
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
                <h1 class="text-3xl font-bold text-gray-900">ECR Generation</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Generate Electronic Challan-cum-Return files for EPFO submission
                </p>
            </div>
        </div>
    </div>

    <!-- ECR Generation Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">ECR Parameters</h3>
        </div>
        <div class="p-6">
            <form id="ecr-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="period_id" class="block text-sm font-medium text-gray-700 mb-2">Payroll Period *</label>
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
                        <label for="establishment_code" class="block text-sm font-medium text-gray-700 mb-2">Establishment Code *</label>
                        <input type="text" name="establishment_code" id="establishment_code" required
                               value="<?php echo htmlspecialchars($pf_settings['establishment_code'] ?? ''); ?>"
                               class="form-input" placeholder="e.g., DLCPM0026293000">
                    </div>
                    
                    <div>
                        <label for="return_month" class="block text-sm font-medium text-gray-700 mb-2">Return Month *</label>
                        <select name="return_month" id="return_month" required class="form-select">
                            <option value="">Select Month</option>
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="return_year" class="block text-sm font-medium text-gray-700 mb-2">Return Year *</label>
                        <input type="number" name="return_year" id="return_year" required
                               value="<?php echo date('Y'); ?>" min="2020" max="2030" class="form-input">
                    </div>
                </div>
                
                <div class="border-t pt-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">ECR Options</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_eps" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Include EPS Contribution</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_edli" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Include EDLI Contribution</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="validate_uan" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Validate UAN Numbers</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="exclude_zero_contribution" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700">Exclude Zero Contributions</span>
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
                        <i class="fas fa-download mr-2"></i>
                        Generate ECR File
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ECR Information -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">About ECR Files</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p class="mb-2">Electronic Challan-cum-Return (ECR) is a monthly return that needs to be filed with EPFO containing:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Employee-wise PF contribution details</li>
                        <li>Employer contribution breakdown (PF, EPS, EDLI)</li>
                        <li>UAN numbers and member details</li>
                        <li>Wage details for the contribution period</li>
                    </ul>
                    <p class="mt-2"><strong>Due Date:</strong> 15th of the following month</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ECR Preview -->
    <div id="ecr-preview" class="mt-6 bg-white shadow-sm rounded-lg border border-gray-200 hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">ECR Preview</h3>
        </div>
        <div class="p-6">
            <div id="ecr-content" class="bg-gray-50 p-4 rounded-md font-mono text-sm overflow-x-auto">
                <!-- ECR content will be displayed here -->
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('ecr-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Validate required fields
    const requiredFields = ['period_id', 'establishment_code', 'return_month', 'return_year'];
    let hasErrors = false;
    
    requiredFields.forEach(field => {
        if (!data[field]) {
            document.querySelector(`[name="${field}"]`).classList.add('border-red-300');
            hasErrors = true;
        } else {
            document.querySelector(`[name="${field}"]`).classList.remove('border-red-300');
        }
    });
    
    if (hasErrors) {
        showMessage('Please fill in all required fields', 'error');
        return;
    }
    
    showLoading();
    
    fetch('/pf/ecr-generation', {
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
            
            if (contentType && contentType.includes('text/plain')) {
                // File download
                return response.blob().then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `ECR_${data.return_month}_${data.return_year}.txt`;
                    a.click();
                    window.URL.revokeObjectURL(url);
                    
                    showMessage('ECR file generated and downloaded successfully', 'success');
                });
            } else {
                return response.json().then(data => {
                    if (data.success) {
                        showMessage('ECR file generated successfully', 'success');
                    } else {
                        showMessage(data.message || 'Failed to generate ECR file', 'error');
                    }
                });
            }
        } else {
            showMessage('Failed to generate ECR file', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while generating ECR file', 'error');
    });
});

// Auto-populate return month based on selected period
document.getElementById('period_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const periodName = selectedOption.text;
        const monthMatch = periodName.match(/(\w+)\s+(\d{4})/);
        
        if (monthMatch) {
            const monthName = monthMatch[1];
            const year = monthMatch[2];
            
            const monthMap = {
                'January': 1, 'February': 2, 'March': 3, 'April': 4,
                'May': 5, 'June': 6, 'July': 7, 'August': 8,
                'September': 9, 'October': 10, 'November': 11, 'December': 12
            };
            
            if (monthMap[monthName]) {
                document.getElementById('return_month').value = monthMap[monthName];
                document.getElementById('return_year').value = year;
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>