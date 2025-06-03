<?php
define('SECURE_ACCESS', true);
require_once '../php/config.php';

// Проверяем авторизацию
requireAdmin();

// Проверяем время последней активности
if (time() - $_SESSION['last_activity'] > 3600) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
$_SESSION['last_activity'] = time();

// Получаем список всех таблиц
$tables = [
    'residential_complexes' => 'Жилые комплексы',
    'complex_features' => 'Характеристики ЖК',
    'apartment_types' => 'Типы квартир',
    'finishing_options' => 'Варианты отделки',
    'apartment_images' => 'Изображения квартир',
    'elevator_info' => 'Информация о лифтах',
    'contact_info' => 'Контактная информация',
    'locations' => 'Города/Районы',
    'property_classes' => 'Классы недвижимости',
    'navigation_menu' => 'Навигация сайта'
];

// Получаем текущую таблицу
$current_table = isset($_GET['table']) ? sanitize($_GET['table']) : 'residential_complexes';

// Проверяем существование таблицы
if (!array_key_exists($current_table, $tables)) {
    die('Недопустимая таблица');
}

// Получаем данные текущей таблицы
$result = dbQuery("SELECT * FROM " . $current_table);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <style>
        .editable:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }
        .editing {
            background-color: #fff3cd;
        }
        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }
        .admin-info {
            color: #fff;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">
            <img src="../images/logo.png" alt="<?php echo SITE_NAME; ?>">
            <?php echo SITE_NAME; ?> - Админ-панель
        </a>
        <div class="ml-auto d-flex align-items-center">
            <span class="admin-info">
                Администратор: <?php echo sanitizeOutput($_SESSION['admin_username']); ?>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Выход</a>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Боковое меню -->
            <div class="col-md-2">
                <div class="list-group">
                    <?php foreach ($tables as $table_name => $table_title): ?>
                        <a href="?table=<?php echo $table_name; ?>" 
                           class="list-group-item list-group-item-action <?php echo $current_table === $table_name ? 'active' : ''; ?>">
                            <?php echo sanitizeOutput($table_title); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Основной контент -->
            <div class="col-md-10">
                <h2><?php echo sanitizeOutput($tables[$current_table]); ?></h2>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable">
                        <thead class="thead-dark">
                            <tr>
                                <?php
                                if ($result && $result->num_rows > 0) {
                                    $fields = $result->fetch_fields();
                                    foreach ($fields as $field) {
                                        echo "<th>" . sanitizeOutput($field->name) . "</th>";
                                    }
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    foreach ($row as $key => $value) {
                                        if ($key === 'id') {
                                            echo "<td>" . sanitizeOutput($value) . "</td>";
                                        } else {
                                            echo "<td class='editable' data-table='" . $current_table . 
                                                 "' data-id='" . $row['id'] . 
                                                 "' data-field='" . $key . "'>" . 
                                                 sanitizeOutput($value) . "</td>";
                                        }
                                    }
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            // Инициализация DataTables
            $('#dataTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.22/i18n/Russian.json"
                }
            });

            // Обработка редактирования ячеек
            $('.editable').on('click', function() {
                const cell = $(this);
                const currentValue = cell.text();
                
                // Создаем поле ввода
                const input = $('<input>')
                    .attr('type', 'text')
                    .val(currentValue)
                    .addClass('form-control');
                
                cell.html(input);
                cell.addClass('editing');
                input.focus();

                // Обработка потери фокуса
                input.on('blur', function() {
                    const newValue = input.val();
                    if (newValue !== currentValue) {
                        // Отправляем AJAX запрос
                        $.ajax({
                            url: '../php/update_data.php',
                            method: 'POST',
                            data: {
                                table: cell.data('table'),
                                id: cell.data('id'),
                                field: cell.data('field'),
                                value: newValue,
                                csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    cell.removeClass('editing');
                                    cell.text(newValue);
                                    
                                    // Если обновили цену квартиры, обновляем отображение
                                    if (response.price_range) {
                                        $(`td[data-field="price_range"][data-id="${cell.data('id')}"]`)
                                            .text(response.price_range);
                                    }
                                } else {
                                    alert('Ошибка при обновлении данных: ' + response.message);
                                    cell.removeClass('editing');
                                    cell.text(currentValue);
                                }
                            },
                            error: function() {
                                alert('Ошибка при отправке запроса');
                                cell.removeClass('editing');
                                cell.text(currentValue);
                            }
                        });
                    } else {
                        cell.removeClass('editing');
                        cell.text(currentValue);
                    }
                });

                // Обработка нажатия Enter
                input.on('keypress', function(e) {
                    if (e.which === 13) {
                        input.blur();
                    }
                });
            });
        });
    </script>
</body>
</html> 