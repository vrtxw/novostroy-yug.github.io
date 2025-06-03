<?php
class ResidentialComplex {
    private $db;
    private $id;
    
    public function __construct($id = null) {
        $this->db = Database::getInstance();
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getAll() {
        $complexes = [];
        $result = $this->db->query("
            SELECT rc.*, 
                   GROUP_CONCAT(DISTINCT CONCAT(cf.feature_category, ':', cf.feature_name, '=', cf.feature_value) 
                   ORDER BY cf.feature_category, cf.display_order 
                   SEPARATOR '||') as features
            FROM residential_complexes rc
            LEFT JOIN complex_features cf ON cf.complex_id = rc.id
            GROUP BY rc.id
            ORDER BY rc.name
        ");
        
        while ($row = $result->fetch_assoc()) {
            $complex = $row;
            $complex['features'] = $this->parseFeatures($row['features']);
            $complexes[] = $complex;
        }
        
        return $complexes;
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT rc.*, 
                   GROUP_CONCAT(DISTINCT CONCAT(cf.feature_category, ':', cf.feature_name, '=', cf.feature_value) 
                   ORDER BY cf.feature_category, cf.display_order 
                   SEPARATOR '||') as features
            FROM residential_complexes rc
            LEFT JOIN complex_features cf ON cf.complex_id = rc.id
            WHERE rc.id = ?
            GROUP BY rc.id
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $complex = $row;
            $complex['features'] = $this->parseFeatures($row['features']);
            return $complex;
        }
        
        return null;
    }
    
    private function parseFeatures($featuresString) {
        if (empty($featuresString)) {
            return [];
        }
        
        $features = [];
        $featuresList = explode('||', $featuresString);
        
        foreach ($featuresList as $feature) {
            list($categoryAndName, $value) = explode('=', $feature);
            list($category, $name) = explode(':', $categoryAndName);
            
            if (!isset($features[$category])) {
                $features[$category] = [];
            }
            
            $features[$category][] = [
                'name' => $name,
                'value' => $value
            ];
        }
        
        return $features;
    }
    
    public function getApartments($complex_id) {
        $stmt = $this->db->prepare("
            SELECT at.*, 
                   GROUP_CONCAT(DISTINCT ai.image_path ORDER BY ai.display_order SEPARATOR '||') as images
            FROM apartment_types at
            LEFT JOIN apartment_images ai ON ai.apartment_id = at.id
            WHERE at.complex_id = ?
            GROUP BY at.id
            ORDER BY at.rooms_count, at.area
        ");
        $stmt->bind_param('i', $complex_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $apartments = [];
        while ($row = $result->fetch_assoc()) {
            $apartment = $row;
            $apartment['images'] = empty($row['images']) ? [] : explode('||', $row['images']);
            $apartments[] = $apartment;
        }
        
        return $apartments;
    }
} 