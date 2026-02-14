<?php
/**
 * Khalti Payment Gateway Configuration
 * 
 * IMPORTANT: You must register as a merchant to get your keys.
 * 
 * For SANDBOX (Testing):
 *   1. Sign up at https://test-admin.khalti.com/#/join/merchant
 *   2. Log in using OTP: 987654
 *   3. Copy your 'live_secret_key' from the dashboard
 *   4. Paste it below as KHALTI_TEST_SECRET_KEY
 * 
 * For PRODUCTION:
 *   1. Sign up at https://admin.khalti.com
 *   2. Copy your 'live_secret_key' from the dashboard  
 *   3. Paste it below as KHALTI_LIVE_SECRET_KEY
 *   4. Change KHALTI_MODE to 'live'
 * 
 * Test Credentials for Khalti Sandbox (user-side payment):
 * - Khalti ID: 9800000000 to 9800000005
 * - MPIN: 1111
 * - OTP: 987654
 */

// Set to 'test' for sandbox or 'live' for production
define('KHALTI_MODE', 'test');

// Sandbox Key (from test-admin.khalti.com → your live_secret_key)
define('KHALTI_TEST_SECRET_KEY', '161107eaf13349958a732ec7d2a85d64');

// Production Key (from admin.khalti.com → your live_secret_key)
define('KHALTI_LIVE_SECRET_KEY', 'your_production_live_secret_key_here');

// Get active secret key based on mode
function getKhaltiSecretKey() {
    return KHALTI_MODE === 'live' ? KHALTI_LIVE_SECRET_KEY : KHALTI_TEST_SECRET_KEY;
}

// Khalti API URLs (Sandbox = dev.khalti.com, Production = khalti.com)
if (KHALTI_MODE === 'test') {
    define('KHALTI_BASE_URL', 'https://dev.khalti.com/api/v2');
} else {
    define('KHALTI_BASE_URL', 'https://khalti.com/api/v2');
}

define('KHALTI_INITIATE_URL', KHALTI_BASE_URL . '/epayment/initiate/');
define('KHALTI_LOOKUP_URL', KHALTI_BASE_URL . '/epayment/lookup/');
