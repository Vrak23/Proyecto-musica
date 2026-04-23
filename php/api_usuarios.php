<?php
header('Content-Type: application/json');
require_once 'db.php';
require_once 'session.php';

// Solo usuarios autenticados pueden consultar esta API
checkSession();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['me'])) {
            $id = $_SESSION['user_id'];
            $stmt = $conn->prepare("SELECT ID, USERNAME, ESTADO FROM USUARIO WHERE ID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            echo json_encode($stmt->get_result()->fetch_assoc());
        } else {
            $sql = "SELECT ID, USERNAME, ESTADO FROM USUARIO";
            $result = $conn->query($sql);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode($data);
        }
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_SESSION['user_id'];
        $new_name = $input['USERNAME'];
        
        $stmt = $conn->prepare("UPDATE USUARIO SET USERNAME=? WHERE ID=?");
        $stmt->bind_param("si", $new_name, $id);
        
        if ($stmt->execute()) {
            $_SESSION['username'] = $new_name;
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        break;
}

$conn->close();
?>
