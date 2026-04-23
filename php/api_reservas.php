<?php
header('Content-Type: application/json');
header("Cache-Control: no-cache, must-revalidate");
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
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

        $dni = $conn->real_escape_string($input['dni']);
        $nom = $conn->real_escape_string($input['nombre']);
        $ape = $conn->real_escape_string($input['apellidos']);
        $tel = $conn->real_escape_string($input['telefono']);
        $fecha_hoy = date('Y-m-d');

        $sql_c = "INSERT INTO CLIENTES (DNI, NOMBRE, APELLIDOS, TELEFONO, FECHA_REGISTRO) 
                  VALUES ('$dni', '$nom', '$ape', '$tel', '$fecha_hoy')
                  ON DUPLICATE KEY UPDATE ID_CLIENTE=LAST_INSERT_ID(ID_CLIENTE)";

        if (!$conn->query($sql_c)) {
            echo json_encode(["status" => "error", "message" => "Error Cliente: " . $conn->error]);
            exit;
        }
        $id_cliente = $conn->insert_id;

        $id_inst = intval($input['id_instrumento']);
        $adelanto = floatval($input['adelanto'] ?? 0);
        $estado = $input['estado'] ?? 'Pendiente';

        $stmt = $conn->prepare("INSERT INTO RESERVAS (ID_CLIENTE, ID_INSTRUMENTO, FECHA_RESERVA, ADELANTO, ESTADO) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisds", $id_cliente, $id_inst, $fecha_hoy, $adelanto, $estado);

        if ($stmt->execute()) {
            $conn->query("UPDATE INSTRUMENTOS SET STOCK = STOCK - 1 WHERE ID_INSTRUMENTO = $id_inst");
            echo json_encode(["status" => "success", "message" => "Reserva creada correctamente"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error Reserva: " . $stmt->error]);
        }
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(["status" => "error", "message" => "No se recibieron datos"]);
            exit;
        }

        $id = intval($input['ID_RESERVA']);
        $adelanto = floatval($input['ADELANTO'] ?? 0);
        $estado = $input['ESTADO'] ?? 'Pendiente';

        $stmt = $conn->prepare("UPDATE RESERVAS SET ADELANTO=?, ESTADO=? WHERE ID_RESERVA=?");
        $stmt->bind_param("dsi", $adelanto, $estado, $id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        break;

    case 'DELETE':
        $id = intval($_GET['id']);

        // Recuperar el instrumento para restaurar stock
        $res = $conn->query("SELECT ID_INSTRUMENTO FROM RESERVAS WHERE ID_RESERVA = $id");
        if ($row = $res->fetch_assoc()) {
            $id_inst = $row['ID_INSTRUMENTO'];
        }

        $stmt = $conn->prepare("DELETE FROM RESERVAS WHERE ID_RESERVA=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Restaurar stock al eliminar la reserva
            if (isset($id_inst)) {
                $conn->query("UPDATE INSTRUMENTOS SET STOCK = STOCK + 1 WHERE ID_INSTRUMENTO = $id_inst");
            }
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        break;
}

$conn->close();
?>