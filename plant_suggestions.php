<?php
// ============================================================
// plant_suggestions.php - Live Search Suggestions for Plants
// Called by JavaScript via AJAX as user types
// ============================================================
include 'includes/dbConnection.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($query === '') { echo json_encode([]); exit; }

$sql = "SELECT id, name, price
        FROM products
        WHERE name LIKE :q1
          AND category = 'plant'
        LIMIT 6";

$stmt = $pdo->prepare($sql);
$stmt->execute(['q1' => '%' . $query . '%']);
$results = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($results);
exit;