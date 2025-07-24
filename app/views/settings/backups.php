<?php 
$title = 'Backup Management - System Settings';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <div class="flex items-center">
                <a href="/settings" class="text-gray-500 hover:text-gray-700 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Backup Management</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Create, manage, and restore system backups
                    </p>
                </div>
            </div>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button onclick="createBackup()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <i class="fas fa-plus mr-2"></i>
                Create Backup
            </button>
        </div>
    </div>

    <!-- Backup Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-database text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Backups</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $backup_stats['total'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Successful</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $backup_stats['successful'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-circle text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Failed</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $backup_stats['failed'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-hdd text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Size</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $backup_stats['total_size'] ?? '0 B'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Backup History</h3>
        </div>
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Backup ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Size
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Created
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($backups)): ?>
                        <?php foreach ($backups as $backup): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($backup['backup_id']); ?>
                                    </div>
                                    <?php if ($backup['description']): ?>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($backup['description']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php echo $backup['type'] === 'automatic' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo ucfirst($backup['type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $backup['size'] ?? 'Unknown'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php 
                                        switch($backup['status']) {
                                            case 'completed': echo 'bg-green-100 text-green-800'; break;
                                            case 'failed': echo 'bg-red-100 text-red-800'; break;
                                            case 'in_progress': echo 'bg-yellow-100 text-yellow-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($backup['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M j, Y H:i', strtotime($backup['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <?php if ($backup['status'] === 'completed'): ?>
                                            <button onclick="downloadBackup('<?php echo $backup['backup_id']; ?>')" 
                                                    class="text-blue-600 hover:text-blue-900" title="Download">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button onclick="restoreBackup('<?php echo $backup['backup_id']; ?>')" 
                                                    class="text-green-600 hover:text-green-900" title="Restore">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="deleteBackup('<?php echo $backup['backup_id']; ?>')" 
                                                class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-database text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No backups found</p>
                                    <p class="text-sm">Create your first backup to get started</p>
                                    <button onclick="createBackup()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Create Backup
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Backup Modal -->
<div id="backup-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Create Backup</h3>
            <form id="backup-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-4">
                    <label for="backup_type" class="block text-sm font-medium text-gray-700 mb-2">Backup Type</label>
                    <select name="backup_type" id="backup_type" class="form-select">
                        <option value="full">Full Backup (Database + Files)</option>
                        <option value="database">Database Only</option>
                        <option value="files">Files Only</option>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3" class="form-textarea" 
                              placeholder="Optional backup description"></textarea>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeBackupModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Create Backup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function createBackup() {
    document.getElementById('backup-modal').classList.remove('hidden');
}

function closeBackupModal() {
    document.getElementById('backup-modal').classList.add('hidden');
}

function downloadBackup(backupId) {
    window.location.href = `/settings/backup?action=download&backup_id=${backupId}`;
}

function restoreBackup(backupId) {
    if (confirm('Are you sure you want to restore this backup? This will overwrite current data and cannot be undone.')) {
        showLoading();
        
        fetch('/settings/backup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'restore',
                backup_id: backupId,
                csrf_token: '<?php echo $csrf_token; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showMessage('Backup restored successfully', 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showMessage(data.message || 'Failed to restore backup', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('An error occurred while restoring backup', 'error');
        });
    }
}

function deleteBackup(backupId) {
    if (confirm('Are you sure you want to delete this backup? This action cannot be undone.')) {
        showLoading();
        
        fetch('/settings/backup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'delete',
                backup_id: backupId,
                csrf_token: '<?php echo $csrf_token; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showMessage('Backup deleted successfully', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage(data.message || 'Failed to delete backup', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('An error occurred while deleting backup', 'error');
        });
    }
}

// Form submission
document.getElementById('backup-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.action = 'create';
    
    showLoading();
    closeBackupModal();
    
    fetch('/settings/backup', {
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
            showMessage(`Backup created successfully (${data.size})`, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showMessage(data.message || 'Failed to create backup', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while creating backup', 'error');
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>