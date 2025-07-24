<?php
/**
 * Notification Dropdown Component
 * Usage: include in header navigation
 */

$notifications = $notifications ?? [];
$unreadCount = $unreadCount ?? 0;
?>

<div class="relative">
    <button type="button" 
            class="p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 rounded-full" 
            onclick="toggleDropdown('notifications-dropdown')">
        <i class="fas fa-bell text-lg"></i>
        <?php if ($unreadCount > 0): ?>
            <span class="absolute -top-1 -right-1 block h-4 w-4 rounded-full bg-red-400 text-xs text-white text-center leading-4">
                <?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?>
            </span>
        <?php endif; ?>
    </button>
    
    <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
        <div class="py-1">
            <div class="px-4 py-2 text-sm font-semibold text-gray-900 border-b flex items-center justify-between">
                <span>Notifications</span>
                <div class="flex items-center space-x-2">
                    <?php if ($unreadCount > 0): ?>
                        <button onclick="markAllNotificationsRead()" class="text-xs text-blue-600 hover:text-blue-800">
                            Mark all read
                        </button>
                    <?php endif; ?>
                    <a href="/notifications" class="text-xs text-blue-600 hover:text-blue-800">View All</a>
                </div>
            </div>
            
            <div class="max-h-64 overflow-y-auto" id="notifications-list">
                <?php if (!empty($notifications)): ?>
                    <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                        <div class="px-4 py-3 hover:bg-gray-50 border-b <?php echo !$notification['is_read'] ? 'bg-blue-50' : ''; ?>">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center 
                                        <?php 
                                        switch($notification['type']) {
                                            case 'success': echo 'bg-green-100'; break;
                                            case 'error': echo 'bg-red-100'; break;
                                            case 'warning': echo 'bg-yellow-100'; break;
                                            case 'payroll': echo 'bg-purple-100'; break;
                                            default: echo 'bg-blue-100';
                                        }
                                        ?>">
                                        <i class="fas 
                                            <?php 
                                            switch($notification['type']) {
                                                case 'success': echo 'fa-check-circle text-green-600'; break;
                                                case 'error': echo 'fa-exclamation-circle text-red-600'; break;
                                                case 'warning': echo 'fa-exclamation-triangle text-yellow-600'; break;
                                                case 'payroll': echo 'fa-money-bill-wave text-purple-600'; break;
                                                default: echo 'fa-info-circle text-blue-600';
                                            }
                                            ?> text-xs"></i>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900 <?php echo !$notification['is_read'] ? 'font-semibold' : ''; ?>">
                                        <?php echo htmlspecialchars($notification['title']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <?php echo $this->timeAgo($notification['created_at']); ?>
                                    </p>
                                </div>
                                <?php if (!$notification['is_read']): ?>
                                    <div class="flex-shrink-0">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-bell-slash text-2xl mb-2"></i>
                        <p class="text-sm">No notifications</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function markAllNotificationsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            csrf_token: '<?php echo $this->generateCSRFToken(); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>