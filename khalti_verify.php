<?php
/**
 * Khalti Payment Verification Callback
 * 
 * This file handles the callback from Khalti after payment.
 * Khalti redirects here with pidx parameter which we verify.
 */
require_once 'config.php';
require_once 'khalti_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pidx = isset($_GET['pidx']) ? $_GET['pidx'] : '';
$order_id = isset($_GET['purchase_order_id']) ? $_GET['purchase_order_id'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

if (empty($pidx) || empty($order_id)) {
    header("Location: checkout.php?error=invalid_payment");
    exit();
}

// Handle user cancellation
if ($status === 'User canceled') {
    // Cancel the order
    $cancel_stmt = mysqli_prepare($conn, "UPDATE orders SET status = 'cancelled', payment_status = 'unpaid' WHERE order_number = ? AND user_id = ?");
    mysqli_stmt_bind_param($cancel_stmt, "si", $order_id, $_SESSION['user_id']);
    mysqli_stmt_execute($cancel_stmt);
    mysqli_stmt_close($cancel_stmt);
    
    header("Location: checkout.php?error=payment_cancelled");
    exit();
}

// Verify payment with Khalti API
$secret_key = getKhaltiSecretKey();

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, KHALTI_LOOKUP_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['pidx' => $pidx]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: key ' . $secret_key,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    header("Location: checkout.php?error=verification_failed");
    exit();
}

$result = json_decode($response, true);

if ($result && $result['status'] === 'Completed') {
    // Payment verified - update order
    $transaction_id = $result['transaction_id'] ?? $pidx;
    
    $update_stmt = mysqli_prepare($conn, "UPDATE orders SET payment_status = 'paid', transaction_id = ? WHERE order_number = ? AND user_id = ?");
    mysqli_stmt_bind_param($update_stmt, "ssi", $transaction_id, $order_id, $_SESSION['user_id']);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
    
    header("Location: index.php?order_success=1&order_id=" . urlencode($order_id) . "&payment=khalti");
    exit();
} else {
    // Payment not completed
    header("Location: checkout.php?error=payment_incomplete&status=" . urlencode($result['status'] ?? 'unknown'));
    exit();
}
