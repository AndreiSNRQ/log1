<?php
// psm_functions.php
require_once 'db_config.php';
require_once 'sws_functions.php';
$procurement = new Procurement();
$result = $procurement->addVendor(
    "Suppliers", 
    "", 
    "", 
    "", 
    ""
);

class Procurement {
    
    // Vendor Management
    public function addVendor($name, $contact, $email, $phone, $categories) {
        global $conn;
        
        $sql = "INSERT INTO vendors (name, contact_person, email, phone, categories, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $contact, $email, $phone, $categories);
        
        if ($stmt->execute()) {
            return ["status" => "success", "vendor_id" => $conn->insert_id];
        } else {
            return ["status" => "error", "message" => "Error adding vendor: " . $stmt->error];
        }
    }
    
    // Purchase Order Creation
    public function createPurchaseOrder($vendor_id, $items, $requester_id) {
        global $conn;
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create PO header
            $po_number = "PO-" . date('Ymd') . "-" . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $sql = "INSERT INTO purchase_orders (po_number, vendor_id, requester_id, status, created_at) 
                    VALUES (?, ?, ?, 'pending', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $po_number, $vendor_id, $requester_id);
            $stmt->execute();
            $po_id = $conn->insert_id;
            
            // Add PO items
            foreach ($items as $item) {
                $sql = "INSERT INTO po_items (po_id, item_id, quantity, unit_price) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiid", $po_id, $item['item_id'], $item['quantity'], $item['unit_price']);
                $stmt->execute();
            }
            
            $conn->commit();
            return ["status" => "success", "po_id" => $po_id, "po_number" => $po_number];
            
        } catch (Exception $e) {
            $conn->rollback();
            return ["status" => "error", "message" => "Error creating PO: " . $e->getMessage()];
        }
    }
    
    // Integration with Warehouse
    public function createPurchaseRequest($item_id, $quantity, $description, $reason) {
        global $conn;
        
        $sql = "INSERT INTO purchase_requests (item_id, quantity, description, reason, status, created_at) 
                VALUES (?, ?, ?, ?, 'pending', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $item_id, $quantity, $description, $reason);
        
        if ($stmt->execute()) {
            return ["status" => "success", "request_id" => $conn->insert_id];
        } else {
            return ["status" => "error", "message" => "Error creating purchase request: " . $stmt->error];
        }
    }
    
    // Integration with Project Logistics
    public function linkPOToProject($po_id, $project_id) {
        global $conn;
        
        $sql = "UPDATE purchase_orders SET project_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $project_id, $po_id);
        
        if ($stmt->execute()) {
            return ["status" => "success", "message" => "PO linked to project successfully"];
        } else {
            return ["status" => "error", "message" => "Error linking PO to project: " . $stmt->error];
        }
    }
    public function getVendors() {
    global $conn;
    
    $sql = "SELECT * FROM vendors ORDER BY name";
    $result = $conn->query($sql);
    
    $vendors = [];
    while ($row = $result->fetch_assoc()) {
        $vendors[] = $row;
    }
    
    return $vendors;
}

public function getPendingPOs() {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count FROM purchase_orders WHERE status = 'pending'";
    $result = $conn->query($sql);
    
    return $result->fetch_assoc()['count'];
}
}
?>