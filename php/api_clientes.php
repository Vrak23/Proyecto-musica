<?php
header('Content-Type: application/json');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sql = "SELECT * FROM CLIENTES";
        $result = $conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $dni = $input['DNI'];
        $nombre = $input['NOMBRE'];
        $apellidos = $input['APELLIDOS'];
        $email = $input['EMAIL'];
        $telefono = $input['TELEFONO'];
        $direccion = $input['DIRECCION'];
        $fecha = date('Y-m-d');

        $stmt = $conn->prepare("INSERT INTO CLIENTES (DNI, NOMBRE, APELLIDOS, EMAIL, TELEFONO, DIRECCION, FECHA_REGISTRO) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $dni, $nombre, $apellidos, $email, $telefono, $direccion, $fecha);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "id" => $stmt->insert_id]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $stmt = $conn->prepare("DELETE FROM CLIENTES WHERE ID_CLIENTE=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        break;
}

$conn->close();
?>
