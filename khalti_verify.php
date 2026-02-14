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

// Verify payment with Khalti Lookup API
$secret_key = getKhaltiSecretKey();

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, KHALTI_LOOKUP_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['pidx' => $pidx]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Key ' . $secret_key,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    error_log("Khalti lookup cURL error: $curl_error");
    header("Location: checkout.php?error=verification_failed");
    exit();
}

$result = json_decode($response, true);

if (!$result) {
    error_log("Khalti lookup: Invalid JSON response - HTTP $http_code");
    header("Location: checkout.php?error=verification_failed");
    exit();
}

$payment_status_khalti = $result['status'] ?? 'unknown';

if ($payment_status_khalti === 'Completed') {
    // Payment verified successfully
    $transaction_id = $result['transaction_id'] ?? $pidx;
    
    $update_stmt = mysqli_prepare($conn, "UPDATE orders SET payment_status = 'paid', transaction_id = ? WHERE order_number = ? AND user_id = ?");
    mysqli_stmt_bind_param($update_stmt, "ssi", $transaction_id, $order_id, $_SESSION['user_id']);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
    
    header("Location: index.php?order_success=1&order_id=" . urlencode($order_id) . "&payment=khalti");
    exit();
    
} elseif ($payment_status_khalti === 'Pending' || $payment_status_khalti === 'Initiated') {
    // Payment still pending
    header("Location: checkout.php?error=payment_incomplete&status=" . urlencode($payment_status_khalti));
    exit();
    
} elseif ($payment_status_khalti === 'Expired' || $payment_status_khalti === 'User canceled') {
    // Payment expired or canceled
    $cancel_stmt = mysqli_prepare($conn, "UPDATE orders SET status = 'cancelled', payment_status = 'unpaid' WHERE order_number = ? AND user_id = ?");
    mysqli_stmt_bind_param($cancel_stmt, "si", $order_id, $_SESSION['user_id']);
    mysqli_stmt_execute($cancel_stmt);
    mysqli_stmt_close($cancel_stmt);
    
    header("Location: checkout.php?error=payment_cancelled");
    exit();
    
} else {
    // Unknown status - log and notify
    error_log("Khalti lookup unknown status: $payment_status_khalti for order $order_id");
    header("Location: checkout.php?error=payment_incomplete&status=" . urlencode($payment_status_khalti));
    exit();
}
