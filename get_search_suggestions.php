<?php
require_once 'config.php';
header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

$suggestions = [];

// Get product names that start with or contain the search query
$search_param = '%' . $query . '%';
$stmt = mysqli_prepare($conn, "SELECT DISTINCT name 
        FROM products 
        WHERE name LIKE ? 
        AND is_active = 1 
        ORDER BY name ASC 
        LIMIT 10");
mysqli_stmt_bind_param($stmt, "s", $search_param);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $suggestions[] = $row['name'];
    }
}
mysqli_stmt_close($stmt);

echo json_encode($suggestions);
?>
