<?php
/**
 * Notification Model
 */

require_once __DIR__ . '/../core/Model.php';

class Notification extends Model {
    protected $table = 'notifications';
    
    public function createNotification($data) {
        $rules = [
            'user_id' => ['required' => true, 'type' => 'numeric'],
            'title' => ['required' => true, 'max_length' => 255],
            'message' => ['required' => true],
            'type' => ['required' => true]
        ];
        
        $errors = $this->validateData($data, $rules);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $this->create($data);
            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create notification'];
        }
    }
    
    public function getUserNotifications($userId, $limit = 50, $unreadOnly = false) {
        $conditions = 'user_id = :user_id';
        $params = ['user_id' => $userId];
        
        if ($unreadOnly) {
            $conditions .= ' AND is_read = 0';
        }
        
        return $this->findAll($conditions, $params, 'created_at DESC', $limit);
    }
    
    public function markAsRead($notificationId, $userId = null) {
        $conditions = 'id = :id';
        $params = ['id' => $notificationId];
        
        if ($userId) {
            $conditions .= ' AND user_id = :user_id';
            $params['user_id'] = $userId;
        }
        
        return $this->db->update($this->table, 
            ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')], 
            $conditions, 
            $params
        );
    }
    
    public function markAllAsRead($userId) {
        return $this->db->update($this->table,
            ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
            'user_id = :user_id AND is_read = 0',
            ['user_id' => $userId]
        );
    }
    
    public function getUnreadCount($userId) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id AND is_read = 0",
            ['user_id' => $userId]
        );
        
        return $result['count'] ?? 0;
    }
    
    public function deleteOldNotifications($days = 30) {
        return $this->db->delete($this->table,
            'created_at < DATE_SUB(CURDATE(), INTERVAL :days DAY)',
            ['days' => $days]
        );
    }
    
    public function sendSystemNotification($title, $message, $type = 'info', $userIds = []) {
        try {
            $this->beginTransaction();
            
            $sent = 0;
            
            if (empty($userIds)) {
                // Send to all active users
                $users = $this->db->fetchAll("SELECT id FROM users WHERE status = 'active'");
                $userIds = array_column($users, 'id');
            }
            
            foreach ($userIds as $userId) {
                $result = $this->createNotification([
                    'user_id' => $userId,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'is_system' => 1
                ]);
                
                if ($result['success']) {
                    $sent++;
                }
            }
            
            $this->commit();
            
            return [
                'success' => true,
                'sent' => $sent,
                'total' => count($userIds)
            ];
        } catch (Exception $e) {
            $this->rollback();
            return [
                'success' => false,
                'message' => 'Failed to send notifications'
            ];
        }
    }
    
    public function getNotificationTypes() {
        return [
            'info' => 'Information',
            'success' => 'Success',
            'warning' => 'Warning',
            'error' => 'Error',
            'payroll' => 'Payroll',
            'attendance' => 'Attendance',
            'loan' => 'Loan',
            'system' => 'System'
        ];
    }
    
    public function createPayrollNotification($periodId, $type, $details = []) {
        $period = $this->db->fetch("SELECT * FROM payroll_periods WHERE id = :id", ['id' => $periodId]);
        
        if (!$period) {
            return ['success' => false, 'message' => 'Period not found'];
        }
        
        $notifications = [
            'processing_started' => [
                'title' => 'Payroll Processing Started',
                'message' => "Payroll processing has started for {$period['period_name']}",
                'type' => 'info'
            ],
            'processing_completed' => [
                'title' => 'Payroll Processing Completed',
                'message' => "Payroll processing completed for {$period['period_name']}. {$details['processed']} employees processed.",
                'type' => 'success'
            ],
            'period_locked' => [
                'title' => 'Payroll Period Locked',
                'message' => "Payroll period {$period['period_name']} has been locked",
                'type' => 'warning'
            ]
        ];
        
        if (!isset($notifications[$type])) {
            return ['success' => false, 'message' => 'Invalid notification type'];
        }
        
        $notification = $notifications[$type];
        
        // Send to payroll managers and admins
        $users = $this->db->fetchAll(
            "SELECT u.id FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE r.name IN ('Super Admin', 'HR Admin', 'Payroll Manager') 
             AND u.status = 'active'"
        );
        
        $userIds = array_column($users, 'id');
        
        return $this->sendSystemNotification(
            $notification['title'],
            $notification['message'],
            $notification['type'],
            $userIds
        );
    }
}