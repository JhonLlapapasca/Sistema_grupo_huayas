<?php
session_start();
require_once ("bootstrap/bootstrap.php");
require_once ("config/connection.php");
$con = connection();

// Obtener los productos disponibles
$sql = "SELECT pp.*, ap.stock AS stock_disponible
        FROM producto_principal pp
        LEFT JOIN almacen_producto_principal ap ON pp.id_producto_principal = ap.id_producto_principal";
$stmt = $con->prepare($sql);
$stmt->execute();

if ($stmt->error) {
    echo "Error en la consulta SQL: " . $stmt->error;
    exit;
}

$result = $stmt->get_result();

$selectedProducts = isset($_SESSION['selected_products']) ? $_SESSION['selected_products'] : [];
$totalPrice = isset($_SESSION['total_price']) ? $_SESSION['total_price'] : 0;
$selectedProductsJSON = json_encode($selectedProducts);
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
                <div class="progress-bar" style="width: 0%"></div>
            </div>
            <button type="button"
                class="position-absolute top-0 start-0 translate-middle btn btn-sm btn-secondary rounded-pill"
                style="width: 2rem; height:2rem;">1</button>
            <div class="position-absolute start-0 translate-middle fw-bold" style="margin-top: 30px">Productos</div>
            <button type="button"
                class="position-absolute top-0 start-50 translate-middle btn btn-sm btn-secondary rounded-pill"
                style="width: 2rem; height:2rem;">2</button>
            <div class="position-absolute start-50 translate-middle fw-bold" style="margin-top: 30px">Implementos</div>
            <button type="button"
                class="position-absolute top-0 start-100 translate-middle btn btn-sm btn-secondary rounded-pill"
                style="width: 2rem; height:2rem;">3</button>
            <div class="position-absolute start-100 translate-middle fw-bold" style="margin-top: 30px">Bebidas</div>
        </div>
        <div class="row" style="margin-top: 60px">
            <div class="col-8">
                <div class="row">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col-4 product-card mt-5"
                            data-id="<?php echo htmlspecialchars($row['id_producto_principal'], ENT_QUOTES, 'UTF-8'); ?>">
                            <div
                                class="card bg-warning-subtle h-100 <?php echo array_key_exists($row['id_producto_principal'], $selectedProducts) ? 'selected' : ''; ?>">
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
                                    <input type="number" class="form-control product-quantity" name="quantity"
                                        value="<?php echo htmlspecialchars($selectedProducts[$row['id_producto_principal']] ?? 1, ENT_QUOTES, 'UTF-8'); ?>"
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
                    <ul id="selected-product-list">
                        <?php
                        if (!empty($selectedProducts)) {
                            foreach ($selectedProducts as $productId => $quantity) {
                                $sqlProduct = "SELECT * FROM producto_principal WHERE id_producto_principal = ?";
                                $stmtProduct = $con->prepare($sqlProduct);
                                $stmtProduct->bind_param("i", $productId);
                                $stmtProduct->execute();
                                $resultProduct = $stmtProduct->get_result();
                                $product = $resultProduct->fetch_assoc();

                                if ($product) {
                                    echo "<li>{$product['nombre']} - {$product['precio']} x {$quantity}</li>";
                                }
                            }
                        }
                        ?>
                    </ul>
                    <p class="total-price">Total: <span id="total-price"><?php echo $totalPrice; ?></span></p>
                    <form method="post" action="implementos.php">
                        <input type="hidden" name="selected_products" id="selected-products"
                            value="<?php echo htmlspecialchars($selectedProductsJSON, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="total_price"
                            value="<?php echo htmlspecialchars($totalPrice, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-primary">Continuar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const productCards = document.querySelectorAll('.product-card');
            const selectedProductsInput = document.getElementById('selected-products');
            const selectedProductList = document.getElementById('selected-product-list');
            const totalPriceElement = document.getElementById('total-price');

            let selectedProducts = JSON.parse(selectedProductsInput.value || '{}');

            const renderSelectedProducts = () => {
                selectedProductList.innerHTML = '';
                let totalPrice = 0;

                for (let productId in selectedProducts) {
                    const quantity = selectedProducts[productId];
                    const productCard = document.querySelector(`.product-card[data-id="${productId}"]`);
                    const productName = productCard.querySelector('.product-name').textContent;
                    const productPrice = parseFloat(productCard.querySelector('.product-price').textContent);

                    const listItem = document.createElement('li');
                    listItem.textContent = `${productName} x ${quantity}`;
                    selectedProductList.appendChild(listItem);

                    totalPrice += productPrice * quantity;
                }

                totalPriceElement.textContent = totalPrice.toFixed(2);
                selectedProductsInput.value = JSON.stringify(selectedProducts);
            };

            productCards.forEach(function (card) {
                const quantityInput = card.querySelector('.product-quantity');
                card.addEventListener('click', function (e) {
                    if (e.target !== quantityInput) {
                        const productId = this.getAttribute('data-id');

                        if (selectedProducts.hasOwnProperty(productId)) {
                            delete selectedProducts[productId];
                            this.querySelector('.card').classList.remove('selected');
                        } else {
                            selectedProducts[productId] = parseInt(quantityInput.value);
                            this.querySelector('.card').classList.add('selected');
                        }

                        renderSelectedProducts();
                    }
                });

                quantityInput.addEventListener('change', function () {
                    const productId = card.getAttribute('data-id');

                    if (selectedProducts.hasOwnProperty(productId)) {
                        selectedProducts[productId] = parseInt(this.value);
                        renderSelectedProducts();
                    }
                });
            });

            renderSelectedProducts();
        });
    </script>
</body>

</html>