<?php
// التحقق من الصلاحيات
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'لوحة تحكم المدير'; ?> - مركز خدمة المواطن</title>
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
            color: white !important;
            padding: 10px 20px !important;
            border-radius: 25px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
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
        
        .page-header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .page-subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        
        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-item {
            color: #666;
        }
        
        .breadcrumb-item.active {
            color: #333;
            font-weight: bold;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: #666;
        }
        
        @media (max-width: 768px) {
            .admin-navbar {
                margin: 10px;
                padding: 10px;
            }
            
            .nav-item {
                margin: 5px 0;
            }
            
            .page-title {
                font-size: 1.5rem;
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
                    <div class="col-md-6">
                        <h1 class="mb-0">لوحة تحكم المدير</h1>
                        <p class="mb-0">مرحباً، <?php echo $_SESSION['username']; ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="php/logout_function.php" class="btn btn-outline-light">تسجيل الخروج</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="admin-navbar">
            <div class="container">
                <nav class="nav justify-content-center">
                    <a href="admin_dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">الرئيسية</a>
                    <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">الإعدادات</a>
                    <a href="statistics.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'statistics.php' ? 'active' : ''; ?>">الإحصائيات</a>
                    <a href="display.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'display.php' ? 'active' : ''; ?>">شاشة العرض</a>
                    <a href="counter.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'counter.php' ? 'active' : ''; ?>">الشباك</a>
                    <a href="error.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'error.php' ? 'active' : ''; ?>">سجل الأخطاء</a>
                </nav>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="admin_dashboard.php">الرئيسية</a></li>
                        <?php if (isset($breadcrumb)): ?>
                            <?php foreach ($breadcrumb as $item): ?>
                                <li class="breadcrumb-item <?php echo $item['active'] ? 'active' : ''; ?>">
                                    <?php if ($item['active']): ?>
                                        <?php echo $item['title']; ?>
                                    <?php else: ?>
                                        <a href="<?php echo $item['url']; ?>"><?php echo $item['title']; ?></a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ol>
                </nav>
                <h1 class="page-title"><?php echo $page_title ?? 'لوحة تحكم المدير'; ?></h1>
                <p class="page-subtitle"><?php echo $page_subtitle ?? 'إدارة نظام طوابير الخدمة'; ?></p>
            </div>
        </div>

        <!-- Page Content -->
        <div class="dashboard-content">
            <div class="container">