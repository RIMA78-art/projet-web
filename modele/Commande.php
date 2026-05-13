<?php
require_once __DIR__ . '/config.php';

class Commande {
    // ── Créer une commande ────────────────────────────────────────────
    public static function creer($data) {
        $customerName = trim($data['customer_name'] ?? '');
        $address      = trim($data['address'] ?? '');
        $telephone    = preg_replace('/\D+/', '', (string)($data['telephone'] ?? ''));
        $totalPrice   = floatval($data['total_price'] ?? 0);
        $userEmail    = trim($data['user_email'] ?? '');
        $itemsPayload = $data['items'] ?? null;

        if ($customerName === '' || $address === '' || $totalPrice <= 0)
            return ['success' => false, 'error' => 'Nom, adresse et prix total valide requis'];
        if (!preg_match('/^\d{8}$/', $telephone))
            return ['success' => false, 'error' => 'Le téléphone doit contenir exactement 8 chiffres'];

        // Décoder les items
        $items = [];
        if (!empty($itemsPayload)) {
            if (is_string($itemsPayload)) {
                $decoded = json_decode($itemsPayload, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                    $items = $decoded;
            } elseif (is_array($itemsPayload)) {
                $items = $itemsPayload;
            }
        }

        // Fallback : récupérer depuis le panier en base
        if (empty($items) && $userEmail !== '')
            $items = self::getCartItemsByEmail($userEmail);

        if (empty($items))
            return ['success' => false, 'error' => 'Les articles de commande sont requis'];

        $pdo = config::getConnexion();

        // Bloquer si l'utilisateur a déjà une commande confirmée
        if ($userEmail !== '') {
            $check = $pdo->prepare("SELECT id FROM orders WHERE user_email = ? LIMIT 1");
            $check->execute([$userEmail]);
            if ($check->fetch())
                return ['success' => false, 'error' => 'Vous avez déjà une commande confirmée. Une seule commande par compte est autorisée.'];
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO orders (customer_name, address, telephone, total_price, user_email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$customerName, $address, $telephone, $totalPrice, $userEmail ?: null]);
            $orderId = $pdo->lastInsertId();

            self::saveOrderItems($pdo, $orderId, $items);

            // Vider le panier de l'utilisateur après commande
            if ($userEmail !== '') {
                $del = $pdo->prepare("DELETE FROM panier WHERE user_email = ?");
                $del->execute([$userEmail]);
            }

            $pdo->commit();
            return ['success' => true, 'id' => $orderId, 'message' => 'Commande confirmée avec succès'];
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ── Toutes les commandes ──────────────────────────────────────────
    public static function toutesLesCommandes() {
        $pdo = config::getConnexion();
        $stmt = $pdo->query("SELECT id, customer_name, address, telephone, total_price, user_email, created_at FROM orders ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    // ── Détails d'une commande ────────────────────────────────────────
    public static function details($orderId) {
        $pdo = config::getConnexion();
        $orderId = intval($orderId);

        $stmt = $pdo->prepare("SELECT id, customer_name, address, telephone, total_price, user_email, created_at FROM orders WHERE id = ? LIMIT 1");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) return null;

        $stmtItems = $pdo->prepare("SELECT product_name, quantity, unit_price, total_price FROM order_items WHERE order_id = ? ORDER BY id ASC");
        $stmtItems->execute([$orderId]);
        $order['items'] = $stmtItems->fetchAll();
        return $order;
    }

    // ── Supprimer une commande ────────────────────────────────────────
    public static function supprimer($orderId) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([intval($orderId)]);
        return $stmt->rowCount() > 0;
    }

    // ── Rechercher des commandes ──────────────────────────────────────
    public static function rechercher($term) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT id, customer_name, address, telephone, total_price, user_email, created_at FROM orders WHERE customer_name LIKE ? ORDER BY created_at DESC LIMIT 20");
        $stmt->execute(['%' . trim($term) . '%']);
        return $stmt->fetchAll();
    }

    // ── Statistiques ──────────────────────────────────────────────────
    public static function getStats($limit = 8) {
        $pdo = config::getConnexion();

        $stmt = $pdo->query("SELECT COALESCE(SUM(total_price), 0) AS total_profit, COUNT(*) AS total_orders FROM orders");
        $totals = $stmt->fetch();

        $limit = intval($limit);
        $stmtRecent = $pdo->query("SELECT id, customer_name, telephone, total_price, created_at FROM orders ORDER BY created_at DESC LIMIT $limit");
        $recentOrders = $stmtRecent->fetchAll();

        return [
            'total_profit'  => floatval($totals['total_profit'] ?? 0),
            'total_orders'  => intval($totals['total_orders'] ?? 0),
            'recent_orders' => $recentOrders
        ];
    }

    // ── Produits populaires ───────────────────────────────────────────
    public static function getPopularItems($limit = 3) {
        $pdo = config::getConnexion();
        $limit = intval($limit);
        $stmt = $pdo->query("SELECT oi.product_name, SUM(oi.quantity) AS total_quantity, COUNT(DISTINCT o.customer_name) AS unique_customers, ROUND(SUM(oi.total_price), 2) AS total_revenue
            FROM order_items oi INNER JOIN orders o ON oi.order_id = o.id
            GROUP BY oi.product_name ORDER BY total_quantity DESC LIMIT $limit");
        return $stmt->fetchAll();
    }

    // ── Achats récents (order items) ──────────────────────────────────
    public static function getRecentPurchaseItems($limit = 8) {
        $pdo = config::getConnexion();
        $limit = intval($limit);
        $stmt = $pdo->query("SELECT oi.id, oi.product_name AS Nom, oi.unit_price AS Prix, oi.quantity, oi.total_price, o.customer_name AS user_email, oi.created_at
            FROM order_items oi INNER JOIN orders o ON oi.order_id = o.id
            ORDER BY oi.created_at DESC LIMIT $limit");
        return $stmt->fetchAll();
    }

    // ── Private helpers ───────────────────────────────────────────────
    private static function saveOrderItems($pdo, $orderId, $items) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
        foreach ($items as $item) {
            $productName = trim($item['product_name'] ?? $item['Nom'] ?? '');
            $quantity = max(1, intval($item['quantity'] ?? 1));
            $unitPrice = floatval($item['unit_price'] ?? $item['Prix'] ?? 0);
            $total = floatval($item['total_price'] ?? ($unitPrice * $quantity));
            if ($productName === '' || $unitPrice <= 0) throw new Exception('Article invalide');
            $stmt->execute([$orderId, $productName, $quantity, $unitPrice, $total]);
        }
    }

    private static function getCartItemsByEmail($email) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT Nom AS product_name, Prix AS unit_price, COUNT(*) AS quantity, SUM(Prix) AS total_price FROM panier WHERE user_email = ? GROUP BY Nom, Prix");
        $stmt->execute([trim($email)]);
        return $stmt->fetchAll();
    }
}
