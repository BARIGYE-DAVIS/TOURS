/**
 * Main Dashboard JavaScript for NSSF Uganda Dashboard
 * 
 * @author BARIGYE-DAVIS
 * @version 1.0
 */

// Global Dashboard Object
window.NSSFDashboard = {
    charts: {},
    config: {
        refreshInterval: 30000, // 30 seconds
        chartColors: {
            primary: '#1e3a8a',
            secondary: '#3b82f6',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#06b6d4',
            light: '#f8fafc',
            dark: '#1f2937'
        }
    },
    
    // Initialize dashboard
    init: function() {
        this.setupEventListeners();
        this.initializeComponents();
        this.startAutoRefresh();
        
        console.log('NSSF Dashboard initialized');
    },
    
    // Setup event listeners
    setupEventListeners: function() {
        // Window resize handler for charts
        $(window).on('resize', this.handleResize.bind(this));
        
        // Form submissions
        $(document).on('submit', 'form[data-ajax="true"]', this.handleAjaxForm.bind(this));
        
        // Data table actions
        $(document).on('click', '[data-action="refresh"]', this.refreshData.bind(this));
        $(document).on('click', '[data-action="export"]', this.exportData.bind(this));
        
        // Filter changes
        $(document).on('change', '.dashboard-filter', this.handleFilterChange.bind(this));
        
        // Chart period changes
        $(document).on('click', '[data-chart-period]', this.changeChartPeriod.bind(this));
    },
    
    // Initialize components
    initializeComponents: function() {
        this.initDataTables();
        this.initSelect2();
        this.initDatePickers();
        this.initTooltips();
        this.initCharts();
    },
    
    // Initialize DataTables
    initDataTables: function() {
        if ($.fn.DataTable) {
            $('.data-table').each(function() {
                const $table = $(this);
                const config = {
                    responsive: true,
                    pageLength: 25,
                    order: [[0, 'desc']], // Default order by first column descending
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search records...",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "No entries to show",
                        infoFiltered: "(filtered from _MAX_ total entries)",
                        zeroRecords: "No matching records found",
                        emptyTable: "No data available in table",
                        paginate: {
                            first: '<i class="fas fa-angle-double-left"></i>',
                            previous: '<i class="fas fa-angle-left"></i>',
                            next: '<i class="fas fa-angle-right"></i>',
                            last: '<i class="fas fa-angle-double-right"></i>'
                        }
                    },
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                         '<"row"<"col-sm-12"tr>>' +
                         '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    drawCallback: function() {
                        // Re-initialize tooltips after table redraw
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }
                };
                
                // Merge with any custom config
                const customConfig = $table.data('config');
                if (customConfig) {
                    $.extend(true, config, customConfig);
                }
                
                $table.DataTable(config);
            });
        }
    },
    
    // Initialize Select2
    initSelect2: function() {
        if ($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder') || 'Choose...';
                },
                allowClear: true
            });
        }
    },
    
    // Initialize date pickers
    initDatePickers: function() {
        if ($.fn.daterangepicker) {
            $('.daterange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'This Quarter': [moment().startOf('quarter'), moment().endOf('quarter')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')]
                }
            });
            
            $('.daterange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });
            
            $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        }
    },
    
    // Initialize tooltips
    initTooltips: function() {
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    boundary: 'viewport'
                });
            });
        }
    },
    
    // Initialize charts
    initCharts: function() {
        // This will be extended by specific chart implementations
        if (typeof Chart !== 'undefined') {
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = this.config.chartColors.dark;
            Chart.defaults.plugins.legend.labels.usePointStyle = true;
        }
    },
    
    // Handle window resize
    handleResize: function() {
        // Resize charts
        Object.keys(this.charts).forEach(chartId => {
            if (this.charts[chartId] && typeof this.charts[chartId].resize === 'function') {
                this.charts[chartId].resize();
            }
        });
        
        // Redraw DataTables
        $('.data-table').each(function() {
            const table = $(this).DataTable();
            if (table) {
                table.columns.adjust().responsive.recalc();
            }
        });
    },
    
    // Handle AJAX forms
    handleAjaxForm: function(e) {
        e.preventDefault();
        
        const $form = $(e.target);
        const url = $form.attr('action') || window.location.href;
        const method = $form.attr('method') || 'POST';
        const formData = new FormData($form[0]);
        
        // Add CSRF token
        formData.append('_token', window.NSSF.csrf_token);
        
        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showToast(response.message || 'Operation completed successfully', 'success');
                    
                    // Handle redirects
                    if (response.redirect) {
                        window.location.href = response.redirect;
                        return;
                    }
                    
                    // Refresh data if needed
                    if (response.refresh) {
                        location.reload();
                    }
                } else {
                    showToast(response.message || 'Operation failed', 'danger');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showToast(message, 'danger');
            }
        });
    },
    
    // Refresh data
    refreshData: function(e) {
        e.preventDefault();
        
        const $btn = $(e.target).closest('[data-action="refresh"]');
        const target = $btn.data('target') || 'page';
        
        if (target === 'page') {
            location.reload();
        } else {
            // Refresh specific component
            this.refreshComponent(target);
        }
    },
    
    // Refresh specific component
    refreshComponent: function(target) {
        switch (target) {
            case 'stats':
                this.loadDashboardStats();
                break;
            case 'charts':
                this.refreshCharts();
                break;
            case 'tables':
                this.refreshTables();
                break;
            default:
                console.warn('Unknown refresh target:', target);
        }
    },
    
    // Load dashboard statistics
    loadDashboardStats: function() {
        $.get('/api/dashboard-stats.php')
            .done(response => {
                if (response.success) {
                    this.updateStats(response.data);
                }
            })
            .fail(() => {
                console.error('Failed to load dashboard stats');
            });
    },
    
    // Update statistics display
    updateStats: function(data) {
        Object.keys(data).forEach(key => {
            const $element = $(`[data-stat="${key}"]`);
            if ($element.length) {
                const value = data[key];
                if (typeof value === 'number') {
                    $element.text(this.formatNumber(value));
                } else {
                    $element.text(value);
                }
            }
        });
    },
    
    // Refresh charts
    refreshCharts: function() {
        Object.keys(this.charts).forEach(chartId => {
            const chart = this.charts[chartId];
            if (chart && chart.config && chart.config.refresh) {
                chart.config.refresh();
            }
        });
    },
    
    // Refresh tables
    refreshTables: function() {
        $('.data-table').each(function() {
            const table = $(this).DataTable();
            if (table && table.ajax) {
                table.ajax.reload(null, false);
            }
        });
    },
    
    // Handle filter changes
    handleFilterChange: function(e) {
        const $filter = $(e.target);
        const filterType = $filter.data('filter');
        const filterValue = $filter.val();
        
        // Apply filter logic based on type
        this.applyFilter(filterType, filterValue);
    },
    
    // Apply filters
    applyFilter: function(type, value) {
        // Implement filter logic based on type
        console.log('Applying filter:', type, value);
        
        // Update URL parameters
        const url = new URL(window.location);
        if (value) {
            url.searchParams.set(type, value);
        } else {
            url.searchParams.delete(type);
        }
        history.replaceState(null, '', url);
        
        // Refresh relevant components
        this.refreshComponent('charts');
        this.refreshComponent('tables');
    },
    
    // Change chart period
    changeChartPeriod: function(e) {
        e.preventDefault();
        
        const $btn = $(e.target).closest('[data-chart-period]');
        const period = $btn.data('chart-period');
        const chartId = $btn.data('chart-id');
        
        if (chartId && this.charts[chartId]) {
            this.updateChartPeriod(chartId, period);
        }
        
        // Update active state
        $btn.siblings().removeClass('active');
        $btn.addClass('active');
    },
    
    // Update chart period
    updateChartPeriod: function(chartId, period) {
        const chart = this.charts[chartId];
        if (chart && chart.config && chart.config.updatePeriod) {
            chart.config.updatePeriod(period);
        }
    },
    
    // Export data
    exportData: function(e) {
        e.preventDefault();
        
        const $btn = $(e.target).closest('[data-action="export"]');
        const exportType = $btn.data('export-type') || 'csv';
        const exportTarget = $btn.data('export-target') || 'current';
        
        // Show export modal or directly export
        if ($btn.data('modal')) {
            $(btn.data('modal')).modal('show');
        } else {
            this.performExport(exportType, exportTarget);
        }
    },
    
    // Perform export
    performExport: function(type, target) {
        const params = new URLSearchParams();
        params.append('type', type);
        params.append('target', target);
        params.append('_token', window.NSSF.csrf_token);
        
        // Add current filters
        const url = new URL(window.location);
        url.searchParams.forEach((value, key) => {
            params.append(key, value);
        });
        
        // Create download link
        const downloadUrl = `/api/export.php?${params.toString()}`;
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `export_${Date.now()}.${type}`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    },
    
    // Start auto-refresh
    startAutoRefresh: function() {
        if (this.config.refreshInterval > 0) {
            setInterval(() => {
                this.loadDashboardStats();
            }, this.config.refreshInterval);
        }
    },
    
    // Utility functions
    formatNumber: function(number) {
        return new Intl.NumberFormat('en-UG').format(number);
    },
    
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('en-UG', {
            style: 'currency',
            currency: 'UGX',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    },
    
    formatDate: function(date) {
        return new Intl.DateTimeFormat('en-UG', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }).format(new Date(date));
    },
    
    formatDateTime: function(date) {
        return new Intl.DateTimeFormat('en-UG', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(date));
    }
};

// Initialize dashboard when document is ready
$(document).ready(function() {
    window.NSSFDashboard.init();
});

// Global utility functions
window.showLoading = function() {
    $('#loading-overlay').removeClass('d-none');
};

window.hideLoading = function() {
    $('#loading-overlay').addClass('d-none');
};

window.showToast = function(message, type = 'info') {
    const toastId = 'toast_' + Date.now();
    const bgClass = type === 'danger' ? 'bg-danger' : type === 'success' ? 'bg-success' : type === 'warning' ? 'bg-warning' : 'bg-info';
    
    const toast = $(`
        <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" id="${toastId}">
            <div class="toast-header ${bgClass} text-white">
                <strong class="me-auto">NSSF Uganda</strong>
                <small class="text-white-50">${new Date().toLocaleTimeString()}</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `);
    
    $('#toast-container').append(toast);
    const bsToast = new bootstrap.Toast(toast[0], {
        autohide: true,
        delay: 5000
    });
    bsToast.show();
    
    // Remove toast after it's hidden
    toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
};

window.confirmAction = function(message, callback) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1e3a8a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
        });
    } else {
        if (confirm(message) && typeof callback === 'function') {
            callback();
        }
    }
};