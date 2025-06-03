<?php
class ResidentialComplex {
    private $db;
    private $id;
    private $data;

    public function __construct($id = null) {
        $this->db = Database::getInstance();
        if ($id) {
            $this->id = $id;
            $this->loadData();
        }
    }

    private function loadData() {
        $stmt = $this->db->prepare("SELECT * FROM residential_complexes WHERE id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement");
        }
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        $this->data = $result->fetch_assoc();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO residential_complexes 
            (name, description, address, price_from, price_to, completion_date, status, main_image, layout_image, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "sssddssssi",
            $data['name'],
            $data['description'],
            $data['address'],
            $data['price_from'],
            $data['price_to'],
            $data['completion_date'],
            $data['status'],
            $data['main_image'],
            $data['layout_image'],
            $data['is_active']
        );
        
        if ($stmt->execute()) {
            $this->id = $stmt->insert_id;
            $this->loadData();
            return true;
        }
        return false;
    }

    public function update($data) {
        $stmt = $this->db->prepare("
            UPDATE residential_complexes 
            SET name = ?, description = ?, address = ?, price_from = ?, price_to = ?, 
                completion_date = ?, status = ?, main_image = ?, layout_image = ?, is_active = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "sssddssssi",
            $data['name'],
            $data['description'],
            $data['address'],
            $data['price_from'],
            $data['price_to'],
            $data['completion_date'],
            $data['status'],
            $data['main_image'],
            $data['layout_image'],
            $data['is_active'],
            $this->id
        );
        
        if ($stmt->execute()) {
            $this->loadData();
            return true;
        }
        return false;
    }

    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM residential_complexes WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }

    public function addFeature($name, $value) {
        $stmt = $this->db->prepare("
            INSERT INTO complex_features (complex_id, feature_name, feature_value)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iss", $this->id, $name, $value);
        return $stmt->execute();
    }

    public function updateFeature($featureId, $name, $value) {
        $stmt = $this->db->prepare("
            UPDATE complex_features 
            SET feature_name = ?, feature_value = ?
            WHERE id = ? AND complex_id = ?
        ");
        $stmt->bind_param("ssii", $name, $value, $featureId, $this->id);
        return $stmt->execute();
    }

    public function deleteFeature($featureId) {
        $stmt = $this->db->prepare("
            DELETE FROM complex_features 
            WHERE id = ? AND complex_id = ?
        ");
        $stmt->bind_param("ii", $featureId, $this->id);
        return $stmt->execute();
    }

    public function getFeatures() {
        $stmt = $this->db->prepare("
            SELECT * FROM complex_features 
            WHERE complex_id = ?
            ORDER BY id ASC
        ");
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $this->id);
        if (!$stmt->execute()) {
            return [];
        }
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function getAll($activeOnly = true) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM residential_complexes";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY id DESC";
        
        $result = $db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getData() {
        return $this->data;
    }

    public function getId() {
        return $this->id;
    }
} 