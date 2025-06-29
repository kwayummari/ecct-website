/**
 * ECCT Admin Panel JavaScript
 * Common functionality for the admin interface
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize admin functionality
    initializeAdmin();
});

function initializeAdmin() {
    // Initialize tooltips
    initializeTooltips();

    // Initialize confirmation dialogs
    initializeConfirmations();

    // Initialize form enhancements
    initializeForms();

    // Initialize table enhancements
    initializeTables();

    // Initialize notifications
    initializeNotifications();

    // Initialize search functionality
    initializeSearch();

    // Initialize auto-save functionality
    initializeAutoSave();

    // Initialize keyboard shortcuts
    initializeKeyboardShortcuts();
}

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize confirmation dialogs for dangerous actions
 */
function initializeConfirmations() {
    // Delete confirmations
    document.querySelectorAll('[data-confirm-delete]').forEach(function (element) {
        element.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Custom confirmations
    document.querySelectorAll('[data-confirm]').forEach(function (element) {
        element.addEventListener('click', function (e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

/**
 * Initialize form enhancements
 */
function initializeForms() {
    // Auto-resize textareas
    document.querySelectorAll('textarea[data-auto-resize]').forEach(function (textarea) {
        textarea.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });

        // Initial resize
        textarea.style.height = textarea.scrollHeight + 'px';
    });

    // Character counters
    document.querySelectorAll('input[maxlength], textarea[maxlength]').forEach(function (input) {
        const maxLength = input.getAttribute('maxlength');
        const counter = document.createElement('div');
        counter.className = 'form-text text-muted small';
        counter.innerHTML = `<span class="char-count">0</span>/${maxLength} characters`;

        input.parentNode.appendChild(counter);

        input.addEventListener('input', function () {
            const currentLength = this.value.length;
            const countSpan = counter.querySelector('.char-count');
            countSpan.textContent = currentLength;

            if (currentLength > maxLength * 0.9) {
                counter.classList.add('text-warning');
            } else {
                counter.classList.remove('text-warning');
            }

            if (currentLength >= maxLength) {
                counter.classList.add('text-danger');
                counter.classList.remove('text-warning');
            } else {
                counter.classList.remove('text-danger');
            }
        });

        // Initial count
        input.dispatchEvent(new Event('input'));
    });

    // Slug generation
    const titleInput = document.querySelector('input[name="title"]');
    const slugInput = document.querySelector('input[name="slug"]');

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function () {
            if (!slugInput.hasAttribute('data-manual')) {
                slugInput.value = generateSlug(this.value);
            }
        });

        slugInput.addEventListener('input', function () {
            this.setAttribute('data-manual', 'true');
        });
    }

    // Form validation feedback
    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();

                // Focus on first invalid field
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
            form.classList.add('was-validated');
        });
    });
}

/**
 * Initialize table enhancements
 */
function initializeTables() {
    // Table row selection
    document.querySelectorAll('table[data-selectable] tbody tr').forEach(function (row) {
        row.addEventListener('click', function () {
            const checkbox = this.querySelector('input[type="checkbox"]');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                this.classList.toggle('table-active', checkbox.checked);
            }
        });
    });

    // Select all functionality
    document.querySelectorAll('input[data-select-all]').forEach(function (selectAll) {
        selectAll.addEventListener('change', function () {
            const target = this.getAttribute('data-select-all');
            const checkboxes = document.querySelectorAll(target);

            checkboxes.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
                const row = checkbox.closest('tr');
                if (row) {
                    row.classList.toggle('table-active', checkbox.checked);
                }
            });
        });
    });

    // Bulk actions
    document.querySelectorAll('[data-bulk-action]').forEach(function (button) {
        button.addEventListener('click', function () {
            const action = this.getAttribute('data-bulk-action');
            const checkboxes = document.querySelectorAll('table input[type="checkbox"]:checked');

            if (checkboxes.length === 0) {
                alert('Please select at least one item.');
                return;
            }

            if (confirm(`Are you sure you want to ${action} ${checkboxes.length} item(s)?`)) {
                // Process bulk action
                const ids = Array.from(checkboxes).map(cb => cb.value);
                console.log(`Bulk ${action}:`, ids);
                // Implement actual bulk action logic here
            }
        });
    });
}

/**
 * Initialize notifications
 */
function initializeNotifications() {
    // Auto-dismiss alerts
    document.querySelectorAll('.alert:not(.alert-persistent)').forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = new bootstrap.Alert(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });

    // Toast notifications
    document.querySelectorAll('.toast').forEach(function (toastEl) {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    });
}

/**
 * Initialize search functionality
 */
function initializeSearch() {
    // Live search
    document.querySelectorAll('input[data-live-search]').forEach(function (searchInput) {
        const target = searchInput.getAttribute('data-live-search');
        let searchTimeout;

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const query = this.value.toLowerCase();

            searchTimeout = setTimeout(function () {
                const items = document.querySelectorAll(target);

                items.forEach(function (item) {
                    const text = item.textContent.toLowerCase();
                    const matches = text.includes(query);
                    item.style.display = matches ? '' : 'none';
                });
            }, 300);
        });
    });

    // Search highlighting
    function highlightSearchTerms(element, query) {
        if (!query) return;

        const regex = new RegExp(`(${query})`, 'gi');
        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );

        const textNodes = [];
        let node;

        while (node = walker.nextNode()) {
            textNodes.push(node);
        }

        textNodes.forEach(function (textNode) {
            if (regex.test(textNode.textContent)) {
                const parent = textNode.parentNode;
                const wrapper = document.createElement('span');
                wrapper.innerHTML = textNode.textContent.replace(regex, '<mark>$1</mark>');
                parent.replaceChild(wrapper, textNode);
            }
        });
    }
}

/**
 * Initialize auto-save functionality
 */
function initializeAutoSave() {
    const forms = document.querySelectorAll('form[data-auto-save]');

    forms.forEach(function (form) {
        const interval = parseInt(form.getAttribute('data-auto-save')) || 30000; // Default 30 seconds
        let autoSaveTimeout;
        let hasChanges = false;

        // Track changes
        form.addEventListener('input', function () {
            hasChanges = true;
            clearTimeout(autoSaveTimeout);

            autoSaveTimeout = setTimeout(function () {
                if (hasChanges) {
                    autoSaveForm(form);
                    hasChanges = false;
                }
            }, interval);
        });

        // Show auto-save indicator
        const indicator = document.createElement('div');
        indicator.className = 'auto-save-indicator';
        indicator.innerHTML = '<small class="text-muted"><i class="fas fa-save"></i> Auto-saving...</small>';
        indicator.style.display = 'none';
        form.appendChild(indicator);

        function autoSaveForm(form) {
            const formData = new FormData(form);
            formData.append('auto_save', '1');

            indicator.style.display = 'block';

            fetch(form.action || window.location.href, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    indicator.innerHTML = '<small class="text-success"><i class="fas fa-check"></i> Auto-saved</small>';
                    setTimeout(() => {
                        indicator.style.display = 'none';
                    }, 2000);
                })
                .catch(error => {
                    indicator.innerHTML = '<small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Auto-save failed</small>';
                    setTimeout(() => {
                        indicator.style.display = 'none';
                    }, 3000);
                });
        }
    });
}

/**
 * Initialize keyboard shortcuts
 */
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function (e) {
        // Ctrl/Cmd + S: Save form
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const saveButton = document.querySelector('button[type="submit"], input[type="submit"]');
            if (saveButton) {
                saveButton.click();
            }
        }

        // Ctrl/Cmd + K: Focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }

        // Escape: Close modals
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                const modal = bootstrap.Modal.getInstance(openModal);
                if (modal) {
                    modal.hide();
                }
            }
        }
    });
}

/**
 * Utility Functions
 */

/**
 * Generate URL-friendly slug from text
 */
function generateSlug(text) {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '') // Remove special characters
        .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
        .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Show loading state
 */
function showLoading(element) {
    element.classList.add('loading');
    element.disabled = true;

    const originalText = element.innerHTML;
    element.setAttribute('data-original-text', originalText);
    element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
}

/**
 * Hide loading state
 */
function hideLoading(element) {
    element.classList.remove('loading');
    element.disabled = false;

    const originalText = element.getAttribute('data-original-text');
    if (originalText) {
        element.innerHTML = originalText;
        element.removeAttribute('data-original-text');
    }
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();

    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    toastContainer.appendChild(toast);

    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();

    // Remove from DOM after hiding
    toast.addEventListener('hidden.bs.toast', function () {
        toast.remove();
    });
}

/**
 * Create toast container if it doesn't exist
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '1080';
    document.body.appendChild(container);
    return container;
}

/**
 * Confirm dialog with custom styling
 */
function confirmDialog(message, title = 'Confirm Action') {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger confirm-yes">Confirm</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        modal.querySelector('.confirm-yes').addEventListener('click', function () {
            resolve(true);
            bsModal.hide();
        });

        modal.addEventListener('hidden.bs.modal', function () {
            if (!modal.querySelector('.confirm-yes').clicked) {
                resolve(false);
            }
            modal.remove();
        });

        modal.querySelector('.confirm-yes').addEventListener('click', function () {
            this.clicked = true;
        });
    });
}

/**
 * AJAX form submission with loading states
 */
function submitFormAjax(form, options = {}) {
    return new Promise((resolve, reject) => {
        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');

        if (submitButton) {
            showLoading(submitButton);
        }

        const formData = new FormData(form);

        fetch(form.action || window.location.href, {
            method: form.method || 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (submitButton) {
                    hideLoading(submitButton);
                }

                if (data.success) {
                    if (options.showSuccess !== false) {
                        showToast(data.message || 'Operation completed successfully', 'success');
                    }
                    resolve(data);
                } else {
                    showToast(data.message || 'An error occurred', 'danger');
                    reject(new Error(data.message || 'Operation failed'));
                }
            })
            .catch(error => {
                if (submitButton) {
                    hideLoading(submitButton);
                }

                showToast('An error occurred while processing your request', 'danger');
                reject(error);
            });
    });
}

/**
 * Data table enhancements
 */
function enhanceDataTable(tableSelector) {
    const table = document.querySelector(tableSelector);
    if (!table) return;

    // Add sorting functionality
    const headers = table.querySelectorAll('th[data-sortable]');
    headers.forEach(header => {
        header.style.cursor = 'pointer';
        header.innerHTML += ' <i class="fas fa-sort text-muted"></i>';

        header.addEventListener('click', function () {
            const column = this.dataset.sortable;
            const currentSort = this.dataset.sort || 'asc';
            const newSort = currentSort === 'asc' ? 'desc' : 'asc';

            // Update header icons
            headers.forEach(h => {
                const icon = h.querySelector('i');
                icon.className = 'fas fa-sort text-muted';
                h.removeAttribute('data-sort');
            });

            const icon = this.querySelector('i');
            icon.className = `fas fa-sort-${newSort === 'asc' ? 'up' : 'down'} text-primary`;
            this.dataset.sort = newSort;

            // Sort table rows
            sortTable(table, column, newSort);
        });
    });
}

/**
 * Sort table by column
 */
function sortTable(table, column, direction) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        const aVal = a.querySelector(`[data-sort-value="${column}"]`)?.dataset.sortValue ||
            a.querySelector(`td:nth-child(${getColumnIndex(table, column)})`)?.textContent || '';
        const bVal = b.querySelector(`[data-sort-value="${column}"]`)?.dataset.sortValue ||
            b.querySelector(`td:nth-child(${getColumnIndex(table, column)})`)?.textContent || '';

        const comparison = aVal.localeCompare(bVal, undefined, { numeric: true });
        return direction === 'asc' ? comparison : -comparison;
    });

    rows.forEach(row => tbody.appendChild(row));
}

/**
 * Get column index by data attribute
 */
function getColumnIndex(table, column) {
    const headers = table.querySelectorAll('th');
    for (let i = 0; i < headers.length; i++) {
        if (headers[i].dataset.sortable === column) {
            return i + 1;
        }
    }
    return 1;
}

/**
 * Image upload with preview
 */
function setupImageUpload(inputSelector, previewSelector) {
    const input = document.querySelector(inputSelector);
    const preview = document.querySelector(previewSelector);

    if (!input || !preview) return;

    input.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            showToast('Please select a valid image file', 'warning');
            return;
        }

        // Validate file size (5MB limit)
        if (file.size > 5 * 1024 * 1024) {
            showToast('Image size must be less than 5MB', 'warning');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
                preview.style.display = 'block';
            } else {
                preview.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" style="max-height: 200px;">`;
            }
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    });
}

/**
 * Initialize rich text editor
 */
function initializeRichTextEditor(selector, options = {}) {
    const element = document.querySelector(selector);
    if (!element) return;

    const defaultOptions = {
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview']]
        ],
        placeholder: 'Start writing...',
        callbacks: {
            onImageUpload: function (files) {
                uploadEditorImage(files[0], this);
            }
        }
    };

    const finalOptions = { ...defaultOptions, ...options };

    if (typeof $ !== 'undefined' && $.fn.summernote) {
        $(element).summernote(finalOptions);
    }
}

/**
 * Upload image for rich text editor
 */
function uploadEditorImage(file, editor) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('action', 'upload_editor_image');

    fetch('/admin/upload-image.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof $ !== 'undefined' && $.fn.summernote) {
                    $(editor).summernote('insertImage', data.url);
                }
            } else {
                showToast('Failed to upload image', 'danger');
            }
        })
        .catch(error => {
            showToast('Error uploading image', 'danger');
        });
}

/**
 * Setup clipboard functionality
 */
function setupClipboard() {
    document.querySelectorAll('[data-clipboard]').forEach(element => {
        element.addEventListener('click', function () {
            const text = this.dataset.clipboard;
            navigator.clipboard.writeText(text).then(() => {
                showToast('Copied to clipboard', 'success');
            }).catch(() => {
                showToast('Failed to copy to clipboard', 'danger');
            });
        });
    });
}

/**
 * Format date for display
 */
function formatDate(dateString, format = 'short') {
    const date = new Date(dateString);
    const options = {
        short: { month: 'short', day: 'numeric', year: 'numeric' },
        long: { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' },
        time: { hour: '2-digit', minute: '2-digit' }
    };

    return date.toLocaleDateString('en-US', options[format] || options.short);
}

/**
 * Debounce function
 */
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
}

/**
 * Throttle function
 */
function throttle(func, limit) {
    let inThrottle;
    return function (...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Export functions for use in other files
window.AdminUtils = {
    showToast,
    confirmDialog,
    submitFormAjax,
    enhanceDataTable,
    setupImageUpload,
    initializeRichTextEditor,
    setupClipboard,
    formatDate,
    generateSlug,
    formatFileSize,
    showLoading,
    hideLoading,
    debounce,
    throttle
};

// Initialize clipboard functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    setupClipboard();
});