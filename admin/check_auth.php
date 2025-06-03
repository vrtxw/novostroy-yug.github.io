<?php
define('SECURE_ACCESS', true);
require_once '../php/config.php';

// Проверяем авторизацию
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Если не авторизован, перенаправляем на страницу входа
    header('Location: login.php');
    exit;
}
?> 