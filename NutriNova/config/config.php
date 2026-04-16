<?php
/**
 * Configuration alternative pour développement/test
 * Utilisez ce fichier pour développement local
 */

// ============================================================
// CONFIGURATION DE DÉVELOPPEMENT
// ============================================================

// Afficher les erreurs (À DÉSACTIVER EN PRODUCTION!)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Créer le dossier logs s'il n'existe pas
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// ============================================================
// PARAMÈTRES DE CONNEXION
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'nutrinova');
define('DB_USER', 'root');
define('DB_PASS', '');


// PARAMÈTRES D'ENVIRONNEMENT


define('APP_ENV', 'development'); // development | production
define('APP_DEBUG', true);
define('APP_NAME', 'NutriNova');
define('APP_VERSION', '1.0.0');


// CONSTANTES DE L'APPLICATION


define('MEAL_TYPES', [
    'petit déjeuner' => 'Petit Déjeuner',
    'déjeuner' => 'Déjeuner',
    'dîner' => 'Dîner'
]);

define('ECO_SCORES', ['A', 'B', 'C', 'D', 'E']);


// FONCTIONS UTILITAIRES



 // Rediriger avec un message

function redirect($url, $message = null, $type = 'success') {
    if ($message) {
        $_SESSION[$type] = $message;
    }
    header("Location: " . $url);
    exit;
}


 // Formater un nombre

function format_number($number, $decimals = 2) {
    return number_format($number, $decimals, ',', ' ');
}

/
 // Vérifier si c'est une requête AJAX
 
function is_ajax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}


 // Log un message
 
function log_message($message, $level = 'info') {
    $timestamp = date('Y-m-d H:i:s');
    $log_file = __DIR__ . '/logs/app.log';
    $log_entry = "[$timestamp] [$level] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}


// Dump une variable (debug)

function dump($var) {
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
}

/**
 * JSON Response
 */
function json_response($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}


// INITIALISATION DE SESSION


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// MESSAGES GLOBAUX


$alerts = [];

if (isset($_SESSION['success'])) {
    $alerts[] = ['type' => 'success', 'message' => $_SESSION['success']];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $alerts[] = ['type' => 'error', 'message' => $_SESSION['error']];
    unset($_SESSION['error']);
}

if (isset($_SESSION['warning'])) {
    $alerts[] = ['type' => 'warning', 'message' => $_SESSION['warning']];
    unset($_SESSION['warning']);
}

if (isset($_SESSION['info'])) {
    $alerts[] = ['type' => 'info', 'message' => $_SESSION['info']];
    unset($_SESSION['info']);
}
?>
