<?php
/**
 * Alert Component
 * Usage: include with $alert array containing type, title, message, etc.
 */

$alertType = $alert['type'] ?? 'info';
$alertTitle = $alert['title'] ?? '';
$alertMessage = $alert['message'] ?? '';
$alertDismissible = $alert['dismissible'] ?? true;
$alertIcon = $alert['icon'] ?? '';

$alertClasses = [
    'success' => 'bg-green-50 border-green-200 text-green-800',
    'error' => 'bg-red-50 border-red-200 text-red-800',
    'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
    'info' => 'bg-blue-50 border-blue-200 text-blue-800'
];

$iconClasses = [
    'success' => 'fas fa-check-circle text-green-400',
    'error' => 'fas fa-exclamation-circle text-red-400',
    'warning' => 'fas fa-exclamation-triangle text-yellow-400',
    'info' => 'fas fa-info-circle text-blue-400'
];

$alertClass = $alertClasses[$alertType] ?? $alertClasses['info'];
$iconClass = $alertIcon ?: ($iconClasses[$alertType] ?? $iconClasses['info']);
?>

<div class="alert <?php echo $alertClass; ?> p-4 rounded-md border" role="alert">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="<?php echo $iconClass; ?>"></i>
        </div>
        <div class="ml-3 flex-1">
            <?php if ($alertTitle): ?>
                <h3 class="text-sm font-medium">
                    <?php echo htmlspecialchars($alertTitle); ?>
                </h3>
            <?php endif; ?>
            
            <?php if ($alertMessage): ?>
                <div class="<?php echo $alertTitle ? 'mt-2' : ''; ?> text-sm">
                    <?php if (is_array($alertMessage)): ?>
                        <ul class="list-disc list-inside space-y-1">
                            <?php foreach ($alertMessage as $msg): ?>
                                <li><?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p><?php echo htmlspecialchars($alertMessage); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($alertDismissible): ?>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button type="button" onclick="this.closest('.alert').remove()" 
                            class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 hover:bg-opacity-20 hover:bg-gray-600">
                        <span class="sr-only">Dismiss</span>
                        <i class="fas fa-times text-current"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>