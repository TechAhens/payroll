<?php
/**
 * Quick Stats Component
 * Usage: include with $stats array containing various statistics
 */

$stats = $stats ?? [];
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Total Employees -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Employees</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['employees']['total'] ?? 0); ?></p>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-gray-500">
                    <i class="fas fa-check-circle text-green-500 mr-1"></i>
                    <?php echo number_format($stats['employees']['active'] ?? 0); ?> Active
                </div>
            </div>
        </div>
    </div>

    <!-- Current Period -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Current Period</p>
                    <p class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($stats['payroll']['current_period'] ?? 'No active period'); ?></p>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-gray-500">
                    <i class="fas fa-user-check text-blue-500 mr-1"></i>
                    <?php echo number_format($stats['payroll']['processed_employees'] ?? 0); ?> Processed
                </div>
            </div>
        </div>
    </div>

    <!-- Total Earnings -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-emerald-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Earnings</p>
                    <p class="text-2xl font-bold text-gray-900">₹<?php echo number_format($stats['payroll']['total_earnings'] ?? 0, 2); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Net Payable -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hand-holding-usd text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Net Payable</p>
                    <p class="text-2xl font-bold text-gray-900">₹<?php echo number_format($stats['payroll']['net_payable'] ?? 0, 2); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>