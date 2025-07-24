<?php
/**
 * Backup Model
 */

require_once __DIR__ . '/../core/Model.php';

class Backup extends Model {
    protected $table = 'backups';
    
    public function createBackup($type = 'manual', $description = null) {
        try {
            $backupId = uniqid('backup_');
            $timestamp = date('Y-m-d_H-i-s');
            
            $backupData = [
                'backup_id' => $backupId,
                'type' => $type,
                'description' => $description,
                'status' => 'in_progress',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $this->create($backupData);
            
            // Create database backup
            $dbBackupFile = $this->createDatabaseBackup($backupId, $timestamp);
            
            // Create files backup
            $filesBackupFile = $this->createFilesBackup($backupId, $timestamp);
            
            // Calculate total size
            $totalSize = 0;
            if (file_exists($dbBackupFile)) {
                $totalSize += filesize($dbBackupFile);
            }
            if (file_exists($filesBackupFile)) {
                $totalSize += filesize($filesBackupFile);
            }
            
            // Update backup record
            $this->update($id, [
                'database_file' => basename($dbBackupFile),
                'files_backup' => basename($filesBackupFile),
                'size_bytes' => $totalSize,
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'success' => true,
                'backup_id' => $backupId,
                'size' => $this->formatBytes($totalSize)
            ];
        } catch (Exception $e) {
            // Update status to failed
            if (isset($id)) {
                $this->update($id, [
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
            
            return [
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ];
        }
    }
    
    public function getBackups($limit = 50) {
        return $this->findAll('', [], 'created_at DESC', $limit);
    }
    
    public function deleteBackup($backupId) {
        $backup = $this->findBy('backup_id', $backupId);
        
        if (!$backup) {
            return ['success' => false, 'message' => 'Backup not found'];
        }
        
        try {
            // Delete backup files
            $backupDir = $this->getBackupDirectory();
            
            if ($backup['database_file']) {
                $dbFile = $backupDir . '/' . $backup['database_file'];
                if (file_exists($dbFile)) {
                    unlink($dbFile);
                }
            }
            
            if ($backup['files_backup']) {
                $filesFile = $backupDir . '/' . $backup['files_backup'];
                if (file_exists($filesFile)) {
                    unlink($filesFile);
                }
            }
            
            // Delete backup record
            $this->delete($backup['id']);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to delete backup'];
        }
    }
    
    public function restoreBackup($backupId) {
        $backup = $this->findBy('backup_id', $backupId);
        
        if (!$backup || $backup['status'] !== 'completed') {
            return ['success' => false, 'message' => 'Invalid backup'];
        }
        
        try {
            $backupDir = $this->getBackupDirectory();
            
            // Restore database
            if ($backup['database_file']) {
                $dbFile = $backupDir . '/' . $backup['database_file'];
                if (file_exists($dbFile)) {
                    $this->restoreDatabase($dbFile);
                }
            }
            
            // Restore files
            if ($backup['files_backup']) {
                $filesFile = $backupDir . '/' . $backup['files_backup'];
                if (file_exists($filesFile)) {
                    $this->restoreFiles($filesFile);
                }
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Restore failed: ' . $e->getMessage()];
        }
    }
    
    public function cleanOldBackups($retentionDays = 30) {
        $cutoffDate = date('Y-m-d', strtotime("-{$retentionDays} days"));
        
        $oldBackups = $this->findAll(
            'DATE(created_at) < :cutoff_date',
            ['cutoff_date' => $cutoffDate]
        );
        
        $deleted = 0;
        foreach ($oldBackups as $backup) {
            $result = $this->deleteBackup($backup['backup_id']);
            if ($result['success']) {
                $deleted++;
            }
        }
        
        return [
            'success' => true,
            'deleted' => $deleted,
            'total' => count($oldBackups)
        ];
    }
    
    private function createDatabaseBackup($backupId, $timestamp) {
        $backupDir = $this->getBackupDirectory();
        $filename = "database_backup_{$backupId}_{$timestamp}.sql";
        $filepath = $backupDir . '/' . $filename;
        
        // In a real implementation, use mysqldump
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            'localhost', // DB host
            'root',      // DB user
            '',          // DB password
            'payroll_system', // DB name
            $filepath
        );
        
        // For demo purposes, create a dummy backup file
        file_put_contents($filepath, "-- Database backup created on " . date('Y-m-d H:i:s') . "\n");
        
        return $filepath;
    }
    
    private function createFilesBackup($backupId, $timestamp) {
        $backupDir = $this->getBackupDirectory();
        $filename = "files_backup_{$backupId}_{$timestamp}.tar.gz";
        $filepath = $backupDir . '/' . $filename;
        
        // In a real implementation, use tar to create archive
        $uploadsDir = __DIR__ . '/../../uploads';
        
        // For demo purposes, create a dummy backup file
        file_put_contents($filepath, "Files backup created on " . date('Y-m-d H:i:s') . "\n");
        
        return $filepath;
    }
    
    private function restoreDatabase($backupFile) {
        // In a real implementation, restore from SQL dump
        // mysql -h host -u user -p database < backup.sql
        return true;
    }
    
    private function restoreFiles($backupFile) {
        // In a real implementation, extract tar archive
        // tar -xzf backup.tar.gz -C /path/to/restore
        return true;
    }
    
    private function getBackupDirectory() {
        $backupDir = __DIR__ . '/../../backups';
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        return $backupDir;
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    public function getBackupStats() {
        $stats = [];
        
        // Total backups
        $stats['total'] = $this->count();
        
        // Successful backups
        $stats['successful'] = $this->count('status = :status', ['status' => 'completed']);
        
        // Failed backups
        $stats['failed'] = $this->count('status = :status', ['status' => 'failed']);
        
        // Total backup size
        $result = $this->db->fetch("SELECT SUM(size_bytes) as total_size FROM {$this->table} WHERE status = 'completed'");
        $stats['total_size'] = $this->formatBytes($result['total_size'] ?? 0);
        
        // Last backup
        $lastBackup = $this->findAll('status = :status', ['status' => 'completed'], 'created_at DESC', 1);
        $stats['last_backup'] = $lastBackup ? $lastBackup[0]['created_at'] : null;
        
        return $stats;
    }
}