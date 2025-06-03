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
$complex_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
$feature = filter_input(INPUT_POST, 'feature', FILTER_SANITIZE_STRING);
$value = filter_input(INPUT_POST, 'value', FILTER_SANITIZE_STRING);

if (!$complex_id || !$category || !$feature) {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Отсутствуют обязательные параметры']));
}

try {
    $db = Database::getInstance();
    
    // Проверка существования ЖК
    $stmt = $db->prepare("SELECT id FROM residential_complexes WHERE id = ?");
    $stmt->bind_param('i', $complex_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        throw new Exception('ЖК не найден');
    }
    
    // Обновление характеристики
    if (!($stmt = $db->prepare("
        UPDATE complex_features 
        SET feature_value = ?
        WHERE complex_id = ? AND feature_category = ? AND feature_name = ?
    "))) {
        throw new Exception('Failed to prepare update statement');
    }
    $stmt->bind_param('siss', $value, $complex_id, $category, $feature);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows === 0) {
            // Если характеристика не существует, создаем новую
            $stmt = $db->prepare("
                INSERT INTO complex_features 
                (complex_id, feature_category, feature_name, feature_value, display_order)
                VALUES (?, ?, ?, ?, (
                    SELECT COALESCE(MAX(display_order), 0) + 1 
                    FROM complex_features 
                    WHERE complex_id = ? AND feature_category = ?
                ))
            ");
            $stmt->bind_param('isssss', $complex_id, $category, $feature, $value, $complex_id, $category);
            
            if (!$stmt->execute()) {
                throw new Exception('Ошибка при добавлении характеристики');
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Характеристика успешно обновлена',
            'value' => $value
        ]);
    } else {
        throw new Exception('Ошибка при обновлении характеристики');
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 