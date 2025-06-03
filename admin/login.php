<?php
// Определяем константу до подключения конфига
define('SECURE_ACCESS', true);

// Подключаем конфигурацию
require_once '../php/config.php';

// Включаем отображение всех ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Если уже авторизован, перенаправляем на главную
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Проверка логина и пароля
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'];
        
        error_log("Попытка входа - Логин: " . $username . ", IP: " . $ip);
        
        // Проверяем количество неудачных попыток
        if (checkLoginAttempts($username, $ip)) {
            throw new Exception("Слишком много неудачных попыток. Попробуйте позже.");
        }
        
        // Проверяем, что поля не пустые
        if (empty($username) || empty($password)) {
            throw new Exception("Логин и пароль обязательны для заполнения");
        }
        
        $db = Database::getInstance();
        
        // Получаем данные администратора
        $stmt = $db->prepare("SELECT id, username, password, is_active FROM administrators WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        
        error_log("Поиск пользователя в БД: " . ($admin ? "найден" : "не найден"));
        
        if (!$admin) {
            logLoginAttempt($username, $ip, 0);
            throw new Exception("Неверное имя пользователя или пароль");
        }
        
        if (!$admin['is_active']) {
            logLoginAttempt($username, $ip, 0);
            throw new Exception("Учетная запись отключена");
        }
        
        // Проверяем пароль
        if (!password_verify($password, $admin['password'])) {
            logLoginAttempt($username, $ip, 0);
            throw new Exception("Неверное имя пользователя или пароль");
        }
        
        // Обновляем время последнего входа
        $update = $db->prepare("UPDATE administrators SET last_login = NOW() WHERE id = ?");
        $update->bind_param("i", $admin['id']);
        $update->execute();
        
        // Логируем успешный вход
        logLoginAttempt($username, $ip, 1);
        
        // Создаем сессию
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf_token'] = generateCSRFToken();
        
        error_log("Успешный вход для: " . $admin['username']);
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        error_log("Ошибка при авторизации: " . $e->getMessage());
        $error = $e->getMessage();
    }
}

// Проверяем наличие директории для логов
$log_dir = __DIR__ . '/../php/logs';
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo img {
            max-width: 150px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-logo">
                <img src="../images/logo.png" alt="<?php echo SITE_NAME; ?>">
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo sanitizeOutput($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="username">Имя пользователя</label>
                    <input type="text" class="form-control" id="username" name="username" required 
                           value="<?php echo sanitizeOutput($username ?? ''); ?>"
                           autocomplete="off" autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" class="form-control" id="password" name="password" required 
                           autocomplete="off">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Войти</button>
            </form>
        </div>
    </div>

    <script>
        // Предотвращаем отправку формы при обновлении страницы
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html> 