<?php
session_start();
require_once ("bootstrap/bootstrap.php");
require_once ("config/connection.php");
$con = connection();

// Variable para almacenar mensajes de error
$error_message = "";

// Inicializar variables para los productos
$selectedProducts = [];
$totalPrice = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el RUC ingresado por el usuario

    if (isset($_POST['ruc'])) {
        $ruc = $_POST['ruc'];
    } else {
       // $error_message = "DNI not provided.";
        $ruc = "";
    }

    // Obtener productos seleccionados
    if (isset($_POST['selected_products'])) {
        $selectedProducts = json_decode($_POST['selected_products'], true);
    }

    // URL de la API donde se envía la solicitud POST
    $api_url = "http://localhost:8080/ruc/" . $ruc;

    // Datos a enviar en la solicitud POST
    $post_data = ['ruc' => $ruc];

    // Inicializar cURL
    $curl = curl_init();

    // Configurar opciones de cURL para la solicitud POST
    curl_setopt_array($curl, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post_data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
        ],
    ]);

    // Ejecutar la solicitud cURL
    $response = curl_exec($curl);

    // Verificar errores de cURL
    if ($response === false) {
        $error_message = "Error en la solicitud: " . curl_error($curl);
    } else {
        // Obtener el código de respuesta HTTP
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Verificar si la solicitud fue exitosa (código 200)
        if ($http_code == 200) {
            // Decodificar la respuesta JSON a un array asociativo
            $data = json_decode($response, true);

            // Verificar si los datos esperados están presentes en la respuesta
            if (isset($data['ruc']) && isset($data['nombre_comercial']) && isset($data['domicilio_fiscal'])) {
                // Asignar los datos a variables para mostrar en el formulario
                $ruc_result = $data['ruc'];
                $nombre_comercial = $data['nombre_comercial'];
                $domicilio_fiscal = $data['domicilio_fiscal'];
            } else {
               // $error_message = "No se encontraron los datos esperados en la respuesta o vuelva a intentarlo.";
            }
        } else {
           // $error_message = "Error al consultar la API: Código " . $http_code;
        }
    }

    // Cerrar la sesión cURL
    curl_close($curl);

    // Calcular el total de los productos seleccionados
    foreach ($selectedProducts as $productId => $quantity) {
        if ($productId > 0) {
            $product = getProductById($con, $productId);
            if ($product) {
                $subtotal = $product['precio'] * $quantity;
                $totalPrice += $subtotal;
            }
        }
    }
} else {
    $ruc_result = "";
    $nombre_comercial = "";
    $domicilio_fiscal = "";
}

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar RUC</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
        <div class="mt-5">
            <h4>Factura</h4>
            <div class="card">
                <div class="card-body">
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="ruc">Ingresar RUC</label>
                            <input type="text" name="ruc" id="ruc" class="form-control">
                        </div>
                        <input type="hidden" name="selected_products" id="selected-products"
                            value="<?php echo htmlspecialchars(json_encode($selectedProducts), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="submit" value="Consultar" class="btn btn-primary mb-2">
                    </form>

                    <?php if (!empty($ruc_result) && !empty($nombre_comercial) && !empty($domicilio_fiscal)): ?>
                        <div class="form-group">
                            <label for="ruc_result">RUC</label>
                            <input type="text" id="ruc_result" class="form-control" value="<?php echo $ruc_result; ?>"
                                readonly>
                        </div>
                        <div class="form-group">
                            <label for="nombre_comercial">Nombre comercial</label>
                            <input type="text" id="nombre_comercial" class="form-control"
                                value="<?php echo $nombre_comercial; ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="domicilio_fiscal">Domicilio fiscal</label>
                            <input type="text" id="domicilio_fiscal" class="form-control"
                                value="<?php echo $domicilio_fiscal; ?>" readonly>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <form method="post" action="factura_venta.php">
                    <input type="hidden" name="ruc"
                        value="<?php echo htmlspecialchars($ruc_result, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="nombre_comercial"
                        value="<?php echo htmlspecialchars($nombre_comercial, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="domicilio_fiscal"
                        value="<?php echo htmlspecialchars($domicilio_fiscal, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="selected_products"
                        value="<?php echo htmlspecialchars(json_encode($selectedProducts), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="total_price"
                        value="<?php echo htmlspecialchars($totalPrice, ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-primary">Continuar</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>