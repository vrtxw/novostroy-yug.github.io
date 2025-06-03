<?php
$password = '2MXIE49z1tpNpsBB'; // Используем текущий пароль
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "New password hash: " . $hash;
?> 