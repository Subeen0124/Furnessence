<?php
/**
 * eSewa Payment Gateway Configuration
 * 
 * IMPORTANT: You must register as a merchant to get your keys.
 * 
 * For SANDBOX (Testing):
 *   - Merchant Code: EPAYTEST
 *   - Secret Key: 8gBm/:&EnhH.1/q
 *   - eSewa Test URL: https://rc-epay.esewa.com.np/api/epay/main/v2/form
 *   - Status Check URL: https://rc.esewa.com.np/api/epay/transaction/status/
 * 
 * For PRODUCTION:
 *   - Get your merchant code from eSewa
 *   - Get your secret key from eSewa
 *   - Change ESEWA_MODE to 'live'
 * 
 * Test Credentials for eSewa Sandbox (user-side payment):
 * - eSewa ID: 9806800001 to 9806800005
 * - Password: Nepal@123
 * - MPIN: 1122
 * - OTP Token: 123456
 */

// Set to 'test' for sandbox or 'live' for production
define('ESEWA_MODE', 'test');

// Sandbox credentials
define('ESEWA_TEST_MERCHANT_CODE', 'EPAYTEST');
define('ESEWA_TEST_SECRET_KEY', '8gBm/:&EnhH.1/q');

// Production credentials (get from eSewa after successful test)
define('ESEWA_LIVE_MERCHANT_CODE', 'your_production_merchant_code_here');
define('ESEWA_LIVE_SECRET_KEY', 'your_production_secret_key_here');

// Get active credentials based on mode
function getEsewaSecretKey() {
    return ESEWA_MODE === 'live' ? ESEWA_LIVE_SECRET_KEY : ESEWA_TEST_SECRET_KEY;
}

function getEsewaMerchantCode() {
    return ESEWA_MODE === 'live' ? ESEWA_LIVE_MERCHANT_CODE : ESEWA_TEST_MERCHANT_CODE;
}

// eSewa API URLs
if (ESEWA_MODE === 'test') {
    define('ESEWA_PAYMENT_URL', 'https://rc-epay.esewa.com.np/api/epay/main/v2/form');
    define('ESEWA_STATUS_URL', 'https://rc.esewa.com.np/api/epay/transaction/status/');
} else {
    define('ESEWA_PAYMENT_URL', 'https://epay.esewa.com.np/api/epay/main/v2/form');
    define('ESEWA_STATUS_URL', 'https://esewa.com.np/api/epay/transaction/status/');
}

/**
 * Generate HMAC SHA256 signature for eSewa ePay v2
 * 
 * @param float $total_amount Total payment amount
 * @param string $transaction_uuid Unique transaction identifier
 * @param string $product_code Merchant code
 * @return string Base64-encoded HMAC SHA256 signature
 */
function generateEsewaSignature($total_amount, $transaction_uuid, $product_code = null) {
    if ($product_code === null) {
        $product_code = getEsewaMerchantCode();
    }
    $secret_key = getEsewaSecretKey();
    
    // Input format: total_amount=VALUE,transaction_uuid=VALUE,product_code=VALUE
    $message = "total_amount={$total_amount},transaction_uuid={$transaction_uuid},product_code={$product_code}";
    
    // Generate HMAC SHA256 and encode as base64
    $hash = hash_hmac('sha256', $message, $secret_key, true);
    return base64_encode($hash);
}

/**
 * Verify eSewa response signature
 * 
 * @param array $response_data Decoded response data from eSewa
 * @return bool True if signature is valid
 */
function verifyEsewaSignature($response_data) {
    $secret_key = getEsewaSecretKey();
    
    // Build the message from signed_field_names
    $signed_fields = explode(',', $response_data['signed_field_names']);
    $parts = [];
    foreach ($signed_fields as $field) {
        $field = trim($field);
        if (isset($response_data[$field])) {
            $parts[] = $field . '=' . $response_data[$field];
        }
    }
    $message = implode(',', $parts);
    
    // Generate expected signature
    $expected_hash = hash_hmac('sha256', $message, $secret_key, true);
    $expected_signature = base64_encode($expected_hash);
    
    // Compare with received signature
    return hash_equals($expected_signature, $response_data['signature'] ?? '');
}
