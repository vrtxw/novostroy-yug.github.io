<?php
// Параметры подключения к базе данных
define('DB_HOST', 'localhost');
define('DB_USER', 'u3145574_devilpurrp'); // Замените на ваше имя пользователя
define('DB_PASS', '2MXIE49z1tpNpsBB'); // Замените на ваш пароль
define('DB_NAME', 'u3145574_default');

// Создаем соединение
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Устанавливаем кодировку
$conn->set_charset("utf8mb4");

// Проверяем соединение
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Функция для безопасного получения данных из POST/GET запросов
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}

// Функция для обновления данных в таблице
function updateTableData($table, $id, $data) {
    global $conn;
    $updates = [];
    foreach ($data as $key => $value) {
        $updates[] = "`$key` = '" . sanitize($value) . "'";
    }
    $sql = "UPDATE $table SET " . implode(', ', $updates) . " WHERE id = " . (int)$id;
    return $conn->query($sql);
}

// Функция для получения данных из таблицы
function getTableData($table, $conditions = '') {
    global $conn;
    $sql = "SELECT * FROM $table" . ($conditions ? " WHERE $conditions" : "");
    return $conn->query($sql);
}
?> 