<?php
define('SECURE_ACCESS', true);
require_once '../php/config.php';

// Проверка авторизации
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$current_page = 'apartments';
$page_title = 'Редактирование квартиры';

try {
    $db = Database::getInstance();
    
    // Получаем список ЖК
    $complexes = [];
    $result = $db->query("SELECT id, name FROM residential_complexes ORDER BY name");
    while ($row = $result->fetch_assoc()) {
        $complexes[] = $row;
    }
    
    // Получаем данные квартиры, если это редактирование
    $apartment = null;
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if ($id) {
        $stmt = $db->prepare("
            SELECT at.*, rc.name as complex_name 
            FROM apartment_types at
            JOIN residential_complexes rc ON rc.id = at.complex_id
            WHERE at.id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $apartment = $stmt->get_result()->fetch_assoc();
        
        if (!$apartment) {
            throw new Exception('Квартира не найдена');
        }
        
        // Получаем изображения квартиры
        $images = [];
        $stmt = $db->prepare("SELECT * FROM apartment_images WHERE apartment_id = ? ORDER BY display_order");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
        $apartment['images'] = $images;
    }
    
    // Обработка формы
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Валидация данных
        $complex_id = filter_input(INPUT_POST, 'complex_id', FILTER_VALIDATE_INT);
        $rooms_count = filter_input(INPUT_POST, 'rooms_count', FILTER_VALIDATE_INT);
        $floor = filter_input(INPUT_POST, 'floor', FILTER_VALIDATE_INT);
        $max_floor = filter_input(INPUT_POST, 'max_floor', FILTER_VALIDATE_INT);
        $area = filter_input(INPUT_POST, 'area', FILTER_VALIDATE_FLOAT);
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        
        if (!$complex_id || !$rooms_count || !$floor || !$max_floor || !$area || !$price) {
            throw new Exception('Пожалуйста, заполните все обязательные поля');
        }
        
        if ($floor > $max_floor) {
            throw new Exception('Этаж не может быть больше максимального этажа');
        }
        
        // Начинаем транзакцию
        $db->begin_transaction();
        
        try {
            if ($id) {
                // Обновляем существующую квартиру
                $stmt = $db->prepare("
                    UPDATE apartment_types SET 
                    complex_id = ?, rooms_count = ?, floor = ?, max_floor = ?,
                    area = ?, price = ?, description = ?
                    WHERE id = ?
                ");
                $stmt->bind_param('iiiiddsi',
                    $complex_id,
                    $rooms_count,
                    $floor,
                    $max_floor,
                    $area,
                    $price,
                    $_POST['description'],
                    $id
                );
            } else {
                // Добавляем новую квартиру
                $stmt = $db->prepare("
                    INSERT INTO apartment_types 
                    (complex_id, rooms_count, floor, max_floor, area, price, description)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param('iiiidds',
                    $complex_id,
                    $rooms_count,
                    $floor,
                    $max_floor,
                    $area,
                    $price,
                    $_POST['description']
                );
            }
            
            if (!$stmt->execute()) {
                throw new Exception('Ошибка при сохранении данных квартиры');
            }
            
            if (!$id) {
                $id = $db->get_insert_id();
            }
            
            // Обработка загруженных изображений
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = '../uploads/apartments/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $filename = uniqid() . '_' . $_FILES['images']['name'][$key];
                        $filepath = $upload_dir . $filename;
                        
                        if (move_uploaded_file($tmp_name, $filepath)) {
                            $stmt = $db->prepare("
                                INSERT INTO apartment_images (apartment_id, image_path, display_order)
                                VALUES (?, ?, (SELECT COALESCE(MAX(display_order), 0) + 1 FROM apartment_images WHERE apartment_id = ?))
                            ");
                            $relative_path = 'uploads/apartments/' . $filename;
                            $stmt->bind_param('isi', $id, $relative_path, $id);
                            $stmt->execute();
                        }
                    }
                }
            }
            
            $db->commit();
            $_SESSION['success'] = 'Квартира успешно ' . ($id ? 'обновлена' : 'добавлена');
            header('Location: apartments.php');
            exit;
            
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
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
    <h1 class="h3 mb-0">
        <?php echo $id ? 'Редактирование квартиры' : 'Добавление квартиры'; ?>
    </h1>
    <a href="apartments.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Назад к списку
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="complex_id">Жилой комплекс <span class="text-danger">*</span></label>
                        <select class="form-control" id="complex_id" name="complex_id" required>
                            <option value="">Выберите ЖК</option>
                            <?php foreach ($complexes as $complex): ?>
                                <option value="<?php echo $complex['id']; ?>"
                                        <?php echo ($apartment && $apartment['complex_id'] == $complex['id']) ? 'selected' : ''; ?>>
                                    <?php echo sanitizeOutput($complex['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="rooms_count">Количество комнат <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="rooms_count" name="rooms_count" min="1" max="10"
                               value="<?php echo $apartment ? $apartment['rooms_count'] : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="floor">Этаж <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="floor" name="floor" min="1"
                               value="<?php echo $apartment ? $apartment['floor'] : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="max_floor">Всего этажей <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="max_floor" name="max_floor" min="1"
                               value="<?php echo $apartment ? $apartment['max_floor'] : ''; ?>" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="area">Площадь (м²) <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" class="form-control" id="area" name="area" min="0"
                               value="<?php echo $apartment ? $apartment['area'] : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="price">Цена (₽) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="price" name="price" min="0"
                               value="<?php echo $apartment ? $apartment['price'] : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Описание</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php 
                            echo $apartment ? sanitizeOutput($apartment['description']) : ''; 
                        ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="images">Изображения планировки</label>
                        <input type="file" class="form-control-file" id="images" name="images[]" multiple accept="image/*">
                        <small class="form-text text-muted">
                            Можно загрузить несколько изображений. Поддерживаемые форматы: JPG, PNG, GIF.
                        </small>
                    </div>
                </div>
            </div>

            <?php if ($apartment && !empty($apartment['images'])): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <h5>Текущие изображения</h5>
                        <div class="row">
                            <?php foreach ($apartment['images'] as $image): ?>
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <img src="<?php echo sanitizeOutput($image['image_path']); ?>" 
                                             class="card-img-top" alt="Планировка">
                                        <div class="card-body p-2">
                                            <a href="delete_image.php?id=<?php echo $image['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger btn-block"
                                               data-confirm="Удалить это изображение?">
                                                <i class="fas fa-trash"></i> Удалить
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="text-right mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> 
                    <?php echo $id ? 'Сохранить изменения' : 'Добавить квартиру'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Подключаем футер
require_once 'templates/footer.php';
?> 