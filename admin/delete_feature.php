<?php
define('SECURE_ACCESS', true);
require_once '../php/config.php';

// Проверка авторизации
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Получение ID характеристики
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error'] = 'Неверный ID характеристики';
    header('Location: features.php');
    exit;
}

try {
    $db = Database::getInstance();
    
    // Получаем информацию о характеристике
    $stmt = $db->prepare("
        SELECT cf.*, rc.id as complex_id 
        FROM complex_features cf
        JOIN residential_complexes rc ON rc.id = cf.complex_id
        WHERE cf.id = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $feature = $stmt->get_result()->fetch_assoc();
    
    if (!$feature) {
        throw new Exception('Характеристика не найдена');
    }
    
    // Удаляем характеристику
    $stmt = $db->prepare("DELETE FROM complex_features WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Характеристика успешно удалена';
    } else {
        throw new Exception('Ошибка при удалении характеристики');
    }
    
    // Перенаправляем обратно на страницу характеристик
    header('Location: features.php?complex_id=' . $feature['complex_id']);
    
} catch (Exception $e) {
    error_log("Ошибка при удалении характеристики: " . $e->getMessage());
    $_SESSION['error'] = 'Ошибка при удалении характеристики: ' . $e->getMessage();
    header('Location: features.php');
}
?> 