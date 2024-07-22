<?php
session_start();
require_once("config/connection.php");
$con = connection();

// Manejar adición de tipo de papas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $nombre = $_POST["nombre"];
    $precio = $_POST["precio"];
    $stock = $_POST["stock"];

    // Insertar nuevo tipo de papas en la tabla tipo_papas
    $sql = "INSERT INTO tipo_papas (nombre, precio) VALUES (?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sd", $nombre, $precio);
    $stmt->execute();

    // Obtener el ID del nuevo tipo de papas insertado
    $tipo_papas_id = $stmt->insert_id;

    // Insertar stock inicial en la tabla almacen_tipo_papas
    $sql = "INSERT INTO almacen_tipo_papas (id_tipo_papas, stock) VALUES (?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $tipo_papas_id, $stock);
    $stmt->execute();

    header("Location: inventario_tipo_papas.php");
    exit;
}

// Manejar edición de tipo de papas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["edit"])) {
    $id = $_POST["id_tipo_papas"];
    $nombre = $_POST["nombre"];
    $precio = $_POST["precio"];
    $stock = $_POST["stock"];

    $sql = "UPDATE tipo_papas tp
            JOIN almacen_tipo_papas atp ON tp.id_tipo_papas = atp.id_tipo_papas
            SET tp.nombre = ?, tp.precio = ?, atp.stock = ?
            WHERE tp.id_tipo_papas = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sddi", $nombre, $precio, $stock, $id);

    if ($stmt->execute()) {
        header("Location: inventario_tipo_papas.php");
        exit;
    } else {
        echo "Error al actualizar el tipo de papas: " . $con->error;
    }
}

// Manejar eliminación de tipo de papas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $id = $_POST["id_tipo_papas"];

    $sql = "DELETE FROM tipo_papas WHERE id_tipo_papas = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: inventario_tipo_papas.php");
        exit;
    } else {
        echo "Error al eliminar el tipo de papas: " . $con->error;
    }
}

// Consultar los tipos de papas y sus stocks
$sql = "SELECT tp.id_tipo_papas, tp.nombre, tp.precio, atp.stock 
        FROM tipo_papas tp 
        JOIN almacen_tipo_papas atp ON tp.id_tipo_papas = atp.id_tipo_papas";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Tipos de Papas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <ul class="nav nav-tabs mt-5">
            <li class="nav-item">
                <a class="nav-link" href="inventario_productos.php">Productos</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle active" aria-current="page" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                <a class="nav-link" href="#">Empleados</a>
            </li>
            <!-- <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

        <!-- Botón para Agregar Tipo de Papas -->
        <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
            Agregar Tipo de Papas
        </button>

        <!-- Tabla de Tipos de Papas -->
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
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<th scope='row'>" . $row["id_tipo_papas"] . "</th>";
                        echo "<td>" . $row["nombre"] . "</td>";
                        echo "<td>" . $row["precio"] . "</td>";
                        echo "<td>" . $row["stock"] . "</td>";
                        echo "<td>
                                <button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#editModal'
                                        onclick='fillEditForm(" . json_encode($row) . ")'>Editar</button>
                                <form method='post' action='inventario_tipo_papas.php' style='display:inline-block;'>
                                    <input type='hidden' name='id_tipo_papas' value='" . $row["id_tipo_papas"] . "'>
                                    <input type='hidden' name='delete' value='true'>
                                    <button type='submit' class='btn btn-danger' onclick='return confirm(\"¿Estás seguro de que quieres eliminar este tipo de papas?\")'>Eliminar</button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No hay tipos de papas disponibles</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para Agregar Tipo de Papas -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addForm" method="post" action="inventario_tipo_papas.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Agregar Nuevo Tipo de Papas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="addNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="addNombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="addPrecio" class="form-label">Precio</label>
                            <input type="number" step="0.01" class="form-control" id="addPrecio" name="precio" required>
                        </div>
                        <div class="mb-3">
                            <label for="addStock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="addStock" name="stock" required>
                        </div>
                        <input type="hidden" name="add" value="true">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Agregar Tipo de Papas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Tipo de Papas -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editForm" method="post" action="inventario_tipo_papas.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Editar Tipo de Papas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="editNombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPrecio" class="form-label">Precio</label>
                            <input type="number" step="0.01" class="form-control" id="editPrecio" name="precio" required>
                        </div>
                        <div class="mb-3">
                            <label for="editStock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="editStock" name="stock" required>
                        </div>
                        <input type="hidden" id="editId" name="id_tipo_papas">
                        <input type="hidden" name="edit" value="true">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript para llenar el formulario de edición -->
    <script>
        function fillEditForm(data) {
            document.getElementById("editNombre").value = data.nombre;
            document.getElementById("editPrecio").value = data.precio;
            document.getElementById("editStock").value = data.stock;
            document.getElementById("editId").value = data.id_tipo_papas;
        }
    </script>

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>
