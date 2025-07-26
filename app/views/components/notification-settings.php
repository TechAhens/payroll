<?php
/**
 * Notification Settings Component
 * Usage: include in settings page
 */

$notificationSettings = $notificationSettings ?? [];
?>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Notification Settings</h3>
    </div>
    <div class="p-6">
        <form id="notification-settings-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
            
            <div class="space-y-6">
                <!-- General Notification Settings -->
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-4">General Settings</h4>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Email Notifications</label>
                                <p class="text-xs text-gray-500">Receive notifications via email</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="enable_email_notifications" 
                                       class="sr-only peer"
                                       <?php echo ($notificationSettings['enable_email_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Browser Notifications</label>
                                <p class="text-xs text-gray-500">Show desktop notifications</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="enable_browser_notifications" 
                                       class="sr-only peer"
                                       <?php echo ($notificationSettings['enable_browser_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div>
                            <label for="notification_frequency" class="block text-sm font-medium text-gray-700 mb-2">Notification Frequency</label>
                            <select name="notification_frequency" id="notification_frequency" class="form-select">
                                <option value="immediate" <?php echo ($notificationSettings['notification_frequency'] ?? 'immediate') === 'immediate' ? 'selected' : ''; ?>>Immediate</option>
                                <option value="hourly" <?php echo ($notificationSettings['notification_frequency'] ?? '') === 'hourly' ? 'selected' : ''; ?>>Hourly</option>
                                <option value="daily" <?php echo ($notificationSettings['notification_frequency'] ?? '') === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                <option value="weekly" <?php echo ($notificationSettings['notification_frequency'] ?? '') === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Module-specific Notifications -->
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-4">Module Notifications</h4>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Payroll Notifications</label>
                                <p class="text-xs text-gray-500">Payroll processing updates and alerts</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="payroll_notifications" 
                                       class="sr-only peer"
                                       <?php echo ($notificationSettings['payroll_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Attendance Notifications</label>
                                <p class="text-xs text-gray-500">Attendance marking and reports</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="attendance_notifications" 
                                       class="sr-only peer"
                                       <?php echo ($notificationSettings['attendance_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Loan Notifications</label>
                                <p class="text-xs text-gray-500">Loan applications and EMI updates</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="loan_notifications" 
                                       class="sr-only peer"
                                       <?php echo ($notificationSettings['loan_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">System Notifications</label>
                                <p class="text-xs text-gray-500">System updates and maintenance alerts</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="system_notifications" 
                                       class="sr-only peer"
                                       <?php echo ($notificationSettings['system_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>
                    Save Notification Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('notification-settings-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    
    // Convert form data to object, handling checkboxes
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    // Handle unchecked checkboxes
    const checkboxes = this.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        if (!checkbox.checked) {
            data[checkbox.name] = '0';
        } else {
            data[checkbox.name] = '1';
        }
    });
    
    showLoading();
    
    fetch('/settings/notifications', {
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
            showMessage('Notification settings saved successfully', 'success');
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
</script>