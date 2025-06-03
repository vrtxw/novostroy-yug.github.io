<?php
require_once '../php/config.php';

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $area = $_POST['area'] ?? 0;
    $rooms = $_POST['rooms'] ?? 0;
    $address = $_POST['address'] ?? '';
    $status = $_POST['status'] ?? 'inactive';

    // Подготовка и выполнение запроса
    $sql = "INSERT INTO properties (title, description, price, area, rooms, address, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $db = Database::getInstance();
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ssdddss", $title, $description, $price, $area, $rooms, $address, $status);
    
    if ($stmt->execute()) {
        $property_id = $db->get_insert_id();
        
        // Обработка загруженных изображений
        if (!empty($_FILES['images']['name'][0])) {
            $upload_dir = '../uploads/properties/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['images']['name'][$key];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_file_name = uniqid() . '.' . $file_ext;
                
                if (move_uploaded_file($tmp_name, $upload_dir . $new_file_name)) {
                    $is_main = isset($_POST['main_image']) && $_POST['main_image'] == $key ? 1 : 0;
                    
                    $sql = "INSERT INTO property_images (property_id, image_path, is_main) VALUES (?, ?, ?)";
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param("isi", $property_id, $new_file_name, $is_main);
                    $stmt->execute();
                }
            }
        }

        // Обработка характеристик
        if (!empty($_POST['features'])) {
            $sql = "INSERT INTO property_features (property_id, feature_name, feature_value) VALUES (?, ?, ?)";
            $stmt = $db->prepare($sql);
            
            foreach ($_POST['features'] as $feature) {
                if (!empty($feature['name']) && !empty($feature['value'])) {
                    $stmt->bind_param("iss", $property_id, $feature['name'], $feature['value']);
                    $stmt->execute();
                }
            }
        }

        header('Location: index.php?success=1');
        exit;
    } else {
        $error = 'Ошибка при добавлении объекта';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить объект недвижимости</title>
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
        .feature-row {
            margin-bottom: 10px;
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
                <a href="properties.php"><i class="fas fa-building mr-2"></i>Объекты</a>
                <a href="add_property.php"><i class="fas fa-plus mr-2"></i>Добавить объект</a>
                <a href="settings.php"><i class="fas fa-cog mr-2"></i>Настройки</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i>Выход</a>
            </div>

            <!-- Основной контент -->
            <div class="col-md-10 content">
                <h2 class="mb-4">Добавить новый объект недвижимости</h2>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="title">Название объекта</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="description">Описание</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="price">Цена (₽)</label>
                                        <input type="number" class="form-control" id="price" name="price" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="area">Площадь (м²)</label>
                                        <input type="number" step="0.01" class="form-control" id="area" name="area" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="rooms">Количество комнат</label>
                                        <input type="number" class="form-control" id="rooms" name="rooms">
                                    </div>

                                    <div class="form-group">
                                        <label for="address">Адрес</label>
                                        <input type="text" class="form-control" id="address" name="address" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="status">Статус</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="active">Активен</option>
                                            <option value="inactive">Неактивен</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Изображения</label>
                                        <input type="file" class="form-control-file" name="images[]" multiple accept="image/*">
                                        <small class="form-text text-muted">Можно загрузить несколько изображений</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Дополнительные характеристики</h5>
                                    <div id="features-container">
                                        <div class="feature-row row">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="features[0][name]" placeholder="Название характеристики">
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="features[0][value]" placeholder="Значение">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger remove-feature">Удалить</button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-secondary mt-2" id="add-feature">
                                        Добавить характеристику
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Сохранить объект</button>
                            <a href="index.php" class="btn btn-secondary">Отмена</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            let featureIndex = 1;

            $('#add-feature').click(function() {
                const newFeature = `
                    <div class="feature-row row mt-2">
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="features[${featureIndex}][name]" placeholder="Название характеристики">
                        </div>
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="features[${featureIndex}][value]" placeholder="Значение">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-feature">Удалить</button>
                        </div>
                    </div>
                `;
                $('#features-container').append(newFeature);
                featureIndex++;
            });

            $(document).on('click', '.remove-feature', function() {
                $(this).closest('.feature-row').remove();
            });
        });
    </script>
</body>
</html> 