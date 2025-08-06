<?php
// Procurement page
require_once '../psm_functions.php';
$procurement = new Procurement();

$action = $_GET['action'] ?? 'vendors';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'add_vendor':
            // Basic input validation
            $name = trim($_POST['name'] ?? '');
            $contact = trim($_POST['contact'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $categories = trim($_POST['categories'] ?? '');
            
            if (empty($name) || empty($contact) || empty($email) || empty($phone)) {
                $error = "Please fill in all required fields";
            } else {
                $result = $procurement->addVendor(
                    $name,
                    $contact,
                    $email,
                    $phone,
                    $categories
                );
                if ($result['status'] === 'success') {
                    header("Location: index.php?page=procurement&action=vendors&message=Vendor added successfully");
                    exit();
                } else {
                    $error = htmlspecialchars($result['message']);
                }
            }
            break;
            
        case 'create_po':
            // Handle PO creation
            break;
            
        default:
            // Invalid action
            header("Location: index.php?page=procurement&action=vendors");
            exit();
    }
}

// Get data based on action
switch ($action) {
    case 'vendors':
        $vendors = $procurement->getVendors();
        break;
        
    case 'pos':
        $purchase_orders = $procurement->getPurchaseOrders();
        break;
        
    case 'add_vendor':
        // Just show the form
        break;
        
    default:
        header("Location: index.php?page=procurement&action=vendors");
        exit();
}
?>

<div class="module-title">
    <i class="fas fa-shopping-cart"></i>
    <h2>Procurement & Sourcing Management</h2>
</div>

<?php if ($action === 'vendors'): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Vendor Management</h3>
        <a href="index.php?page=procurement&action=add_vendor" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Vendor
        </a>
    </div>
    
    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['message']) ?>
        </div>
    <?php endif; ?>
    
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f1f1f1;">
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Name</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Contact</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Email</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Phone</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Categories</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vendors as $vendor): ?>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 10px;"><?= htmlspecialchars($vendor['name']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($vendor['contact_person']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($vendor['email']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($vendor['phone']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($vendor['categories']) ?></td>
                <td style="padding: 10px;">
                    <a href="#" class="btn btn-outline" style="padding: 3px 8px; font-size: 12px;">Edit</a>
                    <a href="#" class="btn btn-outline" style="padding: 3px 8px; font-size: 12px;">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif ($action === 'add_vendor'): ?>
    <h3>Add New Vendor</h3>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error" style="margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="index.php?page=procurement&action=add_vendor" style="max-width: 600px;">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Vendor Name *</label>
            <input type="text" name="name" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Contact Person *</label>
            <input type="text" name="contact" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" value="<?= isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : '' ?>">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Email *</label>
            <input type="email" name="email" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Phone *</label>
            <input type="tel" name="phone" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Categories (comma separated)</label>
            <input type="text" name="categories" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" value="<?= isset($_POST['categories']) ? htmlspecialchars($_POST['categories']) : '' ?>">
        </div>
        
        <button type="submit" class="btn btn-primary">Save Vendor</button>
        <a href="index.php?page=procurement&action=vendors" class="btn btn-outline">Cancel</a>
    </form>

<?php elseif ($action === 'pos'): ?>
    <h3>Purchase Orders</h3>
    <!-- PO management content -->
<?php endif; ?>