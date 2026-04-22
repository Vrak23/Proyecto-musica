<?php
header('Content-Type: application/json');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sql = "SELECT * FROM INSTRUMENTOS";
        $result = $conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $nombre = $input['NOMBRE'];
        $marca = $input['MARCA'];
        $modelo = $input['MODELO'];
        $categoria = $input['CATEGORIA'];
        $material = $input['MATERIAL'];
        $precio = $input['PRECIO'];
        $stock = $input['STOCK'];
        $fecha = $input['FECHA_INGRESO'];

        $stmt = $conn->prepare("INSERT INTO INSTRUMENTOS (NOMBRE, MARCA, MODELO, CATEGORIA, MATERIAL, PRECIO, STOCK, FECHA_INGRESO) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssdis", $nombre, $marca, $modelo, $categoria, $material, $precio, $stock, $fecha);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "id" => $stmt->insert_id]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['ID_INSTRUMENTO'];
        $nombre = $input['NOMBRE'];
        $marca = $input['MARCA'];
        $modelo = $input['MODELO'];
        $categoria = $input['CATEGORIA'];
        $material = $input['MATERIAL'];
        $precio = $input['PRECIO'];
        $stock = $input['STOCK'];
        $fecha = $input['FECHA_INGRESO'];

        $stmt = $conn->prepare("UPDATE INSTRUMENTOS SET NOMBRE=?, MARCA=?, MODELO=?, CATEGORIA=?, MATERIAL=?, PRECIO=?, STOCK=?, FECHA_INGRESO=? WHERE ID_INSTRUMENTO=?");
        $stmt->bind_param("sssssdisi", $nombre, $marca, $modelo, $categoria, $material, $precio, $stock, $fecha, $id);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $stmt = $conn->prepare("DELETE FROM INSTRUMENTOS WHERE ID_INSTRUMENTO=?");
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
