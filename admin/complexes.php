<?php
define('SECURE_ACCESS', true);
require_once '../php/config.php';

// Проверка авторизации
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$current_page = 'complexes';
$page_title = 'Жилые комплексы';

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
    unset($complex);
    
} catch (Exception $e) {
    error_log("Ошибка в админ-панели: " . $e->getMessage());
    $error = "Произошла ошибка при загрузке данных: " . $e->getMessage();
}

// Подключаем шапку
require_once 'templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Жилые комплексы</h1>
    <a href="edit_complex.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Добавить ЖК
    </a>
</div>

<?php if (empty($complexes)): ?>
    <div class="alert alert-info">
        Нет добавленных жилых комплексов
    </div>
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
                                <a href="delete_complex.php?id=<?php echo $complex['id']; ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   data-confirm="Вы уверены, что хотите удалить этот ЖК?">
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
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addFeatureModal<?php echo $complex['id']; ?>">
                                        Добавить характеристику
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php
// Подключаем футер
require_once 'templates/footer.php';
?> 