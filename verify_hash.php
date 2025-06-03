<?php
$password = '2MXIE49z1tpNpsBB';
$current_hash = '$2y$10$YwB0MHB8vRlJOYxFl3Y1/.WcQnY0rI9nXzqkr1YhL4jgzVrxhKmPi';

// Verify current hash
$verify = password_verify($password, $current_hash);
echo "Current hash verification: " . ($verify ? "SUCCESS" : "FAILED") . "\n";

if (!$verify) {
    // Generate new hash
    $new_hash = password_hash($password, PASSWORD_DEFAULT);
    echo "New hash generated: " . $new_hash . "\n";
    
    // Verify new hash
    $verify_new = password_verify($password, $new_hash);
    echo "New hash verification: " . ($verify_new ? "SUCCESS" : "FAILED") . "\n";
} 