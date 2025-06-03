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

// Получение количества комнат из параметра
$rooms = isset($_GET['rooms']) ? (int)$_GET['rooms'] : 1;

// Получение информации о типе квартиры
$sql = "SELECT * FROM apartment_types WHERE complex_id = ? AND rooms_count = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $complex['id'], $rooms);
$stmt->execute();
$apartment = $stmt->get_result()->fetch_assoc();

if (!$apartment) {
    die("Тип квартиры не найден");
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE apartment_types SET 
            total_count = ?, 
            min_area = ?, max_area = ?,
            min_price = ?, max_price = ?,
            min_living_area = ?, max_living_area = ?,
            has_balcony = ?
            WHERE complex_id = ? AND rooms_count = ?";
            
    $stmt = $conn->prepare($sql);
    $has_balcony = isset($_POST['has_balcony']) ? 1 : 0;
    
    $stmt->bind_param("iddddddiis",
        $_POST['total_count'],
        $_POST['min_area'],
        $_POST['max_area'],
        $_POST['min_price'],
        $_POST['max_price'],
        $_POST['min_living_area'],
        $_POST['max_living_area'],
        $has_balcony,
        $complex['id'],
        $rooms
    );
    
    if ($stmt->execute()) {
        $success = "Информация о квартирах успешно обновлена";
        
        // Обновляем общее количество квартир в ЖК
        $sql = "UPDATE residential_complexes 
                SET apartments_count = (
                    SELECT SUM(total_count) 
                    FROM apartment_types 
                    WHERE complex_id = ?
                )
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $complex['id'], $complex['id']);
        $stmt->execute();
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
    <title>Редактирование <?php echo $rooms; ?>-комнатных квартир - ЖК "<?php echo htmlspecialchars($complex['name']); ?>"</title>
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
                <h2 class="mb-4">Редактирование <?php echo $rooms; ?>-комнатных квартир</h2>

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
                                        <label for="total_count">Количество квартир</label>
                                        <input type="number" class="form-control" id="total_count" name="total_count"
                                               value="<?php echo $apartment['total_count']; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="min_area">Минимальная площадь (м²)</label>
                                        <input type="number" step="0.01" class="form-control" id="min_area" name="min_area"
                                               value="<?php echo $apartment['min_area']; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="max_area">Максимальная площадь (м²)</label>
                                        <input type="number" step="0.01" class="form-control" id="max_area" name="max_area"
                                               value="<?php echo $apartment['max_area']; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="min_living_area">Минимальная жилая площадь (м²)</label>
                                        <input type="number" step="0.01" class="form-control" id="min_living_area" name="min_living_area"
                                               value="<?php echo $apartment['min_living_area']; ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="max_living_area">Максимальная жилая площадь (м²)</label>
                                        <input type="number" step="0.01" class="form-control" id="max_living_area" name="max_living_area"
                                               value="<?php echo $apartment['max_living_area']; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="min_price">Минимальная цена (₽)</label>
                                        <input type="number" step="0.01" class="form-control" id="min_price" name="min_price"
                                               value="<?php echo $apartment['min_price']; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="max_price">Максимальная цена (₽)</label>
                                        <input type="number" step="0.01" class="form-control" id="max_price" name="max_price"
                                               value="<?php echo $apartment['max_price']; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="has_balcony" name="has_balcony"
                                                   <?php echo $apartment['has_balcony'] ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="has_balcony">Есть балкон/лоджия</label>
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