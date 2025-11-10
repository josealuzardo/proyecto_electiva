<?php
// filepath: /home/aluzardo/code/docker/php/luzardo/login.php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $identifier = trim($_POST['identifier'] ?? ''); // email o username
  $password = $_POST['password'] ?? '';

  if ($identifier === '' || $password === '') {
    $error = 'Complete todos los campos.';
  } else {
    $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE email = :id OR username = :id LIMIT 1");
    $stmt->execute(['id' => $identifier]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role']; // <-- guardar rol
      header('Location: ./../index.html');
      exit;
    } else {
      $error = 'Credenciales inválidas.';
    }
  }
}
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">
  <div class="container" style="max-width:420px;">
    <h2>Iniciar Sesión</h2>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="mb-3"><label>Email o Usuario</label><input name="identifier" class="form-control" required></div>
      <div class="mb-3"><label>Contraseña</label><input type="password" name="password" class="form-control" required></div>
      <button class="btn btn-primary">Entrar</button>
      <a class="btn btn-link" href="register.php">Crear cuenta</a>
    </form>
  </div>
</body>

</html>