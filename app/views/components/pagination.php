<?php
/**
 * Pagination Component
 * Usage: include with $pagination array
 */

if (!isset($pagination) || $pagination['total_pages'] <= 1) {
    return;
}

$current = $pagination['current_page'];
$total = $pagination['total_pages'];
$hasNext = $pagination['has_next'];
$hasPrevious = $pagination['has_previous'];

// Build query string for pagination links
$queryParams = $_GET;
unset($queryParams['page']);
$queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
?>

<div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
    <!-- Mobile pagination -->
    <div class="flex-1 flex justify-between sm:hidden">
        <?php if ($hasPrevious): ?>
            <a href="?page=<?php echo $current - 1; ?><?php echo $queryString; ?>" 
               class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-chevron-left mr-1"></i>
                Previous
            </a>
        <?php else: ?>
            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50 cursor-not-allowed">
                <i class="fas fa-chevron-left mr-1"></i>
                Previous
            </span>
        <?php endif; ?>
        
        <?php if ($hasNext): ?>
            <a href="?page=<?php echo $current + 1; ?><?php echo $queryString; ?>" 
               class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Next
                <i class="fas fa-chevron-right ml-1"></i>
            </a>
        <?php else: ?>
            <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50 cursor-not-allowed">
                Next
                <i class="fas fa-chevron-right ml-1"></i>
            </span>
        <?php endif; ?>
    </div>
    
    <!-- Desktop pagination -->
    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700">
                Showing page <span class="font-medium"><?php echo $current; ?></span> of 
                <span class="font-medium"><?php echo $total; ?></span>
                <?php if (isset($pagination['total'])): ?>
                    (<?php echo number_format($pagination['total']); ?> total records)
                <?php endif; ?>
            </p>
        </div>
        
        <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <!-- Previous button -->
                <?php if ($hasPrevious): ?>
                    <a href="?page=<?php echo $current - 1; ?><?php echo $queryString; ?>" 
                       class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Previous</span>
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php else: ?>
                    <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-50 text-sm font-medium text-gray-300 cursor-not-allowed">
                        <span class="sr-only">Previous</span>
                        <i class="fas fa-chevron-left"></i>
                    </span>
                <?php endif; ?>
                
                <!-- Page numbers -->
                <?php
                $start = max(1, $current - 2);
                $end = min($total, $current + 2);
                
                // Show first page if not in range
                if ($start > 1): ?>
                    <a href="?page=1<?php echo $queryString; ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        1
                    </a>
                    <?php if ($start > 2): ?>
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                            ...
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Current range -->
                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $current): ?>
                        <span class="relative inline-flex items-center px-4 py-2 border border-primary-500 bg-primary-50 text-sm font-medium text-primary-600">
                            <?php echo $i; ?>
                        </span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?><?php echo $queryString; ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <?php echo $i; ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <!-- Show last page if not in range -->
                <?php if ($end < $total): ?>
                    <?php if ($end < $total - 1): ?>
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                            ...
                        </span>
                    <?php endif; ?>
                    <a href="?page=<?php echo $total; ?><?php echo $queryString; ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <?php echo $total; ?>
                    </a>
                <?php endif; ?>
                
                <!-- Next button -->
                <?php if ($hasNext): ?>
                    <a href="?page=<?php echo $current + 1; ?><?php echo $queryString; ?>" 
                       class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Next</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-50 text-sm font-medium text-gray-300 cursor-not-allowed">
                        <span class="sr-only">Next</span>
                        <i class="fas fa-chevron-right"></i>
                    </span>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</div>