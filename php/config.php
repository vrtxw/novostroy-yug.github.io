<?php
// Настройка обработки ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1); // Временно включаем отображение ошибок
ini_set('log_errors', 1);

// Настройки сессии (должны быть ДО session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Отключаем для http
ini_set('session.cookie_lifetime', 86400); // 24 часа
ini_set('session.gc_maxlifetime', 86400); // 24 часа
ini_set('session.cookie_samesite', 'Strict'); // Добавляем защиту от CSRF
ini_set('session.use_strict_mode', 1); // Включаем строгий режим

// Запускаем сессию
session_start();

// Проверка безопасного доступа
if (!defined('SECURE_ACCESS')) {
    die('Прямой доступ запрещен');
}

// Настройки сайта
define('SITE_NAME', 'ЖК "Простор"');
define('SITE_URL', 'https://sz-novostroi-yug.ru');
define('MAX_MESSAGE_LENGTH', 2000);

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'u3145574_default');
define('DB_USER', 'u3145574_devilpurrp');
define('DB_PASS', '2MXIE49z1tpNpsBB');

// Настройки почты
define('MAIL_FROM', 'noreply@sz-novostroi-yug.ru');
define('ADMIN_EMAIL', 'admin@sz-novostroi-yug.ru');
define('SMTP_HOST', 'mail.hosting.reg.ru');
define('SMTP_PORT', 587);

// Настройки администратора
define('ADMIN_USERNAME', 'u3145574_devilpurrp');
define('ADMIN_PASSWORD_HASH', '$2y$10$YwB0MHB8vRlJOYxFl3Y1/.WcQnY0rI9nXzqkr1YhL4jgzVrxhKmPi');

// Настройки безопасности
define('SECURE_TOKEN', bin2hex(random_bytes(32)));

// Настройки загрузки файлов
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Настройки времени
date_default_timezone_set('Europe/Moscow');
setlocale(LC_TIME, 'ru_RU.UTF-8');

// Настройки SMTP
define('SMTP_SECURE', 'tls');
define('SMTP_AUTH', true);
define('SMTP_USERNAME', 'noreply@sz-novostroi-yug.ru');
define('SMTP_PASSWORD', getenv('demented1488+')); 
define('MAIL_FROM_NAME', 'СЗ Новострой-Юг');
define('MAIL_TO', 'info@sz-novostroi-yug.ru');

// Настройки безопасности
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LENGTH', 32);
define('FORM_TIMEOUT', 3);
define('MAX_ATTEMPTS', 5);
define('ATTEMPT_TIMEOUT', 3600);

// Создаем директорию для логов если её нет
$log_dir = __DIR__ . '/logs';
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}
ini_set('error_log', $log_dir . '/error.log');

// Функции безопасности
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitizeOutput($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Настройки для защиты от спама
define('HONEYPOT_FIELD', 'website');

// Функция проверки на спам
function isSpam($startTime) {
    // Проверка honeypot поля
    if (!empty($_POST[HONEYPOT_FIELD])) {
        return true;
    }
    
    // Проверка времени заполнения
    $timeDiff = time() - $startTime;
    if ($timeDiff < FORM_TIMEOUT) {
        return true;
    }
    
    // Проверка количества попыток
    if (!isset($_SESSION['form_attempts'])) {
        $_SESSION['form_attempts'] = 1;
        $_SESSION['first_attempt_time'] = time();
    } else {
        if (time() - $_SESSION['first_attempt_time'] > ATTEMPT_TIMEOUT) {
            $_SESSION['form_attempts'] = 1;
            $_SESSION['first_attempt_time'] = time();
        } else if ($_SESSION['form_attempts'] >= MAX_ATTEMPTS) {
            return true;
        } else {
            $_SESSION['form_attempts']++;
        }
    }
    
    return false;
}

// Функции для работы с файлами
function isAllowedFileType($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ALLOWED_FILE_TYPES);
}

function generateSafeFilename($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return uniqid() . '.' . $ext;
}

// Функции для работы с датами
function formatDate($date, $format = 'd.m.Y') {
    return date($format, strtotime($date));
}

function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' ₽';
}

// Функции для работы с сессией
function isAdminLoggedIn() {
    error_log("Checking admin login status:");
    error_log("Session data: " . print_r($_SESSION, true));
    error_log("Admin username check: " . (isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'not set') . " vs " . ADMIN_USERNAME);
    error_log("Admin logged in check: " . (isset($_SESSION['admin_logged_in']) ? 'true' : 'false'));
    
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && 
           isset($_SESSION['admin_username']) && $_SESSION['admin_username'] === ADMIN_USERNAME;
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

// Подключение к базе данных
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");
    
    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения к базе данных: " . $conn->connect_error);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Ошибка подключения к базе данных");
}

// Функции для работы с базой данных
function dbQuery($sql, $params = []) {
    global $conn;
    
    try {
        $stmt = $conn->prepare($sql);
        
        if ($params) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

// Функции для работы с ЖК
function getComplexInfo($conn, $complex_name = 'ПРОСТОР') {
    $sql = "SELECT * FROM residential_complexes WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $complex_name);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getComplexFeatures($conn, $complex_id) {
    $sql = "SELECT * FROM complex_features WHERE complex_id = ? ORDER BY feature_category, display_order";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $complex_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Проверка времени последней активности
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 86400)) {
    session_unset();
    session_destroy();
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: /');
    }
    exit;
}
$_SESSION['last_activity'] = time();

// Добавляем отладочную информацию
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    error_log('Login attempt - Username: ' . $_POST['username']);
    error_log('Session data: ' . print_r($_SESSION, true));
    error_log('POST data: ' . print_r($_POST, true));
}