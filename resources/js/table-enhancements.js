// Table Enhancement Script
document.addEventListener('DOMContentLoaded', function() {
    // Find all tables with data-enhance attribute
    const tables = document.querySelectorAll('table[data-enhance="true"]');
    
    tables.forEach(table => {
        enhanceTable(table);
    });
});

function enhanceTable(table) {
    const wrapper = table.closest('.table-wrapper') || table.parentElement;
    
    // Add search functionality
    if (table.dataset.searchable !== 'false') {
        addSearchFunctionality(table, wrapper);
    }
    
    // Add sorting functionality
    if (table.dataset.sortable !== 'false') {
        addSortingFunctionality(table);
    }
    
    // Add NO column
    if (table.dataset.showNo !== 'false') {
        addNumberColumn(table);
    }
    
    // Enhance pagination
    enhancePagination(wrapper);
}

function addSearchFunctionality(table, wrapper) {
    // Create search input
    const searchContainer = document.createElement('div');
    searchContainer.className = 'mb-4';
    searchContainer.innerHTML = `
        <div class="relative max-w-sm">
            <input 
                type="text" 
                class="table-search w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Search..."
            >
            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    `;
    
    // Insert before table
    wrapper.insertBefore(searchContainer, wrapper.firstChild);
    
    // Add search functionality
    const searchInput = searchContainer.querySelector('.table-search');
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
        
        // Update row numbers after filtering
        updateRowNumbers(table);
    });
}

function addSortingFunctionality(table) {
    const headers = table.querySelectorAll('thead th');
    const tbody = table.querySelector('tbody');
    
    headers.forEach((header, index) => {
        // Skip if header has data-sortable="false"
        if (header.dataset.sortable === 'false') return;
        
        // Skip the NO column and Actions column
        if (header.textContent.trim() === 'No' || header.textContent.trim() === 'Actions') return;
        
        header.style.cursor = 'pointer';
        header.style.userSelect = 'none';
        
        // Add sort indicator
        const sortIndicator = document.createElement('span');
        sortIndicator.className = 'ml-2 text-gray-400';
        sortIndicator.innerHTML = `
            <svg class="inline-block w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
            </svg>
        `;
        header.appendChild(sortIndicator);
        
        header.addEventListener('click', function() {
            const rows = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
            const direction = header.dataset.sortDirection === 'asc' ? 'desc' : 'asc';
            
            // Reset all headers
            headers.forEach(h => {
                h.dataset.sortDirection = '';
                const indicator = h.querySelector('svg');
                if (indicator) {
                    indicator.parentElement.className = 'ml-2 text-gray-400';
                }
            });
            
            // Set current header
            header.dataset.sortDirection = direction;
            sortIndicator.className = 'ml-2 text-gray-600';
            
            // Sort rows
            rows.sort((a, b) => {
                const aValue = a.cells[index].textContent.trim();
                const bValue = b.cells[index].textContent.trim();
                
                // Try to parse as number
                const aNum = parseFloat(aValue.replace(/[^0-9.-]/g, ''));
                const bNum = parseFloat(bValue.replace(/[^0-9.-]/g, ''));
                
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return direction === 'asc' ? aNum - bNum : bNum - aNum;
                }
                
                // Sort as string
                return direction === 'asc' 
                    ? aValue.localeCompare(bValue)
                    : bValue.localeCompare(aValue);
            });
            
            // Reorder rows
            rows.forEach(row => tbody.appendChild(row));
            
            // Update row numbers after sorting
            updateRowNumbers(table);
        });
    });
}

function addNumberColumn(table) {
    const thead = table.querySelector('thead');
    const tbody = table.querySelector('tbody');
    const headerRow = thead.querySelector('tr');
    
    // Add header
    const noHeader = document.createElement('th');
    noHeader.className = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider';
    noHeader.textContent = 'No';
    noHeader.dataset.sortable = 'false';
    headerRow.insertBefore(noHeader, headerRow.firstChild);
    
    // Add cells
    const rows = tbody.querySelectorAll('tr');
    rows.forEach((row, index) => {
        const noCell = document.createElement('td');
        noCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';
        noCell.textContent = index + 1;
        row.insertBefore(noCell, row.firstChild);
    });
}

function updateRowNumbers(table) {
    const tbody = table.querySelector('tbody');
    const visibleRows = tbody.querySelectorAll('tr:not([style*="display: none"])');
    
    visibleRows.forEach((row, index) => {
        const noCell = row.querySelector('td:first-child');
        if (noCell && !isNaN(parseInt(noCell.textContent))) {
            noCell.textContent = index + 1;
        }
    });
}

function enhancePagination(wrapper) {
    // Find pagination links
    const paginationLinks = wrapper.querySelectorAll('.pagination a');
    
    paginationLinks.forEach(link => {
        // Preserve search parameters
        link.addEventListener('click', function(e) {
            const searchInput = wrapper.querySelector('.table-search');
            if (searchInput && searchInput.value) {
                e.preventDefault();
                const url = new URL(link.href);
                url.searchParams.set('search', searchInput.value);
                window.location.href = url.toString();
            }
        });
    });
}

// Export for use in other scripts
window.TableEnhancements = {
    enhance: enhanceTable,
    addSearch: addSearchFunctionality,
    addSorting: addSortingFunctionality,
    addNumbers: addNumberColumn
};