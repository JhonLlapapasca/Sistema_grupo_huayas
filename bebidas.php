<?php
session_start();
require_once ("bootstrap/bootstrap.php");
require_once ("config/connection.php");
$con = connection();

// Función para buscar un producto por su ID
// function getProductById($con, $productIdBebidas)
// {
//     $stmt = $con->prepare("SELECT * FROM producto_principal WHERE id_producto_principal = ?");
//     if ($stmt === false) {
//         die('Prepare failed: ' . htmlspecialchars($con->error));
//     }

//     $stmt->bind_param("i", $productIdBebidas);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     if ($result === false) {
//         die('Execute failed: ' . htmlspecialchars($stmt->error));
//     }

//     return $result->fetch_assoc();
// }

// // Verificar si se envió el formulario por método POST
// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     // Obtener datos enviados
//     $selectedProductsBebidas = isset($_POST['selected_products']) ? json_decode($_POST['selected_products'], true) : [];
//     $totalPriceBebidas = 0;

//     // Verificar si los datos recibidos son válidos
//     if (!empty($selectedProductsBebidas) && is_array($selectedProductsBebidas)) {
//         echo '<div>';
//         echo '<h2>Productos Seleccionados</h2>';
//         echo '<ul>';
//         foreach ($selectedProductsBebidas as $productIdBebidas => $quantityBebidas) {
//             // Validar que el ID del producto sea válido (mayor que 0)
//             if ($productIdBebidas > 0) {
//                 // Obtener detalles del producto desde la base de datos
//                 $product = getProductById($con, $productIdBebidas);
//                 if ($product) {
//                     $subtotal = $product['precio'] * $quantityBebidas;
//                     $totalPriceBebidas += $subtotal;
//                     echo "<li>{$product['nombre']} x $quantityBebidas</li>";
//                 } else {
//                     echo "<li>Producto ID: $productIdBebidas - Cantidad: $quantityBebidas - Detalles no disponibles</li>";
//                 }
//             }
//         }
//         echo '</ul>';
//         echo '<p><strong>Total:</strong> ' . htmlspecialchars($totalPriceBebidas, ENT_QUOTES, 'UTF-8') . '</p>';
//         echo '</div>';
//     } else {
//         echo 'No se han recibido productos seleccionados o los datos no son válidos.';
//     }
// }

// Obtener los Tipo de papas disponibles
$sql = "SELECT pp.*, ap.stock AS stock_disponible
        FROM bebidas pp
        LEFT JOIN almacen_bebidas ap ON pp.id_bebidas = ap.id_bebidas";
$stmt = $con->prepare($sql);
$stmt->execute();

if ($stmt->error) {
    echo "Error en la consulta SQL: " . $stmt->error;
    exit;
}

$result = $stmt->get_result();



$selectedProductsBebidas = isset($_SESSION['selected_products']) ? $_SESSION['selected_products'] : [];
$totalPriceBebidas = isset($_SESSION['total_price']) ? $_SESSION['total_price'] : 0;
$selectedProductsImplementosJSON = json_encode($selectedProductsBebidas);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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

        /* .total-price {
            font-weight: bold;
        } */
    </style>
</head>

<body>
    <div class="container mt-5 mx-auto">
        <div class="container mt-5 mx-auto">
            <div class="position-relative m-4">
                <div class="progress" role="progressbar" aria-label="Progress" aria-valuenow="50" aria-valuemin="0"
                    aria-valuemax="100" style="height: 1px;">
                    <div class="progress-bar" style="width: 50%"></div>
                </div>
                <button type="button"
                    class="position-absolute top-0 start-0 translate-middle btn btn-sm btn-primary rounded-pill"
                    style="width: 2rem; height:2rem;">2</button>
                <div class="position-absolute start-0 translate-middle fw-bold" style="margin-top: 30px">
                    Implementos
                </div>
                <button type="button"
                    class="position-absolute top-0 start-50 translate-middle btn btn-sm btn-secondary rounded-pill"
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

            <div class="mt-5">
                <h4>Bebidas</h4>
                <div class="row" style="margin-top: 60px">
                    <div class="col-8">
                        <div class="row">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="col-4 product-card mt-5"
                                    data-id="<?php echo htmlspecialchars($row['id_bebidas'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <div
                                        class="card bg-warning-subtle h-100 <?php echo array_key_exists($row['id_bebidas'], $selectedProductsBebidas) ? 'selected' : ''; ?>">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQcndOf0hI2jvDbcL_NH4uvaxOlNPIGS6a2hQ&s"
                                                        alt="Product Image" class="img-fluid">
                                                </div>
                                                <div class="col-6">
                                                    <div class="mb-3">
                                                        <div class="product-name">
                                                            <?php echo htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </div>
                                                        <div class="product-price">
                                                            <?php echo htmlspecialchars($row['precio'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </div>
                                                        <div class="product-stock">
                                                            Stock disponible:
                                                            <?php echo htmlspecialchars($row['stock_disponible'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <input type="number" class="form-control product-quantityBebidas"
                                                name="quantityBebidas"
                                                value="<?php echo htmlspecialchars($selectedProductsBebidas[$row['id_bebidas']] ?? 1, ENT_QUOTES, 'UTF-8'); ?>"
                                                min="1">
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="selected-products">
                            <h3>Productos Seleccionados</h3>
                            Producto Principal
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
                                    echo '<div>';
                                    echo '<ul>';
                                    foreach ($selectedProducts as $productId => $quantity) {
                                        // Validar que el ID del producto sea válido (mayor que 0)
                                        if ($productId > 0) {
                                            // Obtener detalles del producto desde la base de datos
                                            $product = getProductById($con, $productId);
                                            if ($product) {
                                                $subtotal = $product['precio'] * $quantity;
                                                $totalPrice += $subtotal;
                                                echo "<li>{$product['nombre']} x $quantity</li>";
                                            } else {
                                                echo "<li>Producto ID: $productId - Cantidad: $quantity - Detalles no disponibles</li>";
                                            }
                                        }
                                    }
                                    echo '</ul>';
                                    echo '<p> => S/.' . htmlspecialchars($totalPrice, ENT_QUOTES, 'UTF-8') . '</p>';
                                    echo '</div>';
                                } else {
                                    echo 'No se han recibido productos seleccionados o los datos no son válidos.';
                                }
                            }
                            ?>
                            Implementos
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
                                    echo '<div>';
                                    echo '<ul>';
                                    foreach ($selectedProductsImplementos as $productIdImplementos => $quantityImplementos) {
                                        // Validar que el ID del producto sea válido (mayor que 0)
                                        if ($productIdImplementos > 0) {
                                            // Obtener detalles del producto desde la base de datos
                                            $product = getProductByIdImplementos($con, $productIdImplementos);
                                            if ($product) {
                                                $subtotal = $product['precio'] * $quantityImplementos;
                                                $totalPrice += $subtotal;
                                                echo "<li>{$product['nombre']} x $quantityImplementos</li>";
                                            } else {
                                                echo "<li>Producto ID: $productIdImplementos - Cantidad: $quantityImplementos - Detalles no disponibles</li>";
                                            }
                                        }
                                    }
                                    echo '</ul>';
                                    echo '<p> => S/.' . htmlspecialchars($totalPrice, ENT_QUOTES, 'UTF-8') . '</p>';
                                    echo '</div>';
                                } else {
                                    echo 'No se han recibido productos seleccionados o los datos no son válidos.';
                                }
                            }
                            ?>
                            Bebidas
                            <ul id="selected-product-list">
                                <?php
                                if (!empty($selectedProductsBebidas)) {
                                    foreach ($selectedProductsBebidas as $productIdBebidas => $quantityBebidas) {
                                        $sqlProduct = "SELECT * FROM tipo_papas WHERE id_tipo_papas = ?";
                                        $stmtProduct = $con->prepare($sqlProduct);
                                        $stmtProduct->bind_param("i", $productIdBebidas);
                                        $stmtProduct->execute();
                                        $resultProduct = $stmtProduct->get_result();
                                        $product = $resultProduct->fetch_assoc();

                                        if ($product) {
                                            echo "<li>{$product['nombre']} x {$quantityBebidas}</li>";
                                        }
                                    }
                                }
                                ?>
                            </ul>
                            <p class="total-price">=> S/. <span
                                    id="total-price"><?php echo $totalPriceBebidas; ?></span></p>
                            <form method="post" action="venta.php">
                                <input type="hidden" name="selected_products" id="selected-products"
                                    value="<?php echo htmlspecialchars($selectedProductsImplementosJSON, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="total_price"
                                    value="<?php echo htmlspecialchars($totalPriceBebidas, ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-primary">Continuar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const productCards = document.querySelectorAll('.product-card');
                const selectedProductsImplementosInput = document.getElementById('selected-products');
                const selectedProductList = document.getElementById('selected-product-list');
                const totalPriceBebidasElement = document.getElementById('total-price');

                let selectedProductsBebidas = JSON.parse(selectedProductsImplementosInput.value || '{}');

                const renderSelectedProductsImplementos = () => {
                    selectedProductList.innerHTML = '';
                    let totalPriceBebidas = 0;

                    for (let productIdBebidas in selectedProductsBebidas) {
                        const quantityBebidas = selectedProductsBebidas[productIdBebidas];
                        const productCard = document.querySelector(`.product-card[data-id="${productIdBebidas}"]`);
                        const productName = productCard.querySelector('.product-name').textContent;
                        const productPrice = parseFloat(productCard.querySelector('.product-price').textContent);

                        const listItem = document.createElement('li');
                        listItem.textContent = `${productName} x ${quantityBebidas}`;
                        selectedProductList.appendChild(listItem);

                        totalPriceBebidas += productPrice * quantityBebidas;
                    }

                    totalPriceBebidasElement.textContent = totalPriceBebidas.toFixed(2);
                    selectedProductsImplementosInput.value = JSON.stringify(selectedProductsBebidas);
                };

                productCards.forEach(function (card) {
                    const quantityBebidasInput = card.querySelector('.product-quantityBebidas');
                    card.addEventListener('click', function (e) {
                        if (e.target !== quantityBebidasInput) {
                            const productIdBebidas = this.getAttribute('data-id');

                            if (selectedProductsBebidas.hasOwnProperty(productIdBebidas)) {
                                delete selectedProductsBebidas[productIdBebidas];
                                this.querySelector('.card').classList.remove('selected');
                            } else {
                                selectedProductsBebidas[productIdBebidas] = parseInt(quantityBebidasInput.value);
                                this.querySelector('.card').classList.add('selected');
                            }

                            renderSelectedProductsImplementos();
                        }
                    });

                    quantityBebidasInput.addEventListener('change', function () {
                        const productIdBebidas = card.getAttribute('data-id');

                        if (selectedProductsBebidas.hasOwnProperty(productIdBebidas)) {
                            selectedProductsBebidas[productIdBebidas] = parseInt(this.value);
                            renderSelectedProductsImplementos();
                        }
                    });
                });

                renderSelectedProductsImplementos();
            });
        </script>
</body>

</html>