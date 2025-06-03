<?php
if (!defined('SECURE_ACCESS')) die('Direct access not permitted');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Админ-панель'; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 10px 15px;
        }
        .sidebar .nav-link:hover {
            background-color: #495057;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
        }
        .sidebar .nav-link i {
            width: 25px;
        }
        .content {
            padding: 20px;
        }
        .save-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            display: none;
        }
    </style>
</head>
<body>
    <!-- Индикатор сохранения -->
    <div class="save-indicator alert alert-success">
        Изменения сохранены
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Боковое меню -->
            <div class="col-md-2 sidebar">
                <h4 class="text-white text-center mb-4">
                    <i class="fas fa-building mr-2"></i>
                    <?php echo SITE_NAME; ?>
                </h4>
                <nav class="nav flex-column">
                    <a class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-home"></i> Главная
                    </a>
                    <a class="nav-link <?php echo $current_page === 'complexes' ? 'active' : ''; ?>" href="complexes.php">
                        <i class="fas fa-building"></i> Жилые комплексы
                    </a>
                    <a class="nav-link <?php echo $current_page === 'apartments' ? 'active' : ''; ?>" href="apartments.php">
                        <i class="fas fa-door-open"></i> Квартиры
                    </a>
                    <a class="nav-link <?php echo $current_page === 'features' ? 'active' : ''; ?>" href="features.php">
                        <i class="fas fa-list"></i> Характеристики
                    </a>
                    <a class="nav-link <?php echo $current_page === 'feedback' ? 'active' : ''; ?>" href="feedback.php">
                        <i class="fas fa-comments"></i> Обратная связь
                    </a>
                    <a class="nav-link <?php echo $current_page === 'settings' ? 'active' : ''; ?>" href="settings.php">
                        <i class="fas fa-cog"></i> Настройки
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Выход
                    </a>
                </nav>
            </div>

            <!-- Основной контент -->
            <div class="col-md-10 content">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo sanitizeOutput($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <?php echo sanitizeOutput($success); ?>
                    </div>
                <?php endif; ?> 