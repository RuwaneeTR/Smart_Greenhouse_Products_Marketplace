<?php

// process.php - Payment Processing API

session_start();

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// include database

include '../../includes/dbConnection.php';

// get user ID from session

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if ($user_id <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'You must be logged in to make a payment.'
    ]);
    exit;
}

// payhere credentials

$MERCHANT_ID = "1236366";        // From PayHere Sandbox
$MERCHANT_SECRET = "Nzk4OTE4MDU2MjA1NjgyOTYxNzI4MjI3OTM4MTQ4NjYyNjE5NTU="; // From PayHere Sandbox
$SANDBOX = true;

// get amount from request

$input = json_decode(file_get_contents('php://input'), true);
$amount = $input['amount'] ?? 999.00;

// generate unique order ID
// ============================================================
$order_id = 'ORD_' . uniqid() . '_' . time();

// save transaction to database 

try {
    $sql = "INSERT INTO payment_transactions (order_id, user_id, amount, currency, status) 
            VALUES (?, ?, ?, 'LKR', 'pending')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id, $user_id, $amount]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'DEBUG: ' . $e->getMessage()
    ]);
    exit;
}


// generate payhere hash

$hash = strtoupper(md5(
    $MERCHANT_ID . 
    $order_id . 
    number_format($amount, 2, '.', '') . 
    'LKR' . 
    strtoupper(md5($MERCHANT_SECRET))
));

// return payment data to frontend 

echo json_encode([
    'success' => true,
    'merchant_id' => $MERCHANT_ID,
    'order_id' => $order_id,
    'amount' => number_format($amount, 2, '.', ''),
    'currency' => 'LKR',
    'hash' => $hash,
    'sandbox' => $SANDBOX
]);