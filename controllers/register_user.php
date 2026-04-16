<?php
header('Content-Type: application/json; charset=utf-8');

$response = array();

try {
    require_once 'db_connect.php';

    $nom = isset($_POST['nom']) ? $conn->real_escape_string(trim($_POST['nom'])) : '';
    $prenom = isset($_POST['prenom']) ? $conn->real_escape_string(trim($_POST['prenom'])) : '';
    $email = isset($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : '';
    $mot_de_passe = isset($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';
    $taille = isset($_POST['taille']) && $_POST['taille'] ? intval($_POST['taille']) : 0;
    $poids = isset($_POST['poids']) && $_POST['poids'] ? floatval($_POST['poids']) : 0;
    $objectif = isset($_POST['objectif']) ? $conn->real_escape_string(trim($_POST['objectif'])) : '';
    $niveau_sportif = isset($_POST['niveau_sportif']) ? $conn->real_escape_string(trim($_POST['niveau_sportif'])) : '';

    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe)) {
        $response['success'] = false;
        $response['error'] = 'Missing required fields';
        echo json_encode($response);
        exit;
    }

    if (strlen($mot_de_passe) < 6) {
        $response['success'] = false;
        $response['error'] = 'Password too short';
        echo json_encode($response);
        exit;
    }

    $create_sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        Nom VARCHAR(255) NOT NULL,
        Prenom VARCHAR(255) NOT NULL,
        Email VARCHAR(255) NOT NULL UNIQUE,
        Mot_de_passe VARCHAR(255) NOT NULL,
        Taille_cm INT DEFAULT NULL,
        Poids_kg FLOAT DEFAULT NULL,
        Objectif VARCHAR(255) DEFAULT NULL,
        Niveau_sportif VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!$conn->query($create_sql)) {
        $response['success'] = false;
        $response['error'] = 'Table error: ' . $conn->error;
        echo json_encode($response);
        exit;
    }

    $check_sql = "SELECT id FROM users WHERE Email='" . $email . "' LIMIT 1";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        $response['success'] = false;
        $response['error'] = 'Email already exists';
        echo json_encode($response);
        exit;
    }

    $password_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    $password_hash = $conn->real_escape_string($password_hash);

    $insert_sql = "INSERT INTO users (Nom, Prenom, Email, Mot_de_passe, Taille_cm, Poids_kg, Objectif, Niveau_sportif) 
                   VALUES ('" . $nom . "', '" . $prenom . "', '" . $email . "', '" . $password_hash . "', " . $taille . ", " . $poids . ", '" . $objectif . "', '" . $niveau_sportif . "')";

    if ($conn->query($insert_sql)) {
        $response['success'] = true;
        $response['message'] = 'User created successfully';
        $response['id'] = $conn->insert_id;
    } else {
        $response['success'] = false;
        $response['error'] = 'Insert failed: ' . $conn->error;
    }

    $conn->close();
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = 'Exception: ' . $e->getMessage();
}

echo json_encode($response);
?>
