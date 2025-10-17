<?php
// public/partials/header.php
require __DIR__ . '/../../config/auth.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>لوحة الإدارة</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../assets/css/all/fontawesome-free-6.6.0-web/css/all.min.css" />
  <style>
    nav { background:#37474F; }
    nav a { color:#fff; margin:0 .5rem; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand text-light" href="index.php">نظام الدور</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="services.php">الخدمات</a></li>
        <li class="nav-item"><a class="nav-link" href="queue_display.php">عرض الدور</a></li>
        <li class="nav-item"><a class="nav-link" href="windows.php">النوافذ</a></li>
        <li class="nav-item"><a class="nav-link" href="users.php">المستخدمين</a></li>
        <li class="nav-item"><a class="nav-link" href="printers.php">الطباعات</a></li>
        <li class="nav-item"><a class="nav-link" href="settings.php">الإعدادات</a></li>
        <li class="nav-item"><a class="nav-link" href="stats.php">إحصائيات</a></li>
      </ul>
      <span class="navbar-text text-light">
        مرحبا، <?= htmlspecialchars($_SESSION['username']) ?>
      </span>
      <a class="btn btn-outline-light ms-2" href="../api/logout.php">خروج</a>
    </div>
  </div>
</nav>
<main class="container my-4">
