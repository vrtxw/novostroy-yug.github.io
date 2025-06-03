<?php
define('SECURE_ACCESS', true);
require_once 'config.php';

header('Content-Type: application/json');

// Проверяем авторизацию
if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Проверяем CSRF токен
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

// Получаем и проверяем данные
$table = sanitize($_POST['table'] ?? '');
$id = (int)($_POST['id'] ?? 0);
$field = sanitize($_POST['field'] ?? '');
$value = $_POST['value'] ?? '';

// Проверяем наличие всех необходимых данных
if (!$table || !$id || !$field) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

// Проверяем существование таблицы
$allowed_tables = [
    'residential_complexes',
    'complex_features',
    'apartment_types',
    'finishing_options',
    'apartment_images',
    'elevator_info',
    'contact_info',
    'locations',
    'property_classes',
    'navigation_menu'
];

if (!in_array($table, $allowed_tables)) {
    echo json_encode(['success' => false, 'message' => 'Invalid table']);
    exit;
}

try {
    // Обновляем данные
    $sql = "UPDATE " . $table . " SET " . $field . " = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $value, $id);
    $result = $stmt->execute();

    if ($result) {
        // Если это обновление цены квартиры, обновляем связанные данные
        if ($table === 'apartment_types' && ($field === 'min_price' || $field === 'max_price')) {
            $sql = "SELECT rooms_count, min_price, max_price FROM apartment_types WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $apartment = $stmt->get_result()->fetch_assoc();
            
            if ($apartment) {
                $price_range = formatPrice($apartment['min_price']) . " - " . formatPrice($apartment['max_price']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Data updated successfully',
                    'price_range' => $price_range
                ]);
                exit;
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Data updated successfully']);
    } else {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    error_log("Error updating data: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating data']);
}
?> 