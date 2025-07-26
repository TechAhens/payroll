<?php if (isset($_SESSION['user_id'])): ?>
<!-- Enhanced Navigation with better UX -->
<nav class="bg-white shadow-lg border-b sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <i class="fas fa-calculator text-primary-500 text-2xl mr-2"></i>
                    <span class="font-bold text-xl text-gray-900">PayrollPro</span>
                    <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">v<?php echo APP_VERSION; ?></span>
                </div>
                
                <!-- Main Navigation -->
                <div class="hidden sm:ml-8 sm:flex sm:space-x-8">
                    <a href="/dashboard" class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/dashboard') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Dashboard
                    </a>
                    
                    <?php if (isset($_SESSION['permissions']) && (str_contains($_SESSION['permissions'], 'employees') || $_SESSION['permissions'] === 'all')): ?>
                    <a href="/employees" class="nav-link <?php echo str_contains($_SERVER['REQUEST_URI'], '/employees') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <i class="fas fa-users mr-2"></i>
                        Employees
                        <span class="ml-1 bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full" id="employee-count">
                            <?php echo $stats['employees']['active'] ?? '0'; ?>
                        </span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['permissions']) && (str_contains($_SESSION['permissions'], 'payroll') || $_SESSION['permissions'] === 'all')): ?>
                    <div class="relative inline-block text-left">
                        <button type="button" class="nav-link border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" onclick="toggleDropdown('payroll-dropdown')">
                            <i class="fas fa-money-bill-wave mr-2"></i>
                            Payroll
                            <i class="fas fa-chevron-down ml-1"></i>
                        </button>
                        
                        <div id="payroll-dropdown" class="hidden absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a href="/payroll" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-chart-line mr-2"></i>Overview
                                </a>
                                <a href="/payroll/periods" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-calendar-alt mr-2"></i>Periods
                                </a>
                                <a href="/payroll/process" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-play-circle mr-2"></i>Process Payroll
                                </a>
                                <a href="/payroll/payslips" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-file-invoice mr-2"></i>Payslips
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <a href="/attendance" class="nav-link <?php echo str_contains($_SERVER['REQUEST_URI'], '/attendance') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <i class="fas fa-calendar-check mr-2"></i>
                        Attendance
                    </a>
                    
                    <a href="/loans" class="nav-link <?php echo str_contains($_SERVER['REQUEST_URI'], '/loans') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <i class="fas fa-hand-holding-usd mr-2"></i>
                        Loans
                    </a>
                    
                    <a href="/reports" class="nav-link <?php echo str_contains($_SERVER['REQUEST_URI'], '/reports') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Reports
                    </a>
                    <?php endif; ?>
                    
                    <!-- Masters Dropdown -->
                    <div class="relative inline-block text-left">
                        <button type="button" class="nav-link border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" onclick="toggleDropdown('masters-dropdown')">
                            <i class="fas fa-cogs mr-2"></i>
                            Masters
                            <i class="fas fa-chevron-down ml-1"></i>
                        </button>
                        
                        <div id="masters-dropdown" class="hidden absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Organization</div>
                                <a href="/departments" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-building mr-2"></i>Departments
                                </a>
                                <a href="/designations" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user-tie mr-2"></i>Designations
                                </a>
                                <a href="/cost-centers" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-chart-pie mr-2"></i>Cost Centers
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Payroll</div>
                                <a href="/salary-components" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-calculator mr-2"></i>Salary Components
                                </a>
                                <a href="/tax-slabs" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-percentage mr-2"></i>Tax Slabs
                                </a>
                                <a href="/loan-types" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-hand-holding-usd mr-2"></i>Loan Types
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Leave & Attendance</div>
                                <a href="/leave-types" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-calendar-minus mr-2"></i>Leave Types
                                </a>
                                <a href="/holidays" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-calendar-times mr-2"></i>Holidays
                                </a>
                                <?php if (isset($_SESSION['permissions']) && (str_contains($_SESSION['permissions'], 'users') || $_SESSION['permissions'] === 'all')): ?>
                                <div class="border-t border-gray-100 my-1"></div>
                                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">System</div>
                                <a href="/users" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user-cog mr-2"></i>User Management
                                </a>
                                <a href="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cogs mr-2"></i>System Settings
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right side navigation -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative">
                    <button type="button" class="p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 rounded-full" onclick="toggleDropdown('notifications-dropdown')">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400 ring-2 ring-white"></span>
                    </button>
                    
                    <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-1">
                            <div class="px-4 py-2 text-sm font-semibold text-gray-900 border-b">Notifications</div>
                            <div class="max-h-64 overflow-y-auto">
                                <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 border-b">
                                    <div class="flex items-start">
                                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                        <div>
                                            <p class="font-medium">Payroll processing completed</p>
                                            <p class="text-xs text-gray-500">2 minutes ago</p>
                                        </div>
                                    </div>
                                </a>
                                <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 border-b">
                                    <div class="flex items-start">
                                        <i class="fas fa-user-plus text-green-500 mt-1 mr-3"></i>
                                        <div>
                                            <p class="font-medium">New employee added</p>
                                            <p class="text-xs text-gray-500">1 hour ago</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="px-4 py-2 text-center border-t">
                                <a href="/notifications" class="text-sm text-primary-600 hover:text-primary-800">View all notifications</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="relative">
                    <button type="button" class="p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 rounded-full" onclick="toggleDropdown('quick-actions-dropdown')">
                        <i class="fas fa-plus text-lg"></i>
                    </button>
                    
                    <div id="quick-actions-dropdown" class="hidden absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-1">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Quick Actions</div>
                            <a href="/employees/create" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user-plus mr-2 text-blue-500"></i>Add Employee
                            </a>
                            <a href="/payroll/process" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-play-circle mr-2 text-green-500"></i>Process Payroll
                            </a>
                            <a href="/attendance/mark" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-calendar-check mr-2 text-yellow-500"></i>Mark Attendance
                            </a>
                            <a href="/reports/salary-register" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-file-alt mr-2 text-purple-500"></i>Generate Report
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- User Menu -->
                <div class="relative">
                    <button type="button" class="bg-white rounded-full flex text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" onclick="toggleDropdown('user-menu')">
                        <span class="sr-only">Open user menu</span>
                        <div class="h-8 w-8 rounded-full bg-primary-500 flex items-center justify-center">
                            <span class="text-white text-sm font-medium">
                                <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)); ?>
                            </span>
                        </div>
                        <div class="ml-3 hidden sm:block">
                            <div class="text-sm font-medium text-gray-700"><?php echo $_SESSION['full_name'] ?? 'User'; ?></div>
                            <div class="text-xs text-gray-500"><?php echo $_SESSION['role'] ?? 'User'; ?></div>
                        </div>
                    </button>
                    
                    <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-1">
                            <div class="px-4 py-2 text-sm border-b">
                                <div class="font-medium text-gray-900"><?php echo $_SESSION['full_name'] ?? 'User'; ?></div>
                                <div class="text-xs text-gray-500"><?php echo $_SESSION['role'] ?? 'User'; ?></div>
                            </div>
                            <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Profile
                            </a>
                            <a href="/change-password" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-key mr-2"></i>Change Password
                            </a>
                            <?php if (isset($_SESSION['permissions']) && (str_contains($_SESSION['permissions'], 'settings') || $_SESSION['permissions'] === 'all')): ?>
                            <a href="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cogs mr-2"></i>Settings
                            </a>
                            <?php endif; ?>
                            <div class="border-t border-gray-100"></div>
                            <a href="/logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile menu button -->
                <div class="sm:hidden">
                    <button type="button" class="p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobile-menu" class="sm:hidden border-t border-gray-200 hidden">
            <div class="px-4 pt-2 pb-3 space-y-1">
                <a href="/dashboard" class="block text-gray-600 hover:text-gray-900 px-2 py-1 text-base font-medium">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="/employees" class="block text-gray-600 hover:text-gray-900 px-2 py-1 text-base font-medium">
                    <i class="fas fa-users mr-2"></i>Employees
                </a>
                <a href="/payroll" class="block text-gray-600 hover:text-gray-900 px-2 py-1 text-base font-medium">
                    <i class="fas fa-money-bill-wave mr-2"></i>Payroll
                </a>
                <a href="/attendance" class="block text-gray-600 hover:text-gray-900 px-2 py-1 text-base font-medium">
                    <i class="fas fa-calendar-check mr-2"></i>Attendance
                </a>
                <a href="/loans" class="block text-gray-600 hover:text-gray-900 px-2 py-1 text-base font-medium">
                    <i class="fas fa-hand-holding-usd mr-2"></i>Loans
                </a>
                <a href="/reports" class="block text-gray-600 hover:text-gray-900 px-2 py-1 text-base font-medium">
                    <i class="fas fa-chart-bar mr-2"></i>Reports
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
function toggleDropdown(id) {
    // Close all other dropdowns first
    document.querySelectorAll('[id$="-dropdown"]').forEach(dropdown => {
        if (dropdown.id !== id) {
            dropdown.classList.add('hidden');
        }
    });
    
    // Toggle the requested dropdown
    const dropdown = document.getElementById(id);
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenu) {
        mobileMenu.classList.toggle('hidden');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('[id$="-dropdown"]');
    dropdowns.forEach(dropdown => {
        if (!dropdown.closest('.relative').contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });
});

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuButton = event.target.closest('[onclick="toggleMobileMenu()"]');
    
    if (mobileMenu && !mobileMenu.contains(event.target) && !mobileMenuButton) {
        mobileMenu.classList.add('hidden');
    }
});

// Keyboard navigation for dropdowns
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('[id$="-dropdown"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
        document.getElementById('mobile-menu')?.classList.add('hidden');
    }
});
</script>
<?php endif; ?>