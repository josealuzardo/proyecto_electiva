<?php
session_start();
function require_login()
{
	if (empty($_SESSION['user_id'])) {
		header('Location: login.php');
		exit;
	}
}

function require_role($role)
{
	if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== $role) {
		http_response_code(403);
		echo "Acceso no autorizado.";
		exit;
	}
}
