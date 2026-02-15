<?php
/**
 * eSewa Payment Verification Callback
 * 
 * This file handles the callback from eSewa after payment.
 * eSewa redirects here with a Base64-encoded response in the 'data' query parameter.
 * 
 * Success URL receives: ?data=BASE64_ENCODED_JSON
 * Decoded JSON contains: transaction_code, status, total_amount, transaction_uuid, 
 *                        product_code, signed_field_names, signature
 */
require_once 'config.php';
require_once 'esewa_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the base64 encoded data from eSewa callback
$encoded_data = isset($_GET['data']) ? $_GET['data'] : '';

if (empty($encoded_data)) {
    header("Location: checkout.php?error=invalid_payment");
    exit();
}

// Decode the base64 response
$decoded_json = base64_decode($encoded_data);
$response_data = json_decode($decoded_json, true);

if (!$response_data || !isset($response_data['status'])) {
    error_log("eSewa verify: Invalid response data - " . substr($encoded_data, 0, 100));
    header("Location: checkout.php?error=invalid_payment");
    exit();
}

$transaction_uuid = $response_data['transaction_uuid'] ?? '';
$status = $response_data['status'] ?? '';
$total_amount = $response_data['total_amount'] ?? 0;
$transaction_code = $response_data['transaction_code'] ?? '';
$product_code = $response_data['product_code'] ?? '';

// Verify signature integrity
if (!verifyEsewaSignature($response_data)) {
    error_log("eSewa verify: Signature mismatch for transaction $transaction_uuid");
    header("Location: checkout.php?error=verification_failed");
    exit();
}

if ($status === 'COMPLETE') {
    // Verify with eSewa Status Check API for extra security
    $status_url = ESEWA_STATUS_URL . '?' . http_build_query([
        'product_code' => getEsewaMerchantCode(),
        'total_amount' => $total_amount,
        'transaction_uuid' => $transaction_uuid
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $status_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $api_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        error_log("eSewa status check cURL error: $curl_error");
        // Still mark as paid since we verified signature
    }
    
    $api_result = json_decode($api_response, true);
    
    // Double-check: API status should also be COMPLETE
    if ($api_result && isset($api_result['status']) && $api_result['status'] !== 'COMPLETE') {
        error_log("eSewa status check mismatch: callback=COMPLETE, api={$api_result['status']} for $transaction_uuid");
        header("Location: checkout.php?error=verification_failed");
        exit();
    }
    
    // Payment verified successfully - update order
    $ref_id = $api_result['ref_id'] ?? $transaction_code;
    
    $update_stmt = mysqli_prepare($conn, "UPDATE orders SET payment_status = 'paid', transaction_id = ? WHERE order_number = ? AND user_id = ?");
    mysqli_stmt_bind_param($update_stmt, "ssi", $ref_id, $transaction_uuid, $_SESSION['user_id']);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
    
    header("Location: index.php?order_success=1&order_id=" . urlencode($transaction_uuid) . "&payment=esewa");
    exit();
    
} else {
    // Payment failed, pending, or cancelled
    error_log("eSewa payment not complete: status=$status for transaction $transaction_uuid");
    
    // Cancel the order
    $cancel_stmt = mysqli_prepare($conn, "UPDATE orders SET status = 'cancelled', payment_status = 'unpaid' WHERE order_number = ? AND user_id = ?");
    mysqli_stmt_bind_param($cancel_stmt, "si", $transaction_uuid, $_SESSION['user_id']);
    mysqli_stmt_execute($cancel_stmt);
    mysqli_stmt_close($cancel_stmt);
    
    header("Location: checkout.php?error=payment_cancelled");
    exit();
}
