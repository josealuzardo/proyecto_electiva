<?php
// filepath: /home/aluzardo/code/docker/php/luzardo/register.php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($username === '' || $email === '' || $password === '') {
    $error = 'Complete todos los campos.';
  } else {
    // check existing
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR username = :username LIMIT 1");
    $stmt->execute(['email' => $email, 'username' => $username]);
    if ($stmt->fetch()) {
      $error = 'Usuario o email ya existe.';
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $ins = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:u, :e, :p, 'client')");
      $ins->execute(['u' => $username, 'e' => $email, 'p' => $hash]);
      $_SESSION['user_id'] = $pdo->lastInsertId();
      $_SESSION['username'] = $username;
      header('Location: ./../index.html');
      exit;
    }
  }
}
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Registro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">
  <div class="container" style="max-width:480px;">
    <h2>Registro</h2>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="mb-3"><label>Usuario</label><input name="username" class="form-control" required></div>
      <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
      <div class="mb-3"><label>Contraseña</label><input type="password" name="password" class="form-control" required></div>
      <button class="btn btn-primary">Registrar</button>
      <a class="btn btn-link" href="login.php">Ya tengo cuenta</a>
    </form>
  </div>
</body>

</html>