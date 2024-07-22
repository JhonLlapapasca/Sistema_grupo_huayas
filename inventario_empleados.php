<?php
session_start();
require_once("bootstrap/bootstrap.php");
require_once("config/connection.php");
$con = connection();

// Manejar edición de empleado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["edit"])) {
    $id = $_POST["id_empleado"];
    $nombres = $_POST["nombres"];
    $apellidos = $_POST["apellidos"];
    $dni = $_POST["dni"];
    $telefono = $_POST["telefono"];
    $fecha_de_ingreso = $_POST["fecha_de_ingreso"];
    $rol = $_POST["rol"];
    $correo = $_POST["correo"];
    $contraseña = $_POST["contraseña"];

    $sql = "UPDATE empleado SET nombres=?, apellidos=?, dni=?, telefono=?, fecha_de_ingreso=?, rol=?, correo=?, contraseña=? WHERE id_empleado=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssssssi", $nombres, $apellidos, $dni, $telefono, $fecha_de_ingreso, $rol, $correo, $contraseña, $id);

    if ($stmt->execute()) {
        header("Location: inventario_empleados.php");
        exit;
    } else {
        echo "Error al actualizar el empleado: " . $con->error;
    }
}

// Manejar eliminación de empleado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $id = $_POST["id_empleado"];

    $sql = "DELETE FROM empleado WHERE id_empleado=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: inventario_empleados.php");
        exit;
    } else {
        echo "Error al eliminar el empleado: " . $con->error;
    }
}

// Consultar los empleados
$sql = "SELECT * FROM empleado";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Empleados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <ul class="nav nav-tabs mt-5">
            <li class="nav-item">
                <a class="nav-link" href="inventario_productos.php">Productos</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" aria-current="page" href="#" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Implementos
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="inventario_tipo_papas.php">Tipo de Papas</a></li>
                    <li><a class="dropdown-item" href="inventario_extras.php">Extras</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="inventario_bebidas.php">Bebidas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="#">Empleados</a>
            </li>
            <!-- <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    Ventas
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Registro</a></li>
                    <li><a class="dropdown-item" href="#">Reporte</a></li>
                </ul>
            </li> -->
            <li class="nav-item">
                <a href="index.php" class="btn btn-primary">Salir</a>
            </li>
        </ul>

        <br>

        <!-- Botón para mostrar modal de agregar empleado -->
        <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Agregar
            Empleado</button>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nombres</th>
                    <th scope="col">Apellidos</th>
                    <th scope="col">DNI</th>
                    <th scope="col">Teléfono</th>
                    <th scope="col">Fecha de Ingreso</th>
                    <th scope="col">Rol</th>
                    <th scope="col">Correo</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["id_empleado"] . "</td>";
                        echo "<td>" . $row["nombres"] . "</td>";
                        echo "<td>" . $row["apellidos"] . "</td>";
                        echo "<td>" . $row["dni"] . "</td>";
                        echo "<td>" . $row["telefono"] . "</td>";
                        echo "<td>" . $row["fecha_de_ingreso"] . "</td>";
                        echo "<td>" . $row["rol"] . "</td>";
                        echo "<td>" . $row["correo"] . "</td>";
                        echo "<td>
                                <button type='button' class='btn btn-primary' data-bs-toggle='modal'
                                    data-bs-target='#editModal' onclick='fillEditForm(" . json_encode($row) . ")'>Editar</button>
                                <form method='post' action='inventario_empleados.php' style='display:inline-block;'>
                                    <input type='hidden' name='id_empleado' value='" . $row["id_empleado"] . "'>
                                    <input type='hidden' name='delete' value='true'>
                                    <button type='submit' class='btn btn-danger'
                                        onclick='return confirm(\"¿Estás seguro de que quieres eliminar este empleado?\")'>Eliminar</button>
                                </form>
                            </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No hay empleados disponibles</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para Agregar Empleado -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addForm" method="post" action="inventario_empleados.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Agregar Empleado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="add" value="true">
                        <div class="mb-3">
                            <label for="addNombres" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="addNombres" name="nombres" required>
                        </div>
                        <div class="mb-3">
                            <label for="addApellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="addApellidos" name="apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label for="addDNI" class="form-label">DNI</label>
                            <input type="text" class="form-control" id="addDNI" name="dni" required>
                        </div>
                        <div class="mb-3">
                            <label for="addTelefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="addTelefono" name="telefono" required>
                        </div>
                        <div class="mb-3">
                            <label for="addFechaIngreso" class="form-label">Fecha de Ingreso</label>
                            <input type="date" class="form-control" id="addFechaIngreso" name="fecha_de_ingreso" required>
                        </div>
                        <div class="mb-3">
                            <label for="addRol" class="form-label">Rol</label>
                            <input type="text" class="form-control" id="addRol" name="rol" required>
                        </div>
                        <div class="mb-3">
                            <label for="addCorreo" class="form-label">Correo</label>
                            <input type="email" class="form-control" id="addCorreo" name="correo" required>
                        </div>
                        <div class="mb-3">
                            <label for="addContraseña" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="addContraseña" name="contraseña" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Empleado -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editForm" method="post" action="inventario_empleados.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Editar Empleado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit" value="true">
                        <input type="hidden" name="id_empleado" id="editIdEmpleado">
                        <div class="mb-3">
                            <label for="editNombres" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="editNombres" name="nombres" required>
                        </div>
                        <div class="mb-3">
                            <label for="editApellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="editApellidos" name="apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDNI" class="form-label">DNI</label>
                            <input type="text" class="form-control" id="editDNI" name="dni" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTelefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="editTelefono" name="telefono" required>
                        </div>
                        <div class="mb-3">
                            <label for="editFechaIngreso" class="form-label">Fecha de Ingreso</label>
                            <input type="date" class="form-control" id="editFechaIngreso" name="fecha_de_ingreso" required>
                        </div>
                        <div class="mb-3">
                            <label for="editRol" class="form-label">Rol</label>
                            <input type="text" class="form-control" id="editRol" name="rol" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCorreo" class="form-label">Correo</label>
                            <input type="email" class="form-control" id="editCorreo" name="correo" required>
                        </div>
                        <div class="mb-3">
                            <label for="editContraseña" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="editContraseña" name="contraseña" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        function fillEditForm(employee) {
            document.getElementById('editIdEmpleado').value = employee.id_empleado;
            document.getElementById('editNombres').value = employee.nombres;
            document.getElementById('editApellidos').value = employee.apellidos;
            document.getElementById('editDNI').value = employee.dni;
            document.getElementById('editTelefono').value = employee.telefono;
            document.getElementById('editFechaIngreso').value = employee.fecha_de_ingreso;
            document.getElementById('editRol').value = employee.rol;
            document.getElementById('editCorreo').value = employee.correo;
            document.getElementById('editContraseña').value = employee.contraseña;
        }
    </script>
</body>

</html>
