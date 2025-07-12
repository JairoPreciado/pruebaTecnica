<?php

class Orden {
    private $conn;
    private $table = "ordenes";

    // Propiedades públicas (representan los campos de la tabla)
    public $id;
    public $cliente;
    public $descripcion;
    public $estado;
    public $prioridad;
    public $fecha_estimada;

    // Constructor recibe la conexión
    public function __construct($db) {
        $this->conn = $db;
    }

    // ============================
    // MÉTODO: Obtener todas las órdenes
    // ============================
    public function obtenerTodas() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY creado_en DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // ============================
    // MÉTODO: Crear una orden nueva
    // ============================
    public function crear() {
        try {
            $query = "INSERT INTO " . $this->table . " (cliente, descripcion, estado, prioridad, fecha_estimada)
                    VALUES (:cliente, :descripcion, :estado, :prioridad, :fecha_estimada)";

            $stmt = $this->conn->prepare($query);

            // Limpiar datos
            $this->cliente =        htmlspecialchars(strip_tags($this->cliente));
            $this->descripcion =    htmlspecialchars(strip_tags($this->descripcion));
            $this->estado =         htmlspecialchars(strip_tags($this->estado));
            $this->prioridad =      htmlspecialchars(strip_tags($this->prioridad));
            $this->fecha_estimada = htmlspecialchars(strip_tags($this->fecha_estimada));

            // Bind de parámetros
            $stmt->bindParam(":cliente",        $this->cliente);
            $stmt->bindParam(":descripcion",    $this->descripcion);
            $stmt->bindParam(":estado",         $this->estado);
            $stmt->bindParam(":prioridad",      $this->prioridad);
            $stmt->bindParam(":fecha_estimada", $this->fecha_estimada);

            return $stmt->execute();

        } catch (PDOException $e) {
            registrarLog('EXCEPTION', "crear(): " . $e->getMessage());
            return false;
        }      
    }

    // ============================
    // MÉTODO: Actualizar una orden
    // ============================
    public function actualizar($id) {
        try {
            $query = "UPDATE " . $this->table . " 
                    SET cliente = :cliente, descripcion = :descripcion, estado = :estado,
                        prioridad = :prioridad, fecha_estimada = :fecha_estimada 
                    WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            // Limpiar
            $this->cliente =        htmlspecialchars(strip_tags($this->cliente));
            $this->descripcion =    htmlspecialchars(strip_tags($this->descripcion));
            $this->estado =         htmlspecialchars(strip_tags($this->estado));
            $this->prioridad =      htmlspecialchars(strip_tags($this->prioridad));
            $this->fecha_estimada = htmlspecialchars(strip_tags($this->fecha_estimada));

            // Bind
            $stmt->bindParam(":cliente",        $this->cliente);
            $stmt->bindParam(":descripcion",    $this->descripcion);
            $stmt->bindParam(":estado",         $this->estado);
            $stmt->bindParam(":prioridad",      $this->prioridad);
            $stmt->bindParam(":fecha_estimada", $this->fecha_estimada);
            $stmt->bindParam(":id", $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            registrarLog('EXCEPTION', "actualizar(): " . $e->getMessage());
            return false;
        }
    }

    // ============================
    // MÉTODO: Eliminar una orden
    // ============================
    public function eliminar($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            registrarLog('EXCEPTION', "eliminar(): " . $e->getMessage());
            return false;
        }
    }

    // ============================
    // MÉTODO: Validar si existe un duplicado
    // ============================
    public function existeDuplicado($cliente, $fecha_estimada, $excluirId = null) {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table . " 
                    WHERE cliente = :cliente AND fecha_estimada = :fecha_estimada";

            if ($excluirId !== null) {
                $query .= " AND id != :id";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cliente', $cliente);
            $stmt->bindParam(':fecha_estimada', $fecha_estimada);

            if ($excluirId !== null) {
                $stmt->bindParam(':id', $excluirId, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            registrarLog('EXCEPTION', "existeDuplicado(): " . $e->getMessage());
            return false;
        }
    }
    
    // ============================
    // MÉTODO: Validar datos de entrada
    // ============================
    public function validar($data) {
        return  isset($data['cliente'])     && 
                isset($data['descripcion']) &&
                isset($data['estado'])      && 
                isset($data['prioridad'])   &&
                isset($data['fecha_estimada']);
    }

    // ============================
    // MÉTODO: Obtener una orden por ID
    // ============================
    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) {
            registrarLog('EXCEPTION', "obtenerPorId(): " . $e->getMessage());
            return null;
        }
    }
    
    // ============================
    // MÉTODO: Obtener órdenes paginadas
    // ============================
    public function obtenerPaginado($limit, $offset) {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY creado_en DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt;
        } catch (PDOException $e) {
            registrarLog('EXCEPTION', "obtenerPaginado(): " . $e->getMessage());
            return null;
        }
    }

    // ============================
    // MÉTODO: Contar todas las órdenes
    // ============================
    public function contarTodas() {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $stmt = $this->conn->query($query);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'];
        } catch (PDOException $e) {
            registrarLog('EXCEPTION', "contarTodas(): " . $e->getMessage());
            return 0; 
        }
    }
}
