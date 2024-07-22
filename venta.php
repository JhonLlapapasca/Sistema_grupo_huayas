<?php
session_start();
require_once ("bootstrap/bootstrap.php");
require_once ("config/connection.php");
$con = connection();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Ventas</title>
    <link rel="stylesheet" href="path/to/bootstrap.css">
    <style>
        .product-card {
            margin-bottom: 20px;
            cursor: pointer;
        }

        .product-name {
            font-size: 1.2em;
        }

        .product-price {
            font-size: 1.5em;
            font-weight: bold;
            color: #000;
        }

        .selected {
            border: 2px solid #007bff;
        }

        .selected-products {
            float: right;
            border: 1px solid #ccc;
            padding: 10px;
            margin-left: 20px;
        }

        .total-price {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container mt-5 mx-auto">
        <div class="position-relative m-4">
            <div class="progress" role="progressbar" aria-label="Progress" aria-valuenow="50" aria-valuemin="0"
                aria-valuemax="100" style="height: 1px;">
                <div class="progress-bar" style="width: 100%"></div>
            </div>
            <button type="button"
                class="position-absolute top-0 start-0 translate-middle btn btn-sm btn-primary rounded-pill"
                style="width: 2rem; height:2rem;">2</button>
            <div class="position-absolute start-0 translate-middle fw-bold" style="margin-top: 30px">
                Implementos
            </div>
            <button type="button"
                class="position-absolute top-0 start-50 translate-middle btn btn-sm btn-primary rounded-pill"
                style="width: 2rem; height:2rem;">3</button>
            <div class="position-absolute start-50 translate-middle fw-bold" style="margin-top: 30px">
                Bebidas
            </div>
            <button type="button"
                class="position-absolute top-0 start-100 translate-middle btn btn-sm btn-secondary rounded-pill"
                style="width: 2rem; height:2rem;">4</button>
            <div class="position-absolute start-100 translate-middle fw-bold" style="margin-top: 30px">
                Venta
            </div>
        </div>
        <div class="row mx-auto justify-content-center text-center" style="margin-top: 60px">
            <div class="col-6">
                <a href="#" onclick="document.getElementById('boleta-form').submit();" style="text-decoration: none;">
                    <div class="card bg-success-subtle">
                        <div class="card-body">
                            <h5 class="card-title">BOLETA</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6">
                <a href="#" onclick="document.getElementById('factura-form').submit();" style="text-decoration: none;">
                    <div class="card bg-success-subtle">
                        <div class="card-body">
                            <h5 class="card-title">FACTURA</h5>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div>
          
            <?php
            function getProductById($con, $productId)
            {
                $stmt = $con->prepare("SELECT * FROM producto_principal WHERE id_producto_principal = ?");
                if ($stmt === false) {
                    die('Prepare failed: ' . htmlspecialchars($con->error));
                }

                $stmt->bind_param("i", $productId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result === false) {
                    die('Execute failed: ' . htmlspecialchars($stmt->error));
                }

                return $result->fetch_assoc();
            }

            // Verificar si se envió el formulario por método POST
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Obtener datos enviados
                $selectedProducts = isset($_POST['selected_products']) ? json_decode($_POST['selected_products'], true) : [];
                $totalPrice = 0;

                // Verificar si los datos recibidos son válidos
                if (!empty($selectedProducts) && is_array($selectedProducts)) {
                    //echo '<div>';
                   // echo '<ul>';
                    foreach ($selectedProducts as $productId => $quantity) {
                        // Validar que el ID del producto sea válido (mayor que 0)
                        if ($quantity > 0) {
                            // Obtener detalles del producto desde la base de datos
                            $product = getProductById($con, $productId);
                            if ($product) {
                                $subtotal = $product['precio'] * $quantity;
                                $totalPrice += $subtotal;
                               // echo "<li>{$product['nombre']} x $quantity</li>";
                            } else {
                               // echo "<li>Producto ID: $productId - Cantidad: $quantity - Detalles no disponibles</li>";
                            }
                        }
                    }
                   // echo '</ul>';
                   // echo '<p> => S/.' . htmlspecialchars($totalPrice, ENT_QUOTES, 'UTF-8') . '</p>';
                   // echo '</div>';
                } else {
                    echo 'No se han recibido productos seleccionados o los datos no son válidos.';
                }
            }
            ?>

           
            <?php
            function getProductByIdImplementos($con, $productIdImplementos)
            {
                $stmt = $con->prepare("SELECT * FROM tipo_papas WHERE id_tipo_papas = ?");
                if ($stmt === false) {
                    die('Prepare failed: ' . htmlspecialchars($con->error));
                }

                $stmt->bind_param("i", $productIdImplementos);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result === false) {
                    die('Execute failed: ' . htmlspecialchars($stmt->error));
                }

                return $result->fetch_assoc();
            }

            // Verificar si se envió el formulario por método POST
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Obtener datos enviados
                $selectedProductsImplementos = isset($_POST['selected_products']) ? json_decode($_POST['selected_products'], true) : [];
                $totalPrice = 0;

                // Verificar si los datos recibidos son válidos
                if (!empty($selectedProductsImplementos) && is_array($selectedProductsImplementos)) {
                    //echo '<div>';
                    //echo '<ul>';
                    foreach ($selectedProductsImplementos as $productIdImplementos => $quantityImplementos) {
                        // Validar que el ID del producto sea válido (mayor que 0)
                        if ($quantityImplementos > 0) {
                            // Obtener detalles del producto desde la base de datos
                            $product = getProductByIdImplementos($con, $productIdImplementos);
                            if ($product) {
                                $subtotal = $product['precio'] * $quantityImplementos;
                                $totalPrice += $subtotal;
                                //echo "<li>{$product['nombre']} x $quantityImplementos</li>";
                            } else {
                               // echo "<li>Producto ID: $productIdImplementos - Cantidad: $quantityImplementos - Detalles no disponibles</li>";
                            }
                        }
                    }
                   // echo '</ul>';
                   // echo '<p> => S/.' . htmlspecialchars($totalPrice, ENT_QUOTES, 'UTF-8') . '</p>';
                   // echo '</div>';
                } else {
                    echo 'No se han recibido productos seleccionados o los datos no son válidos.';
                }
            }
            ?>

          
            <?php
            function getProductByIdBebidas($con, $productIdBebidas)
            {
                $stmt = $con->prepare("SELECT * FROM bebidas WHERE id_bebidas = ?");
                if ($stmt === false) {
                    die('Prepare failed: ' . htmlspecialchars($con->error));
                }

                $stmt->bind_param("i", $productIdBebidas);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result === false) {
                    die('Execute failed: ' . htmlspecialchars($stmt->error));
                }

                return $result->fetch_assoc();
            }

            // Verificar si se envió el formulario por método POST
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Obtener datos enviados
                $selectedProductsBebidas = isset($_POST['selected_products']) ? json_decode($_POST['selected_products'], true) : [];
                $totalPrice = 0;

                // Verificar si los datos recibidos son válidos
                if (!empty($selectedProductsBebidas) && is_array($selectedProductsBebidas)) {
                    //echo '<div>';
                   // echo '<ul>';
                    foreach ($selectedProductsBebidas as $productIdBebidas => $quantityBebidas) {
                        // Validar que el ID del producto sea válido (mayor que 0)
                        if ($quantityBebidas > 0) {
                            // Obtener detalles del producto desde la base de datos
                            $product = getProductByIdBebidas($con, $productIdBebidas);
                            if ($product) {
                                $subtotal = $product['precio'] * $quantityBebidas;
                                $totalPrice += $subtotal;
                                //echo "<li>{$product['nombre']} x $quantityBebidas</li>";
                            } else {
                                //echo "<li>Producto ID: $productIdBebidas - Cantidad: $quantityBebidas - Detalles no disponibles</li>";
                            }
                        }
                    }
                    //echo '</ul>';
                    //echo '<p> => S/.' . htmlspecialchars($totalPrice, ENT_QUOTES, 'UTF-8') . '</p>';
                    //echo '</div>';
                } else {
                    echo 'No se han recibido productos seleccionados o los datos no son válidos.';
                }
            }
            ?>
        </div>
        
        <!-- Formularios ocultos para boleta y factura -->
        <form id="boleta-form" method="post" action="boleta.php" style="display:none;">
            <input type="hidden" name="selected_products" value="<?php echo htmlspecialchars(json_encode($selectedProducts), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="total_price" value="<?php echo htmlspecialchars($totalPrice, ENT_QUOTES, 'UTF-8'); ?>">
        </form>
        <form id="factura-form" method="post" action="factura.php" style="display:none;">
            <input type="hidden" name="selected_products" value="<?php echo htmlspecialchars(json_encode($selectedProducts), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="total_price" value="<?php echo htmlspecialchars($totalPrice, ENT_QUOTES, 'UTF-8'); ?>">
        </form>
    </div>
</body>

</html>
