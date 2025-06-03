<?php
define('SECURE_ACCESS', true);
require_once '../php/config.php';

// Удаляем все данные сессии
session_unset();
session_destroy();

// Перенаправляем на страницу входа
header('Location: login.php');
exit;
?>