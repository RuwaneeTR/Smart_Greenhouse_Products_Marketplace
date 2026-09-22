<?php
session_start();
require_once '../includes/dbConnection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$notifId = (int)($data['id'] ?? 0);

if ($notifId > 0) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notifId, $_SESSION['user_id']]);
}

echo json_encode(['success' => true]);