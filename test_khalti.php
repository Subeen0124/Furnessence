<?php
require_once 'config.php';
require_once 'khalti_config.php';

echo "=== KHALTI PAYMENT DEBUG ===\n\n";

// 1. Check Khalti config
echo "1. Khalti Config:\n";
echo "   Mode: " . KHALTI_MODE . "\n";
echo "   Base URL: " . KHALTI_BASE_URL . "\n";
echo "   Initiate URL: " . KHALTI_INITIATE_URL . "\n";
echo "   Lookup URL: " . KHALTI_LOOKUP_URL . "\n";
$key = getKhaltiSecretKey();
echo "   Secret Key: " . ($key === 'your_test_live_secret_key_here' ? '❌ NOT SET (placeholder)' : '✅ Set (' . substr($key, 0, 10) . '...)') . "\n";

// 2. Check cURL extension
echo "\n2. cURL enabled: " . (function_exists('curl_init') ? '✅ YES' : '❌ NO') . "\n";

// 3. Check orders table payment columns
echo "\n3. Orders table payment columns:\n";
$cols_to_check = ['payment_method', 'payment_status', 'transaction_id'];
foreach ($cols_to_check as $col) {
    $r = mysqli_query($conn, "SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = '$col'");
    $row = mysqli_fetch_assoc($r);
    if ($row) {
        echo "   ✅ $col: " . $row['COLUMN_TYPE'] . " (default: " . ($row['COLUMN_DEFAULT'] ?? 'NULL') . ")\n";
    } else {
        echo "   ❌ $col: MISSING!\n";
    }
}

// 4. Test API only if key is set
if ($key !== 'your_test_live_secret_key_here' && $key !== 'your_production_live_secret_key_here') {
    echo "\n4. Testing Khalti API...\n";
    $test_payload = [
        'return_url' => 'http://localhost/Furnessence/khalti_verify.php',
        'website_url' => 'http://localhost/Furnessence/',
        'amount' => 1300,
        'purchase_order_id' => 'TEST-' . time(),
        'purchase_order_name' => 'Test Order',
        'customer_info' => [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '9800000000'
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, KHALTI_INITIATE_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Key ' . $key,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    echo "   HTTP Code: $http_code\n";
    if ($curl_error) echo "   cURL Error: $curl_error\n";
    
    $decoded = json_decode($response, true);
    if ($decoded) {
        if ($http_code === 200 && isset($decoded['payment_url'])) {
            echo "   ✅ SUCCESS! Payment URL: " . $decoded['payment_url'] . "\n";
        } else {
            echo "   ❌ FAILED: " . ($decoded['detail'] ?? json_encode($decoded)) . "\n";
        }
    } else {
        echo "   ❌ No valid JSON response\n";
    }
} else {
    echo "\n4. ⚠️  Khalti API test SKIPPED - secret key not configured yet.\n";
    echo "   → Sign up at https://test-admin.khalti.com/#/join/merchant\n";
    echo "   → Copy your 'live_secret_key' from the merchant dashboard\n";
    echo "   → Paste it in khalti_config.php as KHALTI_TEST_SECRET_KEY\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
