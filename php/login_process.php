<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/php/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Usuario y contraseña requeridos"]);
        exit();
    }

    $stmt = $conn->prepare("SELECT ID, PASSWORD_HASH FROM USUARIO WHERE USERNAME = ? AND ESTADO = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['PASSWORD_HASH'])) {
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['username'] = $username;
            echo json_encode(["status" => "success", "message" => "Acceso concedido"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Contraseña incorrecta"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
}
?>