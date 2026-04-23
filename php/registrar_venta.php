<?php
header('Content-Type: application/json');
require_once 'db.php';

// Leer JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

// Variables según tu esquema de Heidi
$dni       = $conn->real_escape_string($data['dni'] ?? '');
$nombre    = $conn->real_escape_string($data['nombre'] ?? '');
$apellidos = $conn->real_escape_string($data['apellidos'] ?? '');
$email     = $conn->real_escape_string($data['email'] ?? '');
$telefono  = $conn->real_escape_string($data['telefono'] ?? '');
$direccion = $conn->real_escape_string($data['direccion'] ?? '');
$id_inst   = intval($data['id_instrumento'] ?? 0);
$adelanto  = floatval($data['adelanto'] ?? 0);
$fecha_hoy = date('Y-m-d');

// 1. Manejar Cliente (Evitar error si el DNI ya existe)
$sql_cliente = "INSERT INTO CLIENTES (DNI, NOMBRE, APELLIDOS, EMAIL, TELEFONO, DIRECCION, FECHA_REGISTRO)
                VALUES ('$dni', '$nombre', '$apellidos', '$email', '$telefono', '$direccion', '$fecha_hoy')
                ON DUPLICATE KEY UPDATE ID_CLIENTE=LAST_INSERT_ID(ID_CLIENTE)";

if (!$conn->query($sql_cliente)) {
    echo json_encode(['success' => false, 'message' => 'Error Cliente: ' . $conn->error]);
    exit;
}
$id_cliente = $conn->insert_id;

// 2. Insertar en RESERVAS (Nombres idénticos a tu SQL)
$sql_reserva = "INSERT INTO RESERVAS (ID_CLIENTE, ID_INSTRUMENTO, FECHA_RESERVA, ADELANTO, ESTADO)
                VALUES ($id_cliente, $id_inst, '$fecha_hoy', $adelanto, 'Pendiente')";

if ($conn->query($sql_reserva)) {
    // 3. Descontar Stock
    $conn->query("UPDATE INSTRUMENTOS SET STOCK = STOCK - 1 WHERE ID_INSTRUMENTO = $id_inst");
    echo json_encode(['success' => true, 'message' => 'Guardado con éxito']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error Reserva: ' . $conn->error]);
}

$conn->close();