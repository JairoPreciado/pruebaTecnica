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

            echo json_encode([
                "ordenes" => $ordenes,
                "total" => $total
            ]);
        } catch (Exception $e) {
            registrarLog("EXCEPTION", "index(): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Error interno al obtener órdenes"]);
        }
    }

    public function show($id) { // Obtener las ordenes por ID
        try {
            $orden = new Orden($this->db);
            $resultado = $orden->obtenerPorId($id);

            if ($resultado) {
                echo json_encode($resultado);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Orden no encontrada"]);
            }
        } catch (Exception $e) {
            registrarLog("EXCEPTION", "show({$id}): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Error interno al obtener la orden"]);
        }
   }

    // POST /ordenes
    public function store() { // Crear ordenes de manera individual
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            // Normalizar fecha_estimada a YYYY-MM-DD
            if (isset($data['fecha_estimada'])) {
                $data['fecha_estimada'] = substr($data['fecha_estimada'], 0, 10);
            }

            if ($this->validar($data)) {
                $orden = new Orden($this->db);
                $orden->cliente =        $data['cliente'];
                $orden->descripcion =    $data['descripcion'];
                $orden->estado =         $data['estado'];
                $orden->prioridad =      $data['prioridad'];
                $orden->fecha_estimada = $data['fecha_estimada'];

                // Verificar duplicado antes de guardar
                if ($orden->existeDuplicado($orden->cliente, $orden->fecha_estimada)) {
                    registrarLog("ERROR", "Intento duplicado de orden ({$orden->cliente}, {$orden->fecha_estimada})");
                    http_response_code(409);
                    echo json_encode(["error" => "Ya existe una orden con este cliente y fecha"]);
                    return;
                }

                if ($orden->crear()) { 
                    registrarLog("INFO", "Orden creada para cliente: {$orden->cliente}");
                    http_response_code(201);
                    echo json_encode(["mensaje" => "Orden creada correctamente"]);
                } else { 
                    registrarLog("ERROR", "Error al crear orden para cliente: {$orden->cliente}");
                    http_response_code(500);
                    echo json_encode(["error" => "No se pudo crear la orden"]);
                }
            } else {
                registrarLog("ERROR", "Datos inválidos al crear orden");
                http_response_code(400);
                echo json_encode(["error" => "Datos inválidos"]);
            }
        } catch (Exception $e) {
            registrarLog("EXCEPTION", "store(): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Error interno"]);
        }
    }

    // PUT /ordenes/{id}
    public function update($id) { // Basicamente actualizar ordenes
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Normalizar fecha_estimada a YYYY-MM-DD
            if (isset($data['fecha_estimada'])) {
                $data['fecha_estimada'] = substr($data['fecha_estimada'], 0, 10);
            }

            if ($this->validar($data, $id)) {
                $orden = new Orden($this->db);
                $orden->cliente =        $data['cliente'];
                $orden->descripcion =    $data['descripcion'];
                $orden->estado =         $data['estado'];
                $orden->prioridad =      $data['prioridad'];
                $orden->fecha_estimada = $data['fecha_estimada'];

                // Validar duplicado (ignorando su propio ID)
                if ($orden->existeDuplicado($orden->cliente, $orden->fecha_estimada, $id)) {
                    registrarLog("ERROR", "Intento de duplicado al actualizar orden ID $id ({$orden->cliente}, {$orden->fecha_estimada})");
                    http_response_code(409);
                    echo json_encode(["error" => "Ya existe una orden con este cliente y fecha"]);
                    return;
                }

                if ($orden->actualizar($id)) {
                    registrarLog("INFO", "Orden ID $id actualizada correctamente");
                    echo json_encode(["mensaje" => "Orden actualizada"]);
                } else {
                    registrarLog("ERROR", "Fallo al actualizar orden ID $id");
                    http_response_code(500);
                    echo json_encode(["error" => "No se pudo actualizar"]);
                }
            } else {
                registrarLog("ERROR", "Datos inválidos al actualizar orden ID $id");
                http_response_code(400);
                echo json_encode(["error" => "Datos inválidos"]);
            }
        } catch (Exception $e) {
            registrarLog("EXCEPTION", "update(): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Error interno"]);
        }
    }

   // DELETE /ordenes/{id}
    public function destroy($id) { // Eliminar ordenes unicamente
        try {
            $orden = new Orden($this->db);

            if ($orden->eliminar($id)) {
                registrarLog("INFO", "Orden ID $id eliminada correctamente");
                echo json_encode(["mensaje" => "Orden eliminada"]);
            } else {
                registrarLog("ERROR", "Fallo al eliminar la orden ID $id");
                http_response_code(500);
                echo json_encode(["error" => "No se pudo eliminar"]);
            }
        } catch (Exception $e) {
            registrarLog("EXCEPTION", "destroy(): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Error interno al eliminar la orden"]);
        }
    }

    // Validación de datos
    private function validar($data, $excluirId = null) {
        try {
            $requeridos = ['cliente', 'descripcion', 'estado', 'prioridad', 'fecha_estimada'];

            foreach ($requeridos as $campo) {
                if (empty($data[$campo])) {
                    registrarLog("VALIDACION", "Campo requerido vacío: $campo");
                    return false;
                }
            }

            // Validar formato de fecha YYYY-MM-DD
            $fechaValida = preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_estimada']);
            if (!$fechaValida) {
                registrarLog("VALIDACION", "Formato de fecha inválido: " . $data['fecha_estimada']);
                return false;
            }

            // Validar que no sea una fecha pasada
            $fechaHoy = date('Y-m-d');
            $fechaEstimada = $data['fecha_estimada'];
            if ($fechaEstimada < $fechaHoy) {
                registrarLog("VALIDACION", "Fecha estimada en el pasado: $fechaEstimada");
                return false;
            }

            // Validar estado
            if (!in_array($data['estado'], ['Pendiente', 'En proceso', 'Completado'])) {
                registrarLog("VALIDACION", "Estado inválido: " . $data['estado']);
                return false;
            }
            // Validar prioridad
            if (!in_array($data['prioridad'], ['Baja', 'Media', 'Alta'])) {
                registrarLog("VALIDACION", "Prioridad inválida: " . $data['prioridad']);
                return false;
            }

            // Validar duplicados (cliente + fecha)
            $orden = new Orden($this->db);
            if ($orden->existeDuplicado($data['cliente'], $data['fecha_estimada'], $excluirId)) {
                registrarLog("VALIDACION", "Orden duplicada: Cliente=" . $data['cliente'] . " Fecha=" . $data['fecha_estimada']);
                return false;
            }

            return true;

        } catch (Exception $e) {
            registrarLog("EXCEPTION", "validar(): " . $e->getMessage());
            return false;
        }
    }

}
