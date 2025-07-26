<?php 
$title = 'PF Contributions - PF Management';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <div class="flex items-center">
                <a href="/pf" class="text-gray-500 hover:text-gray-700 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">PF Contributions</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        View and manage employee PF contributions
                    </p>
                </div>
            </div>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
            <button onclick="exportContributions()" class="btn btn-outline">
                <i class="fas fa-download mr-2"></i>
                Export
            </button>
            <a href="/pf/ecr-generation" class="btn btn-primary">
                <i class="fas fa-file-export mr-2"></i>
                Generate ECR
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                    <select name="period" id="period" class="form-select">
                        <option value="">All Periods</option>
                        <?php foreach ($periods as $period): ?>
                            <option value="<?php echo $period['id']; ?>" 
                                    <?php echo $selected_period == $period['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($period['period_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <select name="department" id="department" class="form-select">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" 
                                    <?php echo $selected_department == $dept['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="w-full btn btn-primary">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- PF Contributions Table -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employee Details
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            UAN / PF Number
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Basic Salary
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employee PF
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employer PF
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            EPS
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($contributions)): ?>
                        <?php 
                        $totalBasic = 0;
                        $totalEmployeePF = 0;
                        $totalEmployerPF = 0;
                        $totalEPS = 0;
                        $totalPF = 0;
                        ?>
                        <?php foreach ($contributions as $contrib): ?>
                            <?php
                            $totalBasic += $contrib['basic_salary'];
                            $totalEmployeePF += $contrib['employee_pf'];
                            $totalEmployerPF += $contrib['employer_pf'];
                            $totalEPS += $contrib['eps_amount'];
                            $totalPF += $contrib['total_pf'];
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($contrib['first_name'] . ' ' . $contrib['last_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($contrib['emp_code']); ?> • 
                                        <?php echo htmlspecialchars($contrib['department_name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-mono">
                                        <?php echo htmlspecialchars($contrib['uan_number'] ?: 'Not Available'); ?>
                                    </div>
                                    <div class="text-sm text-gray-500 font-mono">
                                        <?php echo htmlspecialchars($contrib['pf_number'] ?: 'Not Available'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₹<?php echo number_format($contrib['basic_salary'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₹<?php echo number_format($contrib['employee_pf'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₹<?php echo number_format($contrib['employer_pf'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₹<?php echo number_format($contrib['eps_amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ₹<?php echo number_format($contrib['total_pf'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- Totals Row -->
                        <tr class="bg-gray-50 font-semibold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" colspan="2">
                                Total (<?php echo count($contributions); ?> employees)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₹<?php echo number_format($totalBasic, 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₹<?php echo number_format($totalEmployeePF, 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₹<?php echo number_format($totalEmployerPF, 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₹<?php echo number_format($totalEPS, 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₹<?php echo number_format($totalPF, 2); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-hand-holding-usd text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No PF contributions found</p>
                                    <p class="text-sm">Select a period to view PF contributions</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PF Calculation Info -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-calculator text-blue-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">PF Calculation Details</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li><strong>Employee PF:</strong> 12% of Basic Salary (max ₹15,000)</li>
                        <li><strong>Employer PF:</strong> 3.67% of Basic Salary goes to PF Account</li>
                        <li><strong>EPS:</strong> 8.33% of Basic Salary goes to Pension Scheme</li>
                        <li><strong>EDLI:</strong> 0.50% of Basic Salary for insurance</li>
                        <li><strong>Admin Charges:</strong> 0.65% of Basic Salary</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportContributions() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'excel');
    
    window.location.href = '/pf/contributions?' + params.toString();
}

// Auto-submit form when filters change
document.getElementById('period').addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('department').addEventListener('change', function() {
    this.form.submit();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>