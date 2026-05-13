<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../modele/config.php';
require_once __DIR__ . '/../../modele/Session.php';
require_once __DIR__ . '/../../modele/Product.php';
require_once __DIR__ . '/../../modele/Panier.php';
require_once __DIR__ . '/../../modele/Commande.php';

Session::demarrer();
$user = Session::getUtilisateur();

$action = $_GET['action'] ?? '';

switch ($action) {

    // ── Produits ──────────────────────────────────────────────────
    case 'get_products':
        echo json_encode(['success' => true, 'products' => Product::tousLesProduits()]);
        break;

    // ── Panier ───────────────────────────────────────────────────
    case 'add_to_cart':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success' => false, 'error' => 'POST requis']); break; }
        $email = $_POST['email'] ?? ($user['email'] ?? '');
        echo json_encode(Panier::ajouter([
            'user_email'  => $email,
            'nom'         => $_POST['nom'] ?? '',
            'prix'        => $_POST['prix'] ?? '',
            'description' => $_POST['description'] ?? ''
        ]));
        break;

    case 'get_cart':
        $email = $_GET['email'] ?? ($user['email'] ?? '');
        echo json_encode(Panier::parEmail($email));
        break;

    case 'remove_from_cart':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success' => false, 'error' => 'POST requis']); break; }
        $email = $_POST['email'] ?? ($user['email'] ?? '');
        if (!empty($_POST['id'])) {
            echo json_encode(Panier::supprimer(intval($_POST['id']), $email));
        } elseif (isset($_POST['nom'], $_POST['prix'])) {
            echo json_encode(Panier::supprimerParNomPrix($_POST['nom'], $_POST['prix'], $email));
        } else {
            echo json_encode(['success' => false, 'error' => 'ID ou nom+prix requis']);
        }
        break;

    // ── Commandes ────────────────────────────────────────────────
    case 'create_order':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success' => false, 'error' => 'POST requis']); break; }
        echo json_encode(Commande::creer([
            'customer_name' => $_POST['customer_name'] ?? '',
            'address'       => $_POST['address'] ?? '',
            'telephone'     => $_POST['telephone'] ?? '',
            'total_price'   => $_POST['total_price'] ?? '',
            'user_email'    => $_POST['user_email'] ?? ($user['email'] ?? ''),
            'items'         => $_POST['items'] ?? null
        ]));
        break;

    case 'get_orders':
        $email = $_GET['email'] ?? ($user['email'] ?? '');
        echo json_encode(['success' => true, 'orders' => Commande::toutesLesCommandes()]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Action inconnue']);
}
