<?php
define('SECURE_ACCESS', true);
require_once 'php/config.php';
require_once 'php/classes/ResidentialComplex.php';

// Получаем список активных жилых комплексов
$complexes = ResidentialComplex::getAll(true);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ЖК "ПРОСТОР" - Новостройки в Аксае</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        .complex-card {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .complex-info {
            margin: 1rem 0;
        }
        
        .feature-category {
            margin: 1rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .feature-category h3 {
            font-size: 1.2rem;
            color: #343a40;
            margin-bottom: 0.5rem;
        }
        
        .feature-category ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .feature-category li {
            margin: 0.5rem 0;
        }
        
        .apartment-card {
            margin: 1rem 0;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .apartment-images {
            margin-bottom: 1rem;
        }
        
        .apartment-slider {
            position: relative;
            overflow: hidden;
        }
        
        .apartment-slide img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        
        .no-image {
            padding: 2rem;
            text-align: center;
            background: #f8f9fa;
            border-radius: 4px;
            color: #6c757d;
        }
        
        #loading-indicator {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,255,255,0.9);
            padding: 1rem;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: none;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">ЖК "ПРОСТОР"</a>
        </div>
    </nav>

    <div class="container my-4">
        <div id="loading-indicator">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Загрузка...</span>
            </div>
        </div>
        
        <div id="complexes-container"></div>
        
        <div id="apartments-container" class="mt-4"></div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/complexes.js"></script>
</body>
</html> 