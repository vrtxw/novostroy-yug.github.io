<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$password = '2MXIE49z1tpNpsBB';

// Generate new hash
$new_hash = password_hash($password, PASSWORD_DEFAULT);
echo "Generated hash: " . $new_hash . "\n";

// Verify the new hash
$verify = password_verify($password, $new_hash);
echo "Hash verification: " . ($verify ? "SUCCESS" : "FAILED") . "\n";

if ($verify) {
    // Store the working hash
    $config_file = __DIR__ . '/php/config.php';
    $config_content = file_get_contents($config_file);
    
    // Replace the old hash with the new one
    $pattern = "/define\('ADMIN_PASSWORD_HASH',\s*'[^']+'\);/";
    $replacement = "define('ADMIN_PASSWORD_HASH', '" . $new_hash . "');";
    
    $new_content = preg_replace($pattern, $replacement, $config_content);
    
    if (file_put_contents($config_file, $new_content)) {
        echo "Config file updated successfully!\n";
    } else {
        echo "Failed to update config file!\n";
    }
} 