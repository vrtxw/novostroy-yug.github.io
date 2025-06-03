<?php
define('SECURE_ACCESS', true);
require_once '../php/config.php';

// Проверка авторизации
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Получение ID квартиры
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error'] = 'Неверный ID квартиры';
    header('Location: apartments.php');
    exit;
}

try {
    $db = Database::getInstance();
    
    // Начинаем транзакцию
    $db->begin_transaction();
    
    try {
        // Получаем информацию о квартире и её изображениях
        $stmt = $db->prepare("
            SELECT at.*, ai.image_path 
            FROM apartment_types at
            LEFT JOIN apartment_images ai ON ai.apartment_id = at.id
            WHERE at.id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $apartment = null;
        $images = [];
        while ($row = $result->fetch_assoc()) {
            if (!$apartment) {
                $apartment = $row;
            }
            if ($row['image_path']) {
                $images[] = $row['image_path'];
            }
        }
        
        if (!$apartment) {
            throw new Exception('Квартира не найдена');
        }
        
        // Удаляем файлы изображений
        foreach ($images as $image_path) {
            $file_path = '../' . $image_path;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Удаляем записи изображений из базы
        $stmt = $db->prepare("DELETE FROM apartment_images WHERE apartment_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        // Удаляем саму квартиру
        $stmt = $db->prepare("DELETE FROM apartment_types WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        // Если все успешно, подтверждаем транзакцию
        $db->commit();
        
        $_SESSION['success'] = 'Квартира успешно удалена';
        
    } catch (Exception $e) {
        // В случае ошибки откатываем все изменения
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Ошибка при удалении квартиры: " . $e->getMessage());
    $_SESSION['error'] = 'Ошибка при удалении квартиры: ' . $e->getMessage();
}

// Перенаправляем обратно на страницу со списком
header('Location: apartments.php');
?> 