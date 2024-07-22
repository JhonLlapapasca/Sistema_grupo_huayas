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
    <title>Venta - Boleta</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .letras-iniciales {
            font-size: 30px;
        }

        @media print {
            .noImprimir {
                display: none;
            }
        }
    </style>
    <script>
        function printBoleta() {
            window.print();
        }
    </script>
</head>

<body>
    <div class="container mt-5 mx-auto">
        <div class="position-relative m-4 noImprimir">
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
                class="position-absolute top-0 start-100 translate-middle btn btn-sm btn-primary rounded-pill"
                style="width: 2rem; height:2rem;">4</button>
            <div class="position-absolute start-100 translate-middle fw-bold" style="margin-top: 30px">
                Venta
            </div>
        </div>
        <div class="mt-5">
            <h4 class="noImprimir">Venta - Boleta</h4>
            <div class="row">
                <div class="col-3">
                    <div class="card noImprimir">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="flexSwitchCheckDefault">
                                    <label class="form-check-label" for="flexSwitchCheckDefault">Incluye
                                        Delivery</label>
                                </div>
                                <div class="mt-2">
                                    <label for="delivery_cost" class="form-label">Costo de Delivery (S/.)</label>
                                    <input type="number" class="form-control" id="delivery_cost" name="delivery_cost"
                                        value="10" readonly>
                                </div>
                                <div class="mt-2">
                                    <label for="direccion_referencia" class="form-label">Direccion de referencia</label>
                                    <input type="text" class="form-control" id="direccion_referencia"
                                        name="direccion_referencia">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <div class="card-body">

                            <div class="mt-1">
                                <p class="text-center fw-bold">Pollos y Parrillas <span class="letras-iniciales">Huayas
                                    </span></p>
                                <p class="text-center">RUC: 20603893981</p>
                                <p class="text-center">Calle 2 de Mayo Nro. 450 Lambayeque - Lambayeque - Lambayeque</p>
                                <p>Nro. Boleta: 0001</p>
                                <p>Fecha de Emisión: <?php echo date("Y-m-d"); ?></p>
                                <hr style="background-color: black;">
                                <?php
                                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                    // Obtener datos enviados
                                    $dni = isset($_POST['dni']) ? htmlspecialchars($_POST['dni'], ENT_QUOTES, 'UTF-8') : '';
                                    $nombre_completo = isset($_POST['nombre_completo']) ? htmlspecialchars($_POST['nombre_completo'], ENT_QUOTES, 'UTF-8') : '';
                                    $direccion = isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion'], ENT_QUOTES, 'UTF-8') : '';

                                    echo "<p>Señor: $nombre_completo</p>";
                                    echo "<p>DNI: $dni</p>";
                                    echo "<p>Dirección: $direccion</p>";
                                }
                                ?>
                            </div>
                            <p class="fw-bold">
                                Productos
                            </p>
                            <hr style="background-color: black;">

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
                                $selectedProducts = isset($_POST['selected_products']) ? json_decode($_POST['selected_products'], true) : [];
                                $selectedProductsImplementos = isset($_POST['selected_products']) ? json_decode($_POST['selected_products'], true) : [];
                                $selectedProductsBebidas = isset($_POST['selected_products']) ? json_decode($_POST['selected_products'], true) : [];
                                $totalPrice = 0;

                                // Producto Principal
                                if (!empty($selectedProducts) && is_array($selectedProducts)) {
                                    echo '<div>';
                                    echo '<p>';
                                    foreach ($selectedProducts as $productId => $quantity) {
                                        // Validar que el ID del producto sea válido (mayor que 0)
                                        if ($quantity > 0) {
                                            // Obtener detalles del producto desde la base de datos
                                            $product = getProductById($con, $productId);
                                            if ($product) {
                                                $subtotal = $product['precio'] * $quantity;
                                                $totalPrice += $subtotal;
                                                echo "<p>{$product['nombre']} x $quantity  - S/. $subtotal.00  </p>";
                                            } else {
                                                echo "<p>Producto ID: $productId - Cantidad: $quantity - Detalles no disponibles</p>";
                                            }
                                        }
                                    }
                                    echo '</p>';
                                    echo '</div>';
                                } else {
                                    echo '<p>No se han recibido productos seleccionados o los datos no son válidos.</p>';
                                }

                                // Implementos
                                if (!empty($selectedProductsImplementos) && is_array($selectedProductsImplementos)) {
                                    echo '<div>';
                                    echo '<p>';
                                    foreach ($selectedProductsImplementos as $productIdImplementos => $quantityImplementos) {
                                        // Validar que el ID del producto sea válido (mayor que 0)
                                        if ($quantityImplementos > 0) {
                                            // Obtener detalles del producto desde la base de datos
                                            $product = getProductByIdImplementos($con, $productIdImplementos);
                                            if ($product) {
                                                $subtotal = $product['precio'] * $quantityImplementos;
                                                $totalPrice += $subtotal;
                                                echo "<p>{$product['nombre']} x $quantityImplementos  - S/. $subtotal.00  </p>";
                                            } else {
                                                echo "<p>Producto ID: $productIdImplementos - Cantidad: $quantityImplementos - Detalles no disponibles</p>";
                                            }
                                        }
                                    }
                                    echo '</p>';
                                    echo '</div>';
                                } else {
                                    echo '<p>No se han recibido productos seleccionados o los datos no son válidos.</p>';
                                }

                                // Bebidas
                            
                                if (!empty($selectedProductsBebidas) && is_array($selectedProductsBebidas)) {
                                    echo '<div>';
                                    echo '<p>';
                                    foreach ($selectedProductsBebidas as $productIdBebidas => $quantityBebidas) {
                                        // Validar que el ID del producto sea válido (mayor que 0)
                                        if ($quantityBebidas > 0) {
                                            // Obtener detalles del producto desde la base de datos
                                            $product = getProductByIdBebidas($con, $productIdBebidas);
                                            if ($product) {
                                                $subtotal = $product['precio'] * $quantityBebidas;
                                                $totalPrice += $subtotal;
                                                echo "<p>{$product['nombre']} x $quantityBebidas  - S/. $subtotal.00  </p>";
                                            } else {
                                                echo "<p>Producto ID: $productIdBebidas - Cantidad: $quantityBebidas - Detalles no disponibles</p>";
                                            }
                                        }
                                    }
                                    echo '</p>';
                                    echo '</div>';
                                } else {
                                    echo '<p>No se han recibido productos seleccionados o los datos no son válidos.</p>';
                                }

                                echo '<hr style="background-color: black;">';


                                echo '<p id="Delivery" > </p>';


                                // Mostrar total acumulado
                                echo '<p id="total_price" class="fs-5 fw-bold"></p>';
                            }
                            ?>


                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <button type="button" class="btn btn-primary noImprimir" onclick="printBoleta()">Imprimir</button>

                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-primary noImprimir" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        YAPE
                    </button>

                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel" style="color: #8724b4">YAPE -
                                        Pollos y Parrillas Huayas</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <img src="img/yape.png" alt="" width="100%">
                                    <div class="text-center" style="color: #8724b4">
                                        <h3 class="fw-bold">Yapear
                                            <?php echo '<p id="yapear"> </p>'; ?>
                                        </h3>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</body>
<script>
    function updateTotal() {
        let deliveryCost = parseFloat(document.getElementById('delivery_cost').value);
        let direccionReferencia = document.getElementById('direccion_referencia').value;
        let totalPrice = <?php echo json_encode($totalPrice); ?>;

        if (document.getElementById('flexSwitchCheckDefault').checked) {
            totalPrice += deliveryCost;
            document.getElementById('Delivery').innerText = 'Delivery: S/. ' + deliveryCost.toFixed(2) + '\nDirección de referencia: ' + direccionReferencia;
        } else {
            document.getElementById('Delivery').innerText = '';
        }

        document.getElementById('total_price').innerText = 'Total: S/. ' + totalPrice.toFixed(2);
        document.getElementById('yapear').innerText = 'S/. ' + totalPrice.toFixed(2);
    }

    document.getElementById('flexSwitchCheckDefault').addEventListener('change', updateTotal);
    document.getElementById('delivery_cost').addEventListener('input', updateTotal);
    document.getElementById('direccion_referencia').addEventListener('change', updateTotal);

    // Initial update on page load
    updateTotal();
</script>

</html>