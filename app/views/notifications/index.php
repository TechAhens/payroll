<?php 
$title = 'Notifications - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
            <p class="mt-1 text-sm text-gray-500">
                View and manage your system notifications
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
            <button onclick="markAllAsRead()" class="btn btn-outline">
                <i class="fas fa-check-double mr-2"></i>
                Mark All Read
            </button>
            <button onclick="clearNotifications()" class="btn btn-outline">
                <i class="fas fa-trash mr-2"></i>
                Clear All
            </button>
        </div>
    </div>

    <!-- Notification Filters -->
    <div class="mb-6">
        <nav class="flex space-x-8">
            <a href="/notifications?filter=all" 
               class="<?php echo ($filter ?? 'all') === 'all' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                All Notifications
            </a>
            <a href="/notifications?filter=unread" 
               class="<?php echo ($filter ?? '') === 'unread' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Unread
                <?php if (isset($unread_count) && $unread_count > 0): ?>
                    <span class="ml-1 bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="/notifications?filter=system" 
               class="<?php echo ($filter ?? '') === 'system' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                System
            </a>
        </nav>
    </div>

    <!-- Notifications List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <?php if (!empty($notifications)): ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($notifications as $notification): ?>
                    <div class="p-6 hover:bg-gray-50 <?php echo !$notification['is_read'] ? 'bg-blue-50' : ''; ?>">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center 
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
                                        ?>"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-gray-900 <?php echo !$notification['is_read'] ? 'font-semibold' : ''; ?>">
                                        <?php echo htmlspecialchars($notification['title']); ?>
                                    </h3>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs text-gray-500">
                                            <?php echo $this->timeAgo($notification['created_at']); ?>
                                        </span>
                                        <?php if (!$notification['is_read']): ?>
                                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($notification['message']); ?>
                                </p>
                                <div class="mt-2 flex items-center space-x-4">
                                    <?php if (!$notification['is_read']): ?>
                                        <button onclick="markAsRead(<?php echo $notification['id']; ?>)" 
                                                class="text-xs text-blue-600 hover:text-blue-800">
                                            Mark as read
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="deleteNotification(<?php echo $notification['id']; ?>)" 
                                            class="text-xs text-red-600 hover:text-red-800">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-12 text-center">
                <div class="text-gray-500">
                    <i class="fas fa-bell-slash text-4xl mb-4"></i>
                    <p class="text-lg font-medium">No notifications</p>
                    <p class="text-sm">You're all caught up!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch('/notifications/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            notification_id: notificationId,
            csrf_token: '<?php echo $this->generateCSRFToken(); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showMessage('Failed to mark notification as read', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred', 'error');
    });
}

function markAllAsRead() {
    if (confirm('Mark all notifications as read?')) {
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
                showMessage('All notifications marked as read', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage('Failed to mark notifications as read', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred', 'error');
        });
    }
}

function deleteNotification(notificationId) {
    if (confirm('Delete this notification?')) {
        fetch('/notifications/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                notification_id: notificationId,
                csrf_token: '<?php echo $this->generateCSRFToken(); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showMessage('Failed to delete notification', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred', 'error');
        });
    }
}

function clearNotifications() {
    if (confirm('Delete all notifications? This action cannot be undone.')) {
        fetch('/notifications/clear-all', {
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
                showMessage('All notifications cleared', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage('Failed to clear notifications', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred', 'error');
        });
    }
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>