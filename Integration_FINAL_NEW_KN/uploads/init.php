<?php
// This file ensures the uploads directory exists
if (!is_dir(__DIR__ . '/uploads')) {
    mkdir(__DIR__ . '/uploads', 0755, true);
}

// Create .htaccess to allow downloads (optional security)
$htaccess = __DIR__ . '/uploads/.htaccess';
if (!file_exists($htaccess)) {
    file_put_contents($htaccess, "AddType application/octet-stream .jpg .jpeg .png .pdf .docx\n");
}

// Optional: index.php to prevent directory listing
$index = __DIR__ . '/uploads/index.php';
if (!file_exists($index)) {
    file_put_contents($index, "<?php header('HTTP/1.0 403 Forbidden'); ?>");
}
?>
