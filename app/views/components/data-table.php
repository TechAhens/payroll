<?php
/**
 * Data Table Component with sorting, filtering, and pagination
 * Usage: include with $table array containing columns, data, etc.
 */

$tableId = $table['id'] ?? 'data-table';
$columns = $table['columns'] ?? [];
$data = $table['data'] ?? [];
$sortable = $table['sortable'] ?? true;
$searchable = $table['searchable'] ?? true;
$pagination = $table['pagination'] ?? null;
$actions = $table['actions'] ?? [];
$emptyMessage = $table['empty_message'] ?? 'No data available';
$showCheckboxes = $table['show_checkboxes'] ?? false;
?>

<div class="data-table-container">
    <!-- Table Header with Search and Actions -->
    <?php if ($searchable || !empty($actions)): ?>
    <div class="flex items-center justify-between mb-4">
        <?php if ($searchable): ?>
        <div class="flex-1 max-w-md">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="<?php echo $tableId; ?>-search" 
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm" 
                       placeholder="Search...">
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($actions)): ?>
        <div class="flex items-center space-x-2">
            <?php foreach ($actions as $action): ?>
                <button type="button" 
                        onclick="<?php echo htmlspecialchars($action['onclick'] ?? ''); ?>"
                        class="btn <?php echo htmlspecialchars($action['class'] ?? 'btn-outline'); ?>">
                    <?php if (isset($action['icon'])): ?>
                        <i class="<?php echo htmlspecialchars($action['icon']); ?> mr-2"></i>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($action['label']); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Table -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="<?php echo $tableId; ?>" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <?php if ($showCheckboxes): ?>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" class="form-checkbox select-all-checkbox" 
                                   onchange="toggleAllCheckboxes('<?php echo $tableId; ?>', this.checked)">
                        </th>
                        <?php endif; ?>
                        
                        <?php foreach ($columns as $column): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider <?php echo $sortable ? 'cursor-pointer hover:bg-gray-100' : ''; ?>"
                            <?php if ($sortable): ?>
                                onclick="sortTable('<?php echo $tableId; ?>', <?php echo $column['index'] ?? 0; ?>)"
                            <?php endif; ?>>
                            <div class="flex items-center">
                                <?php echo htmlspecialchars($column['title']); ?>
                                <?php if ($sortable): ?>
                                    <i class="fas fa-sort ml-1 text-gray-400 sort-icon"></i>
                                <?php endif; ?>
                            </div>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($data)): ?>
                        <?php foreach ($data as $rowIndex => $row): ?>
                        <tr class="hover:bg-gray-50 table-row" data-row-index="<?php echo $rowIndex; ?>">
                            <?php if ($showCheckboxes): ?>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="form-checkbox row-checkbox" 
                                       value="<?php echo htmlspecialchars($row['id'] ?? $rowIndex); ?>">
                            </td>
                            <?php endif; ?>
                            
                            <?php foreach ($columns as $colIndex => $column): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php
                                $value = '';
                                if (isset($column['key'])) {
                                    $value = $row[$column['key']] ?? '';
                                } elseif (isset($column['render'])) {
                                    $value = call_user_func($column['render'], $row, $rowIndex);
                                }
                                
                                if (isset($column['format'])) {
                                    switch ($column['format']) {
                                        case 'currency':
                                            $value = '₹' . number_format($value, 2);
                                            break;
                                        case 'date':
                                            $value = $value ? date('M j, Y', strtotime($value)) : '';
                                            break;
                                        case 'datetime':
                                            $value = $value ? date('M j, Y H:i', strtotime($value)) : '';
                                            break;
                                    }
                                }
                                
                                echo $value;
                                ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo count($columns) + ($showCheckboxes ? 1 : 0); ?>" 
                                class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-4"></i>
                                    <p class="text-lg font-medium"><?php echo htmlspecialchars($emptyMessage); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination): ?>
            <?php include __DIR__ . '/pagination.php'; ?>
        <?php endif; ?>
    </div>
    
    <!-- Bulk Actions (shown when checkboxes are selected) -->
    <?php if ($showCheckboxes && !empty($table['bulk_actions'])): ?>
    <div id="<?php echo $tableId; ?>-bulk-actions" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-sm text-blue-700">
                    <span id="<?php echo $tableId; ?>-selected-count">0</span> items selected
                </span>
            </div>
            <div class="flex items-center space-x-2">
                <?php foreach ($table['bulk_actions'] as $action): ?>
                    <button type="button" 
                            onclick="<?php echo htmlspecialchars($action['onclick'] ?? ''); ?>"
                            class="btn btn-sm <?php echo htmlspecialchars($action['class'] ?? 'btn-outline'); ?>">
                        <?php if (isset($action['icon'])): ?>
                            <i class="<?php echo htmlspecialchars($action['icon']); ?> mr-1"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($action['label']); ?>
                    </button>
                <?php endforeach; ?>
                <button type="button" onclick="clearSelection('<?php echo $tableId; ?>')" 
                        class="btn btn-sm btn-outline">
                    Clear Selection
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Table sorting functionality
function sortTable(tableId, columnIndex) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr.table-row'));
    const header = table.querySelectorAll('th')[columnIndex + (<?php echo $showCheckboxes ? 1 : 0; ?>)];
    const sortIcon = header.querySelector('.sort-icon');
    
    // Determine sort direction
    let direction = 'asc';
    if (sortIcon.classList.contains('fa-sort-up')) {
        direction = 'desc';
    }
    
    // Reset all sort icons
    table.querySelectorAll('.sort-icon').forEach(icon => {
        icon.className = 'fas fa-sort ml-1 text-gray-400 sort-icon';
    });
    
    // Set current sort icon
    sortIcon.className = `fas fa-sort-${direction === 'asc' ? 'up' : 'down'} ml-1 text-gray-600 sort-icon`;
    
    // Sort rows
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex + (<?php echo $showCheckboxes ? 1 : 0; ?>)].textContent.trim();
        const bValue = b.cells[columnIndex + (<?php echo $showCheckboxes ? 1 : 0; ?>)].textContent.trim();
        
        // Try to parse as numbers
        const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
        const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return direction === 'asc' ? aNum - bNum : bNum - aNum;
        }
        
        // String comparison
        return direction === 'asc' ? 
            aValue.localeCompare(bValue) : 
            bValue.localeCompare(aValue);
    });
    
    // Reorder rows in DOM
    rows.forEach(row => tbody.appendChild(row));
}

// Table search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('<?php echo $tableId; ?>-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterTable('<?php echo $tableId; ?>', this.value);
        });
    }
    
    // Initialize checkbox functionality
    <?php if ($showCheckboxes): ?>
    updateBulkActionsVisibility('<?php echo $tableId; ?>');
    
    document.addEventListener('change', function(e) {
        if (e.target.matches('#<?php echo $tableId; ?> .row-checkbox')) {
            updateBulkActionsVisibility('<?php echo $tableId; ?>');
        }
    });
    <?php endif; ?>
});

function filterTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr.table-row');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matches = text.includes(searchTerm.toLowerCase());
        row.style.display = matches ? '' : 'none';
    });
}

<?php if ($showCheckboxes): ?>
function toggleAllCheckboxes(tableId, checked) {
    const table = document.getElementById(tableId);
    const checkboxes = table.querySelectorAll('.row-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
    });
    
    updateBulkActionsVisibility(tableId);
}

function updateBulkActionsVisibility(tableId) {
    const table = document.getElementById(tableId);
    const checkboxes = table.querySelectorAll('.row-checkbox:checked');
    const bulkActions = document.getElementById(tableId + '-bulk-actions');
    const selectedCount = document.getElementById(tableId + '-selected-count');
    const selectAllCheckbox = table.querySelector('.select-all-checkbox');
    
    if (bulkActions) {
        if (checkboxes.length > 0) {
            bulkActions.classList.remove('hidden');
            selectedCount.textContent = checkboxes.length;
        } else {
            bulkActions.classList.add('hidden');
        }
    }
    
    // Update select all checkbox state
    if (selectAllCheckbox) {
        const allCheckboxes = table.querySelectorAll('.row-checkbox');
        selectAllCheckbox.checked = allCheckboxes.length > 0 && checkboxes.length === allCheckboxes.length;
        selectAllCheckbox.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
    }
}

function getSelectedIds(tableId) {
    const table = document.getElementById(tableId);
    const checkboxes = table.querySelectorAll('.row-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function clearSelection(tableId) {
    const table = document.getElementById(tableId);
    const checkboxes = table.querySelectorAll('.row-checkbox');
    const selectAllCheckbox = table.querySelector('.select-all-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
    
    updateBulkActionsVisibility(tableId);
}
<?php endif; ?>
</script>