<?php
require_once __DIR__ . '/../models/Orden.php';

function registrarLog($tipo, $mensaje) {
    $fecha = date('Y-m-d H:i:s');
    $entrada = "[{$fecha}] {$tipo}: {$mensaje}" . PHP_EOL;
    file_put_contents(__DIR__ . '/../logs/general.log', $entrada, FILE_APPEND);
}

class OrdenController {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    // GET /ordenes
    public function index() {   // Obtener todas las órdenes
        try {
            $limit = $_GET['limit'] ?? 10;
            $offset = $_GET['offset'] ?? 0;

            $orden = new Orden($this->db);
            $stmt = $orden->obtenerPaginado($limit, $offset);
            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total = $orden->contarTodas();

            header('Content-Type: application/json');
            echo json_encode([
                "ordenes" => $ordenes,
                "total" => $total
            ]);
        } catch (Exception $e) {
            registrarLog("EXCEPTION", "index(): " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Error interno al obtener órdenes"]);
        }
    }

    // GET /ordenes/{id}
    public function show($id) { // Obtener las ordenes por ID
        try {
            $orden = new Orden($this->db);
            $resultado = $orden->obtenerPorId($id);

            header('Content-Type: application/json');
            if ($resultado) {
                echo json_encode($resultado);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Orden no encontrada"]);
            }
        } catch (Exception $e) {
            registrarLog("EXCEPTION", "show({$id}): " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Error interno al obtener la orden"]);
        }
    }

     // POST /ordenes
    public function store() { // Crear ordenes de manera individual
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (isset($data['fecha_estimada'])) {
                $data['fecha_estimada'] = substr($data['fecha_estimada'], 0, 10);
            }

            $errores = $this->validar($data);
            if (!empty($errores)) {
                registrarLog("VALIDACION", implode("; ", $errores));
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(["error" => $errores]);
                return;
            }

            $orden = new Orden($this->db);
            $this->asignarDatosOrden($orden, $data);

            if ($orden->existeDuplicado($orden->cliente, $orden->fecha_estimada)) {
                registrarLog("ERROR", "Intento duplicado de orden ({$orden->cliente}, {$orden->fecha_estimada})");
                http_response_code(409);
                header('Content-Type: application/json');
                echo json_encode(["error" => "Ya existe una orden con este cliente y fecha"]);
                return;
            }

            if ($orden->crear()) {
                registrarLog("INFO", "Orden creada para cliente: {$orden->cliente}");
                http_response_code(201);
                header('Content-Type: application/json');
                echo json_encode(["mensaje" => "Orden creada correctamente"]);
            } else {
                registrarLog("ERROR", "Error al crear orden para cliente: {$orden->cliente}");
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(["error" => "No se pudo crear la orden"]);
            }
        } catch (Exception $e) {
            registrarLog("EXCEPTION", "store(): " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Error interno"]);
        }
    }

    // PUT /ordenes/{id}
    public function update($id) {  // Basicamente actualizar ordenes
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (isset($data['fecha_estimada'])) {
                $data['fecha_estimada'] = substr($data['fecha_estimada'], 0, 10);
            }

            $errores = $this->validar($data, $id);
            if (!empty($errores)) {
                registrarLog("VALIDACION", implode("; ", $errores));
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(["error" => $errores]);
                return;
            }

            $orden = new Orden($this->db);
            $this->asignarDatosOrden($orden, $data);

            if ($orden->existeDuplicado($orden->cliente, $orden->fecha_estimada, $id)) {
                registrarLog("ERROR", "Intento de duplicado al actualizar orden ID $id ({$orden->cliente}, {$orden->fecha_estimada})");
                http_response_code(409);
                header('Content-Type: application/json');
                echo json_encode(["error" => "Ya existe una orden con este cliente y fecha"]);
                return;
            }

            if ($orden->actualizar($id)) {
                registrarLog("INFO", "Orden ID $id actualizada correctamente");
                header('Content-Type: application/json');
                echo json_encode(["mensaje" => "Orden actualizada"]);
            } else {
                registrarLog("ERROR", "Fallo al actualizar orden ID $id");
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(["error" => "No se pudo actualizar"]);
            }
        } catch (Exception $e) {
            registrarLog("EXCEPTION", "update(): " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Error interno"]);
        }
    }

    // DELETE /ordenes/{id}
    public function destroy($id) { // Eliminar ordenes por id
        try {
            $orden = new Orden($this->db);

            if ($orden->eliminar($id)) {
                registrarLog("INFO", "Orden ID $id eliminada correctamente");
                header('Content-Type: application/json');
                echo json_encode(["mensaje" => "Orden eliminada"]);
            } else {
                registrarLog("ERROR", "Fallo al eliminar la orden ID $id");
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(["error" => "No se pudo eliminar"]);
            }
        } catch (Exception $e) {
            registrarLog("EXCEPTION", "destroy(): " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Error interno al eliminar la orden"]);
        }
    }
    
    // Validación de datos de la orden
    private function validar($data, $excluirId = null) {
        $errores = [];

        // Campos requeridos
        $requeridos = ['cliente', 'descripcion', 'estado', 'prioridad', 'fecha_estimada'];
        foreach ($requeridos as $campo) {
            if (!isset($data[$campo]) || trim($data[$campo]) === '') {
                $errores[] = "Campo requerido vacío: $campo";
            }
        }

        // Validacion del formato de la fecha
        if (!empty($data['fecha_estimada']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_estimada'])) {
            $errores[] = "Formato de fecha inválido";
        }

        //Validacion de fecha en cuanto a que no sea pasada
        if (!empty($data['fecha_estimada']) && $data['fecha_estimada'] < date('Y-m-d')) {
            $errores[] = "Fecha estimada no puede ser pasada";
        }

        // Validación de estado
        if (!empty($data['estado']) && !in_array($data['estado'], ['Pendiente', 'En proceso', 'Completado'])) {
            $errores[] = "Estado inválido";
        }

        // Validación de prioridad
        if (!empty($data['prioridad']) && !in_array($data['prioridad'], ['Baja', 'Media', 'Alta'])) {
            $errores[] = "Prioridad inválida";
        }

        return $errores;
    }

    // Para evitar la redundancia de código, se asignan los datos de la orden
    private function asignarDatosOrden($orden, $data) {
        $orden->cliente = $data['cliente'];
        $orden->descripcion = $data['descripcion'];
        $orden->estado = $data['estado'];
        $orden->prioridad = $data['prioridad'];
        $orden->fecha_estimada = $data['fecha_estimada'];
    }
}
