<?php
// Habilitar CORS (para que Angular pueda acceder al backend)
header("Access-Control-Allow-Origin: *"); // Basicamente usando el pipe "*" hace que el backend permita todas las peticiones de cualquier origen
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

// Manejar OPTIONS (pre-flight para CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Cargar dependencias
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/OrdenController.php';

// Conectar a la base de datos
$database = new Database();
$db = $database->getConnection();

// Instanciar el controlador
$ordenController = new OrdenController($db);

// Obtener la URI y método HTTP
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));
$method = $_SERVER['REQUEST_METHOD'];

// Ruta base (ej. /ordenes o /ordenes/{id})
$recurso = $uri[0] ?? null;
$id = $uri[1] ?? null;

// Enrutamiento
if ($recurso === 'ordenes') {
    switch ($method) {
        case 'GET':
            if ($id) {
                $ordenController->show($id);    //obtner ordenes por ID
            } else {
                $ordenController->index();      // obtener todas las órdenes
            }
            break;
        case 'POST':
            $ordenController->store();          // Crear ordenes de manera individual
            break;
        case 'PUT':
            if ($id) {
                $ordenController->update($id);  // Actualizar ordenes por ID
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Falta el ID para actualizar"]);
            }
            break;
        case 'DELETE':
            if ($id) {
                $ordenController->destroy($id); // Eliminar ordenes por ID
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Falta el ID para eliminar"]);
            }
            break;
        default:
            http_response_code(405); // Método no permitido
            echo json_encode(["error" => "Método no permitido"]);
            break;
    }
} else {
    http_response_code(404);
    echo json_encode(["error" => "Ruta no encontrada"]);
}
