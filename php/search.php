<!-- send.php -->
<?php
header('Content-Type: application/json');

function sendJsonResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Проверка метода запроса
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    sendJsonResponse(false, 'Неверный метод запроса');
}

// Проверка наличия всех полей
if (!isset($_POST['city']) || !isset($_POST['type'])) {
    sendJsonResponse(false, 'Пожалуйста, выберите город и тип жилья');
}

// Получение и очистка данных
$city = filter_var(trim($_POST['city']), FILTER_SANITIZE_STRING);
$type = filter_var(trim($_POST['type']), FILTER_SANITIZE_STRING);

// Проверяем конкретную комбинацию
if ($city === 'aksay' && $type === 'comfort') {
    header('Location: ../prostor.html');
    exit;
} else {
    header('Location: ../index.html?error=not_found');
    exit;
}
?>