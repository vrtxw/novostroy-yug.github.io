-- Создание таблицы для ЖК
CREATE TABLE residential_complexes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    class VARCHAR(50) NOT NULL,
    wall_material VARCHAR(100) NOT NULL,
    finishing_type VARCHAR(100),
    free_layout BOOLEAN DEFAULT FALSE,
    floors_count INT NOT NULL,
    apartments_count INT NOT NULL,
    living_area DECIMAL(10,2),
    ceiling_height DECIMAL(3,2),
    description TEXT,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Создание таблицы для характеристик ЖК
CREATE TABLE complex_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complex_id INT,
    feature_category VARCHAR(100) NOT NULL,
    feature_name VARCHAR(100) NOT NULL,
    feature_value VARCHAR(255),
    display_order INT DEFAULT 0,
    FOREIGN KEY (complex_id) REFERENCES residential_complexes(id) ON DELETE CASCADE
);

-- Создание таблицы для типов квартир
CREATE TABLE apartment_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complex_id INT,
    rooms_count INT NOT NULL,
    total_count INT NOT NULL,
    min_area DECIMAL(10,2),
    max_area DECIMAL(10,2),
    min_price DECIMAL(15,2),
    max_price DECIMAL(15,2),
    min_living_area DECIMAL(10,2),
    max_living_area DECIMAL(10,2),
    has_balcony BOOLEAN DEFAULT TRUE,
    rooms_range VARCHAR(20),
    description TEXT,
    FOREIGN KEY (complex_id) REFERENCES residential_complexes(id) ON DELETE CASCADE
);

-- Создание таблицы для вариантов отделки
CREATE TABLE finishing_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complex_id INT,
    option_name VARCHAR(100) NOT NULL,
    feature_name VARCHAR(100) NOT NULL,
    is_included BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    FOREIGN KEY (complex_id) REFERENCES residential_complexes(id) ON DELETE CASCADE
);

-- Создание таблицы для изображений квартир
CREATE TABLE apartment_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    apartment_type_id INT,
    image_path VARCHAR(255) NOT NULL,
    image_order INT DEFAULT 0,
    is_main BOOLEAN DEFAULT FALSE,
    alt_text VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (apartment_type_id) REFERENCES apartment_types(id) ON DELETE CASCADE
);

-- Создание таблицы для характеристик лифтов
CREATE TABLE elevator_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complex_id INT,
    entrance_count INT NOT NULL,
    passenger_elevator_count INT NOT NULL,
    cargo_elevator_count INT NOT NULL,
    FOREIGN KEY (complex_id) REFERENCES residential_complexes(id) ON DELETE CASCADE
);

-- Создание таблицы для контактной информации
CREATE TABLE contact_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complex_id INT,
    office_address VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    working_hours VARCHAR(255),
    map_coordinates VARCHAR(100),
    FOREIGN KEY (complex_id) REFERENCES residential_complexes(id) ON DELETE CASCADE
);

-- Создание таблицы для навигации сайта
CREATE TABLE navigation_menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    parent_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (parent_id) REFERENCES navigation_menu(id) ON DELETE SET NULL
);

-- Создание таблицы для страниц сайта
CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT,
    meta_keywords VARCHAR(255),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Создание таблицы для городов/районов
CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    region VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE
);

-- Создание таблицы для классов недвижимости
CREATE TABLE property_classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE
);

-- Создание таблицы для связи ЖК с локациями
CREATE TABLE complex_locations (
    complex_id INT,
    location_id INT,
    PRIMARY KEY (complex_id, location_id),
    FOREIGN KEY (complex_id) REFERENCES residential_complexes(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
);

-- Вставка данных о ЖК "Простор"
INSERT INTO residential_complexes (
    name, class, wall_material, finishing_type, free_layout,
    floors_count, apartments_count, living_area, ceiling_height,
    address, phone, email, description
) VALUES (
    'ПРОСТОР', 'Комфорт', 'Кирпич', '1 вариант', FALSE,
    9, 105, 5375.00, 2.70,
    '344020, г.Ростов-на-Дону, ул.Курчатова, 1Д',
    '+7 (993) 445-36-12',
    'vrtxw@list.ru',
    'Жилой комплекс "ПРОСТОР" - современный комфортабельный комплекс в Ростове-на-Дону'
);

-- Получение ID добавленного ЖК
SET @complex_id = LAST_INSERT_ID();

-- Вставка пунктов навигации
INSERT INTO navigation_menu (title, url, display_order) VALUES
('ГЛАВНАЯ', 'index.html', 1),
('О НАС', 'about.html', 2),
('ПРОЕКТЫ', 'house.html', 3),
('ЦЕНЫ', 'price.html', 4),
('КОНТАКТЫ', 'contact.html', 5);

-- Вставка характеристик двора
INSERT INTO complex_features (complex_id, feature_category, feature_name, feature_value, display_order) VALUES
(@complex_id, 'Благоустройство двора', 'Велосипедные дорожки', 'Нет', 1),
(@complex_id, 'Благоустройство двора', 'Количество детских площадок', '1', 2),
(@complex_id, 'Благоустройство двора', 'Количество спортивных площадок', '2', 3),
(@complex_id, 'Благоустройство двора', 'Количество площадок для сбора мусора', '1', 4);

-- Вставка характеристик парковки
INSERT INTO complex_features (complex_id, feature_category, feature_name, feature_value, display_order) VALUES
(@complex_id, 'Парковочное пространство', 'Количество мест в паркинге', '47', 1),
(@complex_id, 'Парковочное пространство', 'Гостевые места на придомовой территории', '5', 2),
(@complex_id, 'Парковочное пространство', 'Гостевые места вне придомовой территории', 'Нет', 3);

-- Вставка характеристик безбарьерной среды
INSERT INTO complex_features (complex_id, feature_category, feature_name, feature_value, display_order) VALUES
(@complex_id, 'Безбарьерная среда', 'Наличие пандуса', 'Есть', 1),
(@complex_id, 'Безбарьерная среда', 'Наличие понижающих площадок', 'Есть', 2),
(@complex_id, 'Безбарьерная среда', 'Количество инвалидных подъемников', 'Нет', 3);

-- Вставка информации о лифтах
INSERT INTO elevator_info (complex_id, entrance_count, passenger_elevator_count, cargo_elevator_count)
VALUES (@complex_id, 2, 2, 2);

-- Вставка контактной информации
INSERT INTO contact_info (complex_id, office_address, phone, email, working_hours)
VALUES (
    @complex_id,
    '344020, г.Ростов-на-Дону, ул.Курчатова, 1Д',
    '+7 (993) 445-36-12',
    'vrtxw@list.ru',
    'Пн-Пт: 9:00-18:00, Сб: 10:00-15:00, Вс: выходной'
);

-- Вставка данных о типах квартир
INSERT INTO apartment_types (
    complex_id, rooms_count, total_count,
    min_area, max_area, min_price, max_price,
    min_living_area, max_living_area, has_balcony,
    rooms_range, description
) VALUES
-- Однокомнатные квартиры
(@complex_id, 1, 77, 36.71, 56.11, 4405000, 6733000, 11.57, 15.13, TRUE, '3 - 6', 
'Просторные однокомнатные квартиры с удобной планировкой'),
-- Двухкомнатные квартиры
(@complex_id, 2, 28, 53.79, 80.72, 6454000, 9686000, 24.19, 33.88, TRUE, '3 - 6',
'Светлые двухкомнатные квартиры с большой кухней');

-- Вставка изображений для квартир
INSERT INTO apartment_images (apartment_type_id, image_path, image_order, is_main, alt_text) VALUES
((SELECT id FROM apartment_types WHERE complex_id = @complex_id AND rooms_count = 1), 'images/prostor11.jpg', 1, TRUE, 'Планировка 1-комнатной квартиры'),
((SELECT id FROM apartment_types WHERE complex_id = @complex_id AND rooms_count = 2), 'images/prostor23.jpg', 1, TRUE, 'Планировка 2-комнатной квартиры'),
((SELECT id FROM apartment_types WHERE complex_id = @complex_id AND rooms_count = 2), 'images/prostor21.jpg', 2, FALSE, 'Вид комнаты'),
((SELECT id FROM apartment_types WHERE complex_id = @complex_id AND rooms_count = 2), 'images/prostor24.jpg', 3, FALSE, 'Вид кухни');

-- Вставка данных о вариантах отделки
INSERT INTO finishing_options (complex_id, option_name, feature_name, is_included, display_order) VALUES
(@complex_id, '1 вариант', 'Установка входной двери', TRUE, 1),
(@complex_id, '1 вариант', 'Установка радиаторов', TRUE, 2),
(@complex_id, '1 вариант', 'Установлено остекление', TRUE, 3),
(@complex_id, '1 вариант', 'Разводка инженерных коммуникаций (ГВ/ХВ)', TRUE, 4),
(@complex_id, '1 вариант', 'Возведение межкомнатных перегородок', TRUE, 5),
(@complex_id, '1 вариант', 'Подготовка стен помещений (кроме санузлов)', TRUE, 6),
(@complex_id, '1 вариант', 'Подготовка стен санузлов', TRUE, 7),
(@complex_id, '1 вариант', 'Стяжка пола помещений (кроме санузлов)', TRUE, 8),
(@complex_id, '1 вариант', 'Стяжка пола санузлов', TRUE, 9),
(@complex_id, '1 вариант', 'Подготовка потолков помещений (кроме санузлов)', TRUE, 10),
(@complex_id, '1 вариант', 'Разводка инженерных коммуникаций (Электрика)', TRUE, 11),
(@complex_id, '1 вариант', 'Разводка инженерных коммуникаций (Слаботочные системы)', TRUE, 12);

-- Создание основных страниц сайта
INSERT INTO pages (title, slug, content, meta_description) VALUES
('Главная', 'index', 'Главная страница ЖК "ПРОСТОР"', 'Жилой комплекс "ПРОСТОР" в Ростове-на-Дону - современное комфортное жилье'),
('О нас', 'about', 'Информация о застройщике', 'Информация о компании-застройщике ЖК "ПРОСТОР"'),
('Проекты', 'house', 'Проекты квартир', 'Планировки и проекты квартир в ЖК "ПРОСТОР"'),
('Цены', 'price', 'Стоимость квартир', 'Актуальные цены на квартиры в ЖК "ПРОСТОР"'),
('Контакты', 'contact', 'Контактная информация', 'Контакты отдела продаж ЖК "ПРОСТОР"');

-- Вставка данных о городах/районах
INSERT INTO locations (name, region) VALUES
('Аксай', 'Ростовская область'),
('Ростов-на-Дону', 'Ростовская область'),
('Батайск', 'Ростовская область'),
('Азов', 'Ростовская область');

-- Вставка данных о классах недвижимости
INSERT INTO property_classes (name, description) VALUES
('Эконом', 'Доступное жилье с базовыми удобствами'),
('Комфорт', 'Современное жилье со всеми удобствами'),
('Бизнес', 'Престижное жилье с улучшенными характеристиками'),
('Элит', 'Элитное жилье премиум-класса');

-- Связываем ЖК "ПРОСТОР" с локацией Аксай
INSERT INTO complex_locations (complex_id, location_id)
SELECT rc.id, l.id
FROM residential_complexes rc
CROSS JOIN locations l
WHERE rc.name = 'ПРОСТОР' AND l.name = 'Аксай';

-- Полезные запросы для получения информации:

-- Получение основной информации о ЖК с контактами
SELECT rc.*, ci.* 
FROM residential_complexes rc
JOIN contact_info ci ON rc.id = ci.complex_id 
WHERE rc.name = 'ПРОСТОР';

-- Получение всех характеристик ЖК по категориям
SELECT feature_category, feature_name, feature_value 
FROM complex_features 
WHERE complex_id = @complex_id 
ORDER BY feature_category, display_order;

-- Получение информации о типах квартир с ценами
SELECT rooms_count, total_count,
       CONCAT(min_area, ' - ', max_area) as area_range,
       CONCAT(FORMAT(min_price/1000000, 2), ' - ', FORMAT(max_price/1000000, 2), ' млн.р.') as price_range,
       description
FROM apartment_types 
WHERE complex_id = @complex_id
ORDER BY rooms_count;

-- Получение списка включенных опций отделки
SELECT feature_name 
FROM finishing_options 
WHERE complex_id = @complex_id AND is_included = TRUE
ORDER BY display_order;

-- Получение информации о лифтах
SELECT * FROM elevator_info WHERE complex_id = @complex_id;

-- Получение контактной информации с рабочими часами
SELECT * FROM contact_info WHERE complex_id = @complex_id;

-- Получение изображений квартир с описанием
SELECT at.rooms_count, ai.image_path, ai.is_main, ai.alt_text
FROM apartment_images ai
JOIN apartment_types at ON ai.apartment_type_id = at.id
WHERE at.complex_id = @complex_id
ORDER BY at.rooms_count, ai.image_order;

-- Получение пунктов навигации
SELECT title, url FROM navigation_menu WHERE is_active = TRUE ORDER BY display_order; 