<?php
// sws_functions.php - Enhanced Smart Warehousing with Robust Database Connectivity
require_once 'db_config.php';

class SmartWarehousing {
    
    // Enhanced Inventory Management with Error Handling
    public function updateInventory($item_id, $quantity, $location) {
        global $conn;
        
        try {
            // Validate input parameters
            if (empty($item_id) || empty($location)) {
                throw new Exception("Missing required inventory parameters");
            }
            
            if (!is_numeric($quantity) || $quantity < 0) {
                throw new Exception("Invalid quantity provided");
            }
            
            // Check if item exists
            $check_sql = "SELECT id FROM inventory WHERE item_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $item_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                throw new Exception("Item not found");
            }
            
            $sql = "UPDATE inventory SET quantity = ?, location = ?, last_updated = NOW() WHERE item_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $quantity, $location, $item_id);
            
            if ($stmt->execute()) {
                return [
                    "status" => "success", 
                    "message" => "Inventory updated successfully",
                    "affected_rows" => $stmt->affected_rows
                ];
            } else {
                throw new Exception("Failed to update inventory: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("Inventory update error: " . $e->getMessage());
            return [
                "status" => "error", 
                "message" => $e->getMessage()
            ];
        }
    }
    
    // Enhanced Automated Replenishment with Error Handling
    public function checkStockLevels($threshold = 10) {
        global $conn;
        
        try {
            if (!is_numeric($threshold) || $threshold < 0) {
                throw new Exception("Invalid threshold value");
            }
            
            $sql = "SELECT item_id, item_name, quantity, min_stock_level FROM inventory WHERE quantity <= ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $threshold);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $low_stock_items = [];
            while ($row = $result->fetch_assoc()) {
                $low_stock_items[] = $row;
            }
            
            return [
                "status" => "success",
                "data" => $low_stock_items,
                "count" => count($low_stock_items)
            ];
            
        } catch (Exception $e) {
            error_log("Stock level check error: " . $e->getMessage());
            return [
                "status" => "error", 
                "message" => $e->getMessage(),
                "data" => []
            ];
        }
    }
    
    // Enhanced IoT Device Integration with Error Handling
    public function logIoTData($device_id, $reading_type, $reading_value) {
        global $conn;
        
        try {
            // Validate input parameters
            if (empty($device_id) || empty($reading_type)) {
                throw new Exception("Missing required IoT data parameters");
            }
            
            if (!is_numeric($reading_value)) {
                throw new Exception("Invalid reading value");
            }
            
            // Check if device exists
            $check_sql = "SELECT id FROM iot_devices WHERE device_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $device_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                throw new Exception("Device not found");
            }
            
            $sql = "INSERT INTO iot_data (device_id, reading_type, reading_value, timestamp) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssd", $device_id, $reading_type, $reading_value);
            
            if ($stmt->execute()) {
                return [
                    "status" => "success", 
                    "message" => "IoT data logged successfully",
                    "data_id" => $conn->insert_id
                ];
            } else {
                throw new Exception("Failed to log IoT data: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("IoT data logging error: " . $e->getMessage());
            return [
                "status" => "error", 
                "message" => $e->getMessage()
            ];
        }
    }
    
    // Enhanced Procurement Integration with Error Handling
    public function generatePurchaseRequest($item_id, $quantity, $reason = 'low_stock') {
        global $conn;
        
        try {
            // Validate input parameters
            if (empty($item_id) || empty($quantity)) {
                throw new Exception("Missing required purchase request parameters");
            }
            
            if (!is_numeric($quantity) || $quantity <= 0) {
                throw new Exception("Invalid quantity provided");
            }
            
            // Get item details
            $item_details = $this->getItemDetails($item_id);
            if (!$item_details) {
                throw new Exception("Item not found");
            }
            
            // Create purchase request
            $description = "Automated replenishment for " . $item_details['item_name'];
            
            // Insert purchase request
            $sql = "INSERT INTO purchase_orders (po_number, vendor_id, requester_id, status, created_at) 
                    VALUES (?, 1, 1, 'pending', NOW())";
            $po_number = "PR-" . date('Ymd') . "-" . uniqid();
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $po_number);
            
            if ($stmt->execute()) {
                $po_id = $conn->insert_id;
                
                // Add purchase order item
                $item_sql = "INSERT INTO po_items (po_id, item_id, quantity, unit_price) 
                             VALUES (?, ?, ?, 0.00)";
                $item_stmt = $conn->prepare($item_sql);
                $item_stmt->bind_param("iii", $po_id, $item_id, $quantity);
                
                if ($item_stmt->execute()) {
                    return [
                        "status" => "success",
                        "message" => "Purchase request generated successfully",
                        "po_id" => $po_id,
                        "po_number" => $po_number
                    ];
                } else {
                    throw new Exception("Failed to add purchase order item: " . $item_stmt->error);
                }
            } else {
                throw new Exception("Failed to create purchase request: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("Purchase request generation error: " . $e->getMessage());
            return [
                "status" => "error", 
                "message" => $e->getMessage()
            ];
        }
    }
    
    // Enhanced Item Details Retrieval with Error Handling
    private function getItemDetails($item_id) {
        global $conn;
        
        try {
            if (empty($item_id)) {
                throw new Exception("Invalid item ID provided");
            }
            
            $sql = "SELECT id, item_name, description, quantity, location FROM inventory WHERE item_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return null;
            }
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log("Item details retrieval error: " . $e->getMessage());
            return null;
        }
    }
    
    // Enhanced Inventory Retrieval with Error Handling
    public function getInventory() {
        global $conn;
        
        try {
            $sql = "SELECT * FROM inventory ORDER BY item_name";
            $result = $conn->query($sql);
            
            if (!$result) {
                throw new Exception("Failed to retrieve inventory: " . $conn->error);
            }
            
            $inventory = [];
            while ($row = $result->fetch_assoc()) {
                $inventory[] = $row;
            }
            
            return [
                "status" => "success",
                "data" => $inventory,
                "count" => count($inventory)
            ];
            
        } catch (Exception $e) {
            error_log("Inventory retrieval error: " . $e->getMessage());
            return [
                "status" => "error", 
                "message" => $e->getMessage(),
                "data" => []
            ];
        }
    }
    
    // Enhanced IoT Devices Retrieval with Error Handling
    public function getIoTDevices() {
        global $conn;
        
        try {
            $sql = "SELECT d.*, 
                    (SELECT reading_value FROM iot_data WHERE device_id = d.device_id ORDER BY timestamp DESC LIMIT 1) as last_reading
                    FROM iot_devices d ORDER BY d.device_name";
            $result = $conn->query($sql);
            
            if (!$result) {
                throw new Exception("Failed to retrieve IoT devices: " . $conn->error);
            }
            
            $devices = [];
            while ($row = $result->fetch_assoc()) {
                $devices[] = $row;
            }
            
            return [
                "status" => "success",
                "data" => $devices,
                "count" => count($devices)
            ];
            
        } catch (Exception $e) {
            error_log("IoT devices retrieval error: " . $e->getMessage());
            return [
                "status" => "error", 
                "message" => $e->getMessage(),
                "data" => []
            ];
        }
    }
}
?>
