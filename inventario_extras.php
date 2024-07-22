<?php
session_start();
require_once("bootstrap/bootstrap.php");
require_once("config/connection.php");
$con = connection();

// Manejar la adición de extras
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $nombre = $_POST["nombre"];
    $precio = $_POST["precio"];
    $stock = $_POST["stock"];

    // Insertar en la tabla 'extras'
    $sql = "INSERT INTO extras (nombre, precio) VALUES (?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sd", $nombre, $precio);
    $stmt->execute();

    // Obtener el ID del último inserto
    $id_extras = $stmt->insert_id;

    // Insertar en la tabla 'almacen_extras'
    $sql = "INSERT INTO almacen_extras (id_extras, stock) VALUES (?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $id_extras, $stock);
    $stmt->execute();

    header("Location: inventario_extras.php");
    exit;
}

// Manejar la actualización de extras
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["edit"])) {
    $id = $_POST["id_extras"];
    $nombre = $_POST["nombre"];
    $precio = $_POST["precio"];
    $stock = $_POST["stock"];

    $sql = "UPDATE extras e
            JOIN almacen_extras ae ON e.id_extras = ae.id_extras
            SET e.nombre = ?, e.precio = ?, ae.stock = ?
            WHERE e.id_extras = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sdsi", $nombre, $precio, $stock, $id);

    if ($stmt->execute()) {
        header("Location: inventario_extras.php");
        exit;
    } else {
        echo "Error al actualizar el extra: " . $con->error;
    }
}

// Manejar eliminación de extras
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $id = $_POST["id_extras"];

    $sql = "DELETE FROM extras WHERE id_extras = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // No es necesario eliminar de 'almacen_extras' debido a la restricción ON DELETE CASCADE
        header("Location: inventario_extras.php");
        exit;
    } else {
        echo "Error al eliminar el extra: " . $con->error;
    }
}

// Consultar los extras y sus stocks
$sql = "SELECT e.id_extras, e.nombre, e.precio, ae.stock 
        FROM extras e 
        JOIN almacen_extras ae ON e.id_extras = ae.id_extras";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Extras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <ul class="nav nav-tabs mt-5">
            <li class="nav-item">
                <a class="nav-link" href="inventario_productos.php">Productos</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle active" aria-current="page" href="#" role="button"
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
                <a class="nav-link" href="inventario_empleados.php">Empleados</a>
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

        <div class="">
            <div class="">
                <!-- Botón para abrir modal de agregar nuevo extra -->
                <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal"
                    data-bs-target="#addModal">Agregar Nuevo Extra</button>
            </div>
            <div class="">
                <!-- Tabla de extras existentes -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">Id</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Precio</th>
                            <th scope="col">Stock</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<th scope='row'>" . $row["id_extras"] . "</th>";
                                echo "<td>" . $row["nombre"] . "</td>";
                                echo "<td>" . $row["precio"] . "</td>";
                                echo "<td>" . $row["stock"] . "</td>";
                                echo "<td>
                                        <button type='button' class='btn btn-primary' data-bs-toggle='modal'
                                                data-bs-target='#editModal' onclick='fillEditForm(" . json_encode($row) . ")'>Editar</button>
                                        <form method='post' action='inventario_extras.php' style='display:inline-block;'>
                                            <input type='hidden' name='id_extras' value='" . $row["id_extras"] . "'>
                                            <input type='hidden' name='delete' value='true'>
                                            <button type='submit' class='btn btn-danger' onclick='return confirm(\"¿Estás seguro de que quieres eliminar este extra?\")'>Eliminar</button>
                                        </form>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No hay extras disponibles</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Agregar Nuevo Extra -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="inventario_extras.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Agregar Nuevo Extra</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio</label>
                            <input type="number" step="0.01" class="form-control" id="precio" name="precio" required>
                        </div>
                        <div class="mb-3">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <input type="hidden" name="add" value="true">
                        <button type="submit" class="btn btn-success">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Extra -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editForm" method="post" action="inventario_extras.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Editar Extra</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id_extras">
                        <input type="hidden" name="edit" value="true">
                        <div class="mb-3">
                            <label for="editNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="editNombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPrecio" class="form-label">Precio</label>
                            <input type="number" step="0.01" class="form-control" id="editPrecio" name="precio"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="editStock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="editStock" name="stock" required>
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

    <script>
        function fillEditForm(extra) {
            document.getElementById('editId').value = extra.id_extras;
            document.getElementById('editNombre').value = extra.nombre;
            document.getElementById('editPrecio').value = extra.precio;
            document.getElementById('editStock').value = extra.stock;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

