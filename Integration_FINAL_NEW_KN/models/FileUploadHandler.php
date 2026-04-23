<?php
/**
 * Image Upload Handler
 * Handles image uploads for posts with validation and storage
 */
class FileUploadHandler {
    private $uploadDir = 'uploads/images/';
    private $maxFileSize = 5 * 1024 * 1024; // 5MB for images
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    public function __construct() {
        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Validate image upload
     * @param array $file $_FILES['fichier']
     * @return array {success, error, filename}
     */
    public function validateFile($file) {
        // Check if file was uploaded without error
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => 'Image upload error or no file provided'
            ];
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return [
                'success' => false,
                'error' => 'Image size exceeds 5MB limit'
            ];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            return [
                'success' => false,
                'error' => 'Invalid image type. Allowed: JPG, PNG, GIF, WEBP'
            ];
        }
        
        // Check MIME type (verify it's actually an image)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedMimes)) {
            return [
                'success' => false,
                'error' => 'Invalid image format'
            ];
        }
        
        return ['success' => true];
    }
    
    /**
     * Save uploaded image
     * @param array $file $_FILES['fichier']
     * @return array {success, error, filename}
     */
    public function saveFile($file) {
        // Validate file
        $validation = $this->validateFile($file);
        if (!$validation['success']) {
            return $validation;
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('post_') . '_' . time() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to save image'
            ];
        }
    }
    
    /**
     * Delete an image
     * @param string $filename
     * @return bool
     */
    public function deleteFile($filename) {
        if (!$filename) return false;
        
        $filepath = $this->uploadDir . basename($filename);
        
        if (file_exists($filepath) && is_file($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
    
    /**
     * Get upload directory path
     * @return string
     */
    public function getUploadDir() {
        return $this->uploadDir;
    }
}
?>

