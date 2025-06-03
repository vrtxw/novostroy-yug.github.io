<?php
define('SECURE_ACCESS', true);
require_once '../../php/config.php';
require_once '../../php/classes/ResidentialComplex.php';

// Проверяем авторизацию
requireAdmin();

// Проверяем CSRF-токен
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    die(json_encode(['error' => 'Invalid CSRF token']));
}

header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? '';
    $response = ['success' => false];

    switch ($action) {
        case 'create':
            $complex = new ResidentialComplex();
            $response['success'] = $complex->create($_POST);
            $response['id'] = $complex->getId();
            break;

        case 'update':
            $complex = new ResidentialComplex($_POST['id']);
            $response['success'] = $complex->update($_POST);
            break;

        case 'delete':
            $complex = new ResidentialComplex($_POST['id']);
            $response['success'] = $complex->delete();
            break;

        case 'add_feature':
            $complex = new ResidentialComplex($_POST['complex_id']);
            $response['success'] = $complex->addFeature(
                $_POST['feature_name'],
                $_POST['feature_value']
            );
            break;

        case 'update_feature':
            $complex = new ResidentialComplex($_POST['complex_id']);
            $response['success'] = $complex->updateFeature(
                $_POST['feature_id'],
                $_POST['feature_name'],
                $_POST['feature_value']
            );
            break;

        case 'delete_feature':
            $complex = new ResidentialComplex($_POST['complex_id']);
            $response['success'] = $complex->deleteFeature($_POST['feature_id']);
            break;

        case 'get_all':
            $response['data'] = ResidentialComplex::getAll(false);
            $response['success'] = true;
            break;

        case 'get_features':
            $complex = new ResidentialComplex($_POST['complex_id']);
            $response['data'] = $complex->getFeatures();
            $response['success'] = true;
            break;

        default:
            $response['error'] = 'Invalid action';
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    error_log("Error in complex_handler.php: " . $e->getMessage());
}

echo json_encode($response); 