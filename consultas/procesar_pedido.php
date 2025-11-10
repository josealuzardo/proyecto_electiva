<?php
$host = '172.17.0.2';
$db   = 'vzlaaventura_db';
$user = 'root';
$pass = 'manager';

try {
	$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	http_response_code(500);
	die("Error de conexión a la BD: " . $e->getMessage());
}

if (
	$_SERVER['REQUEST_METHOD'] !== 'POST' ||
	!isset($_POST['total']) ||
	!isset($_POST['cantidad']) // ¡Nueva comprobación!
) {
	http_response_code(400); // Bad Request
	echo json_encode(['success' => false, 'message' => 'Solicitud o datos incompletos.']);
	exit;
}

$montoTotal = filter_var($_POST['total'], FILTER_VALIDATE_FLOAT);
$cantidadItems = filter_var($_POST['cantidad'], FILTER_VALIDATE_INT); // ¡Nueva sanitización!

if ($montoTotal === false || $montoTotal <= 0 || $cantidadItems === false || $cantidadItems <= 0) {
	http_response_code(400); // Bad Request
	echo json_encode(['success' => false, 'message' => 'Monto total o cantidad de ítems inválidos.']);
	exit;
}

try {
	$sql = "INSERT INTO pedidos (monto_total, cantidad_items) VALUES (:total, :cantidad)";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':total', $montoTotal);
	$stmt->bindParam(':cantidad', $cantidadItems); // ¡Nuevo bind!

	if ($stmt->execute()) {
		echo json_encode([
			'success' => true,
			'message' => 'Pedido registrado con éxito.',
			'id_pedido' => $pdo->lastInsertId()
		]);
	} else {
		throw new Exception("Error al ejecutar la inserción en la base de datos.");
	}
} catch (Exception $e) {
	http_response_code(500);
	error_log("Error de BD: " . $e->getMessage());
	echo json_encode(['success' => false, 'message' => 'Error interno al procesar el pedido.']);
}
