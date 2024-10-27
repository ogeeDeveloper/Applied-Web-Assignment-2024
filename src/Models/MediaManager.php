<?php
namespace App\Models;

use PDO;

class MediaManager {
    private PDO $db;
    private $logger;
    private $allowedMimeTypes = [
        'image' => ['image/jpeg', 'image/png', 'image/webp'],
        'video' => ['video/mp4', 'video/webm'],
        'document' => ['application/pdf']
    ];
    private $maxFileSize = 10485760; // 10MB
    private $uploadBasePath = '/storage/uploads/';

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function uploadFile(array $file, string $entityType, int $entityId): array {
        try {
            // Validate file
            $this->validateFile($file);

            // Generate unique filename
            $fileName = $this->generateUniqueFilename($file['name']);
            $fileType = $this->getFileType($file['type']);
            $uploadPath = $this->uploadBasePath . $entityType . '/' . date('Y/m/');
            
            // Ensure upload directory exists
            if (!is_dir(dirname(PUBLIC_PATH . $uploadPath))) {
                mkdir(dirname(PUBLIC_PATH . $uploadPath), 0755, true);
            }

            // Move file to destination
            $fullPath = PUBLIC_PATH . $uploadPath . $fileName;
            if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                throw new \Exception("Failed to move uploaded file");
            }

            // Create database record
            $sql = "INSERT INTO media_files (
                entity_type, entity_id, file_type, file_path,
                file_name, mime_type, file_size, status
            ) VALUES (
                :entity_type, :entity_id, :file_type, :file_path,
                :file_name, :mime_type, :file_size, 'active'
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'file_type' => $fileType,
                'file_path' => $uploadPath . $fileName,
                'file_name' => $fileName,
                'mime_type' => $file['type'],
                'file_size' => $file['size']
            ]);

            return [
                'success' => true,
                'file_id' => $this->db->lastInsertId(),
                'file_path' => $uploadPath . $fileName
            ];
        } catch (\Exception $e) {
            $this->logger->error("File upload error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function validateFile(array $file): void {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception("Upload error: " . $file['error']);
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new \Exception("File size exceeds limit");
        }

        $fileType = $this->getFileType($file['type']);
        if (!isset($this->allowedMimeTypes[$fileType]) || 
            !in_array($file['type'], $this->allowedMimeTypes[$fileType])) {
            throw new \Exception("Invalid file type");
        }
    }

    private function getFileType(string $mimeType): string {
        foreach ($this->allowedMimeTypes as $type => $mimeTypes) {
            if (in_array($mimeType, $mimeTypes)) {
                return $type;
            }
        }
        throw new \Exception("Unsupported file type");
    }

    private function generateUniqueFilename(string $originalName): string {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    }

    public function getEntityFiles(string $entityType, int $entityId): array {
        $sql = "SELECT * FROM media_files 
                WHERE entity_type = :entity_type 
                AND entity_id = :entity_id 
                AND status = 'active'
                ORDER BY is_primary DESC, created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'entity_type' => $entityType,
            'entity_id' => $entityId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setPrimaryFile(int $fileId, string $entityType, int $entityId): bool {
        try {
            $this->db->beginTransaction();

            // Reset all files for this entity to non-primary
            $sql = "UPDATE media_files 
                    SET is_primary = FALSE 
                    WHERE entity_type = :entity_type 
                    AND entity_id = :entity_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'entity_type' => $entityType,
                'entity_id' => $entityId
            ]);

            // Set the selected file as primary
            $sql = "UPDATE media_files 
                    SET is_primary = TRUE 
                    WHERE file_id = :file_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['file_id' => $fileId]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error("Error setting primary file: " . $e->getMessage());
            return false;
        }
    }
}
