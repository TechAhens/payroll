<?php
/**
 * Empty State Component
 * Usage: include with $empty array containing icon, title, description, action
 */

$emptyIcon = $empty['icon'] ?? 'fas fa-inbox';
$emptyTitle = $empty['title'] ?? 'No data available';
$emptyDescription = $empty['description'] ?? 'Get started by adding your first item';
$emptyActionText = $empty['action_text'] ?? '';
$emptyActionUrl = $empty['action_url'] ?? '';
$emptyActionOnclick = $empty['action_onclick'] ?? '';
$emptySize = $empty['size'] ?? 'md';

$sizeClasses = [
    'sm' => 'py-8',
    'md' => 'py-12',
    'lg' => 'py-16'
];

$iconSizes = [
    'sm' => 'text-3xl',
    'md' => 'text-4xl',
    'lg' => 'text-5xl'
];

$containerClass = $sizeClasses[$emptySize] ?? $sizeClasses['md'];
$iconClass = $iconSizes[$emptySize] ?? $iconSizes['md'];
?>

<div class="text-center text-gray-500 <?php echo $containerClass; ?>">
    <i class="<?php echo htmlspecialchars($emptyIcon); ?> <?php echo $iconClass; ?> mb-4"></i>
    <h3 class="text-lg font-medium text-gray-900 mb-2">
        <?php echo htmlspecialchars($emptyTitle); ?>
    </h3>
    <p class="text-sm text-gray-500 mb-6">
        <?php echo htmlspecialchars($emptyDescription); ?>
    </p>
    
    <?php if ($emptyActionText): ?>
        <?php if ($emptyActionUrl): ?>
            <a href="<?php echo htmlspecialchars($emptyActionUrl); ?>" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200">
                <i class="fas fa-plus mr-2"></i>
                <?php echo htmlspecialchars($emptyActionText); ?>
            </a>
        <?php elseif ($emptyActionOnclick): ?>
            <button type="button" onclick="<?php echo htmlspecialchars($emptyActionOnclick); ?>" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200">
                <i class="fas fa-plus mr-2"></i>
                <?php echo htmlspecialchars($emptyActionText); ?>
            </button>
        <?php endif; ?>
    <?php endif; ?>
</div>