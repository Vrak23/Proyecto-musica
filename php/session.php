<?php
session_start();

function checkSession() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.html");
        exit();
    }
}
?>
