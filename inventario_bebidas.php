<?php
session_start();
require_once("bootstrap/bootstrap.php");
require_once("config/connection.php");
$con = connection();

// Manejar la actualización de bebidas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["edit"])) {
    $id = $_POST["id_bebidas"];
    $nombre = $_POST["nombre"];
    $precio = $_POST["precio"];
    $stock = $_POST["stock"];

    $sql = "UPDATE bebidas b
            JOIN almacen_bebidas ab ON b.id_bebidas = ab.id_bebidas
            SET b.nombre = ?, b.precio = ?, ab.stock = ?
            WHERE b.id_bebidas = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sdsi", $nombre, $precio, $stock, $id);

    if ($stmt->execute()) {
        header("Location: inventario_bebidas.php");
        exit;
    } else {
        echo "Error al actualizar la bebida: " . $con->error;
    }
}

// Manejar eliminación de bebidas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $id = $_POST["id_bebidas"];

    $sql = "DELETE FROM bebidas WHERE id_bebidas = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: inventario_bebidas.php");
        exit;
    } else {
        echo "Error al eliminar la bebida: " . $con->error;
    }
}

// Manejar la inserción de nuevas bebidas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $nombre = $_POST["addNombre"];
    $precio = $_POST["addPrecio"];
    $stock = $_POST["addStock"];

    $sql = "INSERT INTO bebidas (nombre, precio) VALUES (?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sd", $nombre, $precio);

    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;
        $sql_stock = "INSERT INTO almacen_bebidas (id_bebidas, stock) VALUES (?, ?)";
        $stmt_stock = $con->prepare($sql_stock);
        $stmt_stock->bind_param("ii", $last_id, $stock);

        if ($stmt_stock->execute()) {
            header("Location: inventario_bebidas.php");
            exit;
        } else {
            echo "Error al agregar stock de la bebida: " . $con->error;
        }
    } else {
        echo "Error al agregar la bebida: " . $con->error;
    }
}

// Consultar las bebidas y sus stocks
$sql = "SELECT b.id_bebidas, b.nombre, b.precio, ab.stock 
        FROM bebidas b 
        JOIN almacen_bebidas ab ON b.id_bebidas = ab.id_bebidas";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Bebidas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <ul class="nav nav-tabs mt-5">
            <li class="nav-item">
                <a class="nav-link" href="inventario_productos.php">Productos</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" aria-current="page" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Implementos
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="inventario_tipo_papas.php">Tipo de Papas</a></li>
                    <li><a class="dropdown-item" href="inventario_extras.php">Extras</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="#">Bebidas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="inventario_empleados.php">Empleados</a>
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

        <!-- Botón para mostrar modal de agregar bebida -->
        <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Agregar Bebida</button>

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
                        echo "<th scope='row'>" . $row["id_bebidas"] . "</th>";
                        echo "<td>" . $row["nombre"] . "</td>";
                        echo "<td>" . $row["precio"] . "</td>";
                        echo "<td>" . $row["stock"] . "</td>";
                        echo "<td>
                                <button type='button' class='btn btn-primary' data-bs-toggle='modal'
                                        data-bs-target='#editModal' onclick='fillEditForm(" . json_encode($row) . ")'>Editar</button>
                                <form method='post' action='inventario_bebidas.php' style='display:inline-block;'>
                                    <input type='hidden' name='id_bebidas' value='" . $row["id_bebidas"] . "'>
                                    <input type='hidden' name='delete' value='true'>
                                    <button type='submit' class='btn btn-danger' onclick='return confirm(\"¿Estás seguro de que quieres eliminar esta bebida?\")'>Eliminar</button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No hay bebidas disponibles</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para Agregar Bebida -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addForm" method="post" action="inventario_bebidas.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Agregar Bebida</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="add" value="true">
                        <div class="mb-3">
                            <label for="addNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="addNombre" name="addNombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="addPrecio" class="form-label">Precio</label>
                            <input type="number" step="0.01" class="form-control" id="addPrecio" name="addPrecio" required>
                        </div>
                        <div class="mb-3">
                            <label for="addStock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="addStock" name="addStock" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Agregar Bebida</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Bebida -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editForm" method="post" action="inventario_bebidas.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Editar Bebida</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id_bebidas">
                        <input type="hidden" name="edit" value="true">
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
    function fillEditForm(bebida) {
        document.getElementById('editId').value = bebida.id_bebidas;
        document.getElementById('editNombre').value = bebida.nombre;
        document.getElementById('editPrecio').value = bebida.precio;
        document.getElementById('editStock').value = bebida.stock;
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
