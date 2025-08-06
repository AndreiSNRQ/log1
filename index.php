<?php
// index.php
require_once 'db_config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get current user info
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Administrator';
$role = $_SESSION['role'] ?? 'admin';

// Set active page
$active_page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGISTICS1 - Smart Supply Chain Management</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --warehouse: #9b59b6;
            --document: #27ae60;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --sidebar-width: 280px;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--dark);
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .logo {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .logo h1 {
            font-size: 24px;
            margin: 0;
            color: white;
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        .logo .tagline {
            font-size: 12px;
            color: rgba(255,255,255,0.7);
            margin-top: 5px;
        }
        
        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .nav-item {
            padding: 12px 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            border-left: 4px solid transparent;
        }
        
        .nav-item:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .nav-item.active {
            background-color: rgba(255,255,255,0.05);
            border-left: 4px solid var(--secondary);
        }
        
        .nav-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }
        
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 30px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .page-title h2 {
            margin: 0;
            font-size: 24px;
            color: var(--dark);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            background-color: #ddd;
            border: 2px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--primary);
        }
        
        .content-area {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .module-title {
            color: var(--secondary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .module-title i {
            margin-right: 10px;
            font-size: 20px;
        }
        
        .entity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .entity-card {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            transition: all 0.3s;
            border-left: 4px solid var(--secondary);
        }
        
        .entity-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .entity-card h3 {
            margin: 0 0 10px;
            font-size: 16px;
            color: var(--primary);
        }
        
        .entity-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--secondary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid #ddd;
            color: #666;
        }
        
        .btn-outline:hover {
            background: #f0f0f0;
        }
        
        /* Module-specific colors */
        .nav-item.psm i { color: var(--primary); }
        .nav-item.plt i { color: var(--secondary); }
        .nav-item.alms i { color: var(--accent); }
        .nav-item.dtrs i { color: var(--document); }
        .nav-item.sws i { color: var(--warehouse); }
        
        /* Alert messages */
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h1>LOGISTICS<span>1</span></h1>
            <p class="tagline">Smart Supply Chain & Procurement</p>
        </div>
        <ul class="nav-menu">
            <li class="nav-item <?= $active_page === 'dashboard' ? 'active' : '' ?>">
                <a href="index.php?page=dashboard" style="color: inherit; text-decoration: none; display: flex; align-items: center; width: 100%;">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item psm <?= $active_page === 'procurement' ? 'active' : '' ?>">
                <a href="index.php?page=procurement" style="color: inherit; text-decoration: none; display: flex; align-items: center; width: 100%;">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Procurement & Sourcing</span>
                </a>
            </li>
            <li class="nav-item plt <?= $active_page === 'project' ? 'active' : '' ?>">
                <a href="index.php?page=project" style="color: inherit; text-decoration: none; display: flex; align-items: center; width: 100%;">
                    <i class="fas fa-project-diagram"></i>
                    <span>Project Logistics</span>
                </a>
            </li>
            <li class="nav-item alms <?= $active_page === 'assets' ? 'active' : '' ?>">
                <a href="index.php?page=assets" style="color: inherit; text-decoration: none; display: flex; align-items: center; width: 100%;">
                    <i class="fas fa-cogs"></i>
                    <span>Asset Lifecycle</span>
                </a>
            </li>
            <li class="nav-item dtrs <?= $active_page === 'documents' ? 'active' : '' ?>">
                <a href="index.php?page=documents" style="color: inherit; text-decoration: none; display: flex; align-items: center; width: 100%;">
                    <i class="fas fa-file-alt"></i>
                    <span>Document Tracking</span>
                </a>
            </li>
            <li class="nav-item sws <?= $active_page === 'warehouse' ? 'active' : '' ?>">
                <a href="index.php?page=warehouse" style="color: inherit; text-decoration: none; display: flex; align-items: center; width: 100%;">
                    <i class="fas fa-warehouse"></i>
                    <span>Smart Warehousing</span>
                </a>
            </li>
            <li class="nav-item <?= $active_page === 'analytics' ? 'active' : '' ?>">
                <a href="index.php?page=analytics" style="color: inherit; text-decoration: none; display: flex; align-items: center; width: 100%;">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
            </li>
            <li class="nav-item <?= $active_page === 'settings' ? 'active' : '' ?>">
                <a href="index.php?page=settings" style="color: inherit; text-decoration: none; display: flex; align-items: center; width: 100%;">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="logout.php" style="color: inherit; text-decoration: none; display: flex; align-items: center; width: 100%;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div class="page-title">
                <h2><?= ucfirst($active_page) ?> Overview</h2>
                <p>Welcome to Logistics1 Management System</p>
            </div>
            <div class="user-profile">
                <div class="user-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
                <span><?= htmlspecialchars($username) ?> (<?= ucfirst($role) ?>)</span>
            </div>
        </div>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_GET['message']) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>
        
        <div class="content-area">
            <?php
            // Include the appropriate content based on active page
            $page_file = "pages/{$active_page}.php";
            if (file_exists($page_file)) {
                include $page_file;
            } else {
                include 'pages/dashboard.php';
            }
            ?>
        </div>
    </div>
</body>
</html>