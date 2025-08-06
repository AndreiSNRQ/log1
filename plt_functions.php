<?php
// plt_functions.php - Enhanced Project Logistics with Robust Database Connectivity
require_once 'db_config.php';

class ProjectLogistics {
    
    // Enhanced Project Creation with Error Handling
    public function createProject($name, $description, $start_date, $end_date, $manager_id) {
        global $conn;
        
        try {
            // Validate input parameters
            if (empty($name) || empty($start_date) || empty($end_date) || empty($manager_id)) {
                throw new Exception("Missing required project parameters");
            }
            
            // Validate date format
            if (!strtotime($start_date) || !strtotime($end_date)) {
                throw new Exception("Invalid date format provided");
            }
            
            // Check if manager exists
            $check_sql = "SELECT id FROM users WHERE id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $manager_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                throw new Exception("Invalid manager ID provided");
            }
            
            $sql = "INSERT INTO projects (name, description, start_date, end_date, manager_id, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, 'planning', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $name, $description, $start_date, $end_date, $manager_id);
            
            if ($stmt->execute()) {
                return [
                    "status" => "success", 
                    "project_id" => $conn->insert_id,
                    "message" => "Project created successfully"
                ];
            } else {
                throw new Exception("Failed to create project: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("Project creation error: " . $e->getMessage());
            return [
                "status" => "error", 
                "message" => $e->getMessage()
            ];
        }
    }
    
    // Enhanced Milestone Tracking with Validation
    public function addMilestone($project_id, $name, $target_date, $dependencies) {
        global $conn;
        
        try {
            // Validate project exists
            $check_sql = "SELECT id FROM projects WHERE id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $project_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                throw new Exception("Invalid project ID provided");
            }
            
            // Validate date format
            if (!strtotime($target_date)) {
                throw new Exception("Invalid target date format");
            }
            
            $sql = "INSERT INTO milestones (project_id, name, target_date, dependencies, status, created_at) 
                    VALUES (?, ?, ?, ?, 'pending', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $project_id, $name, $target_date, $dependencies);
            
            if ($stmt->execute()) {
                return [
                    "status" => "success", 
                    "milestone_id" => $conn->insert_id,
                    "message" => "Milestone added successfully"
                ];
            } else {
                throw new Exception("Failed to add milestone: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("Milestone addition error: " . $e->getMessage());
            return [
                "status" => "error", 
                "message" => $e->getMessage()
            ];
        }
    }
    
    // Enhanced Resource Allocation with Validation
    public function allocateResource($project_id, $resource_type, $resource_id, $quantity, $start_date, $end_date) {
        global $conn;
        
        try {
            // Validate input parameters
            if (empty($project_id) || empty($resource_type) || empty($resource_id) || empty($quantity)) {
                throw new Exception("Missing required resource allocation parameters");
            }
            
            // Validate project exists
            $check_sql = "SELECT id FROM projects WHERE id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $project_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                throw new Exception("Invalid project ID provided");
            }
            
            // Validate date formats
            if (!strtotime($start_date) || !strtotime($end_date)) {
                throw new Exception("Invalid date format provided");
            }
            
            // Validate quantity is positive
            if ($quantity <= 0) {
                throw new Exception("Quantity must be positive");
            }
            
            $sql = "INSERT INTO project_resources (project_id, resource_type, resource_id, quantity, start_date, end_date, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'allocated')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ississ", $project_id, $resource_type, $resource_id, $quantity, $start_date, $end_date);
            
            if ($stmt->execute()) {
                // Handle inventory allocation if needed
                if ($resource_type == 'inventory') {
                    $this->updateInventoryAvailability($resource_id, $quantity);
                }
                
                return [
                    "status" => "success", 
                    "allocation_id" => $conn->insert_id,
                    "message" => "Resource allocated successfully"
                ];
            } else {
                throw new Exception("Failed to allocate resource: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("Resource allocation error: " . $e->getMessage());
            return [
                "status" => "error", 
                "message" => $e->getMessage()
            ];
        }
    }
    
    // Enhanced Asset Integration with Validation
    public function requestAssetsForProject($project_id, $asset_ids) {
        global $conn;
        
        try {
            if (!is_array($asset_ids) || empty($asset_ids)) {
                throw new Exception("Invalid asset IDs provided");
            }
            
            // Validate project exists
            $check_sql = "SELECT id FROM projects WHERE id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $project_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                throw new Exception("Invalid project ID provided");
            }
            
            $results = [];
            foreach ($asset_ids as $asset_id) {
                if (!is_numeric($asset_id)) {
                    $results[] = [
                        "asset_id" => $asset_id,
                        "status" => "error",
                        "message" => "Invalid asset ID format"
                    ];
                    continue;
                }
                
                // Check if asset exists
                $asset_check = "SELECT id FROM assets WHERE id = ?";
                $asset_stmt = $conn->prepare($asset_check);
                $asset_stmt->bind_param("i", $asset_id);
                $asset_stmt->execute();
                $asset_result = $asset_stmt->get_result();
                
                if ($asset_result->num_rows === 0) {
                    $results[] = [
                        "asset_id" => $asset_id,
                        "status" => "error",
                        "message" => "Asset not found"
                    ];
                    continue;
                }
                
                // Assign asset to project
                $assign_sql = "UPDATE assets SET current_location = ?, status = 'allocated' WHERE id = ?";
                $assign_stmt = $conn->prepare($assign_sql);
                $assign_stmt->bind_param("si", $project_id, $asset_id);
                
                if ($assign_stmt->execute()) {
                    $results[] = [
                        "asset_id" => $asset_id,
                        "status" => "success",
                        "message" => "Asset assigned successfully"
                    ];
                } else {
                    $results[] = [
                        "asset_id" => $asset_id,
                        "status" => "error",
                        "message" => "Failed to assign asset"
                    ];
                }
            }
            
            return [
                "status" => "success",
                "results" => $results
            ];
            
        } catch (Exception $e) {
            error_log("Asset assignment error: " . $e->getMessage());
            return [
                "status" => "error",
                "message" => $e->getMessage()
            ];
        }
    }
    
    // Helper function to update inventory availability
    private function updateInventoryAvailability($resource_id, $quantity) {
        global $conn;
        
        try {
            $sql = "UPDATE inventory SET quantity = quantity - ? WHERE id = ? AND quantity >= ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $quantity, $resource_id, $quantity);
            
            if ($stmt->execute()) {
                return true;
            } else {
                throw new Exception("Failed to update inventory: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("Inventory update error: " . $e->getMessage());
            return false;
        }
    }
}
?>
