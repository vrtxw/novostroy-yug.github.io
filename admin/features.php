<?php
define('SECURE_ACCESS', true);
require_once '../php/config.php';

// Проверка авторизации
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$current_page = 'features';
$page_title = 'Управление характеристиками';

try {
    $db = Database::getInstance();
    
    // Получаем список ЖК для фильтра
    $complexes = [];
    $result = $db->query("SELECT id, name FROM residential_complexes ORDER BY name");
    while ($row = $result->fetch_assoc()) {
        $complexes[] = $row;
    }
    
    // Фильтр по ЖК
    $complex_id = filter_input(INPUT_GET, 'complex_id', FILTER_VALIDATE_INT);
    
    // Получаем характеристики
    $features = [];
    $sql = "
        SELECT cf.*, rc.name as complex_name
        FROM complex_features cf
        JOIN residential_complexes rc ON rc.id = cf.complex_id
    ";
    
    if ($complex_id) {
        $sql .= " WHERE cf.complex_id = " . $complex_id;
    }
    
    $sql .= " ORDER BY rc.name, cf.feature_category, cf.display_order";
    
    $result = $db->query($sql);
    while ($row = $result->fetch_assoc()) {
        $complex_id = $row['complex_id'];
        $category = $row['feature_category'];
        
        if (!isset($features[$complex_id])) {
            $features[$complex_id] = [
                'name' => $row['complex_name'],
                'categories' => []
            ];
        }
        
        if (!isset($features[$complex_id]['categories'][$category])) {
            $features[$complex_id]['categories'][$category] = [];
        }
        
        $features[$complex_id]['categories'][$category][] = $row;
    }
    
    // Обработка формы добавления характеристики
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $complex_id = filter_input(INPUT_POST, 'complex_id', FILTER_VALIDATE_INT);
        $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $value = filter_input(INPUT_POST, 'value', FILTER_SANITIZE_STRING);
        
        if (!$complex_id || !$category || !$name || !$value) {
            throw new Exception('Пожалуйста, заполните все поля');
        }
        
        // Проверяем существование ЖК
        $stmt = $db->prepare("SELECT id FROM residential_complexes WHERE id = ?");
        $stmt->bind_param('i', $complex_id);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            throw new Exception('ЖК не найден');
        }
        
        // Добавляем характеристику
        $stmt = $db->prepare("
            INSERT INTO complex_features 
            (complex_id, feature_category, feature_name, feature_value, display_order)
            VALUES (?, ?, ?, ?, (
                SELECT COALESCE(MAX(display_order), 0) + 1 
                FROM complex_features 
                WHERE complex_id = ? AND feature_category = ?
            ))
        ");
        $stmt->bind_param('isssss', $complex_id, $category, $name, $value, $complex_id, $category);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Характеристика успешно добавлена';
            header('Location: features.php' . ($complex_id ? "?complex_id=$complex_id" : ''));
            exit;
        } else {
            throw new Exception('Ошибка при добавлении характеристики');
        }
    }
    
} catch (Exception $e) {
    error_log("Ошибка в админ-панели: " . $e->getMessage());
    $error = "Произошла ошибка: " . $e->getMessage();
}

// Подключаем шапку
require_once 'templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Управление характеристиками</h1>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addFeatureModal">
        <i class="fas fa-plus"></i> Добавить характеристику
    </button>
</div>

<!-- Фильтр по ЖК -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="form-inline">
            <div class="form-group mr-3">
                <label for="complex_id" class="mr-2">Жилой комплекс:</label>
                <select name="complex_id" id="complex_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Все ЖК</option>
                    <?php foreach ($complexes as $complex): ?>
                        <option value="<?php echo $complex['id']; ?>" 
                                <?php echo $complex_id == $complex['id'] ? 'selected' : ''; ?>>
                            <?php echo sanitizeOutput($complex['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if (empty($features)): ?>
    <div class="alert alert-info">
        Нет добавленных характеристик
    </div>
<?php else: ?>
    <?php foreach ($features as $complex_id => $complex): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?php echo sanitizeOutput($complex['name']); ?></h5>
            </div>
            <div class="card-body">
                <?php if (empty($complex['categories'])): ?>
                    <p class="text-muted">Нет добавленных характеристик</p>
                <?php else: ?>
                    <?php foreach ($complex['categories'] as $category => $features): ?>
                        <h6 class="feature-category mb-3"><?php echo sanitizeOutput($category); ?></h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Характеристика</th>
                                        <th>Значение</th>
                                        <th style="width: 100px;">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($features as $feature): ?>
                                        <tr>
                                            <td class="editable" 
                                                data-type="feature"
                                                data-id="<?php echo $complex_id; ?>"
                                                data-category="<?php echo sanitizeOutput($category); ?>"
                                                data-feature="<?php echo sanitizeOutput($feature['feature_name']); ?>">
                                                <?php echo sanitizeOutput($feature['feature_name']); ?>
                                            </td>
                                            <td class="editable"
                                                data-type="feature-value"
                                                data-id="<?php echo $complex_id; ?>"
                                                data-category="<?php echo sanitizeOutput($category); ?>"
                                                data-feature="<?php echo sanitizeOutput($feature['feature_name']); ?>">
                                                <?php echo sanitizeOutput($feature['feature_value']); ?>
                                            </td>
                                            <td>
                                                <a href="delete_feature.php?id=<?php echo $feature['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   data-confirm="Удалить эту характеристику?">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Модальное окно добавления характеристики -->
<div class="modal fade" id="addFeatureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Добавление характеристики</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="complex_id">Жилой комплекс <span class="text-danger">*</span></label>
                        <select class="form-control" id="complex_id" name="complex_id" required>
                            <option value="">Выберите ЖК</option>
                            <?php foreach ($complexes as $complex): ?>
                                <option value="<?php echo $complex['id']; ?>">
                                    <?php echo sanitizeOutput($complex['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Категория <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category" name="category" required
                               placeholder="Например: Безопасность, Инфраструктура">
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Название характеристики <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required
                               placeholder="Например: Видеонаблюдение, Парковка">
                    </div>
                    
                    <div class="form-group">
                        <label for="value">Значение <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="value" name="value" required
                               placeholder="Например: Круглосуточно, 500 мест">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Подключаем футер
require_once 'templates/footer.php';
?> 