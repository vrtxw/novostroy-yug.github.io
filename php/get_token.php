<?php
define('SECURE_ACCESS', true);
require_once 'config.php';
require_once 'functions.php';

// Добавляем заголовки безопасности
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

try {
    // Генерация нового CSRF токена
    $token = generateCSRFToken();
    
    if (empty($token)) {
        throw new Exception('Failed to generate CSRF token');
    }
    
    // Отправка токена клиенту
    echo json_encode([
        'success' => true,
        'token' => $token
    ]);
} catch (Exception $e) {
    error_log("Error generating CSRF token: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal Server Error'
    ]);
} 