<?php
define('SECURE_ACCESS', true);
require_once '../php/config.php';

// Проверка авторизации
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$current_page = 'apartments';
$page_title = 'Управление квартирами';

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
    
    // Получаем список квартир
    $apartments = [];
    $sql = "
        SELECT at.*, rc.name as complex_name,
               (SELECT image_path FROM apartment_images WHERE apartment_id = at.id LIMIT 1) as main_image
        FROM apartment_types at
        JOIN residential_complexes rc ON rc.id = at.complex_id
    ";
    
    if ($complex_id) {
        $sql .= " WHERE at.complex_id = " . $complex_id;
    }
    
    $sql .= " ORDER BY rc.name, at.rooms_count, at.area";
    
    $result = $db->query($sql);
    while ($row = $result->fetch_assoc()) {
        $apartments[] = $row;
    }
    
} catch (Exception $e) {
    error_log("Ошибка в админ-панели: " . $e->getMessage());
    $error = "Произошла ошибка при загрузке данных: " . $e->getMessage();
}

// Подключаем шапку
require_once 'templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Управление квартирами</h1>
    <a href="edit_apartment.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Добавить квартиру
    </a>
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

<?php if (empty($apartments)): ?>
    <div class="alert alert-info">
        Нет добавленных квартир
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($apartments as $apartment): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php if ($apartment['main_image']): ?>
                        <img src="<?php echo sanitizeOutput($apartment['main_image']); ?>" 
                             class="card-img-top" alt="Планировка" style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <i class="fas fa-image fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo $apartment['rooms_count']; ?>-комнатная, 
                            <?php echo $apartment['area']; ?> м²
                        </h5>
                        <p class="card-text">
                            <small class="text-muted">
                                <?php echo sanitizeOutput($apartment['complex_name']); ?>
                            </small>
                        </p>
                        <ul class="list-unstyled">
                            <li>
                                <strong>Этаж:</strong> 
                                <?php echo $apartment['floor']; ?> из <?php echo $apartment['max_floor']; ?>
                            </li>
                            <li>
                                <strong>Цена:</strong> 
                                <?php echo number_format($apartment['price'], 0, ',', ' '); ?> ₽
                            </li>
                            <?php if ($apartment['description']): ?>
                                <li class="mt-2">
                                    <?php echo nl2br(sanitizeOutput($apartment['description'])); ?>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <div class="card-footer bg-white border-top-0">
                        <div class="btn-group w-100">
                            <a href="edit_apartment.php?id=<?php echo $apartment['id']; ?>" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-edit"></i> Редактировать
                            </a>
                            <a href="delete_apartment.php?id=<?php echo $apartment['id']; ?>" 
                               class="btn btn-outline-danger"
                               data-confirm="Вы уверены, что хотите удалить эту квартиру?">
                                <i class="fas fa-trash"></i> Удалить
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
// Подключаем футер
require_once 'templates/footer.php';
?> 