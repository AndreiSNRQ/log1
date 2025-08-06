<?php
// api.php
header("Content-Type: application/json");
require_once 'sws_functions.php';
require_once 'psm_functions.php';
require_once 'plt_functions.php';
require_once 'alms_functions.php';
require_once 'dtrs_functions.php';

$action = $_GET['action'] ?? '';
$module = $_GET['module'] ?? '';

try {
    switch ($module) {
        case 'sws':
            $sws = new SmartWarehousing();
            switch ($action) {
                case 'update_inventory':
                    $response = $sws->updateInventory($_POST['item_id'], $_POST['quantity'], $_POST['location']);
                    break;
                case 'check_stock':
                    $response = $sws->checkStockLevels($_POST['threshold'] ?? 10);
                    break;
                default:
                    $response = ["status" => "error", "message" => "Invalid action for SWS"];
            }
            break;
            
        case 'psm':
            $psm = new Procurement();
            switch ($action) {
                case 'add_vendor':
                    $response = $psm->addVendor($_POST['name'], $_POST['contact'], $_POST['email'], $_POST['phone'], $_POST['categories']);
                    break;
                case 'create_po':
                    $response = $psm->createPurchaseOrder($_POST['vendor_id'], $_POST['items'], $_POST['requester_id']);
                    break;
                default:
                    $response = ["status" => "error", "message" => "Invalid action for PSM"];
            }
            break;
            
        // Add cases for other modules similarly
            
        default:
            $response = ["status" => "error", "message" => "Invalid module specified"];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>