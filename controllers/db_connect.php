<?php
// Database connection file for NutriNova project
// Connects to the integration_nutrition_ai database

$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password (empty)
$dbname = "integration_nutrition_ai";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    throw new Exception("Connection failed: " . $conn->connect_error);
}

// Optional: Set charset to utf8mb4 for better Unicode support
$conn->set_charset("utf8mb4");

// Connection successful
// You can now use $conn to perform database operations
?>