<?php
/**
 * Statistics Card Component
 * Usage: include with $stat array containing title, value, icon, etc.
 */

$statTitle = $stat['title'] ?? 'Statistic';
$statValue = $stat['value'] ?? '0';
$statIcon = $stat['icon'] ?? 'fas fa-chart-bar';
$statColor = $stat['color'] ?? 'blue';
$statChange = $stat['change'] ?? null;
$statChangeType = $stat['change_type'] ?? 'neutral';
$statDescription = $stat['description'] ?? '';
$statLink = $stat['link'] ?? '';

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

$iconColorClass = $colorClasses[$statColor] ?? $colorClasses['blue'];
?>

<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
    <div class="p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 <?php echo $iconColorClass; ?> rounded-lg flex items-center justify-center">
                    <i class="<?php echo htmlspecialchars($statIcon); ?>"></i>
                </div>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500"><?php echo htmlspecialchars($statTitle); ?></p>
                <div class="flex items-baseline">
                    <p class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($statValue); ?></p>
                    <?php if ($statChange !== null): ?>
                        <span class="ml-2 text-sm font-medium <?php 
                            echo $statChangeType === 'positive' ? 'text-green-600' : 
                                ($statChangeType === 'negative' ? 'text-red-600' : 'text-gray-500'); 
                        ?>">
                            <?php if ($statChangeType === 'positive'): ?>
                                <i class="fas fa-arrow-up mr-1"></i>
                            <?php elseif ($statChangeType === 'negative'): ?>
                                <i class="fas fa-arrow-down mr-1"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($statChange); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if ($statDescription): ?>
                    <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($statDescription); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($statLink): ?>
            <div class="mt-4">
                <a href="<?php echo htmlspecialchars($statLink); ?>" 
                   class="text-sm font-medium text-<?php echo $statColor; ?>-600 hover:text-<?php echo $statColor; ?>-800">
                    View details →
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>