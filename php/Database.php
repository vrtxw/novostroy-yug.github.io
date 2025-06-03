<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        
        try {
            // Пробуем подключиться через TCP/IP
            $this->connection = mysqli_init();
            
            if (!$this->connection) {
                throw new Exception('mysqli_init failed');
            }
            
            // Устанавливаем таймаут подключения и другие опции
            $this->connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
            $this->connection->options(MYSQLI_INIT_COMMAND, "SET NAMES 'utf8mb4'");
            $this->connection->options(MYSQLI_INIT_COMMAND, "SET time_zone = '+03:00'");
            
            // Пробуем подключиться
            if (!$this->connection->real_connect(
                DB_HOST,
                DB_USER,
                DB_PASSWORD,
                DB_NAME,
                DB_PORT
            )) {
                throw new Exception($this->connection->connect_error);
            }
            
            // Устанавливаем кодировку
            $this->connection->set_charset('utf8mb4');
            
        } catch (mysqli_sql_exception $e) {
            error_log("MySQL Exception: " . $e->getMessage());
            throw new Exception('Ошибка MySQL: ' . $e->getMessage());
        } catch (Exception $e) {
            error_log("General Exception: " . $e->getMessage());
            throw new Exception('Общая ошибка: ' . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get the database connection
     * @return mysqli The database connection object
     * @throws Exception if no connection is established
     */
    public function getConnection() {
        if (!$this->connection) {
            throw new Exception('No database connection established');
        }
        return $this->connection;
    }
    
    public function query($sql) {
        try {
            $result = $this->connection->query($sql);
            if ($result === false) {
                throw new Exception('Ошибка выполнения запроса: ' . $this->connection->error);
            }
            return $result;
        } catch (Exception $e) {
            error_log("Query Error: " . $e->getMessage() . "\nQuery: " . $sql);
            throw $e;
        }
    }
    
    public function prepare($sql) {
        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare Error: " . $this->connection->error . "\nQuery: " . $sql);
            throw new Exception('Ошибка подготовки запроса: ' . $this->connection->error);
        }
        return $stmt;
    }
    
    public function begin_transaction() {
        return $this->connection->begin_transaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    public function escape_string($str) {
        return $this->connection->real_escape_string($str);
    }
    
    public function get_insert_id() {
        return $this->connection->insert_id;
    }
    
    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
} 