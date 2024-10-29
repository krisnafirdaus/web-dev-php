<!-- index.php -->
<?php
define('ALLOWED_ACCESS', true);
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Initialize Auth class
$auth = new Auth();
$currentUser = $auth->getCurrentUser();

if ($currentUser) {
    // Set page title
    $pageTitle = 'Dashboard';
    require_once 'includes/header.php';
    ?>

    <div class="container py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Welcome, <?php echo htmlspecialchars($currentUser['name']); ?>!</h4>
                        <p class="card-text">This is your dashboard. Here's an overview of your system.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <!-- Total Users Card -->
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Users</h6>
                                <h2 class="mb-0" id="totalUsers">0</h2>
                            </div>
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="users/list.php" class="text-white text-decoration-none">View Details</a>
                        <i class="fas fa-angle-right"></i>
                    </div>
                </div>
            </div>

            <!-- Active Users Card -->
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Active Users</h6>
                                <h2 class="mb-0" id="activeUsers">0</h2>
                            </div>
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="users/active.php" class="text-white text-decoration-none">View Details</a>
                        <i class="fas fa-angle-right"></i>
                    </div>
                </div>
            </div>

            <!-- Recent Activities Card -->
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Recent Activities</h6>
                                <h2 class="mb-0" id="recentActivities">0</h2>
                            </div>
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="activities/log.php" class="text-white text-decoration-none">View Details</a>
                        <i class="fas fa-angle-right"></i>
                    </div>
                </div>
            </div>

            <!-- System Status Card -->
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">System Status</h6>
                                <h2 class="mb-0">Active</h2>
                            </div>
                            <i class="fas fa-server fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="system/status.php" class="text-white text-decoration-none">View Details</a>
                        <i class="fas fa-angle-right"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users Table -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Users</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="recentUsersTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom JavaScript for Dashboard -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load dashboard statistics
        loadDashboardStats();
        // Load recent users
        loadRecentUsers();
    });

    function loadDashboardStats() {
        fetch('api/dashboard/stats.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('totalUsers').textContent = data.totalUsers;
                document.getElementById('activeUsers').textContent = data.activeUsers;
                document.getElementById('recentActivities').textContent = data.recentActivities;
            })
            .catch(error => console.error('Error loading dashboard stats:', error));
    }

    function loadRecentUsers() {
        fetch('api/users/recent.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#recentUsersTable tbody');
                tbody.innerHTML = '';
                
                data.forEach(user => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(user.name)}</td>
                        <td>${escapeHtml(user.email)}</td>
                        <td><span class="badge bg-${user.role === 'admin' ? 'danger' : 'primary'}">${user.role}</span></td>
                        <td><span class="badge bg-${getStatusBadgeColor(user.status)}">${user.status}</span></td>
                        <td>${formatDate(user.created_at)}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewUser(${user.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editUser(${user.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            })
            .catch(error => console.error('Error loading recent users:', error));
    }

    function getStatusBadgeColor(status) {
        switch(status) {
            case 'active': return 'success';
            case 'inactive': return 'warning';
            case 'banned': return 'danger';
            default: return 'secondary';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }

    // User action functions
    function viewUser(id) {
        window.location.href = `users/view.php?id=${id}`;
    }

    function editUser(id) {
        window.location.href = `users/edit.php?id=${id}`;
    }

    function deleteUser(id) {
        if (confirm('Are you sure you want to delete this user?')) {
            fetch(`api/users/delete.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadRecentUsers();
                    showToast('User deleted successfully', 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while deleting the user', 'error');
            });
        }
    }

    function showToast(message, type = 'info') {
        // Implement your toast notification here
    }
    </script>

    <?php require_once 'includes/footer.php'; ?>
<?php
} else {
    // Redirect to login
    header('Location: login.php');
    exit();
}