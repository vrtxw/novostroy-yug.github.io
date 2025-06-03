<?php
// Настройка обработки ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Настройки сессии
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_lifetime', 86400);
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

// Запускаем сессию
session_start();

// Проверка безопасного доступа
if (!defined('SECURE_ACCESS')) {
    die('Прямой доступ запрещен');
}

// Настройки сайта
define('SITE_NAME', 'ЖК "ПРОСТОР"');
define('SITE_URL', 'https://sz-novostroi-yug.ru');
define('MAX_MESSAGE_LENGTH', 2000);

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'u3145574_default');
define('DB_USER', 'u3145574_devilpurrp');
define('DB_PASSWORD', '2MXIE49z1tpNpsBB');
define('DB_PORT', 3306);

// Настройки почты
define('MAIL_FROM', 'noreply@sz-novostroi-yug.ru');
define('ADMIN_EMAIL', 'admin@sz-novostroi-yug.ru');
define('SMTP_HOST', 'mail.hosting.reg.ru');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_AUTH', true);
define('SMTP_USERNAME', 'noreply@sz-novostroi-yug.ru');
define('SMTP_PASSWORD', 'demented1488+');
define('MAIL_FROM_NAME', 'СЗ Новострой-Юг');

// Настройки безопасности
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LENGTH', 32);
define('MAX_LOGIN_ATTEMPTS', 10);
define('LOGIN_ATTEMPT_TIMEOUT', 900);
define('SESSION_LIFETIME', 86400);

// Создаем директорию для логов
$log_dir = __DIR__ . '/logs';
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}
ini_set('error_log', $log_dir . '/error.log');

// Подключаем необходимые файлы
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/functions.php';

// Проверка времени последней активности
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
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

// Данные для админ-панели
define('ADMIN_USERNAME', 'u3145574_devilpurrp');
define('ADMIN_PASSWORD', '2MXIE49z1tpNpsBB');