<?php
/**
 * eSewa Payment Integration Test Script
 * Run: php test_esewa.php (CLI) or visit in browser
 */
require_once 'esewa_config.php';

echo "=== ESEWA PAYMENT DEBUG ===\n\n";

// 1. Check eSewa config
echo "1. eSewa Config:\n";
echo "   Mode: " . ESEWA_MODE . "\n";
echo "   Merchant Code: " . getEsewaMerchantCode() . "\n";
echo "   Payment URL: " . ESEWA_PAYMENT_URL . "\n";
echo "   Status Check URL: " . ESEWA_STATUS_URL . "\n";
$key = getEsewaSecretKey();
echo "   Secret Key: " . ($key && strlen($key) > 5 ? '✅ Set (' . strlen($key) . ' chars)' : '❌ NOT SET') . "\n\n";

// 2. Check cURL
echo "2. cURL Extension: " . (function_exists('curl_init') ? '✅ Available' : '❌ Missing') . "\n";
echo "   hash_hmac: " . (function_exists('hash_hmac') ? '✅ Available' : '❌ Missing') . "\n\n";

// 3. Test signature generation
echo "3. Signature Generation Test:\n";
$test_signature = generateEsewaSignature("110", "241028", "EPAYTEST");
echo "   Input: total_amount=110,transaction_uuid=241028,product_code=EPAYTEST\n";
echo "   Generated: $test_signature\n";
// Expected from eSewa docs: i94zsd3oXF6ZsSr/kGqT4sSzYQzjj1W/waxjWyRwaME=
$expected = "i94zsd3oXF6ZsSr/kGqT4sSzYQzjj1W/waxjWyRwaME=";
echo "   Expected:  $expected\n";
echo "   Match: " . ($test_signature === $expected ? '✅ MATCH' : '❌ MISMATCH') . "\n\n";

// 4. Check DB columns
echo "4. Database Columns Check:\n";
require_once 'config.php';
$cols_to_check = ['payment_method', 'payment_status', 'transaction_id'];
foreach ($cols_to_check as $col) {
    $check = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE '$col'");
    echo "   orders.$col: " . (mysqli_num_rows($check) > 0 ? '✅ Exists' : '❌ Missing') . "\n";
}

// 5. Test Status Check API
echo "\n5. Testing eSewa Status Check API...\n";
$test_url = ESEWA_STATUS_URL . '?' . http_build_query([
    'product_code' => 'EPAYTEST',
    'total_amount' => '100',
    'transaction_uuid' => 'test-' . time()
]);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    echo "   ❌ cURL Error: $curl_error\n";
} else {
    echo "   HTTP Code: $http_code\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "   Status: " . ($result['status'] ?? 'N/A') . "\n";
        echo "   ✅ API is reachable\n";
    } else {
        echo "   Response: " . substr($response, 0, 200) . "\n";
        echo "   " . ($http_code >= 200 && $http_code < 500 ? '✅ API is reachable' : '❌ API issue') . "\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";
echo "\nTo test a payment:\n";
echo "1. Add a product to cart and go to checkout\n";
echo "2. Select 'eSewa' payment method\n";
echo "3. eSewa Test Credentials:\n";
echo "   - eSewa ID: 9806800001 (or 9806800002-5)\n";
echo "   - Password: Nepal@123\n";
echo "   - MPIN: 1122\n";
echo "   - OTP Token: 123456\n";
