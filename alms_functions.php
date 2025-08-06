<?php
// alms_functions.php
require_once 'db_config.php';
require_once 'alms_functions.php';
$assetMgmt = new AssetManagement();
$result = $assetMgmt->registerAsset(
    "Industrial Forklift", 
    "Heavy Equipment", 
    "FL-2023-001", 
    "2023-06-15", 
    42500.00, 
    12, // vendor ID
    "Warehouse B"
);
class AssetManagement {
    
    // Asset Registration
    public function registerAsset($name, $category, $serial_number, $purchase_date, $purchase_cost, $vendor_id, $location) {
        global $conn;
        
        $asset_tag = "AST-" . date('Ym') . "-" . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        $sql = "INSERT INTO assets (asset_tag, name, category, serial_number, purchase_date, purchase_cost, vendor_id, current_location, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssdis", $asset_tag, $name, $category, $serial_number, $purchase_date, $purchase_cost, $vendor_id, $location);
        
        if ($stmt->execute()) {
            return ["status" => "success", "asset_id" => $conn->insert_id, "asset_tag" => $asset_tag];
        } else {
            return ["status" => "error", "message" => "Error registering asset: " . $stmt->error];
        }
    }
    
    // Asset Assignment
    public function assignAssetToProject($asset_id, $project_id) {
        global $conn;
        
        // Check if asset is available
        $check_sql = "SELECT status FROM assets WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $asset_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $asset_status = $check_result->fetch_assoc()['status'];
        
        if ($asset_status != 'available') {
            return ["status" => "error", "message" => "Asset is not available for assignment"];
        }
        
        // Update asset status and location
        $sql = "UPDATE assets SET status = 'assigned', current_location = ? WHERE id = ?";
        $location = "Project #$project_id";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $location, $asset_id);
        
        if ($stmt->execute()) {
            // Log the assignment
            $this->logMaintenance($asset_id, "Assigned to Project #$project_id", "assignment");
            
            return ["status" => "success", "message" => "Asset assigned to project successfully"];
        } else {
            return ["status" => "error", "message" => "Error assigning asset: " . $stmt->error];
        }
    }
    
    // Maintenance Tracking
    public function logMaintenance($asset_id, $description, $type, $cost = 0) {
        global $conn;
        
        $sql = "INSERT INTO maintenance_logs (asset_id, description, type, cost, logged_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issd", $asset_id, $description, $type, $cost);
        
        if ($stmt->execute()) {
            return ["status" => "success", "log_id" => $conn->insert_id];
        } else {
            return ["status" => "error", "message" => "Error logging maintenance: " . $stmt->error];
        }
    }
    
    // Integration with Document Tracking
    public function attachDocumentToAsset($asset_id, $document_id) {
        require_once 'dtrs_functions.php';
        $doc_tracking = new DocumentTracking();
        
        return $doc_tracking->linkDocumentToEntity($document_id, 'asset', $asset_id);
    }
}
?>
