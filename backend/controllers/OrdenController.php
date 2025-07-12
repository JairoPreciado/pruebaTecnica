<?php
require_once __DIR__ . '/../models/Orden.php';

class OrdenController {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    // GET /ordenes
    public function index() {   // Obtener todas las órdenes
        $limit = $_GET['limit'] ?? 10;
        $offset = $_GET['offset'] ?? 0;

        $orden = new Orden($this->db);
        $stmt = $orden->obtenerPaginado($limit, $offset);
        $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total = $orden->contarTodas();

        echo json_encode([
            "ordenes" => $ordenes,
            "total" => $total
        ]);

    }

    public function show($id) { // Obtener las ordenes por ID
        $orden = new Orden($this->db);
        $resultado = $orden->obtenerPorId($id);

        if ($resultado) {
            echo json_encode($resultado);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Orden no encontrada"]);
        }
   }


    // POST /ordenes
    public function store() { // Crear ordenes de manera individual
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->validar($data)) {
            $orden = new Orden($this->db);
            $orden->cliente =        $data['cliente'];
            $orden->descripcion =    $data['descripcion'];
            $orden->estado =         $data['estado'];
            $orden->prioridad =      $data['prioridad'];
            $orden->fecha_estimada = $data['fecha_estimada'];

            // Verificar duplicado antes de guardar
            if ($orden->existeDuplicado($orden->cliente, $orden->fecha_estimada)) {
                http_response_code(409);
                echo json_encode(["error" => "Ya existe una orden con este cliente y fecha"]);
                return;
            }

            if ($orden->crear()) { 
                http_response_code(201);
                echo json_encode(["mensaje" => "Orden creada correctamente"]);
            } else { 
                http_response_code(500);
                echo json_encode(["error" => "No se pudo crear la orden"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Datos inválidos"]);
        }
    }

    // PUT /ordenes/{id}
    public function update($id) { // Basicamente actualizar ordenes
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->validar($data)) {
            $orden = new Orden($this->db);
            $orden->cliente =        $data['cliente'];
            $orden->descripcion =    $data['descripcion'];
            $orden->estado =         $data['estado'];
            $orden->prioridad =      $data['prioridad'];
            $orden->fecha_estimada = $data['fecha_estimada'];

            // Validar duplicado (ignorando su propio ID)
            if ($orden->existeDuplicado($orden->cliente, $orden->fecha_estimada, $id)) {
                http_response_code(409);
                echo json_encode(["error" => "Ya existe una orden con este cliente y fecha"]);
                return;
            }

            if ($orden->actualizar($id)) {
                echo json_encode(["mensaje" => "Orden actualizada"]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "No se pudo actualizar"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Datos inválidos"]);
        }
    }

    // DELETE /ordenes/{id}
    public function destroy($id) { // Eliminar ordenes sin mas
        $orden = new Orden($this->db);
        if ($orden->eliminar($id)) {
            echo json_encode(["mensaje" => "Orden eliminada"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo eliminar"]);
        }
    }

    // Validación de datos
    private function validar($data, $excluirId = null) { // Validar los campos requeridos y sus formatos
        $requeridos = ['cliente', 'descripcion', 'estado', 'prioridad', 'fecha_estimada'];

        foreach ($requeridos as $campo) {
            if (empty($data[$campo])) {
            return false;
            }
        }

        $fechaValida = preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_estimada']); // validar formato de fecha YYYY-MM-DD

        // Validar que no sea una fecha pasada
        $fechaHoy = date('Y-m-d');
        $fechaEstimada = $data['fecha_estimada'];

        if (!$fechaValida || $fechaEstimada < $fechaHoy) {
            return false;
        }

        if (!in_array($data['estado'], ['Pendiente', 'En proceso', 'Completado'])) return false;
        if (!in_array($data['prioridad'], ['Baja', 'Media', 'Alta'])) return false;

        // Validar duplicados (cliente + fecha)
        $orden = new Orden($this->db);
        if ($orden->existeDuplicado($data['cliente'], $data['fecha_estimada'], $excluirId)) {
            return false;
        }

        return true;
    }

}