<?php
define('SECURE_ACCESS', true);
require_once 'config.php';

// Настройка обработки ошибок
error_reporting(E_ALL);                 // Включает отображение всех ошибок
ini_set('display_errors', 1);           // Показывать ошибки на экране
ini_set('log_errors', 1);              // Записывать ошибки в лог

// Настройки сессии
ini_set('session.cookie_httponly', 1);  // Защита cookie от доступа через JavaScript
ini_set('session.use_only_cookies', 1); // Использовать только cookie для сессий
ini_set('session.cookie_secure', 0);    // Cookie не требуют HTTPS
ini_set('session.cookie_lifetime', 86400); // Время жизни cookie - 24 часа

function formatPrice($price) {
    return number_format($price, 0, '.', ' ') . ' ₽';
}

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
$table = filter_input(INPUT_POST, 'table', FILTER_SANITIZE_STRING);
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$field = filter_input(INPUT_POST, 'field', FILTER_SANITIZE_STRING);
$value = filter_input(INPUT_POST, 'value', FILTER_UNSAFE_RAW);

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
        $db = Database::getInstance();
        throw new Exception($db->getConnection()->error);
    }
} catch (Exception $e) {
    error_log("Error updating data: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating data']);
}
?> 