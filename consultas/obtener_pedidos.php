<?php
// =========================================================
// Archivo: obtener_pedidos.php
// Objetivo: Consultar y devolver el historial de pedidos en formato JSON.
// =========================================================

// --- 1. Configuración de la Base de Datos (Igual que procesar_pedido.php) ---
$host = '172.17.0.2';
$db   = 'vzlaaventura_db';
$user = 'root';
$pass = 'manager';

// Configurar encabezados para devolver JSON
header('Content-Type: application/json');

try {
	// Conexión
	$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// --- 2. Consulta a la Base de Datos ---
	// Seleccionar todos los pedidos, ordenados por el más reciente
	$sql = "SELECT id_pedido, monto_total, cantidad_items, fecha_pedido FROM pedidos ORDER BY fecha_pedido DESC";
	$stmt = $pdo->query($sql);

	// Obtener todos los resultados como un array asociativo
	$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// --- 3. Devolver Respuesta JSON ---
	echo json_encode(['success' => true, 'pedidos' => $pedidos]);
} catch (PDOException $e) {
	// Manejo de errores de conexión/consulta
	http_response_code(500); // Internal Server Error
	error_log("Error al obtener pedidos: " . $e->getMessage());
	echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
}
