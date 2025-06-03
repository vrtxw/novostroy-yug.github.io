<?php
require_once 'config.php';

// Создаем таблицу для администраторов
$sql = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Error creating admins table: " . $conn->error);
}

// Создаем таблицу для проектов недвижимости
$sql = "CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(15,2),
    area DECIMAL(10,2),
    rooms INT,
    address VARCHAR(255),
    main_image VARCHAR(255),
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Error creating properties table: " . $conn->error);
}

// Создаем таблицу для изображений проектов
$sql = "CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    image_path VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
)";

if (!$conn->query($sql)) {
    die("Error creating property_images table: " . $conn->error);
}

// Создаем таблицу для характеристик проектов
$sql = "CREATE TABLE IF NOT EXISTS property_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    feature_name VARCHAR(100) NOT NULL,
    feature_value TEXT,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
)";

if (!$conn->query($sql)) {
    die("Error creating property_features table: " . $conn->error);
}

// Добавляем администратора по умолчанию
$admin_username = ADMIN_USERNAME;
$admin_password = ADMIN_PASSWORD_HASH;
$admin_email = 'admin@example.com';

$sql = "INSERT IGNORE INTO admins (username, password, email) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $admin_username, $admin_password, $admin_email);
$stmt->execute();

echo "Database initialization completed successfully!";
?> 