/**
 * Table utilities and enhancements
 */

window.TableUtils = {
    // Initialize sortable table
    makeSortable: function(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const headers = table.querySelectorAll('th[data-sortable]');
        
        headers.forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.innerHTML += ' <i class="fas fa-sort text-gray-400 ml-1 sort-icon"></i>';
            
            header.addEventListener('click', () => {
                this.sortTable(table, index, header);
            });
        });
    },
    
    // Sort table by column
    sortTable: function(table, columnIndex, header) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const sortIcon = header.querySelector('.sort-icon');
        
        // Determine sort direction
        let direction = 'asc';
        if (sortIcon.classList.contains('fa-sort-up')) {
            direction = 'desc';
        }
        
        // Reset all sort icons
        table.querySelectorAll('.sort-icon').forEach(icon => {
            icon.className = 'fas fa-sort text-gray-400 ml-1 sort-icon';
        });
        
        // Set current sort icon
        sortIcon.className = `fas fa-sort-${direction === 'asc' ? 'up' : 'down'} text-gray-600 ml-1 sort-icon`;
        
        // Sort rows
        rows.sort((a, b) => {
            const aValue = a.cells[columnIndex].textContent.trim();
            const bValue = b.cells[columnIndex].textContent.trim();
            
            // Try to parse as numbers
            const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
            const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return direction === 'asc' ? aNum - bNum : bNum - aNum;
            }
            
            // Date comparison
            const aDate = new Date(aValue);
            const bDate = new Date(bValue);
            if (!isNaN(aDate) && !isNaN(bDate)) {
                return direction === 'asc' ? aDate - bDate : bDate - aDate;
            }
            
            // String comparison
            return direction === 'asc' ? 
                aValue.localeCompare(bValue) : 
                bValue.localeCompare(aValue);
        });
        
        // Reorder rows in DOM
        rows.forEach(row => tbody.appendChild(row));
    },
    
    // Add search functionality
    addSearch: function(tableId, searchInputId) {
        const table = document.getElementById(tableId);
        const searchInput = document.getElementById(searchInputId);
        
        if (!table || !searchInput) return;
        
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const matches = text.includes(searchTerm);
                row.style.display = matches ? '' : 'none';
            });
            
            // Update row count if exists
            const rowCount = document.getElementById(tableId + '-row-count');
            if (rowCount) {
                const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none').length;
                rowCount.textContent = `Showing ${visibleRows} of ${rows.length} records`;
            }
        });
    },
    
    // Add row selection functionality
    addRowSelection: function(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        // Add select all checkbox to header
        const headerRow = table.querySelector('thead tr');
        if (headerRow) {
            const selectAllCell = document.createElement('th');
            selectAllCell.innerHTML = '<input type="checkbox" class="form-checkbox select-all-checkbox">';
            headerRow.insertBefore(selectAllCell, headerRow.firstChild);
            
            const selectAllCheckbox = selectAllCell.querySelector('.select-all-checkbox');
            selectAllCheckbox.addEventListener('change', function() {
                const rowCheckboxes = table.querySelectorAll('tbody .row-checkbox');
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectionCount(table);
            });
        }
        
        // Add checkboxes to each row
        const bodyRows = table.querySelectorAll('tbody tr');
        bodyRows.forEach(row => {
            const selectCell = document.createElement('td');
            selectCell.innerHTML = '<input type="checkbox" class="form-checkbox row-checkbox">';
            row.insertBefore(selectCell, row.firstChild);
            
            const checkbox = selectCell.querySelector('.row-checkbox');
            checkbox.addEventListener('change', function() {
                updateSelectionCount(table);
                updateSelectAllState(table);
            });
        });
        
        function updateSelectionCount(table) {
            const selectedCount = table.querySelectorAll('tbody .row-checkbox:checked').length;
            const countElement = document.getElementById(tableId + '-selected-count');
            if (countElement) {
                countElement.textContent = selectedCount;
            }
            
            // Show/hide bulk actions
            const bulkActions = document.getElementById(tableId + '-bulk-actions');
            if (bulkActions) {
                if (selectedCount > 0) {
                    bulkActions.classList.remove('hidden');
                } else {
                    bulkActions.classList.add('hidden');
                }
            }
        }
        
        function updateSelectAllState(table) {
            const selectAllCheckbox = table.querySelector('.select-all-checkbox');
            const rowCheckboxes = table.querySelectorAll('tbody .row-checkbox');
            const checkedBoxes = table.querySelectorAll('tbody .row-checkbox:checked');
            
            if (checkedBoxes.length === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedBoxes.length === rowCheckboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }
    },
    
    // Get selected row IDs
    getSelectedIds: function(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return [];
        
        const selectedCheckboxes = table.querySelectorAll('tbody .row-checkbox:checked');
        return Array.from(selectedCheckboxes).map(checkbox => {
            const row = checkbox.closest('tr');
            return row.dataset.id || row.querySelector('[data-id]')?.dataset.id;
        }).filter(id => id);
    },
    
    // Clear selection
    clearSelection: function(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const checkboxes = table.querySelectorAll('.row-checkbox, .select-all-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            checkbox.indeterminate = false;
        });
        
        // Hide bulk actions
        const bulkActions = document.getElementById(tableId + '-bulk-actions');
        if (bulkActions) {
            bulkActions.classList.add('hidden');
        }
    },
    
    // Export table to CSV
    exportToCSV: function(tableId, filename = 'export.csv') {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const rows = table.querySelectorAll('tr');
        const csv = [];
        
        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const rowData = Array.from(cols).map(col => {
                // Skip checkbox columns
                if (col.querySelector('input[type="checkbox"]')) {
                    return null;
                }
                return '"' + col.textContent.replace(/"/g, '""').trim() + '"';
            }).filter(cell => cell !== null);
            
            if (rowData.length > 0) {
                csv.push(rowData.join(','));
            }
        });
        
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        PayrollUtils.file.downloadBlob(blob, filename);
    },
    
    // Add pagination
    addPagination: function(tableId, itemsPerPage = 25) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const totalPages = Math.ceil(rows.length / itemsPerPage);
        let currentPage = 1;
        
        // Create pagination container
        const paginationContainer = document.createElement('div');
        paginationContainer.className = 'pagination-container flex items-center justify-between mt-4';
        paginationContainer.innerHTML = `
            <div class="pagination-info text-sm text-gray-700">
                Showing <span class="font-medium" id="${tableId}-start">1</span> to 
                <span class="font-medium" id="${tableId}-end">${Math.min(itemsPerPage, rows.length)}</span> of 
                <span class="font-medium">${rows.length}</span> results
            </div>
            <div class="pagination-controls flex items-center space-x-2">
                <button type="button" class="pagination-btn" data-action="prev" disabled>
                    <i class="fas fa-chevron-left"></i> Previous
                </button>
                <div class="pagination-pages flex items-center space-x-1" id="${tableId}-pages"></div>
                <button type="button" class="pagination-btn" data-action="next">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        `;
        
        table.parentNode.appendChild(paginationContainer);
        
        // Show page
        function showPage(page) {
            currentPage = page;
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            
            rows.forEach((row, index) => {
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });
            
            // Update pagination info
            document.getElementById(`${tableId}-start`).textContent = start + 1;
            document.getElementById(`${tableId}-end`).textContent = Math.min(end, rows.length);
            
            // Update pagination controls
            updatePaginationControls();
        }
        
        // Update pagination controls
        function updatePaginationControls() {
            const prevBtn = paginationContainer.querySelector('[data-action="prev"]');
            const nextBtn = paginationContainer.querySelector('[data-action="next"]');
            const pagesContainer = document.getElementById(`${tableId}-pages`);
            
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages;
            
            // Generate page buttons
            let pagesHtml = '';
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
            
            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
            
            for (let i = startPage; i <= endPage; i++) {
                pagesHtml += `
                    <button type="button" 
                            class="pagination-page-btn ${i === currentPage ? 'active' : ''}" 
                            data-page="${i}">
                        ${i}
                    </button>
                `;
            }
            
            pagesContainer.innerHTML = pagesHtml;
        }
        
        // Event listeners
        paginationContainer.addEventListener('click', function(e) {
            if (e.target.matches('[data-action="prev"]') && currentPage > 1) {
                showPage(currentPage - 1);
            } else if (e.target.matches('[data-action="next"]') && currentPage < totalPages) {
                showPage(currentPage + 1);
            } else if (e.target.matches('.pagination-page-btn')) {
                const page = parseInt(e.target.dataset.page);
                showPage(page);
            }
        });
        
        // Initial page
        showPage(1);
    }
};

// Auto-initialize tables
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sortable tables
    document.querySelectorAll('table[data-sortable]').forEach(table => {
        TableUtils.makeSortable(table.id);
    });
    
    // Initialize searchable tables
    document.querySelectorAll('table[data-searchable]').forEach(table => {
        const searchInput = document.querySelector(`[data-search-table="${table.id}"]`);
        if (searchInput) {
            TableUtils.addSearch(table.id, searchInput.id);
        }
    });
    
    // Initialize selectable tables
    document.querySelectorAll('table[data-selectable]').forEach(table => {
        TableUtils.addRowSelection(table.id);
    });
    
    // Initialize paginated tables
    document.querySelectorAll('table[data-paginate]').forEach(table => {
        const itemsPerPage = parseInt(table.dataset.paginate) || 25;
        TableUtils.addPagination(table.id, itemsPerPage);
    });
});

// Add CSS for table enhancements
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            background: white;
            color: #374151;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .pagination-btn:hover:not(:disabled) {
            background: #f3f4f6;
            border-color: #9ca3af;
        }
        
        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination-page-btn {
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            background: white;
            color: #374151;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .pagination-page-btn:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }
        
        .pagination-page-btn.active {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }
        
        .sortable-header {
            user-select: none;
        }
        
        .sortable-header:hover {
            background-color: #f9fafb;
        }
        
        .table-row-hover:hover {
            background-color: #f9fafb;
        }
        
        .table-striped tbody tr:nth-child(odd) {
            background-color: #f9fafb;
        }
    `;
    document.head.appendChild(style);
});

// Export for global access
window.Tables = TableUtils;