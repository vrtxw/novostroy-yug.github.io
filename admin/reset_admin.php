<?php
define('SECURE_ACCESS', true);
require_once '../php/config.php';

try {
    // Пробуем прямое подключение сначала
    $direct_conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);
    if ($direct_conn->connect_error) {
        throw new Exception('Ошибка прямого подключения: ' . $direct_conn->connect_error);
    }
    
    // Создаем базу если её нет
    $direct_conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
    $direct_conn->select_db(DB_NAME);
    
    // Теперь используем обычное подключение через класс Database
    $db = Database::getInstance();
    
    // Данные администратора
    $username = 'u3145574_devilpurrp';
    $password = '2MXIE49z1tpNpsBB';
    $email = 'admin@sz-novostroi-yug.ru';
    
    // Хешируем пароль
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Проверяем существование таблицы administrators
    $db->query("CREATE TABLE IF NOT EXISTS administrators (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        last_login DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Проверяем существование таблицы login_attempts
    $db->query("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        success TINYINT(1) DEFAULT 0,
        attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username_time (username, attempt_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Сначала пытаемся обновить существующего админа
    $stmt = $db->prepare("UPDATE administrators SET 
        password = ?, 
        email = ?,
        is_active = 1 
        WHERE username = ?");
    $stmt->bind_param('sss', $password_hash, $email, $username);
    $stmt->execute();
    
    // Если админ не существует, создаем нового
    if ($stmt->affected_rows == 0) {
        $stmt = $db->prepare("INSERT INTO administrators (username, password, email, is_active) VALUES (?, ?, ?, 1)");
        $stmt->bind_param('sss', $username, $password_hash, $email);
        $stmt->execute();
    }
    
    // Очищаем неудачные попытки входа
    $db->query("TRUNCATE TABLE login_attempts");
    
    echo "Администратор успешно активирован!<br>";
    echo "Логин: $username<br>";
    echo "Пароль: $password<br>";
    echo "<a href='login.php'>Перейти на страницу входа</a>";
    
    // Закрываем прямое подключение
    $direct_conn->close();
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
} 