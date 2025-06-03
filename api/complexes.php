<?php
require_once '../php/config.php';
require_once '../php/Database.php';
require_once '../php/ResidentialComplex.php';

header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? 'list';
    $complex = new ResidentialComplex();
    
    switch ($action) {
        case 'list':
            $data = $complex->getAll();
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            break;
            
        case 'get':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception('ID не указан');
            }
            
            $data = $complex->getById($id);
            if (!$data) {
                throw new Exception('ЖК не найден');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            break;
            
        case 'apartments':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception('ID не указан');
            }
            
            $data = $complex->getApartments($id);
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            break;
            
        default:
            throw new Exception('Неизвестное действие');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 