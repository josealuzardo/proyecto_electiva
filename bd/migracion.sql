CREATE DATABASE vzlaaventura_db;

USE vzlaaventura_db;

SELECT * FROM pedidos;
CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    monto_total DECIMAL(10, 2) NOT NULL,
    cantidad_items INT NOT NULL,  -- ¡Nuevo campo!
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tours (
    id_tour VARCHAR(50) PRIMARY KEY, -- Usaremos un ID legible (e.g., 'mochima')
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    duracion VARCHAR(50),
    fecha_disponible VARCHAR(50),
    imagen_placeholder VARCHAR(100)
);

-- Datos de ejemplo (INSERTs)
INSERT INTO tours (id_tour, nombre, descripcion, precio, duracion, fecha_disponible, imagen_placeholder) VALUES 
('mochima', 'Full Day Mochima 🏝️', 'Disfruta de las aguas cristalinas del Parque Nacional Mochima. Incluye traslado marítimo, almuerzo ligero y equipo de snorkel.', 50.00, '12 horas (5:00 AM - 5:00 PM)', 'Todos los sábados', 'placeholder-mochima.jpg'),
('salto-angel', 'Canaima Mágico (Vuelo) ✈️', 'Vuelo panorámico sobre el tepuy Auyantepui y visita a un campamento cerca del Salto Ángel (el salto más alto del mundo). Incluye refrigerio.', 120.00, '10 horas (6:00 AM - 4:00 PM)', 'Próximo Martes y Jueves', 'placeholder-salto.jpg'),
('tovar', 'Día de Invierno en Tovar 🍓', 'Viaje a la pintoresca Colonia Tovar para disfrutar de su arquitectura alemana, clima frío y deliciosas fresas con crema.', 35.00, '8 horas (7:00 AM - 3:00 PM)', 'Todos los domingos', 'placeholder-tovar.jpg');

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT * FROM users;

SHOW TABLES;