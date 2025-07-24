<?php
/**
 * Backup Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class BackupController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('settings');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleBackupAction();
        } else {
            $this->showBackups();
        }
    }
    
    private function handleBackupAction() {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        $csrfToken = $input['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createBackup($input);
                break;
            case 'download':
                $this->downloadBackup($input);
                break;
            case 'restore':
                $this->restoreBackup($input);
                break;
            case 'delete':
                $this->deleteBackup($input);
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
    }
    
    private function createBackup($data) {
        $backupType = $data['backup_type'] ?? 'full';
        $description = $data['description'] ?? null;
        
        $backupModel = $this->loadModel('Backup');
        $result = $backupModel->createBackup('manual', $description);
        
        if ($result['success']) {
            $this->logActivity('create_backup', 'backups', $result['backup_id']);
            $this->jsonResponse([
                'success' => true, 
                'message' => 'Backup created successfully',
                'backup_id' => $result['backup_id'],
                'size' => $result['size']
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => $result['message']], 500);
        }
    }
    
    private function downloadBackup($data) {
        $backupId = $data['backup_id'] ?? '';
        
        if (empty($backupId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Backup ID is required'], 400);
            return;
        }
        
        $backupModel = $this->loadModel('Backup');
        $backup = $backupModel->findBy('backup_id', $backupId);
        
        if (!$backup || $backup['status'] !== 'completed') {
            $this->jsonResponse(['success' => false, 'message' => 'Backup not found or incomplete'], 404);
            return;
        }
        
        // In a real implementation, serve the backup file
        $this->logActivity('download_backup', 'backups', $backupId);
        $this->jsonResponse(['success' => true, 'message' => 'Download initiated']);
    }
    
    private function restoreBackup($data) {
        $backupId = $data['backup_id'] ?? '';
        
        if (empty($backupId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Backup ID is required'], 400);
            return;
        }
        
        $backupModel = $this->loadModel('Backup');
        $result = $backupModel->restoreBackup($backupId);
        
        if ($result['success']) {
            $this->logActivity('restore_backup', 'backups', $backupId);
            $this->jsonResponse(['success' => true, 'message' => 'Backup restored successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => $result['message']], 500);
        }
    }
    
    private function deleteBackup($data) {
        $backupId = $data['backup_id'] ?? '';
        
        if (empty($backupId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Backup ID is required'], 400);
            return;
        }
        
        $backupModel = $this->loadModel('Backup');
        $result = $backupModel->deleteBackup($backupId);
        
        if ($result['success']) {
            $this->logActivity('delete_backup', 'backups', $backupId);
            $this->jsonResponse(['success' => true, 'message' => 'Backup deleted successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => $result['message']], 500);
        }
    }
    
    private function showBackups() {
        $backupModel = $this->loadModel('Backup');
        $backups = $backupModel->getBackups(50);
        $backupStats = $backupModel->getBackupStats();
        
        $this->loadView('settings/backups', [
            'backups' => $backups,
            'backup_stats' => $backupStats,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
}