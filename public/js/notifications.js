/**
 * Notifications JavaScript functionality
 */

// Notification utilities
window.NotificationUtils = {
    // Check for new notifications
    checkForUpdates: function() {
        fetch('/api/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateNotificationBadge(data.count);
                }
            })
            .catch(error => {
                console.error('Error checking notifications:', error);
            });
    },
    
    // Update notification badge
    updateNotificationBadge: function(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    },
    
    // Show notification popup
    showNotification: function(title, message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg border border-gray-200 transform transition-all duration-300 translate-x-full`;
        
        const typeColors = {
            success: 'border-l-4 border-l-green-500',
            error: 'border-l-4 border-l-red-500',
            warning: 'border-l-4 border-l-yellow-500',
            info: 'border-l-4 border-l-blue-500'
        };
        
        const typeIcons = {
            success: 'fas fa-check-circle text-green-500',
            error: 'fas fa-exclamation-circle text-red-500',
            warning: 'fas fa-exclamation-triangle text-yellow-500',
            info: 'fas fa-info-circle text-blue-500'
        };
        
        notification.className += ' ' + (typeColors[type] || typeColors.info);
        
        notification.innerHTML = `
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="${typeIcons[type] || typeIcons.info}"></i>
                    </div>
                    <div class="ml-3 w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900">${title}</p>
                        <p class="mt-1 text-sm text-gray-500">${message}</p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button onclick="this.closest('.fixed').remove()" 
                                class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, duration);
        }
    },
    
    // Request notification permission
    requestPermission: function() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.showNotification('Notifications Enabled', 'You will now receive browser notifications', 'success');
                }
            });
        }
    },
    
    // Show browser notification
    showBrowserNotification: function(title, message, icon = '/favicon.ico') {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: icon,
                badge: icon
            });
        }
    }
};

// Real-time notification checking
document.addEventListener('DOMContentLoaded', function() {
    // Check for notifications every 30 seconds
    setInterval(() => {
        NotificationUtils.checkForUpdates();
    }, 30000);
    
    // Initial check
    NotificationUtils.checkForUpdates();
    
    // Request notification permission on first visit
    if ('Notification' in window && Notification.permission === 'default') {
        setTimeout(() => {
            NotificationUtils.requestPermission();
        }, 5000);
    }
});

// WebSocket connection for real-time notifications (if available)
if (typeof WebSocket !== 'undefined') {
    let ws = null;
    
    function connectWebSocket() {
        try {
            ws = new WebSocket('ws://localhost:8080/notifications');
            
            ws.onopen = function() {
                console.log('Notification WebSocket connected');
                // Send user authentication
                ws.send(JSON.stringify({
                    type: 'auth',
                    user_id: '<?php echo $_SESSION[\'user_id'] ?? ''; ?>',
                    token: '<?php echo $_SESSION[\'csrf_token'] ?? ''; ?>'
                }));
            };
            
            ws.onmessage = function(event) {
                const data = JSON.parse(event.data);
                
                if (data.type === 'notification') {
                    NotificationUtils.showNotification(
                        data.title,
                        data.message,
                        data.notification_type || 'info'
                    );
                    
                    NotificationUtils.showBrowserNotification(
                        data.title,
                        data.message
                    );
                    
                    // Update badge count
                    NotificationUtils.checkForUpdates();
                }
            };
            
            ws.onclose = function() {
                console.log('Notification WebSocket disconnected');
                // Reconnect after 5 seconds
                setTimeout(connectWebSocket, 5000);
            };
            
            ws.onerror = function(error) {
                console.error('WebSocket error:', error);
            };
        } catch (error) {
            console.error('Failed to connect WebSocket:', error);
        }
    }
    
    // Connect WebSocket if user is logged in
    if (document.querySelector('meta[name="user-id"]')) {
        connectWebSocket();
    }
}

// Export for global access
window.Notifications = NotificationUtils;