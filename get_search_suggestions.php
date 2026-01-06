<?php
require_once 'config.php';
header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

$suggestions = [];
$query_escaped = mysqli_real_escape_string($conn, $query);

// Get product names that start with or contain the search query
$sql = "SELECT DISTINCT name 
        FROM products 
        WHERE name LIKE '%{$query_escaped}%' 
        AND is_active = 1 
        ORDER BY name ASC 
        LIMIT 10";

$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $suggestions[] = $row['name'];
    }
}

echo json_encode($suggestions);
?>
