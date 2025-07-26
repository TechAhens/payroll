/**
 * Dashboard specific JavaScript functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard widgets
    initializeWidgets();
    
    // Setup real-time updates
    setupRealTimeUpdates();
    
    // Initialize charts if needed
    initializeCharts();
});

function initializeWidgets() {
    // Load attendance summary widget
    loadAttendanceSummary();
    
    // Load current period widget
    loadCurrentPeriod();
    
    // Setup widget refresh intervals
    setInterval(loadAttendanceSummary, 300000); // Refresh every 5 minutes
}

function loadAttendanceSummary() {
    const today = new Date().toISOString().split('T')[0];
    
    fetch(`/api/attendance-summary?date=${today}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateAttendanceWidget(data.summary);
            }
        })
        .catch(error => {
            console.error('Error loading attendance summary:', error);
        });
}

function updateAttendanceWidget(summary) {
    const presentElement = document.getElementById('present-count');
    const absentElement = document.getElementById('absent-count');
    const lateElement = document.getElementById('late-count');
    const halfDayElement = document.getElementById('half-day-count');
    
    if (presentElement) presentElement.textContent = summary.present || 0;
    if (absentElement) absentElement.textContent = summary.absent || 0;
    if (lateElement) lateElement.textContent = summary.late || 0;
    if (halfDayElement) halfDayElement.textContent = summary.half_day || 0;
    
    // Add animation to numbers
    [presentElement, absentElement, lateElement, halfDayElement].forEach(element => {
        if (element) {
            element.style.transform = 'scale(1.1)';
            setTimeout(() => {
                element.style.transform = 'scale(1)';
            }, 200);
        }
    });
}

function loadCurrentPeriod() {
    fetch('/api/current-period')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCurrentPeriodWidget(data);
            }
        })
        .catch(error => {
            console.error('Error loading current period:', error);
        });
}

function updateCurrentPeriodWidget(periodData) {
    const periodElement = document.getElementById('current-period-name');
    if (periodElement) {
        periodElement.textContent = periodData.period_name || 'No active period';
    }
}

function setupRealTimeUpdates() {
    // Check for new notifications every minute
    setInterval(checkNotifications, 60000);
}

function checkNotifications() {
    // Implementation for checking new notifications
    // This could be expanded to show real-time alerts
}

function initializeCharts() {
    // Initialize any charts on the dashboard
    // This could use Chart.js or similar library
    const chartElements = document.querySelectorAll('[data-chart]');
    
    chartElements.forEach(element => {
        const chartType = element.dataset.chart;
        switch (chartType) {
            case 'employee-distribution':
                initializeEmployeeDistributionChart(element);
                break;
            case 'salary-trends':
                initializeSalaryTrendsChart(element);
                break;
        }
    });
}

function initializeEmployeeDistributionChart(element) {
    // Simple bar chart implementation
    // In a real implementation, you'd use a charting library
    console.log('Initializing employee distribution chart');
}

function initializeSalaryTrendsChart(element) {
    // Line chart for salary trends
    console.log('Initializing salary trends chart');
}

// Quick action handlers
function quickAddEmployee() {
    window.location.href = '/employees/create';
}

function quickProcessPayroll() {
    window.location.href = '/payroll/process';
}

function quickMarkAttendance() {
    window.location.href = '/attendance/mark';
}

function quickGenerateReport() {
    window.location.href = '/reports/salary-register';
}

// Export functions for global access
window.DashboardUtils = {
    refreshWidgets: function() {
        loadAttendanceSummary();
        loadCurrentPeriod();
    },
    
    showQuickStats: function() {
        // Show modal with quick statistics
        showMessage('Quick stats feature coming soon', 'info');
    }
};