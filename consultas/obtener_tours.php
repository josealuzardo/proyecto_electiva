<?php
$host = '172.17.0.2';
$db   = 'vzlaaventura_db';
$user = 'root';
$pass = 'manager';

header('Content-Type: application/json');

try {
	$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$sql = "SELECT id_tour, nombre, descripcion, precio, duracion, fecha_disponible, imagen_placeholder FROM tours ORDER BY precio ASC";
	$stmt = $pdo->query($sql);

	$tours = $stmt->fetchAll(PDO::FETCH_ASSOC);

	echo json_encode(['success' => true, 'tours' => $tours]);
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => 'Error al cargar los tours: ' . $e->getMessage()]);
}
