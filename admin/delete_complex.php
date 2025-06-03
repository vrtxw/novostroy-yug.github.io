<?php
define('SECURE_ACCESS', true);
require_once '../php/config.php';

// Проверка авторизации
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Получение ID комплекса
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error'] = 'Неверный ID жилого комплекса';
    header('Location: complexes.php');
    exit;
}

try {
    $db = Database::getInstance();
    
    // Начинаем транзакцию
    $db->begin_transaction();
    
    try {
        // Удаляем характеристики
        $stmt = $db->prepare("DELETE FROM complex_features WHERE complex_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        // Удаляем изображения квартир
        $stmt = $db->prepare("DELETE FROM apartment_images WHERE apartment_id IN (SELECT id FROM apartment_types WHERE complex_id = ?)");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        // Удаляем типы квартир
        $stmt = $db->prepare("DELETE FROM apartment_types WHERE complex_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        // Удаляем сам комплекс
        $stmt = $db->prepare("DELETE FROM residential_complexes WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        // Если все успешно, подтверждаем транзакцию
        $db->commit();
        
        $_SESSION['success'] = 'Жилой комплекс успешно удален';
        
    } catch (Exception $e) {
        // В случае ошибки откатываем все изменения
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Ошибка при удалении ЖК: " . $e->getMessage());
    $_SESSION['error'] = 'Ошибка при удалении жилого комплекса: ' . $e->getMessage();
}

// Перенаправляем обратно на страницу со списком
header('Location: complexes.php');
?> 