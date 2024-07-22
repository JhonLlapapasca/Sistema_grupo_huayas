<?php
session_start();
require_once("config/connection.php");
$con = connection();

// Manejar edición de producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["edit"])) {
    $id = $_POST["id_producto_principal"];
    $nombre = $_POST["nombre"];
    $precio = $_POST["precio"];
    $stock = $_POST["stock"];

    $sql = "UPDATE producto_principal p
            JOIN almacen_producto_principal a ON p.id_producto_principal = a.id_producto_principal
            SET p.nombre = ?, p.precio = ?, a.stock = ?
            WHERE p.id_producto_principal = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sidi", $nombre, $precio, $stock, $id);

    if ($stmt->execute()) {
        header("Location: inventario_productos.php");
        exit;
    } else {
        echo "Error al actualizar el producto: " . $con->error;
    }
}

// Manejar eliminación de producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $id = $_POST["id_producto_principal"];

    $sql = "DELETE FROM producto_principal WHERE id_producto_principal = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: inventario_productos.php");
        exit;
    } else {
        echo "Error al eliminar el producto: " . $con->error;
    }
}

// Manejar agregado de nuevo producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $nombre = $_POST["nombre"];
    $precio = $_POST["precio"];
    $stock = $_POST["stock"];

    $sql = "INSERT INTO producto_principal (nombre, precio) VALUES (?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sd", $nombre, $precio);

    if ($stmt->execute()) {
        $producto_id = $stmt->insert_id;

        $sql_stock = "INSERT INTO almacen_producto_principal (id_producto_principal, stock) VALUES (?, ?)";
        $stmt_stock = $con->prepare($sql_stock);
        $stmt_stock->bind_param("ii", $producto_id, $stock);

        if ($stmt_stock->execute()) {
            header("Location: inventario_productos.php");
            exit;
        } else {
            echo "Error al agregar el stock del producto: " . $con->error;
        }
    } else {
        echo "Error al agregar el producto: " . $con->error;
    }
}

// Consultar los productos y sus stocks
$sql = "SELECT p.id_producto_principal, p.nombre, p.precio, a.stock 
        FROM producto_principal p 
        JOIN almacen_producto_principal a ON p.id_producto_principal = a.id_producto_principal";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <ul class="nav nav-tabs mt-5">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">Productos</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

        <div class="">
            <div class="">
                <!-- Botón para abrir modal de agregar nuevo producto -->
                <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Agregar Nuevo Producto</button>
            </div>
            <div class="">
                <!-- Tabla de productos existentes -->
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
                                echo "<th scope='row'>" . $row["id_producto_principal"] . "</th>";
                                echo "<td>" . $row["nombre"] . "</td>";
                                echo "<td>" . $row["precio"] . "</td>";
                                echo "<td>" . $row["stock"] . "</td>";
                                echo "<td>
                                        <button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#editModal' onclick='fillEditForm(" . json_encode($row) . ")'>Editar</button>
                                        <form method='post' action='inventario_productos.php' style='display:inline-block;'>
                                            <input type='hidden' name='id_producto_principal' value='" . $row["id_producto_principal"] . "'>
                                            <input type='hidden' name='delete' value='true'>
                                            <button type='submit' class='btn btn-danger' onclick='return confirm(\"¿Estás seguro de que quieres eliminar este producto?\")'>Eliminar</button>
                                        </form>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No hay productos disponibles</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Agregar Nuevo Producto -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="inventario_productos.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Agregar Nuevo Producto</h5>
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

    <!-- Modal para Editar Producto -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editForm" method="post" action="inventario_productos.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Editar Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id_producto_principal">
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
        function fillEditForm(product) {
            document.getElementById('editId').value = product.id_producto_principal;
            document.getElementById('editNombre').value = product.nombre;
            document.getElementById('editPrecio').value = product.precio;
            document.getElementById('editStock').value = product.stock;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
