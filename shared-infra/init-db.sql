-- 1. Crear la base de datos
CREATE DATABASE IF NOT EXISTS officespace_db;
USE officespace_db;

-- 2. Tabla de Usuarios (Con roles obligatorios)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('ADMINISTRADOR', 'COLABORADOR') NOT NULL,
    activo TINYINT(1) DEFAULT 1
);

-- Insertamos los usuarios que IBM pide por defecto
INSERT INTO usuarios (email, password, rol) VALUES 
('admin@corporativoalpha.com', 'Admin123', 'ADMINISTRADOR'),
('carlos.mendez@corporativoalpha.com', 'User123', 'COLABORADOR'),
('ana.torres@corporativoalpha.com', 'User123', 'COLABORADOR');

-- 3. Tabla de Espacios (Equivalente a tus mesas/platillos)
CREATE TABLE espacios (
    id_espacio INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('SALA', 'DESK') NOT NULL,
    capacidad INT NOT NULL,
    recursos VARCHAR(255), -- Ej: "Proyector, Pizarrón, AC"
    piso VARCHAR(50),
    activo TINYINT(1) DEFAULT 1
);

-- Insertamos un par de espacios para tener datos con qué probar
INSERT INTO espacios (nombre, tipo, capacidad, recursos, piso) VALUES 
('Sala Creativa', 'SALA', 8, 'Proyector, Pantalla 65, AC', 'Piso 2'),
('Sala Ejecutiva', 'SALA', 4, 'TV, Teléfono IP', 'Piso 2'),
('Escritorio Ventana 1', 'DESK', 1, 'Monitor Extra', 'Piso 3'),
('Escritorio Silencio', 'DESK', 1, 'Ergonómico', 'Piso 3');

-- 4. Tabla de Reservas (El núcleo del sistema)
CREATE TABLE reservas (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_espacio INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    asistentes INT NOT NULL,
    estatus ENUM('Activa', 'Cancelada') DEFAULT 'Activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_espacio) REFERENCES espacios(id_espacio),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);
INSERT INTO espacios (nombre, tipo, capacidad, recursos, piso) VALUES 
('Sala de Consejo VIP', 'SALA', 15, 'Pantalla Interactiva 85 4K, Sistema de Audio Premium, AC', 'Piso 4'),
('Sala de Innovación', 'SALA', 10, 'Proyector Láser de Alta Gama, Pizarrón de Cristal', 'Piso 1');
-- Ampliación de la tabla de espacios para una demostración realista
INSERT INTO espacios (nombre, tipo, capacidad, recursos, piso) VALUES 
('Zona Abierta - Desk 01', 'DESK', 1, 'Monitor Estándar', 'Piso 3'),
('Zona Abierta - Desk 02', 'DESK', 1, 'Monitor Estándar', 'Piso 3'),
('Zona Abierta - Desk 03', 'DESK', 1, 'Ninguno', 'Piso 3'),
('Zona Abierta - Desk 04', 'DESK', 1, 'Ninguno', 'Piso 3'),
('Sala de Consejo VIP', 'SALA', 15, 'Pantalla Interactiva 85 4K, Audio Premium, AC', 'Piso 4'),
('Sala de Innovación', 'SALA', 10, 'Proyector Láser, Pizarrón de Cristal', 'Piso 1');