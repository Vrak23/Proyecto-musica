<?php
header('Content-Type: application/json');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sql = "SELECT R.*, C.NOMBRE as CLIENTE, I.NOMBRE as INSTRUMENTO 
                FROM RESERVAS R
                JOIN CLIENTES C ON R.ID_CLIENTE = C.ID_CLIENTE
                JOIN INSTRUMENTOS I ON R.ID_INSTRUMENTO = I.ID_INSTRUMENTO";
        $result = $conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $id_cliente = $input['ID_CLIENTE'];
        $id_instrumento = $input['ID_INSTRUMENTO'];
        $fecha = $input['FECHA_RESERVA'];
        $adelanto = $input['ADELANTO'];
        $estado = $input['ESTADO'] ?? 'Pendiente';

        $stmt = $conn->prepare("INSERT INTO RESERVAS (ID_CLIENTE, ID_INSTRUMENTO, FECHA_RESERVA, ADELANTO, ESTADO) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisds", $id_cliente, $id_instrumento, $fecha, $adelanto, $estado);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "id" => $stmt->insert_id]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        break;
}

$conn->close();
?>
