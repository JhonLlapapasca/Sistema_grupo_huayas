<?php
session_start();
require_once ("bootstrap/bootstrap.php");
require_once ("config/connection.php");
$con = connection();

// Función para buscar un producto por su ID
// function getProductById($con, $productIdImplementos)
// {
//     $stmt = $con->prepare("SELECT * FROM producto_principal WHERE id_producto_principal = ?");
//     if ($stmt === false) {
//         die('Prepare failed: ' . htmlspecialchars($con->error));
//     }

//     $stmt->bind_param("i", $productIdImplementos);
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
//     $selectedProductsImplementos = isset($_POST['selected_products']) ? json_decode($_POST['selected_products'], true) : [];
//     $totalPriceImplementos = 0;

//     // Verificar si los datos recibidos son válidos
//     if (!empty($selectedProductsImplementos) && is_array($selectedProductsImplementos)) {
//         echo '<div>';
//         echo '<h2>Productos Seleccionados</h2>';
//         echo '<ul>';
//         foreach ($selectedProductsImplementos as $productIdImplementos => $quantityImplementos) {
//             // Validar que el ID del producto sea válido (mayor que 0)
//             if ($productIdImplementos > 0) {
//                 // Obtener detalles del producto desde la base de datos
//                 $product = getProductById($con, $productIdImplementos);
//                 if ($product) {
//                     $subtotal = $product['precio'] * $quantityImplementos;
//                     $totalPriceImplementos += $subtotal;
//                     echo "<li>{$product['nombre']} x $quantityImplementos</li>";
//                 } else {
//                     echo "<li>Producto ID: $productIdImplementos - Cantidad: $quantityImplementos - Detalles no disponibles</li>";
//                 }
//             }
//         }
//         echo '</ul>';
//         echo '<p><strong>Total:</strong> ' . htmlspecialchars($totalPriceImplementos, ENT_QUOTES, 'UTF-8') . '</p>';
//         echo '</div>';
//     } else {
//         echo 'No se han recibido productos seleccionados o los datos no son válidos.';
//     }
// }

// Obtener los Tipo de papas disponibles
$sql = "SELECT pp.*, ap.stock AS stock_disponible
        FROM tipo_papas pp
        LEFT JOIN almacen_tipo_papas ap ON pp.id_tipo_papas = ap.id_tipo_papas";
$stmt = $con->prepare($sql);
$stmt->execute();

if ($stmt->error) {
    echo "Error en la consulta SQL: " . $stmt->error;
    exit;
}

$result = $stmt->get_result();

// Obtener los extras disponibles
$sql2 = "SELECT pp.*, ap.stock AS stock_disponible
        FROM extras pp
        LEFT JOIN almacen_extras ap ON pp.id_extras = ap.id_extras";
$stmt2 = $con->prepare($sql2);
$stmt2->execute();

if ($stmt2->error) {
    echo "Error en la consulta SQL: " . $stmt2->error;
    exit;
}

$result2 = $stmt2->get_result();

$selectedProductsImplementos = isset($_SESSION['selected_products']) ? $_SESSION['selected_products'] : [];
$totalPriceImplementos = isset($_SESSION['total_price']) ? $_SESSION['total_price'] : 0;
$selectedProductsImplementosJSON = json_encode($selectedProductsImplementos);
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
        <div class="position-relative m-4">
            <div class="progress" role="progressbar" aria-label="Progress" aria-valuenow="50" aria-valuemin="0"
                aria-valuemax="100" style="height: 1px;">
                <div class="progress-bar" style="width: 50%"></div>
            </div>
            <button type="button"
                class="position-absolute top-0 start-0 translate-middle btn btn-sm btn-primary rounded-pill"
                style="width: 2rem; height:2rem;">1</button>
            <div class="position-absolute start-0 translate-middle fw-bold" style="margin-top: 30px">
            Productos
            </div>
            <button type="button"
                class="position-absolute top-0 start-50 translate-middle btn btn-sm btn-secondary rounded-pill"
                style="width: 2rem; height:2rem;">2</button>
            <div class="position-absolute start-50 translate-middle fw-bold" style="margin-top: 30px">
                Implementos
            </div>
            <button type="button"
                class="position-absolute top-0 start-100 translate-middle btn btn-sm btn-secondary rounded-pill"
                style="width: 2rem; height:2rem;">3</button>
            <div class="position-absolute start-100 translate-middle fw-bold" style="margin-top: 30px">
                Bebidas
            </div>
        </div>

        <div class="mt-5">
            <h4>Tipo de papas</h4>
            <div class="row" style="margin-top: 60px">
                <div class="col-8">
                    <div class="row">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="col-4 product-card mt-5"
                                data-id="<?php echo htmlspecialchars($row['id_tipo_papas'], ENT_QUOTES, 'UTF-8'); ?>">
                                <div
                                    class="card bg-warning-subtle h-100 <?php echo array_key_exists($row['id_tipo_papas'], $selectedProductsImplementos) ? 'selected' : ''; ?>">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <img src="https://polleriaslagranja.com/wp-content/uploads/2022/10/La-Granja-Real-Food-Chicken-1.4-de-Pollo-a-la-Brasa-600x600.png"
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
                                        <input type="number" class="form-control product-quantityImplementos"
                                            name="quantityImplementos"
                                            value="<?php echo htmlspecialchars($selectedProductsImplementos[$row['id_tipo_papas']] ?? 1, ENT_QUOTES, 'UTF-8'); ?>"
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
                        <ul id="selected-product-list">
                            <?php
                            if (!empty($selectedProductsImplementos)) {
                                foreach ($selectedProductsImplementos as $productIdImplementos => $quantityImplementos) {
                                    $sqlProduct = "SELECT * FROM tipo_papas WHERE id_tipo_papas = ?";
                                    $stmtProduct = $con->prepare($sqlProduct);
                                    $stmtProduct->bind_param("i", $productIdImplementos);
                                    $stmtProduct->execute();
                                    $resultProduct = $stmtProduct->get_result();
                                    $product = $resultProduct->fetch_assoc();

                                    if ($product) {
                                        echo "<li>{$product['nombre']} x {$quantityImplementos}</li>";
                                    }
                                }
                            }
                            ?>
                        </ul>
                        <p class="total-price">=> S/. <span
                                id="total-price"><?php echo $totalPriceImplementos; ?></span>
                        </p>
                        <form method="post" action="bebidas.php">
                            <input type="hidden" name="selected_products" id="selected-products"
                                value="<?php echo htmlspecialchars($selectedProductsImplementosJSON, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="total_price"
                                value="<?php echo htmlspecialchars($totalPriceImplementos, ENT_QUOTES, 'UTF-8'); ?>">
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
            const totalPriceImplementosElement = document.getElementById('total-price');

            let selectedProductsImplementos = JSON.parse(selectedProductsImplementosInput.value || '{}');

            const renderSelectedProductsImplementos = () => {
                selectedProductList.innerHTML = '';
                let totalPriceImplementos = 0;

                for (let productIdImplementos in selectedProductsImplementos) {
                    const quantityImplementos = selectedProductsImplementos[productIdImplementos];
                    const productCard = document.querySelector(`.product-card[data-id="${productIdImplementos}"]`);
                    const productName = productCard.querySelector('.product-name').textContent;
                    const productPrice = parseFloat(productCard.querySelector('.product-price').textContent);

                    const listItem = document.createElement('li');
                    listItem.textContent = `${productName} x ${quantityImplementos}`;
                    selectedProductList.appendChild(listItem);

                    totalPriceImplementos += productPrice * quantityImplementos;
                }

                totalPriceImplementosElement.textContent = totalPriceImplementos.toFixed(2);
                selectedProductsImplementosInput.value = JSON.stringify(selectedProductsImplementos);
            };

            productCards.forEach(function (card) {
                const quantityImplementosInput = card.querySelector('.product-quantityImplementos');
                card.addEventListener('click', function (e) {
                    if (e.target !== quantityImplementosInput) {
                        const productIdImplementos = this.getAttribute('data-id');

                        if (selectedProductsImplementos.hasOwnProperty(productIdImplementos)) {
                            delete selectedProductsImplementos[productIdImplementos];
                            this.querySelector('.card').classList.remove('selected');
                        } else {
                            selectedProductsImplementos[productIdImplementos] = parseInt(quantityImplementosInput.value);
                            this.querySelector('.card').classList.add('selected');
                        }

                        renderSelectedProductsImplementos();
                    }
                });

                quantityImplementosInput.addEventListener('change', function () {
                    const productIdImplementos = card.getAttribute('data-id');

                    if (selectedProductsImplementos.hasOwnProperty(productIdImplementos)) {
                        selectedProductsImplementos[productIdImplementos] = parseInt(this.value);
                        renderSelectedProductsImplementos();
                    }
                });
            });

            renderSelectedProductsImplementos();
        });
    </script>
</body>

</html>