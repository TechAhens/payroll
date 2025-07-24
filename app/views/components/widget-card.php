<?php
/**
 * Widget Card Component
 * Usage: include with $widget array containing configuration
 */

$widgetTitle = $widget['title'] ?? 'Widget';
$widgetValue = $widget['value'] ?? '0';
$widgetIcon = $widget['icon'] ?? 'fas fa-chart-bar';
$widgetColor = $widget['color'] ?? 'blue';
$widgetChange = $widget['change'] ?? null;
$widgetChangeType = $widget['change_type'] ?? 'neutral';
$widgetDescription = $widget['description'] ?? '';
$widgetLink = $widget['link'] ?? '';
$widgetSize = $widget['size'] ?? 'md';

$colorClasses = [
    'blue' => 'bg-blue-100 text-blue-600',
    'green' => 'bg-green-100 text-green-600',
    'red' => 'bg-red-100 text-red-600',
    'yellow' => 'bg-yellow-100 text-yellow-600',
    'purple' => 'bg-purple-100 text-purple-600',
    'indigo' => 'bg-indigo-100 text-indigo-600',
    'pink' => 'bg-pink-100 text-pink-600',
    'gray' => 'bg-gray-100 text-gray-600'
];

$sizeClasses = [
    'sm' => 'p-4',
    'md' => 'p-6',
    'lg' => 'p-8'
];

$iconColorClass = $colorClasses[$widgetColor] ?? $colorClasses['blue'];
$paddingClass = $sizeClasses[$widgetSize] ?? $sizeClasses['md'];
?>

<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
    <div class="<?php echo $paddingClass; ?>">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 <?php echo $iconColorClass; ?> rounded-lg flex items-center justify-center">
                    <i class="<?php echo htmlspecialchars($widgetIcon); ?>"></i>
                </div>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500"><?php echo htmlspecialchars($widgetTitle); ?></p>
                <div class="flex items-baseline">
                    <p class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($widgetValue); ?></p>
                    <?php if ($widgetChange !== null): ?>
                        <span class="ml-2 text-sm font-medium <?php 
                            echo $widgetChangeType === 'positive' ? 'text-green-600' : 
                                ($widgetChangeType === 'negative' ? 'text-red-600' : 'text-gray-500'); 
                        ?>">
                            <?php if ($widgetChangeType === 'positive'): ?>
                                <i class="fas fa-arrow-up mr-1"></i>
                            <?php elseif ($widgetChangeType === 'negative'): ?>
                                <i class="fas fa-arrow-down mr-1"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($widgetChange); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if ($widgetDescription): ?>
                    <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($widgetDescription); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($widgetLink): ?>
            <div class="mt-4">
                <a href="<?php echo htmlspecialchars($widgetLink); ?>" 
                   class="text-sm font-medium text-<?php echo $widgetColor; ?>-600 hover:text-<?php echo $widgetColor; ?>-800">
                    View details →
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>