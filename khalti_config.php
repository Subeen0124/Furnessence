<?php
/**
 * Khalti Payment Gateway Configuration
 * 
 * For TESTING: Use the test keys below
 * For PRODUCTION: Replace with your live Khalti keys from https://admin.khalti.com
 * 
 * Test Credentials for Khalti Sandbox:
 * - Khalti ID: 9800000000 (or any 10-digit number)
 * - MPIN: 1111
 * - OTP: 987654
 */

// Set to 'test' for sandbox or 'live' for production
define('KHALTI_MODE', 'test');

// Test Keys (Khalti Sandbox)
define('KHALTI_TEST_PUBLIC_KEY', 'test_public_key_dc74e0fd57cb46cd93832aee0a507256');
define('KHALTI_TEST_SECRET_KEY', 'test_secret_key_f59e8b7d18b4499ca40f68195a846e9b');

// Live Keys (Replace with your actual Khalti keys)
define('KHALTI_LIVE_PUBLIC_KEY', 'your_live_public_key_here');
define('KHALTI_LIVE_SECRET_KEY', 'your_live_secret_key_here');

// Get active keys based on mode
function getKhaltiPublicKey() {
    return KHALTI_MODE === 'live' ? KHALTI_LIVE_PUBLIC_KEY : KHALTI_TEST_PUBLIC_KEY;
}

function getKhaltiSecretKey() {
    return KHALTI_MODE === 'live' ? KHALTI_LIVE_SECRET_KEY : KHALTI_TEST_SECRET_KEY;
}

// Khalti API URLs
define('KHALTI_INITIATE_URL', 'https://a.khalti.com/api/v2/epayment/initiate/');
define('KHALTI_LOOKUP_URL', 'https://a.khalti.com/api/v2/epayment/lookup/');
