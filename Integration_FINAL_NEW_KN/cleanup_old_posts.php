<?php
/**
 * Cleanup Script - Remove old test posts
 * Run this once to clear old posts that cause 404 errors
 */

require_once 'models/Database.php';
require_once 'models/Post.php';

try {
    // Create Database connection
    $db = new Database();
    $conn = $db->connect();
    
    // First disable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS=0");
    
    // Delete all comments first
    $conn->exec("TRUNCATE TABLE commentaire");
    
    // Delete all posts
    $conn->exec("TRUNCATE TABLE post");
    
    // Re-enable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS=1");
    
    echo "<h2 style='color: green; font-family: Arial;'>✅ All old posts and comments have been deleted!</h2>";
    echo "<p style='font-family: Arial;'>The 404 errors should now be gone. You can start creating new posts with images.</p>";
    echo "<p style='font-family: Arial;'><a href='index.php?action=home'>← Back to home</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red; font-family: Arial;'>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
