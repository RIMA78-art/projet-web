<?php
/**
 * FileUploadHandler — adapté pour NutriNova MVC
 * Gère l'upload d'images pour les posts du forum
 * Upload dir : <NUTRINOVA_MVC_ROOT>/uploads/images/
 */
class FileUploadHandler {
    private $uploadDir;
    private $maxSizeBytes;
    private $allowedExtensions;
    private $allowedMimeTypes;

    public function __construct() {
        // Chemin absolu vers uploads/images/ (racine de NUTRINOVA_MVC)
        $this->uploadDir         = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
        $this->maxSizeBytes      = 5 * 1024 * 1024; // 5 MB
        $this->allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $this->allowedMimeTypes  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    }

    /**
     * Vérifie que le répertoire d'upload existe (le crée si besoin)
     */
    private function ensureUploadDir(): bool {
        if (!is_dir($this->uploadDir)) {
            return mkdir($this->uploadDir, 0755, true);
        }
        return true;
    }

    /**
     * Valide le fichier uploadé
     * @param array $file  Entrée depuis $_FILES['fichier']
     * @return array { valid, error? }
     */
    public function validateFile(array $file): array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE   => 'Fichier trop volumineux (limite php.ini)',
                UPLOAD_ERR_FORM_SIZE  => 'Fichier trop volumineux (limite formulaire)',
                UPLOAD_ERR_PARTIAL    => 'Upload incomplet',
                UPLOAD_ERR_NO_FILE    => 'Aucun fichier',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temp manquant',
                UPLOAD_ERR_CANT_WRITE => 'Écriture impossible',
                UPLOAD_ERR_EXTENSION  => 'Extension PHP bloquante',
            ];
            return ['valid' => false, 'error' => $errors[$file['error']] ?? 'Erreur upload inconnue'];
        }

        if ($file['size'] > $this->maxSizeBytes) {
            return ['valid' => false, 'error' => 'Fichier trop volumineux (max 5MB)'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExtensions, true)) {
            return ['valid' => false, 'error' => 'Extension non autorisée (' . implode(', ', $this->allowedExtensions) . ')'];
        }

        // Vérification MIME réelle
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $this->allowedMimeTypes, true)) {
            return ['valid' => false, 'error' => 'Type de fichier non autorisé'];
        }

        return ['valid' => true];
    }

    /**
     * Sauvegarde le fichier dans uploads/images/
     * @param array $file  Entrée depuis $_FILES['fichier']
     * @return array { success, filename?, error? }
     */
    public function saveFile(array $file): array {
        $validation = $this->validateFile($file);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        if (!$this->ensureUploadDir()) {
            return ['success' => false, 'error' => 'Impossible de créer le dossier upload'];
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'post_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
        $dest     = $this->uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ['success' => false, 'error' => 'Échec du déplacement du fichier'];
        }

        return ['success' => true, 'filename' => $filename];
    }
}
?>
