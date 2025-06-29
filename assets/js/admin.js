/**
 * ECCT Admin Panel JavaScript
 * Handles sidebar functionality, notifications, and UI interactions
 */

document.addEventListener('DOMContentLoaded', function () {

    // ===== Sidebar Functionality =====
    const sidebar = document.getElementById('sidebar');
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebarToggle = document.getElementById('sidebarToggle');

    // Sidebar collapse/expand for desktop
    if (sidebarCollapse) {
        sidebarCollapse.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');

            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebar-collapsed', isCollapsed);

            // Update collapse button icon
            const icon = sidebarCollapse.querySelector('i');
            if (isCollapsed) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-arrows-alt-h');
            } else {
                icon.classList.remove('fa-arrows-alt-h');
                icon.classList.add('fa-bars');
            }
        });
    }

    // Mobile sidebar toggle
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function () {
            sidebar.classList.add('show');
            document.body.classList.add('sidebar-open');
        });
    }

    // Close sidebar on mobile
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 991) {
            if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        }
    });

    // Restore sidebar state on page load
    const savedState = localStorage.getItem('sidebar-collapsed');
    if (savedState === 'true' && window.innerWidth > 991) {
        sidebar.classList.add('collapsed');
        if (sidebarCollapse) {
            const icon = sidebarCollapse.querySelector('i');
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-arrows-alt-h');
        }
    }

    // Handle window resize
    window.addEventListener('resize', function () {
        if (window.innerWidth > 991) {
            sidebar.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        } else {
            sidebar.classList.remove('collapsed');
        }
    });

    // ===== Submenu Functionality =====
    const submenuToggles = document.querySelectorAll('.has-submenu');

    submenuToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            // Don't collapse if sidebar is collapsed
            if (sidebar.classList.contains('collapsed')) {
                return;
            }

            e.preventDefault();

            const submenu = document.querySelector(toggle.getAttribute('data-bs-target'));
            const arrow = toggle.querySelector('.submenu-arrow');

            if (submenu) {
                // Close other submenus
                submenuToggles.forEach(function (otherToggle) {
                    if (otherToggle !== toggle) {
                        const otherSubmenu = document.querySelector(otherToggle.getAttribute('data-bs-target'));
                        const otherArrow = otherToggle.querySelector('.submenu-arrow');

                        if (otherSubmenu && otherSubmenu.classList.contains('show')) {
                            otherSubmenu.classList.remove('show');
                            otherToggle.setAttribute('aria-expanded', 'false');
                            if (otherArrow) {
                                otherArrow.style.transform = 'rotate(0deg)';
                            }
                        }
                    }
                });

                // Toggle current submenu
                submenu.classList.toggle('show');
                const isExpanded = submenu.classList.contains('show');
                toggle.setAttribute('aria-expanded', isExpanded);

                if (arrow) {
                    arrow.style.transform = isExpanded ? 'rotate(90deg)' : 'rotate(0deg)';
                }
            }
        });
    });

    // ===== Auto-dismiss Alerts =====
    const alerts = document.querySelectorAll('.alert:not(.alert-persistent)');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            if (alert && alert.parentNode) {
                alert.classList.remove('show');
                setTimeout(function () {
                    if (alert && alert.parentNode) {
                        alert.remove();
                    }
                }, 300);
            }
        }, 5000);
    });

    // ===== Form Validation =====
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // ===== DataTables Initialization =====
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.data-table').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }

    // ===== Tooltips Initialization =====
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // ===== Confirmation Dialogs =====
    const confirmLinks = document.querySelectorAll('[data-confirm]');
    confirmLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const message = this.getAttribute('data-confirm') || 'Are you sure?';

            if (confirm(message)) {
                window.location.href = this.href;
            }
        });
    });

    // ===== AJAX Form Submissions =====
    const ajaxForms = document.querySelectorAll('.ajax-form');
    ajaxForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

            fetch(form.action, {
                method: form.method,
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', data.message);
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1000);
                        }
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    showNotification('error', 'An error occurred. Please try again.');
                    console.error('Error:', error);
                })
                .finally(() => {
                    // Restore button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
        });
    });

    // ===== Image Preview =====
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            const file = this.files[0];
            const previewId = this.getAttribute('data-preview');

            if (file && previewId) {
                const preview = document.getElementById(previewId);
                if (preview) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    });

    // ===== Character Counter =====
    const textareas = document.querySelectorAll('textarea[data-max-length]');
    textareas.forEach(function (textarea) {
        const maxLength = parseInt(textarea.getAttribute('data-max-length'));
        const counter = document.createElement('div');
        counter.className = 'form-text text-end';
        textarea.parentNode.appendChild(counter);

        function updateCounter() {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = `${textarea.value.length}/${maxLength} characters`;
            counter.className = remaining < 50 ? 'form-text text-end text-warning' : 'form-text text-end text-muted';
        }

        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });

    // ===== Bulk Actions =====
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkActionBtn = document.getElementById('bulkActionBtn');

    if (selectAllCheckbox && itemCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function () {
            itemCheckboxes.forEach(function (checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateBulkActionButton();
        });

        itemCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);

                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;

                updateBulkActionButton();
            });
        });

        function updateBulkActionButton() {
            const checkedCount = Array.from(itemCheckboxes).filter(cb => cb.checked).length;
            if (bulkActionBtn) {
                bulkActionBtn.disabled = checkedCount === 0;
                bulkActionBtn.textContent = `Bulk Actions (${checkedCount})`;
            }
        }
    }

    // ===== Auto-save Draft =====
    const draftForms = document.querySelectorAll('.auto-save');
    draftForms.forEach(function (form) {
        const formId = form.id;
        let saveTimeout;

        // Load saved draft
        const savedData = localStorage.getItem(`draft_${formId}`);
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                Object.keys(data).forEach(function (key) {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field) {
                        field.value = data[key];
                    }
                });
            } catch (e) {
                console.error('Error loading draft:', e);
            }
        }

        // Auto-save on input
        form.addEventListener('input', function () {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function () {
                const formData = new FormData(form);
                const data = {};

                formData.forEach(function (value, key) {
                    data[key] = value;
                });

                localStorage.setItem(`draft_${formId}`, JSON.stringify(data));
                showNotification('info', 'Draft saved automatically', 2000);
            }, 2000);
        });

        // Clear draft on successful submit
        form.addEventListener('submit', function () {
            localStorage.removeItem(`draft_${formId}`);
        });
    });

    // ===== Notification System =====
    function showNotification(type, message, duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';

        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto-remove notification
        setTimeout(function () {
            if (notification && notification.parentNode) {
                notification.classList.remove('show');
                setTimeout(function () {
                    if (notification && notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }
        }, duration);
    }

    // ===== Search Functionality =====
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(function (input) {
        let searchTimeout;

        input.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.toLowerCase();
            const targetSelector = this.getAttribute('data-target');
            const targets = document.querySelectorAll(targetSelector);

            searchTimeout = setTimeout(function () {
                targets.forEach(function (target) {
                    const text = target.textContent.toLowerCase();
                    const shouldShow = text.includes(searchTerm) || searchTerm === '';
                    target.style.display = shouldShow ? '' : 'none';
                });
            }, 300);
        });
    });

    // ===== Statistics Animation =====
    const statNumbers = document.querySelectorAll('.stat-number');

    function animateNumbers() {
        statNumbers.forEach(function (stat) {
            const target = parseInt(stat.getAttribute('data-target') || stat.textContent);
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;

            const timer = setInterval(function () {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                stat.textContent = Math.floor(current).toLocaleString();
            }, 16);
        });
    }

    // Trigger animation when stats come into view
    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                animateNumbers();
                observer.disconnect();
            }
        });
    });

    if (statNumbers.length > 0) {
        observer.observe(statNumbers[0].closest('.container, .container-fluid') || statNumbers[0]);
    }

    // ===== Global Functions =====
    window.adminPanel = {
        showNotification: showNotification,

        confirmAction: function (message, callback) {
            if (confirm(message)) {
                callback();
            }
        },

        toggleSidebar: function () {
            sidebar.classList.toggle('collapsed');
        },

        reloadPage: function () {
            window.location.reload();
        }
    };

    // ===== Performance Monitoring =====
    if (window.performance && window.performance.timing) {
        window.addEventListener('load', function () {
            const loadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
            console.log(`Page loaded in ${loadTime}ms`);

            // Log slow page loads
            if (loadTime > 3000) {
                console.warn('Slow page load detected:', loadTime + 'ms');
            }
        });
    }
});

// ===== Service Worker Registration (Optional) =====
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/admin/sw.js')
            .then(function (registration) {
                console.log('ServiceWorker registration successful');
            })
            .catch(function (err) {
                console.log('ServiceWorker registration failed');
            });
    });
}