<?php
if (!defined('SECURE_ACCESS')) {
    die('Прямой доступ запрещен');
}

/**
 * Генерирует CSRF токен
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > 3600) { // Токен действителен 1 час
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Проверяет CSRF токен
 */
function validateCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        error_log("CSRF validation failed: token is empty");
        return false;
    }
    
    if (!isset($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time']) > 3600) {
        error_log("CSRF validation failed: token expired");
        return false;
    }
    
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        error_log("CSRF validation failed: tokens do not match");
        error_log("Session token: " . $_SESSION['csrf_token']);
        error_log("Submitted token: " . $token);
        return false;
    }
    
    return true;
}

/**
 * Безопасно очищает вывод
 */
function sanitizeOutput($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
} 