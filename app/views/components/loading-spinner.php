<?php
/**
 * Loading Spinner Component
 * Usage: include with $spinner array for customization
 */

$spinnerSize = $spinner['size'] ?? 'md';
$spinnerColor = $spinner['color'] ?? 'blue';
$spinnerText = $spinner['text'] ?? 'Loading...';
$spinnerOverlay = $spinner['overlay'] ?? false;

$sizeClasses = [
    'sm' => 'h-4 w-4',
    'md' => 'h-6 w-6',
    'lg' => 'h-8 w-8',
    'xl' => 'h-12 w-12'
];

$colorClasses = [
    'blue' => 'text-blue-600',
    'green' => 'text-green-600',
    'red' => 'text-red-600',
    'yellow' => 'text-yellow-600',
    'purple' => 'text-purple-600',
    'gray' => 'text-gray-600'
];

$sizeClass = $sizeClasses[$spinnerSize] ?? $sizeClasses['md'];
$colorClass = $colorClasses[$spinnerColor] ?? $colorClasses['blue'];
?>

<?php if ($spinnerOverlay): ?>
<div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 flex items-center">
<?php endif; ?>

<div class="flex items-center <?php echo $spinnerOverlay ? '' : 'justify-center'; ?>">
    <svg class="animate-spin <?php echo $sizeClass; ?> <?php echo $colorClass; ?>" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <?php if ($spinnerText): ?>
        <span class="ml-3 text-gray-700"><?php echo htmlspecialchars($spinnerText); ?></span>
    <?php endif; ?>
</div>

<?php if ($spinnerOverlay): ?>
    </div>
</div>
<?php endif; ?>