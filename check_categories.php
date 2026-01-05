<?php
require_once 'config.php';

echo "=== Categories Check ===\n\n";

$result = mysqli_query($conn, 'SELECT * FROM categories ORDER BY id');

if ($result && mysqli_num_rows($result) > 0) {
    echo "Categories in database:\n";
    echo "---------------------------------------------------\n";
    while($cat = mysqli_fetch_assoc($result)) {
        echo "ID: " . $cat['id'] . " | ";
        echo "Name: " . $cat['name'] . " | ";
        echo "Slug: " . $cat['slug'] . "\n";
    }
} else {
    echo "No categories found or query error: " . mysqli_error($conn) . "\n";
}
?>
