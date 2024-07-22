<?php
require_once ("./bootstrap/bootstrap.php");
require_once ("./config/connection.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <style>
        body {
            background-image: url('img/fondo_index.png'); /* Reemplaza con la ruta a tu imagen */
            background-size: cover;
            background-position: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            height: 100%;
        }
        .position-absolute {
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="position-absolute">
            <div class="card" style="width: 25rem;">
                <div class="card-body">
                    <h5 class="card-title">
                        Iniciar Sesión
                    </h5>
                    <form method="post" action="controller/loginController.php">
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo: </label>
                            <input type="email" class="form-control" id="correo" name="correo" required>
                        </div>
                        <div class="mb-3">
                            <label for="contraseña" class="form-label">Contraseña: </label>
                            <input type="password" class="form-control" id="contraseña" name="contraseña" required>
                        </div>
                        
                        <select class="form-select" aria-label="Default select example">
                            <option selected>Seleccione su Rol</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Empleado">Empleado</option>
                        </select>

                        <input type="submit" class="btn btn-primary mt-5" value="Iniciar Sesión">
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
