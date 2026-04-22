<?php
header('Content-Type: application/json');
header("Cache-Control: no-cache, must-revalidate");
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Consulta para mostrar en el Dashboard
        $sql = "SELECT R.*, C.NOMBRE as CLIENTE, I.NOMBRE as INSTRUMENTO 
                FROM RESERVAS R
                LEFT JOIN CLIENTES C ON R.ID_CLIENTE = C.ID_CLIENTE
                LEFT JOIN INSTRUMENTOS I ON R.ID_INSTRUMENTO = I.ID_INSTRUMENTO
                ORDER BY R.ID_RESERVA DESC";
        
        $result = $conn->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode($data);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(["status" => "error", "message" => "No se recibieron datos"]);
            exit;
        }

        // 1. OBTENER/CREAR CLIENTE (Basado en tu esquema de HeidiSQL)
        $dni = $conn->real_escape_string($input['dni']);
        $nom = $conn->real_escape_string($input['nombre']);
        $ape = $conn->real_escape_string($input['apellidos']);
        $tel = $conn->real_escape_string($input['telefono']);
        $fecha_hoy = date('Y-m-d');

        // Insertamos cliente o recuperamos ID si ya existe por DNI
        $sql_c = "INSERT INTO CLIENTES (DNI, NOMBRE, APELLIDOS, TELEFONO, FECHA_REGISTRO) 
                  VALUES ('$dni', '$nom', '$ape', '$tel', '$fecha_hoy')
                  ON DUPLICATE KEY UPDATE ID_CLIENTE=LAST_INSERT_ID(ID_CLIENTE)";
        
        if (!$conn->query($sql_c)) {
            echo json_encode(["status" => "error", "message" => "Error Cliente: " . $conn->error]);
            exit;
        }
        $id_cliente = $conn->insert_id;

        // 2. INSERTAR RESERVA
        $id_inst = intval($input['id_instrumento']);
        $adelanto = floatval($input['adelanto'] ?? 0);
        $estado = $input['estado'] ?? 'Pendiente';

        $stmt = $conn->prepare("INSERT INTO RESERVAS (ID_CLIENTE, ID_INSTRUMENTO, FECHA_RESERVA, ADELANTO, ESTADO) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisds", $id_cliente, $id_inst, $fecha_hoy, $adelanto, $estado);
        
        if ($stmt->execute()) {
            // 3. DESCONTAR STOCK
            $conn->query("UPDATE INSTRUMENTOS SET STOCK = STOCK - 1 WHERE ID_INSTRUMENTO = $id_inst");
            echo json_encode(["status" => "success", "message" => "Venta guardada en HeidiSQL"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error Reserva: " . $stmt->error]);
        }
        break;
}

$conn->close();
?>