<?php
$host = '172.17.0.2';
$db   = 'vzlaaventura_db';
$user = 'root';
$pass = 'manager';

header('Content-Type: application/json');

try {
	$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$sql = "SELECT id_pedido, monto_total, cantidad_items, fecha_pedido FROM pedidos ORDER BY fecha_pedido DESC";
	$stmt = $pdo->query($sql);

	$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

	echo json_encode(['success' => true, 'pedidos' => $pedidos]);
} catch (PDOException $e) {
	http_response_code(500); // Internal Server Error
	error_log("Error al obtener pedidos: " . $e->getMessage());
	echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
}
