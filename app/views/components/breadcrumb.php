<?php
/**
 * Breadcrumb Component
 * Usage: include with $breadcrumbs array
 */

if (!isset($breadcrumbs) || empty($breadcrumbs)) {
    return;
}
?>

<nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="/dashboard" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                <i class="fas fa-home mr-2"></i>
                Dashboard
            </a>
        </li>
        
        <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <?php if (isset($breadcrumb['url']) && $index < count($breadcrumbs) - 1): ?>
                        <a href="<?php echo htmlspecialchars($breadcrumb['url']); ?>" 
                           class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">
                            <?php if (isset($breadcrumb['icon'])): ?>
                                <i class="<?php echo htmlspecialchars($breadcrumb['icon']); ?> mr-1"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($breadcrumb['title']); ?>
                        </a>
                    <?php else: ?>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                            <?php if (isset($breadcrumb['icon'])): ?>
                                <i class="<?php echo htmlspecialchars($breadcrumb['icon']); ?> mr-1"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($breadcrumb['title']); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>