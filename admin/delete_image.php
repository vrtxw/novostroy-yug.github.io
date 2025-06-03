<?php
define('SECURE_ACCESS', true);
require_once '../php/config.php';

// Проверка авторизации
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Получение ID изображения
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error'] = 'Неверный ID изображения';
    header('Location: apartments.php');
    exit;
}

try {
    $db = Database::getInstance();
    
    // Получаем информацию об изображении
    $stmt = $db->prepare("
        SELECT ai.*, at.id as apartment_id 
        FROM apartment_images ai
        JOIN apartment_types at ON at.id = ai.apartment_id
        WHERE ai.id = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $image = $stmt->get_result()->fetch_assoc();
    
    if (!$image) {
        throw new Exception('Изображение не найдено');
    }
    
    // Удаляем файл
    $file_path = '../' . $image['image_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    // Удаляем запись из базы
    $stmt = $db->prepare("DELETE FROM apartment_images WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Изображение успешно удалено';
    } else {
        throw new Exception('Ошибка при удалении изображения из базы данных');
    }
    
    // Перенаправляем обратно на страницу редактирования квартиры
    header('Location: edit_apartment.php?id=' . $image['apartment_id']);
    
} catch (Exception $e) {
    error_log("Ошибка при удалении изображения: " . $e->getMessage());
    $_SESSION['error'] = 'Ошибка при удалении изображения: ' . $e->getMessage();
    header('Location: apartments.php');
}
?> 