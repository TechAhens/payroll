<?php
/**
 * Status Badge Component
 * Usage: include with $badge array containing status, text, etc.
 */

$badgeStatus = $badge['status'] ?? 'default';
$badgeText = $badge['text'] ?? $badgeStatus;
$badgeSize = $badge['size'] ?? 'md';

$statusClasses = [
    'active' => 'bg-green-100 text-green-800',
    'inactive' => 'bg-yellow-100 text-yellow-800',
    'terminated' => 'bg-red-100 text-red-800',
    'pending' => 'bg-blue-100 text-blue-800',
    'approved' => 'bg-green-100 text-green-800',
    'rejected' => 'bg-red-100 text-red-800',
    'processing' => 'bg-yellow-100 text-yellow-800',
    'completed' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-gray-100 text-gray-800',
    'open' => 'bg-blue-100 text-blue-800',
    'closed' => 'bg-gray-100 text-gray-800',
    'locked' => 'bg-green-100 text-green-800',
    'paid' => 'bg-green-100 text-green-800',
    'unpaid' => 'bg-red-100 text-red-800',
    'overdue' => 'bg-red-100 text-red-800',
    'default' => 'bg-gray-100 text-gray-800'
];

$sizeClasses = [
    'sm' => 'px-2 py-1 text-xs',
    'md' => 'px-2.5 py-0.5 text-xs',
    'lg' => 'px-3 py-1 text-sm'
];

$badgeClass = $statusClasses[$badgeStatus] ?? $statusClasses['default'];
$sizeClass = $sizeClasses[$badgeSize] ?? $sizeClasses['md'];
?>

<span class="inline-flex items-center <?php echo $sizeClass; ?> font-semibold rounded-full <?php echo $badgeClass; ?>">
    <?php echo htmlspecialchars(ucfirst($badgeText)); ?>
</span>