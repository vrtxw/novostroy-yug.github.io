<?php
require_once '../php/config.php';

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Получение информации о ЖК
$complex = getComplexInfo($conn);
if (!$complex) {
    die("ЖК не найден");
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Обновление основной информации
    $sql = "UPDATE residential_complexes SET 
            name = ?, class = ?, wall_material = ?, finishing_type = ?,
            free_layout = ?, floors_count = ?, apartments_count = ?,
            living_area = ?, ceiling_height = ?
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $free_layout = isset($_POST['free_layout']) ? 1 : 0;
    
    $stmt->bind_param("ssssiiiddi",
        $_POST['name'],
        $_POST['class'],
        $_POST['wall_material'],
        $_POST['finishing_type'],
        $free_layout,
        $_POST['floors_count'],
        $_POST['apartments_count'],
        $_POST['living_area'],
        $_POST['ceiling_height'],
        $complex['id']
    );
    
    if ($stmt->execute()) {
        $success = "Информация о ЖК успешно обновлена";
    } else {
        $error = "Ошибка при обновлении информации: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование ЖК - <?php echo htmlspecialchars($complex['name']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            color: #fff;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
            text-decoration: none;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Боковое меню -->
            <div class="col-md-2 sidebar">
                <h4 class="text-white text-center mb-4">Панель управления</h4>
                <a href="index.php"><i class="fas fa-home mr-2"></i>Главная</a>
                <a href="edit_complex.php"><i class="fas fa-building mr-2"></i>Редактировать ЖК</a>
                <a href="apartments.php"><i class="fas fa-door-open mr-2"></i>Квартиры</a>
                <a href="features.php"><i class="fas fa-list mr-2"></i>Характеристики</a>
                <a href="settings.php"><i class="fas fa-cog mr-2"></i>Настройки</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i>Выход</a>
            </div>

            <!-- Основной контент -->
            <div class="col-md-10 content">
                <h2 class="mb-4">Редактирование ЖК "<?php echo htmlspecialchars($complex['name']); ?>"</h2>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Название ЖК</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($complex['name']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="class">Класс недвижимости</label>
                                        <select class="form-control" id="class" name="class" required>
                                            <option value="Эконом" <?php echo $complex['class'] === 'Эконом' ? 'selected' : ''; ?>>Эконом</option>
                                            <option value="Комфорт" <?php echo $complex['class'] === 'Комфорт' ? 'selected' : ''; ?>>Комфорт</option>
                                            <option value="Бизнес" <?php echo $complex['class'] === 'Бизнес' ? 'selected' : ''; ?>>Бизнес</option>
                                            <option value="Элит" <?php echo $complex['class'] === 'Элит' ? 'selected' : ''; ?>>Элит</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="wall_material">Материал стен</label>
                                        <input type="text" class="form-control" id="wall_material" name="wall_material"
                                               value="<?php echo htmlspecialchars($complex['wall_material']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="finishing_type">Тип отделки</label>
                                        <input type="text" class="form-control" id="finishing_type" name="finishing_type"
                                               value="<?php echo htmlspecialchars($complex['finishing_type']); ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="floors_count">Количество этажей</label>
                                        <input type="number" class="form-control" id="floors_count" name="floors_count"
                                               value="<?php echo $complex['floors_count']; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="apartments_count">Количество квартир</label>
                                        <input type="number" class="form-control" id="apartments_count" name="apartments_count"
                                               value="<?php echo $complex['apartments_count']; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="living_area">Жилая площадь (м²)</label>
                                        <input type="number" step="0.01" class="form-control" id="living_area" name="living_area"
                                               value="<?php echo $complex['living_area']; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="ceiling_height">Высота потолков (м)</label>
                                        <input type="number" step="0.01" class="form-control" id="ceiling_height" name="ceiling_height"
                                               value="<?php echo $complex['ceiling_height']; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="free_layout" name="free_layout"
                                                   <?php echo $complex['free_layout'] ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="free_layout">Свободная планировка</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                                <a href="index.php" class="btn btn-secondary">Отмена</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 