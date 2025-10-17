<?php
// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? 'counter';
$username = $_SESSION['username'] ?? 'مستخدم';
$window_number = $_SESSION['windowNumber'] ?? '';
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'نظام إدارة الطوابير'; ?></title>
    <link rel="icon" type="image/x-icon" href="images/logo/logo.ico">
    <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: #f8f9fa;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: white !important;
            font-weight: 500;
            padding: 0.75rem 1rem !important;
            border-radius: 5px;
            margin: 0 2px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white !important;
        }
        
        .user-info {
            background: rgba(255,255,255,0.1);
            padding: 8px 15px;
            border-radius: 20px;
            color: white;
            font-size: 0.9rem;
        }
        
        .main-content {
            min-height: calc(100vh - 76px);
            padding: 20px 0;
        }
        
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .page-title {
            color: #333;
            font-weight: bold;
            margin: 0;
        }
        
        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-item a {
            color: #667eea;
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: #6c757d;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 20px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-1px);
        }
        
        .footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
        }
        
        @media (max-width: 768px) {
            .navbar-nav {
                text-align: center;
                margin-top: 10px;
            }
            
            .nav-link {
                margin: 2px 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo/logo.png" alt="Logo" height="30" class="me-2">
                نظام إدارة الطوابير
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            🏠 الرئيسية
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'services.php' ? 'active' : ''; ?>" href="services.php">
                            🏥 الخدمات
                        </a>
                    </li>
                    <?php if ($user_role == 'counter'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'counter.php' ? 'active' : ''; ?>" href="counter.php">
                            🖥️ النافذة <?php echo $window_number; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'statistics.php' ? 'active' : ''; ?>" href="statistics.php">
                            📊 الإحصائيات
                        </a>
                    </li>
                    <?php if ($user_role == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="users.php">
                            👥 المستخدمين
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'clinics.html' ? 'active' : ''; ?>" href="clinics.html">
                            🏥 إدارة العيادات
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <div class="d-flex align-items-center">
                    <div class="user-info me-3">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($username); ?>
                        <?php if ($window_number): ?>
                        - النافذة <?php echo $window_number; ?>
                        <?php endif; ?>
                    </div>
                    <a class="btn btn-outline-light" href="php/logout_function.php">
                        <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <?php if (isset($page_title) && isset($breadcrumbs)): ?>
            <div class="page-header">
                <h1 class="page-title"><?php echo $page_title; ?></h1>
                <?php if (!empty($breadcrumbs)): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                            <?php if ($index == count($breadcrumbs) - 1): ?>
                                <li class="breadcrumb-item active"><?php echo $breadcrumb['title']; ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item">
                                    <a href="<?php echo $breadcrumb['url']; ?>"><?php echo $breadcrumb['title']; ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
                <?php endif; ?>
            </div>
            <?php endif; ?>