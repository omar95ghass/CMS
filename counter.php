<?php
    session_start();
    $winNum = $_SESSION['windowNumber'];
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>Window / <?php echo $winNum; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/logo/logo.ico">
    <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .list-group {
            height: 450px; /* تحديد ارتفاع ثابت لعنصر القائمة */
            overflow-y: auto; /* تمكين التمرير العمودي */
        }
        @media only screen and (max-width: 600px) {
            .list-group {
                height: 405px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mt-1">CMS - Window / <?php echo $winNum; ?></h1>
        <button id="callNext" class="btn btn-success btn-block mt-3">نداء على الدور التالي</button>
        <button id="callSpecific" class="btn btn-primary btn-block mt-3 mb-3">نداء على دور محدد</button>
        <div id="accountantQueue" class="list-group m-2">
            <!-- قائمة الأدوار ستأتي هنا من JavaScript -->
        </div>
        <a href="php/logout_function.php" class="btn btn-danger btn-block mt-3 mb-2">تسجيل الخروج</a>
    </div>

    <script src="css/bootstrap/jQuery/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
