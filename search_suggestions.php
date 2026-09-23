<?php
// ============================================================
// search_suggestions.php - Live Search Suggestions API
// Called by JavaScript via AJAX as user types
// Returns matching stores as JSON
// ============================================================

include 'includes/dbConnection.php';

// Get the typed text from URL e.g. ?q=green
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Return empty array if nothing typed
if ($query === '') {
    echo json_encode([]);
    exit;
}

// -------------------------------------------------------
// FIX: PDO does not allow using the same named placeholder
// (:q) more than once in a query.
// Solution: use two different names :q1 and :q2
// and bind the same value to both
// -------------------------------------------------------
$sql = "SELECT id, store_name, city
        FROM stores
        WHERE store_name LIKE :q1 OR city LIKE :q2
        LIMIT 6";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'q1' => '%' . $query . '%',
    'q2' => '%' . $query . '%'
]);
$results = $stmt->fetchAll();

// Return results as JSON
header('Content-Type: application/json');
echo json_encode($results);
exit;
