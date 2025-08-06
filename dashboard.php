<?php
// Dashboard content
require_once 'sws_functions.php';
require_once 'psm_functions.php';
require_once 'plt_functions.php';

$warehouse = new SmartWarehousing();
$procurement = new Procurement();
$projectLogistics = new ProjectLogistics();

//gawa gawa
$pending_pos = '10';
$active_projects = '5';

?>

<div class="module-title">
    <i class="fas fa-tachometer-alt"></i>
    <h2>System Overview</h2>
</div>

<div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
    <div class="stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #3498db;">
        <h3 style="margin: 0 0 10px; font-size: 16px; color: #2c3e50;">Low Stock Items</h3>
        <p style="font-size: 24px; margin: 0; font-weight: bold;"><?= $warehouse->checkStockLevels()['count'] ?></p>
    </div>
    
    <div class="stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #e74c3c;">
        <h3 style="margin: 0 0 10px; font-size: 16px; color: #2c3e50;">Pending POs</h3>
        <p style="font-size: 24px; margin: 0; font-weight: bold;"><?= $pending_pos ?></p>
    </div>
    
    <div class="stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #9b59b6;">
        <h3 style="margin: 0 0 10px; font-size: 16px; color: #2c3e50;">Active Projects</h3>
        <p style="font-size: 24px; margin: 0; font-weight: bold;"><?= $active_projects ?></p>
    </div>
    
    <div class="stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #27ae60;">
        <h3 style="margin: 0 0 10px; font-size: 16px; color: #2c3e50;">Open Tasks</h3>
        <p style="font-size: 24px; margin: 0; font-weight: bold;">12</p>
    </div>
</div>

<div class="module-title">
    <i class="fas fa-th-large"></i>
    <h2>Quick Access</h2>
</div>

<div class="entity-grid">
    <!-- Procurement Entities -->
    <div class="entity-card">
        <h3>Vendor Management</h3>
        <p>Manage supplier relationships, contracts, and performance metrics</p>
        <div class="entity-actions">
            <a href="index.php?page=procurement&action=vendors" class="btn btn-primary">Access</a>
            <button class="btn btn-outline">Details</button>
        </div>
    </div>
    
    <div class="entity-card">
        <h3>Purchase Orders</h3>
        <p>Create, track, and manage all purchase orders</p>
        <div class="entity-actions">
            <a href="index.php?page=procurement&action=pos" class="btn btn-primary">Access</a>
            <button class="btn btn-outline">Details</button>
        </div>
    </div>
    
    <!-- Project Logistics Entities -->
    <div class="entity-card">
        <h3>Project Tracking</h3>
        <p>Monitor project milestones, resources, and timelines</p>
        <div class="entity-actions">
            <a href="index.php?page=project&action=tracking" class="btn btn-primary">Access</a>
            <button class="btn btn-outline">Details</button>
        </div>
    </div>
    
    <!-- Asset Management Entities -->
    <div class="entity-card">
        <h3>Asset Registry</h3>
        <p>Comprehensive tracking of all physical assets</p>
        <div class="entity-actions">
            <a href="index.php?page=assets&action=registry" class="btn btn-primary">Access</a>
            <button class="btn btn-outline">Details</button>
        </div>
    </div>
    
    <!-- Document Management Entities -->
    <div class="entity-card">
        <h3>Document Control</h3>
        <p>Version control and approval workflows</p>
        <div class="entity-actions">
            <a href="index.php?page=documents&action=control" class="btn btn-primary">Access</a>
            <button class="btn btn-outline">Details</button>
        </div>
    </div>
    
    <!-- Warehouse Management Entities -->
    <div class="entity-card">
        <h3>Inventory Control</h3>
        <p>Real-time inventory tracking and management</p>
        <div class="entity-actions">
            <a href="index.php?page=warehouse&action=inventory" class="btn btn-primary">Access</a>
            <button class="btn btn-outline">Details</button>
        </div>
    </div>
    
    <div class="entity-card">
        <h3>Automated Replenishment</h3>
        <p>Smart inventory restocking algorithms</p>
        <div class="entity-actions">
            <a href="index.php?page=warehouse&action=replenishment" class="btn btn-primary">Access</a>
            <button class="btn btn-outline">Details</button>
        </div>
    </div>
    
    <div class="entity-card">
        <h3>IoT Monitoring</h3>
        <p>Connected devices for warehouse operations</p>
        <div class="entity-actions">
            <a href="index.php?page=warehouse&action=iot" class="btn btn-primary">Access</a>
            <button class="btn btn-outline">Details</button>
        </div>
    </div>
</div>