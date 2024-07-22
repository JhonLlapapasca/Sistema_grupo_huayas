<?php
session_start();

require_once("../config/connection.php");
$con = connection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST["correo"];
    $contraseña = $_POST["contraseña"];

    // Consultar el rol del usuario
    $sql = "SELECT rol FROM empleado WHERE correo = ? AND contraseña = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ss", $correo, $contraseña);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $rol = $row['rol'];

        $_SESSION["logged_in"] = true;
        unset($_SESSION['selected_products']);
        unset($_SESSION['total_price']);

        // Redirigir según el rol del usuario
        if ($rol == 'Administrador') {
            header("Location: ../inventario_productos.php");
        } else if ($rol == 'Empleado') {
            header("Location: ../productos.php");
        }
        exit;
    } else {
        header("Location: ../index.php");
        exit;
    }
}
?>
