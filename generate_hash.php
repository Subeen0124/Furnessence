<?php
// Generate correct password hash for "admin123"
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password hash for 'admin123':\n";
echo $hash . "\n";
?>
