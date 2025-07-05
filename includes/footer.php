            </main>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-white border-top mt-5">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        &copy; <?php echo date('Y'); ?> NSSF Uganda Dashboard. 
                        <span class="text-primary">Version 1.0.0</span>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        Built with <i class="fas fa-heart text-danger"></i> by 
                        <a href="#" class="text-decoration-none text-primary">BARIGYE-DAVIS</a>
                    </small>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none" style="z-index: 9999;">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container"></div>
    
    <!-- JavaScript Libraries -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    
    <!-- DateRangePicker JS -->
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="/assets/js/dashboard.js"></script>
    
    <script>
        // Global JavaScript Configuration
        window.NSSF = {
            csrf_token: '<?php echo $_SESSION['csrf_token'] ?? ''; ?>',
            user_id: <?php echo $_SESSION['user_id'] ?? 'null'; ?>,
            base_url: '<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; ?>',
            api_url: '<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; ?>/api',
            debug: <?php echo (isset($config) && $config['debug']) ? 'true' : 'false'; ?>
        };
        
        // Common functions
        function showLoading() {
            $('#loading-overlay').removeClass('d-none');
        }
        
        function hideLoading() {
            $('#loading-overlay').addClass('d-none');
        }
        
        function showToast(message, type = 'info') {
            const toast = $(`
                <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-${type} text-white">
                        <strong class="me-auto">NSSF Uganda</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `);
            
            $('#toast-container').append(toast);
            new bootstrap.Toast(toast[0]).show();
            
            // Remove toast after it's hidden
            toast.on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }
        
        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-UG', {
                style: 'currency',
                currency: 'UGX',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }
        
        function formatNumber(number) {
            return new Intl.NumberFormat('en-UG').format(number);
        }
        
        // AJAX setup with CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': window.NSSF.csrf_token
            },
            beforeSend: function() {
                showLoading();
            },
            complete: function() {
                hideLoading();
            },
            error: function(xhr, status, error) {
                hideLoading();
                let message = 'An error occurred. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 401) {
                    message = 'Session expired. Please login again.';
                    window.location.href = '/views/auth/login.php';
                    return;
                } else if (xhr.status === 403) {
                    message = 'Access denied. You do not have permission to perform this action.';
                } else if (xhr.status === 500) {
                    message = 'Server error. Please contact support if the problem persists.';
                }
                
                showToast(message, 'danger');
            }
        });
        
        // Initialize common components
        $(document).ready(function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Initialize popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
            
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5'
            });
            
            // Initialize DataTables with default settings
            $('.data-table').DataTable({
                responsive: true,
                pageLength: 25,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        previous: '<i class="fas fa-angle-left"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>'
                    }
                }
            });
            
            // Load notifications
            loadNotifications();
            
            // Auto-refresh notifications every 5 minutes
            setInterval(loadNotifications, 300000);
        });
        
        function loadNotifications() {
            $.get('/api/notifications.php')
                .done(function(response) {
                    if (response.success) {
                        updateNotificationDropdown(response.data);
                    }
                })
                .fail(function() {
                    // Silently fail for notifications
                });
        }
        
        function updateNotificationDropdown(notifications) {
            const count = notifications.length;
            const countBadge = $('#notification-count');
            const notificationList = $('#notification-list');
            
            if (count > 0) {
                countBadge.text(count).removeClass('d-none');
                
                let html = '';
                notifications.slice(0, 5).forEach(function(notification) {
                    html += `
                        <li>
                            <a class="dropdown-item py-2" href="${notification.url || '#'}">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas ${notification.icon || 'fa-info-circle'} text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <p class="mb-0 small">${notification.message}</p>
                                        <small class="text-muted">${moment(notification.created_at).fromNow()}</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                    `;
                });
                
                notificationList.html(html);
            } else {
                countBadge.addClass('d-none');
                notificationList.html('<div class="dropdown-item text-muted">No new notifications</div>');
            }
        }
    </script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>