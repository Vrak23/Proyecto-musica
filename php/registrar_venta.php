<?php
// Mostrar errores para debug (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once 'db.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Leer JSON del body
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'JSON inválido.']);
    exit;
}

// ── Sanitizar campos ─────────────────────────────────────────
$dni            = trim($data['dni']            ?? '');
$nombre         = trim($data['nombre']         ?? '');
$apellidos      = trim($data['apellidos']      ?? '');
$telefono       = trim($data['telefono']       ?? '');
$email          = trim($data['email']          ?? '');
$direccion      = trim($data['direccion']      ?? '');
$adelanto       = number_format(floatval($data['adelanto'] ?? 0), 2, '.', '');
$estado         = trim($data['estado']         ?? 'Pendiente');
$id_instrumento = intval($data['id_instrumento'] ?? 0);

// ── Validar obligatorios ─────────────────────────────────────
if (!$dni || !$nombre || !$apellidos || !$telefono || $id_instrumento <= 0) {
    echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
    exit;
}

// ── Verificar que el instrumento existe y tiene stock ────────
$id_esc = intval($id_instrumento);
$result = $conn->query("SELECT STOCK FROM INSTRUMENTOS WHERE ID_INSTRUMENTO = $id_esc");

if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Instrumento no encontrado.']);
    exit;
}

$row = $result->fetch_assoc();
if (intval($row['STOCK']) <= 0) {
    echo json_encode(['success' => false, 'message' => 'Sin stock disponible.']);
    exit;
}

// ── Escapar strings para consultas ───────────────────────────
$dni_e       = $conn->real_escape_string($dni);
$nombre_e    = $conn->real_escape_string($nombre);
$apellidos_e = $conn->real_escape_string($apellidos);
$telefono_e  = $conn->real_escape_string($telefono);
$email_e     = $conn->real_escape_string($email);
$direccion_e = $conn->real_escape_string($direccion);
$estado_e    = $conn->real_escape_string($estado);
$fecha_hoy   = date('Y-m-d');

// ── Insertar cliente ─────────────────────────────────────────
$sql_cliente = "INSERT INTO CLIENTES (DNI, NOMBRE, APELLIDOS, EMAIL, TELEFONO, DIRECCION, FECHA_REGISTRO)
                VALUES ('$dni_e', '$nombre_e', '$apellidos_e', '$email_e', '$telefono_e', '$direccion_e', '$fecha_hoy')";

if (!$conn->query($sql_cliente)) {
    echo json_encode(['success' => false, 'message' => 'Error al registrar cliente: ' . $conn->error]);
    exit;
}

$id_cliente = $conn->insert_id;

// ── Insertar reserva ─────────────────────────────────────────
$sql_reserva = "INSERT INTO RESERVAS (ID_CLIENTE, ID_INSTRUMENTO, FECHA_RESERVA, ADELANTO, ESTADO)
                VALUES ($id_cliente, $id_esc, '$fecha_hoy', $adelanto, '$estado_e')";

if (!$conn->query($sql_reserva)) {
    echo json_encode(['success' => false, 'message' => 'Error al registrar reserva: ' . $conn->error]);
    exit;
}

// ── Descontar stock ──────────────────────────────────────────
$sql_stock = "UPDATE INSTRUMENTOS SET STOCK = STOCK - 1 WHERE ID_INSTRUMENTO = $id_esc";

if (!$conn->query($sql_stock)) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar stock: ' . $conn->error]);
    exit;
}

$conn->close();

echo json_encode([
    'success'    => true,
    'message'    => 'Venta registrada correctamente.',
    'id_cliente' => $id_cliente
]);
?>