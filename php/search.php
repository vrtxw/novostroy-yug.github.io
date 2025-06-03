<!-- send.php -->
<?php
require_once 'config.php';

// Получаем параметры поиска
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$propertyClass = isset($_GET['class']) ? trim($_GET['class']) : '';

// Подготавливаем ответ
$response = array(
    'success' => false,
    'message' => '',
    'redirect' => ''
);

// Проверяем параметры поиска
if (empty($location) || empty($propertyClass)) {
    $response['message'] = 'Пожалуйста, выберите город и класс недвижимости';
    echo json_encode($response);
    exit;
}

// Ищем ЖК по заданным параметрам
$sql = "SELECT rc.name, rc.class, l.name as location
        FROM residential_complexes rc
        JOIN complex_locations cl ON rc.id = cl.complex_id
        JOIN locations l ON cl.location_id = l.id
        WHERE l.name = ? AND rc.class = ?
        AND l.is_active = TRUE";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $location, $propertyClass);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Если найден подходящий ЖК
    $complex = $result->fetch_assoc();
    if ($complex['name'] === 'ПРОСТОР' && $complex['location'] === 'Аксай' && $complex['class'] === 'Комфорт') {
        $response['success'] = true;
        $response['redirect'] = 'prostor.html';
    } else {
        $response['message'] = 'К сожалению, в данный момент нет доступных ЖК с выбранными параметрами';
    }
} else {
    $response['message'] = 'К сожалению, в данный момент нет доступных ЖК с выбранными параметрами';
}

// Возвращаем результат
echo json_encode($response);
?>