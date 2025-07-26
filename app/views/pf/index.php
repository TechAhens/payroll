<?php 
$title = 'PF Management - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">Provident Fund Management</h1>
            <p class="mt-1 text-sm text-gray-500">
                Manage PF contributions, generate ECR files, and ensure compliance
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
            <a href="/pf/ecr-generation" class="btn btn-outline">
                <i class="fas fa-file-export mr-2"></i>
                Generate ECR
            </a>
            <a href="/pf/reports" class="btn btn-primary">
                <i class="fas fa-chart-bar mr-2"></i>
                PF Reports
            </a>
        </div>
    </div>

    <!-- PF Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">PF Members</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($pf_summary['total_employees'] ?? 0); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-hand-holding-usd text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Employee Contribution</p>
                        <p class="text-2xl font-bold text-gray-900">₹<?php echo number_format($pf_summary['total_employee_contribution'] ?? 0, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-building text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Employer Contribution</p>
                        <p class="text-2xl font-bold text-gray-900">₹<?php echo number_format($pf_summary['total_employer_contribution'] ?? 0, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calculator text-orange-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total PF</p>
                        <p class="text-2xl font-bold text-gray-900">₹<?php echo number_format($pf_summary['total_pf_contribution'] ?? 0, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-export text-blue-600"></i>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">ECR Generation</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">Generate Electronic Challan-cum-Return files for EPFO submission</p>
            <a href="/pf/ecr-generation" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Generate ECR →
            </a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-green-600"></i>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">PF Contributions</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">View and manage monthly PF contributions by employees</p>
            <a href="/pf/contributions" class="text-green-600 hover:text-green-800 text-sm font-medium">
                View Contributions →
            </a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-balance-scale text-purple-600"></i>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">Reconciliation</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">Reconcile PF data with EPFO records and identify discrepancies</p>
            <a href="/pf/reconciliation" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                Start Reconciliation →
            </a>
        </div>
    </div>

    <!-- Recent PF Transactions -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent PF Transactions</h3>
                <a href="/pf/contributions" class="text-sm text-blue-600 hover:text-blue-800">View All →</a>
            </div>
        </div>
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employee
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Period
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            PF Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($recent_transactions)): ?>
                        <?php foreach ($recent_transactions as $transaction): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($transaction['emp_code']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($transaction['period_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₹<?php echo number_format($transaction['pf_amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M j, Y', strtotime($transaction['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No PF transactions found</p>
                                    <p class="text-sm">Process payroll to see PF contributions</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PF Compliance Status -->
    <div class="mt-8 bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">PF Compliance Status</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Current Month ECR</p>
                        <p class="text-xs text-gray-500">Generated and submitted</p>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Challan Payment</p>
                        <p class="text-xs text-gray-500">Due on 15th of next month</p>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Annual Return</p>
                        <p class="text-xs text-gray-500">Up to date for FY <?php echo $current_fy; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>