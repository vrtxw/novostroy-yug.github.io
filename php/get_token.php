<?php
require_once 'config.php';

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

// Генерация нового CSRF токена
$token = generateCSRFToken();

// Отправка токена клиенту
echo json_encode(['token' => $token]); 