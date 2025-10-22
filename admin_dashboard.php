<?php
session_start();

// التحقق من الصلاحيات
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include 'php/db.php';
// include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المدير - مركز خدمة المواطن</title>
    <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="images/logo/logo.ico">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            text-align: right;
        }
        
        .admin-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #1f3c88 0%, #2c5aa0 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .admin-navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 15px;
            margin: 20px;
        }
        
        .nav-item {
            margin: 0 10px;
        }
        
        .nav-link {
            color: black !important;
            padding: 10px 20px !important;
            border-radius: 25px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .nav-link-danger {
            color: white !important;
            padding: 10px 20px !important;
            border-radius: 25px;
            transition: all 0.3s ease;
            text-decoration: none !important;
            display: inline-block;
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .nav-link-danger:hover {
            background: rgba(240, 7, 7, 0.7);
            transform: translateY(-2px);
        }
        
        .nav-link.active {
            background: rgba(255, 255, 255, 0.3);
            font-weight: bold;
        }
        
        .dashboard-content {
            flex: 1;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .stat-card.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-card.success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            color: white;
        }
        
        .stat-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .stat-card.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .stat-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .charts-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .chart-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        
        .windows-status {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .window-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .window-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .window-status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .status-serving {
            background: #ffc107;
            color: #000;
        }
        
        .status-available {
            background: #28a745;
            color: white;
        }
        
        .status-closed {
            background: #dc3545;
            color: white;
        }
        
        .refresh-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        
        .loading {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-navbar {
                margin: 10px;
                padding: 10px;
            }
            
            .nav-item {
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <div class="admin-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-10">
                        <h1 class="mb-0">لوحة تحكم المدير</h1>
                        <p class="mb-0">مرحباً، <?php echo $_SESSION['username']; ?></p>
                    </div>
                    <div class="col-md-2 text-end">
                        <a href="php/logout_function.php" style="color: red !important;" class="btn btn-outline-dark">تسجيل الخروج</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="admin-navbar">
            <div class="container">
                <nav class="nav justify-content-around">
                    <a href="admin_dashboard.php" class="nav-link active">الرئيسية</a>
                    <a href="settings.php" class="nav-link">الإعدادات</a>
                    <a href="statistics.php" class="nav-link">الإحصائيات</a>
                    <a href="services.php" class="nav-link">الخدمات</a>
                    <a href="index_screens.php" class="nav-link">شاشات Dot-matrex</a>
                    <a href="users.php" class="nav-link">المستخدمين</a>
                    <a href="display.php" class="nav-link">شاشة العرض</a>
                    <a href="php/complete_all.php" class="btn-danger nav-link-danger">إعادة ضبط الحالات الدور</a>
                    <!-- <a href="php/logout_function.php" class="nav-link">تسجيل خروج</a> -->
                </nav>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <div class="container">
                <!-- Statistics Cards -->
                <div class="stats-grid" id="statsGrid">
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        جاري تحميل الإحصائيات...
                    </div>
                </div>

                <!-- Charts Section -->
                <div hidden class="charts-section">
                    <div class="chart-card">
                        <div class="chart-title">توزيع الأدوار حسب الحالة</div>
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="chart-card">
                        <div class="chart-title">الأدوار حسب الشباك</div>
                        <canvas id="windowsChart"></canvas>
                    </div>
                </div>

                <!-- Windows Status -->
                <div class="windows-status">
                    <h3 class="mb-4">حالة الشبابيك</h3>
                    <div id="windowsList">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            جاري تحميل حالة الشبابيك...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Refresh Button -->
    <button class="refresh-btn" onclick="refreshDashboard()" title="تحديث البيانات">
        <i class="fas fa-sync-alt"></i>
    </button>

    <script src="css/bootstrap/jQuery/jquery-3.6.0.min.js"></script>
    <script src="css/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    
    <script>
        let statusChart, windowsChart;
        
        // تحميل البيانات عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            
            // تحديث البيانات كل 30 ثانية
            setInterval(loadDashboardData, 30000);
        });
        
        // تحميل بيانات الداشبورد
        function loadDashboardData() {
            loadStatistics();
            loadCharts();
            loadWindowsStatus();
        }
        
        // تحميل الإحصائيات
        function loadStatistics() {
            fetch('php/get_admin_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        displayStatistics(data.stats);
                    } else {
                        console.error('Error loading statistics:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading statistics:', error);
                });
        }
        
        // عرض الإحصائيات
        function displayStatistics(stats) {
            const statsGrid = document.getElementById('statsGrid');
            statsGrid.innerHTML = `
                <div class="stat-card primary">
                    <div class="stat-icon">📊</div>
                    <div class="stat-number">${stats.total_queues}</div>
                    <div style="color: white !important;" class="stat-label">إجمالي الأدوار</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number">${stats.completed_queues}</div>
                    <div style="color: white !important;" class="stat-label">الأدوار المكتملة</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-number">${stats.waiting_queues}</div>
                    <div style="color: white !important;" class="stat-label">الأدوار في الانتظار</div>
                </div>
            `;
        }
        
        // تحميل الرسوم البيانية
        function loadCharts() {
            fetch('php/get_chart_data.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        createStatusChart(data.status_data);
                        createWindowsChart(data.windows_data);
                    }
                })
                .catch(error => {
                    console.error('Error loading chart data:', error);
                });
        }
        
        // إنشاء رسم توزيع الأدوار
        function createStatusChart(data) {
            const ctx = document.getElementById('statusChart').getContext('2d');
            
            if (statusChart) {
                statusChart.destroy();
            }
            
            statusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: [
                            '#28a745',
                            '#ffc107',
                            '#dc3545',
                            '#17a2b8'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: {
                                    family: 'Cairo',
                                    size: 14
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // إنشاء رسم الأدوار حسب الشباك
        function createWindowsChart(data) {
            const ctx = document.getElementById('windowsChart').getContext('2d');
            
            if (windowsChart) {
                windowsChart.destroy();
            }
            
            windowsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'عدد الأدوار',
                        data: data.values,
                        backgroundColor: 'rgba(102, 126, 234, 0.8)',
                        borderColor: 'rgba(102, 126, 234, 1)',
                        borderWidth: 2,
                        borderRadius: 5
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
                                font: {
                                    family: 'Cairo'
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    family: 'Cairo'
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // تحميل حالة الشبابيك
        function loadWindowsStatus() {
            fetch('php/get_windows_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        displayWindowsStatus(data.windows);
                    }
                })
                .catch(error => {
                    console.error('Error loading windows status:', error);
                });
        }
        
        // عرض حالة الشبابيك
        function displayWindowsStatus(windows) {
            const windowsList = document.getElementById('windowsList');
            const windowsArray = Object.values(windows);
            
            if (windowsArray.length === 0) {
                windowsList.innerHTML = '<p class="text-center text-muted">لا توجد شبابيك متاحة</p>';
                return;
            }
            
            windowsList.innerHTML = windowsArray.map(window => {
                const statusClass = window.status === 'serving' ? 'status-serving' : 
                                  window.status === 'available' ? 'status-available' : 'status-closed';
                const statusText = window.status === 'serving' ? 'يقدم خدمة' : 
                                 window.status === 'available' ? 'متاح' : 'مغلق';
                
                return `
                    <div class="window-item">
                        <div>
                            <h5 class="mb-1">شباك ${window.window_number}</h5>
                            <small class="text-muted d-block mt-1">دور الشباك لليوم: ${window.active_queues || 0} </small>
                        </div>
                        <div>
                            <span class="window-status-badge ${statusClass}">${statusText}</span>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // تحديث الداشبورد
        function refreshDashboard() {
            const refreshBtn = document.querySelector('.refresh-btn');
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            loadDashboardData();
            
            setTimeout(() => {
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i>';
            }, 2000);
        }
    </script>
</body>
</html>