<?php
// ============================================================
// product_suggestions.php - Live Search Suggestions for Products
// Called by JavaScript via AJAX as user types
// Returns matching products as JSON
// ============================================================
include 'includes/dbConnection.php';

$query    = isset($_GET['q'])        ? trim($_GET['q'])   : '';
$category = isset($_GET['category']) ? $_GET['category']  : '';

if ($query === '') { echo json_encode([]); exit; }

// Build query - filter by name, optionally by category
$sql = "SELECT id, name, category, price
        FROM products
        WHERE name LIKE :q1";

$params = ['q1' => '%' . $query . '%'];

// Filter by specific category if provided
if ($category === 'vegetable' || $category === 'fruit') {
    $sql .= " AND category = :category";
    $params['category'] = $category;
} else {
    // Default: only show veg and fruit (not plants)
    $sql .= " AND category IN ('vegetable', 'fruit')";
}

$sql .= " LIMIT 6";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($results);
exit;