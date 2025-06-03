<?php
define('SECURE_ACCESS', true);
require_once '../php/config.php';

// Проверка авторизации
if (!isAdminLoggedIn()) {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Требуется авторизация']));
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Неверный метод запроса']));
}

// Получение и валидация данных
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$field = filter_input(INPUT_POST, 'field', FILTER_SANITIZE_STRING);
$value = filter_input(INPUT_POST, 'value', FILTER_SANITIZE_STRING);

if (!$id || !$field) {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Отсутствуют обязательные параметры']));
}

try {
    $db = Database::getInstance();
    
    // Проверка существования ЖК
    $stmt = $db->prepare("SELECT id FROM residential_complexes WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        throw new Exception('ЖК не найден');
    }
    
    // Валидация значения в зависимости от поля
    switch ($field) {
        case 'floors_count':
        case 'apartments_count':
            if (!filter_var($value, FILTER_VALIDATE_INT) || $value < 0) {
                throw new Exception('Значение должно быть положительным целым числом');
            }
            break;
            
        case 'living_area':
        case 'ceiling_height':
            if (!filter_var($value, FILTER_VALIDATE_FLOAT) || $value <= 0) {
                throw new Exception('Значение должно быть положительным числом');
            }
            break;
            
        case 'email':
            if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Неверный формат email');
            }
            break;
            
        case 'phone':
            // Простая валидация телефона
            if ($value && !preg_match('/^[0-9+\-() ]{10,20}$/', $value)) {
                throw new Exception('Неверный формат телефона');
            }
            break;
    }
    
    // Обновление данных
    $sql = "UPDATE residential_complexes SET $field = ? WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('si', $value, $id);
    
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Данные успешно обновлены',
            'value' => $value
        ]);
    } else {
        throw new Exception('Ошибка при обновлении данных');
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 