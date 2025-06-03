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

// Обработка формы добавления/редактирования характеристики
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $sql = "INSERT INTO complex_features (complex_id, feature_category, feature_name, feature_value) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", 
                $complex['id'],
                $_POST['feature_category'],
                $_POST['feature_name'],
                $_POST['feature_value']
            );
            
            if ($stmt->execute()) {
                $success = "Характеристика успешно добавлена";
            } else {
                $error = "Ошибка при добавлении характеристики: " . $conn->error;
            }
        } elseif ($_POST['action'] === 'edit' && isset($_POST['feature_id'])) {
            $sql = "UPDATE complex_features 
                    SET feature_category = ?, feature_name = ?, feature_value = ?
                    WHERE id = ? AND complex_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssii",
                $_POST['feature_category'],
                $_POST['feature_name'],
                $_POST['feature_value'],
                $_POST['feature_id'],
                $complex['id']
            );
            
            if ($stmt->execute()) {
                $success = "Характеристика успешно обновлена";
            } else {
                $error = "Ошибка при обновлении характеристики: " . $conn->error;
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['feature_id'])) {
            $sql = "DELETE FROM complex_features WHERE id = ? AND complex_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $_POST['feature_id'], $complex['id']);
            
            if ($stmt->execute()) {
                $success = "Характеристика успешно удалена";
            } else {
                $error = "Ошибка при удалении характеристики: " . $conn->error;
            }
        }
    }
}

// Получение всех характеристик
$features = getComplexFeatures($conn, $complex['id']);

// Группировка характеристик по категориям
$grouped_features = [];
while ($feature = $features->fetch_assoc()) {
    $category = $feature['feature_category'];
    if (!isset($grouped_features[$category])) {
        $grouped_features[$category] = [];
    }
    $grouped_features[$category][] = $feature;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Характеристики - ЖК "<?php echo htmlspecialchars($complex['name']); ?>"</title>
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
        .feature-category {
            margin-bottom: 30px;
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
                <h2 class="mb-4">Характеристики ЖК "<?php echo htmlspecialchars($complex['name']); ?>"</h2>

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

                <!-- Форма добавления новой характеристики -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Добавить характеристику</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="feature_category">Категория</label>
                                        <input type="text" class="form-control" id="feature_category" name="feature_category" required
                                               list="categories">
                                        <datalist id="categories">
                                            <?php foreach (array_keys($grouped_features) as $category): ?>
                                                <option value="<?php echo htmlspecialchars($category); ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="feature_name">Название</label>
                                        <input type="text" class="form-control" id="feature_name" name="feature_name" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="feature_value">Значение</label>
                                        <input type="text" class="form-control" id="feature_value" name="feature_value" required>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Добавить характеристику</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Список характеристик по категориям -->
                <?php foreach ($grouped_features as $category => $features): ?>
                <div class="feature-category">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><?php echo htmlspecialchars($category); ?></h5>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>Значение</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($features as $feature): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($feature['feature_name']); ?></td>
                                        <td><?php echo htmlspecialchars($feature['feature_value']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-feature" 
                                                    data-id="<?php echo $feature['id']; ?>"
                                                    data-category="<?php echo htmlspecialchars($feature['feature_category']); ?>"
                                                    data-name="<?php echo htmlspecialchars($feature['feature_name']); ?>"
                                                    data-value="<?php echo htmlspecialchars($feature['feature_value']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="feature_id" value="<?php echo $feature['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Вы уверены, что хотите удалить эту характеристику?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Модальное окно для редактирования -->
    <div class="modal fade" id="editFeatureModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактировать характеристику</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="feature_id" id="edit_feature_id">
                        
                        <div class="form-group">
                            <label for="edit_feature_category">Категория</label>
                            <input type="text" class="form-control" id="edit_feature_category" name="feature_category" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_feature_name">Название</label>
                            <input type="text" class="form-control" id="edit_feature_name" name="feature_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_feature_value">Значение</label>
                            <input type="text" class="form-control" id="edit_feature_value" name="feature_value" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
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
            $('.edit-feature').click(function() {
                var id = $(this).data('id');
                var category = $(this).data('category');
                var name = $(this).data('name');
                var value = $(this).data('value');
                
                $('#edit_feature_id').val(id);
                $('#edit_feature_category').val(category);
                $('#edit_feature_name').val(name);
                $('#edit_feature_value').val(value);
                
                $('#editFeatureModal').modal('show');
            });
        });
    </script>
</body>
</html> 