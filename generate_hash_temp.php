<?php
$password = '2MXIE49z1tpNpsBB';
$hash = password_hash($password, PASSWORD_DEFAULT);

// Проверяем, что хеш работает
$verify = password_verify($password, $hash);

echo "Password: " . $password . "\n";
echo "Generated hash: " . $hash . "\n";
echo "Verification test: " . ($verify ? "SUCCESS" : "FAILED") . "\n";

// Проверяем текущий хеш из конфига
$current_hash = '$2y$10$YwB0MHB8vRlJOYxFl3Y1/.WcQnY0rI9nXzqkr1YhL4jgzVrxhKmPi';
$verify_current = password_verify($password, $current_hash);
echo "Current hash verification: " . ($verify_current ? "SUCCESS" : "FAILED") . "\n";
?> 