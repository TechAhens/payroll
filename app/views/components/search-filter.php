<?php
/**
 * Search and Filter Component
 * Usage: include with $filter array containing configuration
 */

$filterId = $filter['id'] ?? 'search-filter';
$searchPlaceholder = $filter['search_placeholder'] ?? 'Search...';
$filters = $filter['filters'] ?? [];
$showSearch = $filter['show_search'] ?? true;
$showExport = $filter['show_export'] ?? false;
$exportFormats = $filter['export_formats'] ?? ['excel', 'csv'];
?>

<div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
    <div class="p-6">
        <form method="GET" class="space-y-4">
            <div class="flex flex-col md:flex-row md:items-end md:space-x-4 space-y-4 md:space-y-0">
                <?php if ($showSearch): ?>
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" 
                               name="search" 
                               id="search" 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" 
                               class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" 
                               placeholder="<?php echo htmlspecialchars($searchPlaceholder); ?>">
                    </div>
                </div>
                <?php endif; ?>
                
                <?php foreach ($filters as $filterConfig): ?>
                <div class="<?php echo $filterConfig['width'] ?? 'w-full md:w-48'; ?>">
                    <label for="<?php echo $filterConfig['name']; ?>" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo htmlspecialchars($filterConfig['label']); ?>
                    </label>
                    
                    <?php if ($filterConfig['type'] === 'select'): ?>
                        <select name="<?php echo $filterConfig['name']; ?>" 
                                id="<?php echo $filterConfig['name']; ?>" 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            <option value=""><?php echo $filterConfig['placeholder'] ?? 'All'; ?></option>
                            <?php foreach ($filterConfig['options'] as $option): ?>
                                <option value="<?php echo htmlspecialchars($option['value']); ?>" 
                                        <?php echo (($_GET[$filterConfig['name']] ?? '') == $option['value']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($option['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($filterConfig['type'] === 'date'): ?>
                        <input type="date" 
                               name="<?php echo $filterConfig['name']; ?>" 
                               id="<?php echo $filterConfig['name']; ?>"
                               value="<?php echo htmlspecialchars($_GET[$filterConfig['name']] ?? ''); ?>"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                
                <div class="flex items-center space-x-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>
                    
                    <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>" 
                       class="btn btn-outline">
                        <i class="fas fa-times mr-2"></i>
                        Clear
                    </a>
                    
                    <?php if ($showExport): ?>
                        <div class="relative">
                            <button type="button" 
                                    class="btn btn-outline"
                                    onclick="toggleDropdown('export-dropdown-<?php echo $filterId; ?>')">
                                <i class="fas fa-download mr-2"></i>
                                Export
                                <i class="fas fa-chevron-down ml-1"></i>
                            </button>
                            
                            <div id="export-dropdown-<?php echo $filterId; ?>" 
                                 class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                <div class="py-1">
                                    <?php foreach ($exportFormats as $format): ?>
                                        <button type="button" 
                                                onclick="exportData('<?php echo $format; ?>')"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-file-<?php echo $format === 'excel' ? 'excel' : $format; ?> mr-2 
                                                <?php echo $format === 'excel' ? 'text-green-500' : 'text-blue-500'; ?>"></i>
                                            <?php echo strtoupper($format); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function exportData(format) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', format);
    window.location.href = currentUrl.toString();
}

// Auto-submit form when filters change (optional)
document.addEventListener('DOMContentLoaded', function() {
    const autoSubmitElements = document.querySelectorAll('[data-auto-submit="true"]');
    autoSubmitElements.forEach(element => {
        element.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
});
</script>