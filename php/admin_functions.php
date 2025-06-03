<?php
if (!defined('SECURE_ACCESS')) {
    die('Прямой доступ запрещен');
}

function isAdminLoggedIn() {
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in'] || 
        !isset($_SESSION['admin_username']) || !isset($_SESSION['admin_id'])) {
        return false;
    }
    
    try {
        $db = Database::getInstance();
        if (!$stmt = $db->prepare("SELECT id FROM administrators WHERE id = ? AND username = ? AND is_active = 1 LIMIT 1")) {
            throw new Exception("Failed to prepare statement");
        }
        $stmt->bind_param("is", $_SESSION['admin_id'], $_SESSION['admin_username']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows === 1;
    } catch (Exception $e) {
        error_log("Ошибка проверки авторизации: " . $e->getMessage());
        return false;
    }
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function checkLoginAttempts($username, $ip) {
    try {
        $db = Database::getInstance();
        $timeout = LOGIN_ATTEMPT_TIMEOUT;
        
        // Проверяем только по имени пользователя
        $stmt = $db->prepare("SELECT COUNT(*) as attempts FROM login_attempts 
                             WHERE username = ? 
                             AND success = 0 
                             AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->bind_param("si", $username, $timeout);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['attempts'] >= MAX_LOGIN_ATTEMPTS;
    } catch (Exception $e) {
        error_log("Ошибка проверки попыток входа: " . $e->getMessage());
        return false; // В случае ошибки разрешаем вход
    }
}

function logLoginAttempt($username, $ip, $success) {
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $username, $ip, $success);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Ошибка логирования попытки входа: " . $e->getMessage());
    }
} 