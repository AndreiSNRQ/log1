<?php
// Warehouse page
require_once '../sws_functions.php';
$warehouse = new SmartWarehousing();

$action = $_GET['action'] ?? 'inventory';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'update_inventory':
            $result = $warehouse->updateInventory(
                $_POST['item_id'],
                $_POST['quantity'],
                $_POST['location']
            );
            if ($result['status'] === 'success') {
                header("Location: index.php?page=warehouse&action=inventory&message=Inventory updated successfully");
                exit();
            } else {
                $error = $result['message'];
            }
            break;
    }
}

// Get data based on action
switch ($action) {
    case 'inventory':
        $inventory_items = $warehouse->getInventory();
        break;
        
    case 'replenishment':
        $low_stock_items = $warehouse->checkStockLevels();
        break;
        
    case 'iot':
        $iot_devices = $warehouse->getIoTDevices();
        break;
}
?>

<div class="module-title">
    <i class="fas fa-warehouse"></i>
    <h2>Smart Warehousing System</h2>
</div>

<?php if ($action === 'inventory'): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Inventory Management</h3>
        <a href="index.php?page=warehouse&action=add_item" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Item
        </a>
    </div>
    
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f1f1f1;">
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Item ID</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Name</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Quantity</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Location</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Last Updated</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inventory_items as $item): ?>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 10px;"><?= htmlspecialchars($item['item_id']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($item['item_name']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($item['quantity']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($item['location']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($item['last_updated']) ?></td>
                <td style="padding: 10px;">
                    <a href="index.php?page=warehouse&action=edit_item&id=<?= $item['id'] ?>" class="btn btn-outline" style="padding: 3px 8px; font-size: 12px;">Edit</a>
                    <a href="#" class="btn btn-outline" style="padding: 3px 8px; font-size: 12px;">History</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif ($action === 'replenishment'): ?>
    <h3>Automated Replenishment</h3>
    
    <div class="alert" style="background-color: #fff3cd; color: #856404; margin-bottom: 20px;">
        <i class="fas fa-info-circle"></i> Items below minimum stock level will be highlighted.
    </div>
    
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f1f1f1;">
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Item ID</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Name</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Current Qty</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Min Qty</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($low_stock_items as $item): ?>
            <tr style="border-bottom: 1px solid #ddd; <?= $item['quantity'] <= $item['min_stock_level'] ? 'background-color: #f8d7da;' : '' ?>">
                <td style="padding: 10px;"><?= htmlspecialchars($item['item_id']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($item['item_name']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($item['quantity']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($item['min_stock_level']) ?></td>
                <td style="padding: 10px;">
                    <a href="index.php?page=procurement&action=create_po&item_id=<?= $item['id'] ?>" class="btn btn-primary" style="padding: 3px 8px; font-size: 12px;">Create PO</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif ($action === 'iot'): ?>
    <h3>IoT Device Monitoring</h3>
    
    <div class="entity-grid">
        <?php foreach ($iot_devices as $device): ?>
        <div class="entity-card">
            <h3><?= htmlspecialchars($device['device_name']) ?></h3>
            <p><strong>Type:</strong> <?= htmlspecialchars($device['device_type']) ?></p>
            <p><strong>Status:</strong> <span style="color: <?= $device['status'] === 'online' ? '#28a745' : '#dc3545' ?>"><?= htmlspecialchars($device['status']) ?></span></p>
            <p><strong>Last Reading:</strong> <?= htmlspecialchars($device['last_reading']) ?></p>
            <div class="entity-actions">
                <a href="#" class="btn btn-primary">Details</a>
                <a href="#" class="btn btn-outline">Configure</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>