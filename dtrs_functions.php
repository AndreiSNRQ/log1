<?php
// dtrs_functions.php
require_once 'db_config.php';
require_once 'dtrs_functions.php';
$docTracking = new DocumentTracking();
$result = $docTracking->uploadDocument(
    "contract.pdf", 
    "/uploads/documents/contract_123.pdf", 
    "application/pdf", 
    filesize("/uploads/documents/contract_123.pdf"), 
    45, // user ID who uploaded
    "Contracts"
);
class DocumentTracking {
    
    // Document Upload
    public function uploadDocument($file_name, $file_path, $file_type, $file_size, $uploaded_by, $category) {
        global $conn;
        
        $doc_number = "DOC-" . date('Ymd') . "-" . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        $sql = "INSERT INTO documents (doc_number, file_name, file_path, file_type, file_size, uploaded_by, category, status, uploaded_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssis", $doc_number, $file_name, $file_path, $file_type, $file_size, $uploaded_by, $category);
        
        if ($stmt->execute()) {
            return ["status" => "success", "document_id" => $conn->insert_id, "doc_number" => $doc_number];
        } else {
            return ["status" => "error", "message" => "Error uploading document: " . $stmt->error];
        }
    }
    
    // Document Version Control
    public function createNewVersion($original_doc_id, $new_file_path, $version_notes, $updated_by) {
        global $conn;
        
        // Get original document details
        $original_sql = "SELECT doc_number, file_name, file_type, category FROM documents WHERE id = ?";
        $original_stmt = $conn->prepare($original_sql);
        $original_stmt->bind_param("i", $original_doc_id);
        $original_stmt->execute();
        $original_doc = $original_stmt->get_result()->fetch_assoc();
        
        if (!$original_doc) {
            return ["status" => "error", "message" => "Original document not found"];
        }
        
        // Archive current version
        $archive_sql = "UPDATE documents SET status = 'archived', replaced_by = ? WHERE id = ?";
        $archive_stmt = $conn->prepare($archive_sql);
        $archive_stmt->bind_param("ii", $conn->insert_id, $original_doc_id);
        
        // Create new version
        $new_doc_number = $original_doc['doc_number'] . "-v" . time();
        $new_sql = "INSERT INTO documents (doc_number, file_name, file_path, file_type, file_size, uploaded_by, category, status, version_notes, previous_version, uploaded_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, NOW())";
        $new_stmt = $conn->prepare($new_sql);
        $new_stmt->bind_param("sssssissi", $new_doc_number, $original_doc['file_name'], $new_file_path, $original_doc['file_type'], filesize($new_file_path), $updated_by, $original_doc['category'], $version_notes, $original_doc_id);
        
        $conn->begin_transaction();
        
        try {
            $new_stmt->execute();
            $new_doc_id = $conn->insert_id;
            $archive_stmt->bind_param("ii", $new_doc_id, $original_doc_id);
            $archive_stmt->execute();
            
            $conn->commit();
            return ["status" => "success", "new_document_id" => $new_doc_id];
            
        } catch (Exception $e) {
            $conn->rollback();
            return ["status" => "error", "message" => "Error creating new version: " . $e->getMessage()];
        }
    }
    
    // Document Linking
    public function linkDocumentToEntity($document_id, $entity_type, $entity_id) {
        global $conn;
        
        $sql = "INSERT INTO document_links (document_id, entity_type, entity_id, linked_at) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $document_id, $entity_type, $entity_id);
        
        if ($stmt->execute()) {
            return ["status" => "success", "link_id" => $conn->insert_id];
        } else {
            return ["status" => "error", "message" => "Error linking document: " . $stmt->error];
        }
    }
    
    // Integration with other modules
    public function getDocumentsForEntity($entity_type, $entity_id) {
        global $conn;
        
        $sql = "SELECT d.* FROM documents d
                JOIN document_links dl ON d.id = dl.document_id
                WHERE dl.entity_type = ? AND dl.entity_id = ?
                ORDER BY d.uploaded_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $entity_type, $entity_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $documents = [];
        while ($row = $result->fetch_assoc()) {
            $documents[] = $row;
        }
        
        return $documents;
    }
}
?>