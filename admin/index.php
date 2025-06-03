<?php
define('SECURE_ACCESS', true);
require_once '../php/config.php';

// Проверяем авторизацию
requireAdmin();

// Получаем данные
try {
    $db = Database::getInstance();
    
    // Получаем список ЖК с основными характеристиками
    $complexes = [];
    $result = $db->query("
        SELECT rc.*
        FROM residential_complexes rc
        ORDER BY rc.name
    ");
    while ($row = $result->fetch_assoc()) {
        $complexes[] = $row;
    }
    
    // Получаем характеристики для каждого ЖК
    foreach ($complexes as &$complex) {
        $features = [];
        $stmt = $db->prepare("
            SELECT feature_category, feature_name, feature_value 
            FROM complex_features 
            WHERE complex_id = ? 
            ORDER BY feature_category, display_order
        ");
        $stmt->bind_param('i', $complex['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if (!isset($features[$row['feature_category']])) {
                $features[$row['feature_category']] = [];
            }
            $features[$row['feature_category']][] = [
                'name' => $row['feature_name'],
                'value' => $row['feature_value']
            ];
        }
        $complex['features'] = $features;
    }
    unset($complex); // Разрываем ссылку на последний элемент
    
} catch (Exception $e) {
    error_log("Ошибка в админ-панели: " . $e->getMessage());
    $error = "Произошла ошибка при загрузке данных: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .editable {
            cursor: pointer;
            padding: 5px;
            border-radius: 3px;
            transition: background-color 0.2s;
        }
        .editable:hover {
            background-color: #f8f9fa;
        }
        .editable.editing {
            background-color: #fff3cd;
            padding: 0;
        }
        .editable textarea {
            width: 100%;
            min-height: 100px;
            resize: vertical;
        }
        .save-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            display: none;
        }
        .features-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .features-list li {
            margin-bottom: 5px;
        }
        .feature-category {
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Индикатор сохранения -->
    <div class="save-indicator alert alert-success">
        Изменения сохранены
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><?php echo SITE_NAME; ?> - Админ-панель</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Выход</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo sanitizeOutput($error); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Жилые комплексы</h5>
                        <a href="edit_complex.php" class="btn btn-primary btn-sm">Добавить ЖК</a>
                </div>
                    <div class="card-body">
                        <?php if (empty($complexes)): ?>
                            <p class="text-muted">Нет добавленных жилых комплексов</p>
                        <?php else: ?>
                <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Название</th>
                                            <th>Класс</th>
                                            <th>Материал стен</th>
                                            <th>Отделка</th>
                                            <th>Этажность</th>
                                            <th>Квартир</th>
                                            <th>Площадь</th>
                                            <th>Высота потолков</th>
                                            <th>Адрес</th>
                                            <th>Контакты</th>
                                            <th>Характеристики</th>
                                            <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                                        <?php foreach ($complexes as $complex): ?>
                                            <tr>
                                                <td class="editable" data-field="name" data-id="<?php echo $complex['id']; ?>" data-type="text">
                                                    <?php echo sanitizeOutput($complex['name']); ?>
                                                </td>
                                                <td class="editable" data-field="class" data-id="<?php echo $complex['id']; ?>" data-type="text">
                                                    <?php echo sanitizeOutput($complex['class']); ?>
                                                </td>
                                                <td class="editable" data-field="wall_material" data-id="<?php echo $complex['id']; ?>" data-type="text">
                                                    <?php echo sanitizeOutput($complex['wall_material']); ?>
                                                </td>
                                                <td class="editable" data-field="finishing_type" data-id="<?php echo $complex['id']; ?>" data-type="text">
                                                    <?php echo sanitizeOutput($complex['finishing_type']); ?>
                                                </td>
                                                <td class="editable" data-field="floors_count" data-id="<?php echo $complex['id']; ?>" data-type="number">
                                                    <?php echo $complex['floors_count']; ?>
                                                </td>
                                                <td class="editable" data-field="apartments_count" data-id="<?php echo $complex['id']; ?>" data-type="number">
                                                    <?php echo $complex['apartments_count']; ?>
                                                </td>
                                                <td class="editable" data-field="living_area" data-id="<?php echo $complex['id']; ?>" data-type="number">
                                                    <?php echo $complex['living_area']; ?>
                                                </td>
                                                <td class="editable" data-field="ceiling_height" data-id="<?php echo $complex['id']; ?>" data-type="number">
                                                    <?php echo $complex['ceiling_height']; ?>
                                                </td>
                                                <td class="editable" data-field="address" data-id="<?php echo $complex['id']; ?>" data-type="text">
                                                    <?php echo sanitizeOutput($complex['address']); ?>
                                                </td>
                                                <td>
                                                    <div class="editable" data-field="phone" data-id="<?php echo $complex['id']; ?>" data-type="text">
                                                        <?php echo sanitizeOutput($complex['phone'] ?? ''); ?>
                                                    </div>
                                                    <div class="editable" data-field="email" data-id="<?php echo $complex['id']; ?>" data-type="email">
                                                        <?php echo sanitizeOutput($complex['email'] ?? ''); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#featuresModal<?php echo $complex['id']; ?>">
                                                        <i class="fas fa-list"></i> Просмотр
                                                    </button>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="edit_complex.php?id=<?php echo $complex['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="delete_complex.php?id=<?php echo $complex['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                                           onclick="return confirm('Вы уверены, что хотите удалить этот ЖК?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Модальное окно с характеристиками -->
                                            <div class="modal fade" id="featuresModal<?php echo $complex['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Характеристики ЖК "<?php echo sanitizeOutput($complex['name']); ?>"</h5>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php if (!empty($complex['features'])): ?>
                                                                <?php foreach ($complex['features'] as $category => $features): ?>
                                                                    <h6 class="feature-category"><?php echo sanitizeOutput($category); ?></h6>
                                                                    <ul class="features-list">
                                                                        <?php foreach ($features as $feature): ?>
                                                                            <li>
                                                                                <div class="d-flex justify-content-between">
                                                                                    <span class="feature-name editable" 
                                                                                          data-type="feature"
                                                                                          data-id="<?php echo $complex['id']; ?>"
                                                                                          data-category="<?php echo sanitizeOutput($category); ?>"
                                                                                          data-feature="<?php echo sanitizeOutput($feature['name']); ?>">
                                                                                        <?php echo sanitizeOutput($feature['name']); ?>:
                                                                                    </span>
                                                                                    <span class="feature-value editable"
                                                                                          data-type="feature-value"
                                                                                          data-id="<?php echo $complex['id']; ?>"
                                                                                          data-category="<?php echo sanitizeOutput($category); ?>"
                                                                                          data-feature="<?php echo sanitizeOutput($feature['name']); ?>">
                                                                                        <?php echo sanitizeOutput($feature['value']); ?>
                                                                                    </span>
                                                                                </div>
                                                                            </li>
                                                                        <?php endforeach; ?>
                                                                    </ul>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <p class="text-muted">Нет добавленных характеристик</p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                        </tbody>
                    </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            function showSaveIndicator(message = 'Изменения сохранены') {
                $('.save-indicator').text(message).fadeIn().delay(2000).fadeOut();
            }

            function showError(message) {
                alert(message);
            }

            function handleUpdate(cell, currentValue, newValue, url) {
                if (newValue === currentValue) {
                    cell.html(currentValue);
                    cell.removeClass('editing');
                    return;
                }

                const data = {
                    id: cell.data('id'),
                    field: cell.data('field'),
                    value: newValue
                };

                // Добавляем дополнительные параметры для характеристик
                if (cell.data('type').startsWith('feature')) {
                    data.category = cell.data('category');
                    data.feature = cell.data('feature');
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            cell.html(response.value);
                            showSaveIndicator(response.message);
                        } else {
                            showError(response.message);
                            cell.html(currentValue);
                        }
                    },
                    error: function(xhr) {
                        showError('Ошибка при сохранении: ' + xhr.statusText);
                        cell.html(currentValue);
                    },
                    complete: function() {
                        cell.removeClass('editing');
                    }
                });
            }

            $('.editable').on('click', function() {
                if ($(this).hasClass('editing')) return;
                
                const cell = $(this);
                const currentValue = cell.text().trim();
                const fieldType = cell.data('type');
                let input;

                if (fieldType === 'textarea') {
                    input = $('<textarea>').addClass('form-control').val(currentValue);
                } else if (fieldType === 'number') {
                    input = $('<input>').attr('type', 'number').addClass('form-control').val(currentValue);
                } else if (fieldType === 'email') {
                    input = $('<input>').attr('type', 'email').addClass('form-control').val(currentValue);
                } else {
                    input = $('<input>').attr('type', 'text').addClass('form-control').val(currentValue);
                }
                
                cell.html(input);
                cell.addClass('editing');
                input.focus();

                input.on('blur', function() {
                    const newValue = input.val().trim();
                    const updateUrl = fieldType.startsWith('feature') ? 'update_feature.php' : 'update_complex.php';
                    handleUpdate(cell, currentValue, newValue, updateUrl);
                });

                input.on('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        input.blur();
                    } else if (e.key === 'Escape') {
                        cell.html(currentValue);
                        cell.removeClass('editing');
                    }
                });
            });
        });
    </script>
</body>
</html> 