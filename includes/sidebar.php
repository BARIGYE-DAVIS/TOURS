<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <div class="rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                <i class="fas fa-shield-alt fa-2x"></i>
            </div>
            <h6 class="text-white mt-2 mb-0">NSSF Uganda</h6>
            <small class="text-white-50">Dashboard v1.0</small>
        </div>
        
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false) ? 'active' : ''; ?>" href="/views/dashboard/">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            
            <!-- Members -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/members') !== false) ? 'active' : ''; ?>" href="/views/members/">
                    <i class="fas fa-users me-2"></i>
                    Members
                    <span class="badge bg-light text-dark ms-auto" id="members-count">0</span>
                </a>
            </li>
            
            <!-- Employers -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/employers') !== false) ? 'active' : ''; ?>" href="/views/employers/">
                    <i class="fas fa-building me-2"></i>
                    Employers
                    <span class="badge bg-light text-dark ms-auto" id="employers-count">0</span>
                </a>
            </li>
            
            <!-- Contributions -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/contributions') !== false) ? 'active' : ''; ?>" href="/views/contributions/">
                    <i class="fas fa-coins me-2"></i>
                    Contributions
                </a>
            </li>
            
            <!-- Claims -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/claims') !== false) ? 'active' : ''; ?>" href="/views/claims/">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Claims
                    <span class="badge bg-warning text-dark ms-auto" id="pending-claims">0</span>
                </a>
            </li>
            
            <!-- Reports -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/reports') !== false) ? 'active' : ''; ?>" href="/views/reports/">
                    <i class="fas fa-chart-bar me-2"></i>
                    Reports
                </a>
            </li>
            
            <!-- Analytics -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/analytics') !== false) ? 'active' : ''; ?>" href="/views/analytics/">
                    <i class="fas fa-chart-line me-2"></i>
                    Analytics
                </a>
            </li>
            
            <!-- Campaigns -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/campaigns') !== false) ? 'active' : ''; ?>" href="/views/campaigns/">
                    <i class="fas fa-bullhorn me-2"></i>
                    Campaigns
                </a>
            </li>
            
            <hr class="border-light">
            
            <!-- Admin Section (Only for admins) -->
            <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin'])): ?>
                <li class="nav-item">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white-50">
                        <span>Administration</span>
                    </h6>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/users') !== false) ? 'active' : ''; ?>" href="/views/users/">
                        <i class="fas fa-user-cog me-2"></i>
                        Users
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/settings') !== false) ? 'active' : ''; ?>" href="/views/settings/">
                        <i class="fas fa-cogs me-2"></i>
                        Settings
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/audit') !== false) ? 'active' : ''; ?>" href="/views/audit/">
                        <i class="fas fa-history me-2"></i>
                        Audit Trail
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/system') !== false) ? 'active' : ''; ?>" href="/views/system/">
                        <i class="fas fa-server me-2"></i>
                        System Status
                    </a>
                </li>
            <?php endif; ?>
            
            <hr class="border-light">
            
            <!-- Help & Support -->
            <li class="nav-item">
                <a class="nav-link" href="/views/help/">
                    <i class="fas fa-question-circle me-2"></i>
                    Help & Support
                </a>
            </li>
            
            <!-- Documentation -->
            <li class="nav-item">
                <a class="nav-link" href="/docs/" target="_blank">
                    <i class="fas fa-book me-2"></i>
                    Documentation
                    <i class="fas fa-external-link-alt ms-auto" style="font-size: 0.8em;"></i>
                </a>
            </li>
        </ul>
        
        <!-- Quick Stats -->
        <div class="mt-4 p-3 bg-white bg-opacity-10 rounded">
            <h6 class="text-white mb-2">Quick Stats</h6>
            <div class="d-flex justify-content-between text-white-50 small">
                <span>Today's Collections:</span>
                <span id="today-collections">UGX 0</span>
            </div>
            <div class="d-flex justify-content-between text-white-50 small">
                <span>Active Members:</span>
                <span id="active-members">0</span>
            </div>
            <div class="d-flex justify-content-between text-white-50 small">
                <span>Pending Claims:</span>
                <span id="sidebar-pending-claims">0</span>
            </div>
        </div>
        
        <!-- System Status -->
        <div class="mt-3 p-2 text-center">
            <div class="d-flex align-items-center justify-content-center text-white-50 small">
                <div class="status-indicator bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                <span>System Online</span>
            </div>
            <div class="text-white-50 small mt-1">
                Last Updated: <span id="last-updated"><?php echo date('H:i'); ?></span>
            </div>
        </div>
    </div>
</nav>

<script>
$(document).ready(function() {
    // Load sidebar statistics
    loadSidebarStats();
    
    // Update statistics every 30 seconds
    setInterval(loadSidebarStats, 30000);
    
    // Update last updated time every minute
    setInterval(function() {
        $('#last-updated').text(moment().format('HH:mm'));
    }, 60000);
});

function loadSidebarStats() {
    $.get('/api/dashboard-stats.php')
        .done(function(response) {
            if (response.success) {
                const data = response.data;
                
                // Update counts
                $('#members-count').text(formatNumber(data.total_members || 0));
                $('#employers-count').text(formatNumber(data.total_employers || 0));
                $('#pending-claims').text(data.pending_claims || 0);
                $('#sidebar-pending-claims').text(data.pending_claims || 0);
                
                // Update quick stats
                $('#today-collections').text(formatCurrency(data.today_collections || 0));
                $('#active-members').text(formatNumber(data.active_members || 0));
                
                // Update system status
                if (data.system_status === 'online') {
                    $('.status-indicator').removeClass('bg-warning bg-danger').addClass('bg-success');
                } else if (data.system_status === 'maintenance') {
                    $('.status-indicator').removeClass('bg-success bg-danger').addClass('bg-warning');
                } else {
                    $('.status-indicator').removeClass('bg-success bg-warning').addClass('bg-danger');
                }
            }
        })
        .fail(function() {
            // Silently fail for sidebar stats
        });
}
</script>