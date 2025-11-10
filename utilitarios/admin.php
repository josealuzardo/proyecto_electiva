<?php
require_once 'config.php';
require_once 'auth.php';
require_role('admin');

$action = $_REQUEST['action'] ?? '';

if ($action !== '') {
  header('Content-Type: application/json; charset=utf-8');
  try {
    if ($action === 'list_users') {
      $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
      $users = $stmt->fetchAll();
      echo json_encode(['success' => true, 'users' => $users]);
      exit;
    }

    if ($action === 'create_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
      $username = trim($_POST['username'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $password = $_POST['password'] ?? '';
      $role = in_array($_POST['role'] ?? 'client', ['client', 'admin']) ? $_POST['role'] : 'client';

      if ($username === '' || $email === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Completa los campos.']);
        exit;
      }

      $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR username = :username LIMIT 1");
      $stmt->execute(['email' => $email, 'username' => $username]);
      if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Usuario o email ya existe.']);
        exit;
      }

      $hash = password_hash($password, PASSWORD_DEFAULT);
      $ins = $pdo->prepare("INSERT INTO users (username,email,password,role) VALUES (:u,:e,:p,:r)");
      $ins->execute(['u' => $username, 'e' => $email, 'p' => $hash, 'r' => $role]);
      echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
      exit;
    }

    if ($action === 'list_products') {
      $stmt = $pdo->query("SELECT * FROM tours");
      $items = $stmt->fetchAll();
      echo json_encode(['success' => true, 'products' => $items]);
      exit;
    }

    if ($action === 'create_product' && $_SERVER['REQUEST_METHOD'] === 'POST') {
      $id_tour = trim($_POST['id_tour'] ?? '');
      $nombre = trim($_POST['nombre'] ?? '');
      $descripcion = trim($_POST['descripcion'] ?? '');
      $precio = floatval($_POST['precio'] ?? 0);
      $imagen = trim($_POST['imagen'] ?? null);
      $duracion = trim($_POST['duracion'] ?? null);
      $fecha = trim($_POST['fecha_disponible'] ?? null);

      if ($nombre === '' || $precio <= 0) {
        echo json_encode(['success' => false, 'message' => 'Nombre y precio son requeridos (precio > 0).']);
        exit;
      }

      $ins = $pdo->prepare("INSERT INTO tours (id_tour, nombre, descripcion, precio, duracion, fecha_disponible, imagen_placeholder) VALUES (:it,:n,:d,:p, :du, :f, :ip)");
      $ins->execute(['it' => $id_tour, 'n' => $nombre, 'd' => $descripcion, 'p' => $precio, 'du' => $duracion, 'f' => $fecha, 'ip' => $imagen]);

      echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
      exit;
    }

    if ($action === 'list_orders') {
      $stmt = $pdo->query("SELECT id, monto_total, cantidad_items, fecha_pedido FROM pedidos");
      $orders = $stmt->fetchAll();
      echo json_encode(['success' => true, 'orders' => $orders]);
      exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción desconocida']);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
  }
  exit;
}

?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Admin - Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Panel de Administración</h2>
      <div>
        <a class="btn btn-outline-secondary btn-sm" href="./../index.html">Sitio</a>
        <a class="btn btn-outline-danger btn-sm" href="logout.php">Salir</a>
      </div>
    </div>

    <div class="list-group mb-4" id="admin-home">
      <button class="list-group-item list-group-item-action" data-module="users">Usuarios</button>
      <button class="list-group-item list-group-item-action" data-module="products">Productos</button>
      <button class="list-group-item list-group-item-action" data-module="orders">Pedidos</button>
    </div>

    <div id="module-container"></div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Carga de módulos: cuando el admin elige un módulo, inyectamos su UI y cargamos datos
    const home = document.getElementById('admin-home');
    const container = document.getElementById('module-container');

    home.addEventListener('click', (e) => {
      const module = e.target.dataset.module;
      if (!module) return;
      if (module === 'users') loadUsersModule();
      if (module === 'products') loadProductsModule();
      if (module === 'orders') loadOrdersModule();
      // Oculta home
      home.style.display = 'none';
    });

    // Helper API
    async function api(action, data = null) {
      const url = 'admin.php?action=' + encodeURIComponent(action);
      const opts = {
        method: data ? 'POST' : 'GET'
      };
      if (data) opts.body = new URLSearchParams(data);
      const res = await fetch(url, opts);
      return await res.json();
    }

    function escapeHtml(s) {
      return String(s || '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      })[c]);
    }

    // Usuarios module
    function usersModuleHtml() {
      return `
        <div>
          <button class="btn btn-link mb-2" id="back-btn">← Volver</button>
          <h4>Usuarios</h4>
          <form id="form-create-user" class="row g-2 mb-3">
            <div class="col-md-3"><input name="username" class="form-control" placeholder="Usuario" required></div>
            <div class="col-md-3"><input name="email" type="email" class="form-control" placeholder="Email" required></div>
            <div class="col-md-3"><input name="password" type="password" class="form-control" placeholder="Contraseña" required></div>
            <div class="col-md-2">
              <select name="role" class="form-select">
                <option value="client" selected>Client</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div class="col-md-1"><button class="btn btn-primary" type="submit">Crear</button></div>
          </form>
          <div id="msg-user"></div>
          <div id="users-list"></div>
        </div>
      `;
    }

    async function loadUsersModule() {
      container.innerHTML = usersModuleHtml();
      document.getElementById('back-btn').addEventListener('click', () => {
        container.innerHTML = '';
        home.style.display = 'block';
      });
      document.getElementById('form-create-user').addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target).entries());
        const r = await api('create_user', data);
        const msg = document.getElementById('msg-user');
        if (r.success) {
          msg.innerHTML = '<div class="alert alert-success">Usuario creado (ID: ' + r.id + ')</div>';
          e.target.reset();
          loadUsersList();
        } else {
          msg.innerHTML = '<div class="alert alert-danger">' + (r.message || 'Error') + '</div>';
        }
      });
      loadUsersList();
    }

    async function loadUsersList() {
      const r = await api('list_users');
      if (r.success) {
        const rows = r.users.map(u => `<tr><td>${u.id}</td><td>${escapeHtml(u.username)}</td><td>${escapeHtml(u.email)}</td><td>${u.role}</td><td>${u.created_at}</td></tr>`).join('');
        document.getElementById('users-list').innerHTML = `<table class="table table-sm"><thead><tr><th>ID</th><th>Usuario</th><th>Email</th><th>Rol</th><th>Creado</th></tr></thead><tbody>${rows}</tbody></table>`;
      }
    }

    // Productos module
    function productsModuleHtml() {
      return `
        <div>
          <button class="btn btn-link mb-2" id="back-btn">← Volver</button>
          <h4>Productos</h4>
          <form id="form-create-product" class="row g-2 mb-3">
            <div class="col-md-4"><input name="id_tour" class="form-control" placeholder="Etiqueta" required></div>
            <div class="col-md-4"><input name="nombre" class="form-control" placeholder="Nombre" required></div>
            <div class="col-md-2"><input name="precio" type="number" step="0.01" class="form-control" placeholder="Precio" required></div>
            <div class="col-md-6"><input name="imagen" class="form-control" placeholder="imagen_placeholder (filename)"></div>
            <div class="col-12"><textarea name="descripcion" class="form-control" placeholder="Descripción"></textarea></div>
            <div class="col-md-4"><input name="duracion" class="form-control" placeholder="Duración"></div>
            <div class="col-md-4"><input name="fecha_disponible" class="form-control" placeholder="Fecha disponible"></div>
            <div class="col-12"><button class="btn btn-primary" type="submit">Crear producto</button></div>
          </form>
          <div id="msg-product"></div>
          <div id="products-list"></div>
        </div>
      `;
    }

    async function loadProductsModule() {
      container.innerHTML = productsModuleHtml();
      document.getElementById('back-btn').addEventListener('click', () => {
        container.innerHTML = '';
        home.style.display = 'block';
      });
      document.getElementById('form-create-product').addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target).entries());
        const r = await api('create_product', data);
        const msg = document.getElementById('msg-product');
        if (r.success) {
          msg.innerHTML = '<div class="alert alert-success">Producto creado (ID: ' + r.id + ')</div>';
          e.target.reset();
          loadProductsList();
        } else {
          msg.innerHTML = '<div class="alert alert-danger">' + (r.message || 'Error') + '</div>';
        }
      });
      loadProductsList();
    }

    async function loadProductsList() {
      const r = await api('list_products');
      if (r.success) {
        const rows = r.products.map(p => `<tr><td>${p.id_tour}</td><td>${escapeHtml(p.nombre)}</td><td>$${parseFloat(p.precio).toFixed(2)}</td><td>${escapeHtml(p.imagen_placeholder||'')}</td></tr>`).join('');
        document.getElementById('products-list').innerHTML = `<table class="table table-sm"><thead><tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Imagen</th></tr></thead><tbody>${rows}</tbody></table>`;
      }
    }

    // Orders module
    function ordersModuleHtml() {
      return `
        <div>
          <button class="btn btn-link mb-2" id="back-btn">← Volver</button>
          <h4>Pedidos</h4>
          <div id="orders-list"></div>
        </div>
      `;
    }

    async function loadOrdersModule() {
      container.innerHTML = ordersModuleHtml();
      document.getElementById('back-btn').addEventListener('click', () => {
        container.innerHTML = '';
        home.style.display = 'block';
      });
      loadOrdersList();
    }

    async function loadOrdersList() {
      const r = await api('list_orders');
      if (r.success) {
        const rows = r.orders.map(o => `<tr><td>${o.id}</td><td>${o.user_id}</td><td>${escapeHtml(o.username||'')}</td><td>$${parseFloat(o.monto_total).toFixed(2)}</td><td>${o.cantidad_items}</td><td>${o.fecha_pedido}</td><td><pre style="max-width:300px">${escapeHtml(JSON.stringify(o.detalles))}</pre></td></tr>`).join('');
        document.getElementById('orders-list').innerHTML = `<table class="table table-sm"><thead><tr><th>ID</th><th>User ID</th><th>User</th><th>Total</th><th>#Items</th><th>Fecha</th><th>Detalles</th></tr></thead><tbody>${rows}</tbody></table>`;
      }
    }
  </script>
</body>

</html>