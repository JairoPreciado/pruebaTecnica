<?php   //Configuración para conectar a la base de datos
class Database {
    private $host = "localhost";
    private $db_name = "ordenes_db";
    private $username = "root";
    private $password = ""; // Este campo se modifica en cuyo caso se tenga contraseña para el gestor de base de datos
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
