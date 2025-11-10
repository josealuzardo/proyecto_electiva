<?php
session_start();

/**
 * Lanza redirección a login si no hay sesión.
 */
function require_login()
{
	if (empty($_SESSION['user_id'])) {
		header('Location: login.php');
		exit;
	}
}

/**
 * Requiere rol específico (p.ej. 'admin'). Si no, 403.
 */
function require_role($role)
{
	if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== $role) {
		http_response_code(403);
		echo "Acceso no autorizado.";
		exit;
	}
}
