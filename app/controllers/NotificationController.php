<?php
/**
 * Notification Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class NotificationController extends Controller {
    
    public function index() {
        $this->checkAuth();
        
        $filter = $_GET['filter'] ?? 'all';
        $notificationModel = $this->loadModel('Notification');
        
        $notifications = [];
        $unreadCount = 0;
        
        switch ($filter) {
            case 'unread':
                $notifications = $notificationModel->getUserNotifications($_SESSION['user_id'], 50, true);
                break;
            case 'system':
                $notifications = $notificationModel->findAll(
                    'user_id = :user_id AND is_system = 1',
                    ['user_id' => $_SESSION['user_id']],
                    'created_at DESC',
                    50
                );
                break;
            default:
                $notifications = $notificationModel->getUserNotifications($_SESSION['user_id'], 50);
        }
        
        $unreadCount = $notificationModel->getUnreadCount($_SESSION['user_id']);
        
        $this->loadView('notifications/index', [
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'filter' => $filter
        ]);
    }
    
    public function markRead() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $notificationId = $input['notification_id'] ?? '';
            $csrfToken = $input['csrf_token'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
                return;
            }
            
            $notificationModel = $this->loadModel('Notification');
            $result = $notificationModel->markAsRead($notificationId, $_SESSION['user_id']);
            
            if ($result) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to mark as read'], 400);
            }
        }
    }
    
    public function markAllRead() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $csrfToken = $input['csrf_token'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
                return;
            }
            
            $notificationModel = $this->loadModel('Notification');
            $result = $notificationModel->markAllAsRead($_SESSION['user_id']);
            
            if ($result) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to mark all as read'], 400);
            }
        }
    }
    
    public function delete() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $notificationId = $input['notification_id'] ?? '';
            $csrfToken = $input['csrf_token'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
                return;
            }
            
            $notificationModel = $this->loadModel('Notification');
            $result = $notificationModel->delete($notificationId);
            
            if ($result) {
                $this->logActivity('delete_notification', 'notifications', $notificationId);
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to delete notification'], 400);
            }
        }
    }
    
    public function clearAll() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $csrfToken = $input['csrf_token'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
                return;
            }
            
            $notificationModel = $this->loadModel('Notification');
            $result = $notificationModel->db->delete(
                'notifications',
                'user_id = :user_id',
                ['user_id' => $_SESSION['user_id']]
            );
            
            if ($result) {
                $this->logActivity('clear_all_notifications', 'notifications', null);
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to clear notifications'], 400);
            }
        }
    }
    
    public function getUnreadCount() {
        $this->checkAuth();
        
        $notificationModel = $this->loadModel('Notification');
        $count = $notificationModel->getUnreadCount($_SESSION['user_id']);
        
        $this->jsonResponse(['success' => true, 'count' => $count]);
    }
}