<?php
/**
 * Main Dashboard Page for NSSF Uganda Dashboard
 * 
 * @author BARIGYE-DAVIS
 * @version 1.0
 */

session_start();
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../classes/Database.php';

use App\Auth;
use App\Database;

$auth = new Auth();
$auth->requireAuth();

$db = Database::getInstance();
$pageTitle = "Dashboard";

// Get dashboard statistics
try {
    // Get current month stats
    $currentMonth = date('Y-m');
    
    // Total members
    $totalMembers = $db->selectOne("SELECT COUNT(*) as count FROM members WHERE status = 'active'")['count'] ?? 0;
    
    // Total employers
    $totalEmployers = $db->selectOne("SELECT COUNT(*) as count FROM employers WHERE status = 'active'")['count'] ?? 0;
    
    // This month's contributions
    $monthlyContributions = $db->selectOne(
        "SELECT COALESCE(SUM(amount), 0) as total FROM contributions WHERE DATE_FORMAT(contribution_date, '%Y-%m') = ?",
        [$currentMonth]
    )['total'] ?? 0;
    
    // Pending claims
    $pendingClaims = $db->selectOne("SELECT COUNT(*) as count FROM claims WHERE status = 'pending'")['count'] ?? 0;
    
    // This year's total contributions
    $yearlyContributions = $db->selectOne(
        "SELECT COALESCE(SUM(amount), 0) as total FROM contributions WHERE YEAR(contribution_date) = YEAR(CURDATE())"
    )['total'] ?? 0;
    
    // Growth rate (compared to last month)
    $lastMonth = date('Y-m', strtotime('-1 month'));
    $lastMonthContributions = $db->selectOne(
        "SELECT COALESCE(SUM(amount), 0) as total FROM contributions WHERE DATE_FORMAT(contribution_date, '%Y-%m') = ?",
        [$lastMonth]
    )['total'] ?? 0;
    
    $growthRate = 0;
    if ($lastMonthContributions > 0) {
        $growthRate = (($monthlyContributions - $lastMonthContributions) / $lastMonthContributions) * 100;
    }
    
    // Recent activities
    $recentActivities = $db->select(
        "SELECT 'member' as type, CONCAT(first_name, ' ', last_name) as description, created_at 
         FROM members 
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         UNION ALL
         SELECT 'contribution' as type, CONCAT('UGX ', FORMAT(amount, 0), ' contribution') as description, created_at
         FROM contributions 
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         UNION ALL
         SELECT 'claim' as type, CONCAT('Claim #', id, ' submitted') as description, created_at
         FROM claims 
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         ORDER BY created_at DESC 
         LIMIT 10"
    );
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    // Set default values
    $totalMembers = 0;
    $totalEmployers = 0;
    $monthlyContributions = 0;
    $pendingClaims = 0;
    $yearlyContributions = 0;
    $growthRate = 0;
    $recentActivities = [];
}

include '../../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#quickActionsModal">
            <i class="fas fa-plus"></i> Quick Actions
        </button>
    </div>
</div>

<!-- KPI Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Members
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-members">
                            <?php echo number_format($totalMembers); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Monthly Contributions
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="monthly-contributions">
                            UGX <?php echo number_format($monthlyContributions); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-coins fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Active Employers
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-employers">
                            <?php echo number_format($totalEmployers); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending Claims
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-claims">
                            <?php echo number_format($pendingClaims); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Contributions Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Contributions Overview</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow">
                        <div class="dropdown-header">Chart Options:</div>
                        <a class="dropdown-item" href="#" onclick="changeChartPeriod('week')">Last Week</a>
                        <a class="dropdown-item" href="#" onclick="changeChartPeriod('month')">Last Month</a>
                        <a class="dropdown-item" href="#" onclick="changeChartPeriod('year')">Last Year</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="contributionsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Growth Rate Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Growth Rate</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="growthChart" width="300" height="300"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-primary"></i> This Month
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> Last Month
                    </span>
                </div>
                <div class="text-center mt-3">
                    <h4 class="<?php echo $growthRate >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $growthRate >= 0 ? '+' : ''; ?><?php echo number_format($growthRate, 1); ?>%
                    </h4>
                    <p class="text-muted">Growth Rate</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities & Quick Stats -->
<div class="row">
    <!-- Recent Activities -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush" id="recent-activities">
                    <?php if (!empty($recentActivities)): ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <?php if ($activity['type'] === 'member'): ?>
                                            <i class="fas fa-user-plus text-primary"></i>
                                        <?php elseif ($activity['type'] === 'contribution'): ?>
                                            <i class="fas fa-coins text-success"></i>
                                        <?php else: ?>
                                            <i class="fas fa-file-invoice-dollar text-warning"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-0"><?php echo htmlspecialchars($activity['description']); ?></p>
                                        <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent activities</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-primary mb-0">UGX <?php echo number_format($yearlyContributions); ?></h3>
                            <p class="text-muted mb-0">Yearly Collections</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-success mb-0"><?php echo number_format($totalMembers + $totalEmployers); ?></h3>
                            <p class="text-muted mb-0">Total Entities</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-info mb-0" id="avg-contribution">UGX 0</h3>
                            <p class="text-muted mb-0">Avg. Contribution</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-warning mb-0" id="completion-rate">0%</h3>
                            <p class="text-muted mb-0">Completion Rate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Modal -->
<div class="modal fade" id="quickActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="/views/members/create.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-user-plus mb-2"></i><br>
                            Add Member
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="/views/employers/create.php" class="btn btn-outline-success w-100">
                            <i class="fas fa-building mb-2"></i><br>
                            Add Employer
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="/views/contributions/upload.php" class="btn btn-outline-info w-100">
                            <i class="fas fa-upload mb-2"></i><br>
                            Upload Contributions
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="/views/claims/process.php" class="btn btn-outline-warning w-100">
                            <i class="fas fa-file-invoice-dollar mb-2"></i><br>
                            Process Claims
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="/views/reports/builder.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-chart-bar mb-2"></i><br>
                            Generate Report
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="/views/campaigns/create.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-bullhorn mb-2"></i><br>
                            Create Campaign
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Dashboard Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <div class="mb-3">
                        <label for="exportType" class="form-label">Export Type</label>
                        <select class="form-select" id="exportType" name="exportType" required>
                            <option value="">Select Export Type</option>
                            <option value="summary">Dashboard Summary</option>
                            <option value="contributions">Contributions Data</option>
                            <option value="members">Members Data</option>
                            <option value="claims">Claims Data</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="exportFormat" class="form-label">Format</label>
                        <select class="form-select" id="exportFormat" name="exportFormat" required>
                            <option value="">Select Format</option>
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="dateRange" class="form-label">Date Range</label>
                        <input type="text" class="form-control" id="dateRange" name="dateRange" placeholder="Select date range">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="exportData()">Export</button>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.chart-area {
    position: relative;
    height: 300px;
}
.chart-pie {
    position: relative;
    height: 200px;
}
</style>

<script>
let contributionsChart;
let growthChart;

$(document).ready(function() {
    initializeCharts();
    calculateAdditionalStats();
    
    // Initialize date range picker
    $('#dateRange').daterangepicker({
        startDate: moment().subtract(29, 'days'),
        endDate: moment(),
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });
});

function initializeCharts() {
    // Contributions Chart
    const ctx1 = document.getElementById('contributionsChart').getContext('2d');
    contributionsChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Contributions (UGX)',
                data: [],
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'UGX ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Growth Chart
    const ctx2 = document.getElementById('growthChart').getContext('2d');
    growthChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['This Month', 'Last Month'],
            datasets: [{
                data: [<?php echo $monthlyContributions; ?>, <?php echo $lastMonthContributions; ?>],
                backgroundColor: ['#4e73df', '#1cc88a'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Load chart data
    loadContributionsData('month');
}

function loadContributionsData(period = 'month') {
    $.get('/api/dashboard-charts.php', { period: period })
        .done(function(response) {
            if (response.success && contributionsChart) {
                contributionsChart.data.labels = response.data.labels;
                contributionsChart.data.datasets[0].data = response.data.values;
                contributionsChart.update();
            }
        });
}

function changeChartPeriod(period) {
    loadContributionsData(period);
}

function calculateAdditionalStats() {
    // Calculate average contribution
    const totalMembers = <?php echo $totalMembers; ?>;
    const monthlyContributions = <?php echo $monthlyContributions; ?>;
    
    if (totalMembers > 0) {
        const avgContribution = monthlyContributions / totalMembers;
        $('#avg-contribution').text(formatCurrency(avgContribution));
    }
    
    // Calculate completion rate (example: contributions vs expected)
    const expectedContributions = totalMembers * 50000; // Assuming 50k average
    const completionRate = expectedContributions > 0 ? (monthlyContributions / expectedContributions) * 100 : 0;
    $('#completion-rate').text(Math.min(completionRate, 100).toFixed(1) + '%');
}

function refreshDashboard() {
    location.reload();
}

function exportData() {
    const form = $('#exportForm')[0];
    const formData = new FormData(form);
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: '/api/export.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhrFields: {
            responseType: 'blob'
        },
        success: function(data, status, xhr) {
            hideLoading();
            
            // Create download link
            const blob = new Blob([data]);
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            
            // Get filename from response header
            const disposition = xhr.getResponseHeader('Content-Disposition');
            let filename = 'export.pdf';
            if (disposition && disposition.indexOf('filename=') !== -1) {
                filename = disposition.split('filename=')[1].replace(/"/g, '');
            }
            
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            
            window.URL.revokeObjectURL(url);
            document.body.removeChild(link);
            
            $('#exportModal').modal('hide');
            showToast('Export completed successfully', 'success');
        },
        error: function() {
            hideLoading();
            showToast('Export failed. Please try again.', 'danger');
        }
    });
}
</script>

<?php include '../../includes/footer.php'; ?>