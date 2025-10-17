<?php
session_start();
include 'php/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// جلب الإحصائيات
try {
    $today = date('Y-m-d');
    
    // إحصائيات اليوم
    $stats_today = [];
    
    // إجمالي الأدوار اليوم
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM queue WHERE date = ?");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats_today['total'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // الأدوار المنتظرة
    $stmt = $conn->prepare("SELECT COUNT(*) as waiting FROM queue WHERE date = ? AND status = 'waiting'");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats_today['waiting'] = $result->fetch_assoc()['waiting'];
    $stmt->close();
    
    // الأدوار المكتملة
    $stmt = $conn->prepare("SELECT COUNT(*) as completed FROM queue WHERE date = ? AND status = 'completed'");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats_today['completed'] = $result->fetch_assoc()['completed'];
    $stmt->close();
    
    // الأدوار المدعوة
    $stmt = $conn->prepare("SELECT COUNT(*) as called FROM queue WHERE date = ? AND status = 'called'");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats_today['called'] = $result->fetch_assoc()['called'];
    $stmt->close();
    
    // إحصائيات حسب الخدمة
    $stmt = $conn->prepare("SELECT clinic, COUNT(*) as count FROM queue WHERE date = ? GROUP BY clinic ORDER BY count DESC");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $clinic_stats = [];
    while ($row = $result->fetch_assoc()) {
        $clinic_stats[] = $row;
    }
    $stmt->close();
    
    // إحصائيات الأسبوع
    $week_start = date('Y-m-d', strtotime('-7 days'));
    $stmt = $conn->prepare("SELECT DATE(date) as date, COUNT(*) as count FROM queue WHERE date >= ? GROUP BY DATE(date) ORDER BY date");
    $stmt->bind_param("s", $week_start);
    $stmt->execute();
    $result = $stmt->get_result();
    $week_stats = [];
    while ($row = $result->fetch_assoc()) {
        $week_stats[] = $row;
    }
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Statistics error: " . $e->getMessage());
    header("Location: error.php?error=db_query&message=" . urlencode($e->getMessage()));
    exit();
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإحصائيات - نظام إدارة الطوابير</title>
    <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="images/logo/logo.ico">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .stats-card p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .nav-pills .nav-link {
            border-radius: 25px;
            margin: 0 5px;
        }
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- شريط التنقل -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <img src="images/logo/logo.png" alt="Logo" height="30" class="me-2">
                    نظام إدارة الطوابير
                </a>
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="index.php">الرئيسية</a>
                    <a class="nav-link" href="services.php">الخدمات</a>
                    <a class="nav-link" href="counter.php">النوافذ</a>
                    <a class="nav-link active" href="statistics.php">الإحصائيات</a>
                    <a class="nav-link" href="php/logout_function.php">تسجيل الخروج</a>
                </div>
            </div>
        </nav>

        <div class="container">
            <h1 class="text-center mb-4">الإحصائيات والتقارير</h1>
            
            <!-- إحصائيات اليوم -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <h3><?php echo $stats_today['total']; ?></h3>
                        <p>إجمالي الأدوار اليوم</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);">
                        <h3><?php echo $stats_today['waiting']; ?></h3>
                        <p>في الانتظار</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center" style="background: linear-gradient(135deg, #26de81 0%, #20bf6b 100%);">
                        <h3><?php echo $stats_today['completed']; ?></h3>
                        <p>مكتملة</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                        <h3><?php echo $stats_today['called']; ?></h3>
                        <p>مدعوة</p>
                    </div>
                </div>
            </div>

            <!-- الرسوم البيانية -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="chart-container">
                        <h4 class="mb-3">توزيع الأدوار حسب الخدمة</h4>
                        <canvas id="clinicChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <h4 class="mb-3">إحصائيات الأسبوع</h4>
                        <canvas id="weekChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- جدول تفصيلي -->
            <div class="table-container">
                <h4 class="mb-3">تفاصيل الأدوار حسب الخدمة</h4>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>الخدمة</th>
                                <th>عدد الأدوار</th>
                                <th>النسبة المئوية</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_clinics = array_sum(array_column($clinic_stats, 'count'));
                            foreach ($clinic_stats as $clinic): 
                                $percentage = $total_clinics > 0 ? round(($clinic['count'] / $total_clinics) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($clinic['clinic']); ?></td>
                                <td><?php echo $clinic['count']; ?></td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" style="width: <?php echo $percentage; ?>%">
                                            <?php echo $percentage; ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // رسم بياني لتوزيع الأدوار حسب الخدمة
        const clinicCtx = document.getElementById('clinicChart').getContext('2d');
        new Chart(clinicCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($clinic_stats, 'clinic')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($clinic_stats, 'count')); ?>,
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#f093fb',
                        '#f5576c',
                        '#4facfe',
                        '#00f2fe'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // رسم بياني لإحصائيات الأسبوع
        const weekCtx = document.getElementById('weekChart').getContext('2d');
        new Chart(weekCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($week_stats, 'date')); ?>,
                datasets: [{
                    label: 'عدد الأدوار',
                    data: <?php echo json_encode(array_column($week_stats, 'count')); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>