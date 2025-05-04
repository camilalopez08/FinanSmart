-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS finansmart;
USE finansmart;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME DEFAULT NULL
);

-- Tabla de transacciones
CREATE TABLE IF NOT EXISTS transacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo ENUM('ingreso', 'gasto') NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    descripcion TEXT,
    fecha DATE NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de presupuestos
CREATE TABLE IF NOT EXISTS presupuestos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    monto_limite DECIMAL(10, 2) NOT NULL,
    periodo ENUM('semanal', 'mensual', 'trimestral', 'anual') NOT NULL DEFAULT 'mensual',
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_presupuesto (id_usuario, categoria, periodo, fecha_inicio)
);

-- Tabla de metas de ahorro
CREATE TABLE IF NOT EXISTS metas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    monto_objetivo DECIMAL(10, 2) NOT NULL,
    monto_actual DECIMAL(10, 2) NOT NULL DEFAULT 0,
    fecha_limite DATE NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de aportes a metas
CREATE TABLE IF NOT EXISTS aportes_metas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_meta INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    fecha DATE NOT NULL,
    nota TEXT,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_meta) REFERENCES metas(id) ON DELETE CASCADE
);

-- Tabla de categorías predefinidas
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    tipo ENUM('ingreso', 'gasto') NOT NULL,
    icono VARCHAR(50) DEFAULT NULL,
    color VARCHAR(20) DEFAULT NULL,
    es_predeterminada BOOLEAN DEFAULT FALSE
);

-- Insertar categorías predefinidas para ingresos
INSERT INTO categorias (nombre, tipo, icono, color, es_predeterminada) VALUES
('Salario', 'ingreso', 'fa-money-bill-wave', '#28a745', TRUE),
('Freelance', 'ingreso', 'fa-laptop', '#17a2b8', TRUE),
('Inversiones', 'ingreso', 'fa-chart-line', '#6610f2', TRUE),
('Regalos', 'ingreso', 'fa-gift', '#e83e8c', TRUE),
('Otros Ingresos', 'ingreso', 'fa-plus-circle', '#6c757d', TRUE);

-- Insertar categorías predefinidas para gastos
INSERT INTO categorias (nombre, tipo, icono, color, es_predeterminada) VALUES
('Alimentación', 'gasto', 'fa-utensils', '#dc3545', TRUE),
('Transporte', 'gasto', 'fa-car', '#fd7e14', TRUE),
('Entretenimiento', 'gasto', 'fa-film', '#ffc107', TRUE),
('Servicios', 'gasto', 'fa-home', '#20c997', TRUE),
('Salud', 'gasto', 'fa-heartbeat', '#e83e8c', TRUE),
('Educación', 'gasto', 'fa-graduation-cap', '#6f42c1', TRUE),
('Otros Gastos', 'gasto', 'fa-shopping-bag', '#6c757d', TRUE);

-- Tabla de configuración del usuario
CREATE TABLE IF NOT EXISTS configuracion_usuario (
    id_usuario INT PRIMARY KEY,
    moneda VARCHAR(10) DEFAULT '$',
    tema VARCHAR(20) DEFAULT 'light',
    notificaciones_email BOOLEAN DEFAULT TRUE,
    notificaciones_presupuesto BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Índices para optimizar consultas
CREATE INDEX idx_transacciones_usuario_fecha ON transacciones(id_usuario, fecha);
CREATE INDEX idx_transacciones_usuario_tipo ON transacciones(id_usuario, tipo);
CREATE INDEX idx_transacciones_usuario_categoria ON transacciones(id_usuario, categoria);
CREATE INDEX idx_presupuestos_usuario ON presupuestos(id_usuario);
CREATE INDEX idx_metas_usuario ON metas(id_usuario);
