-- =========================================================
-- BASE DE DATOS: CONTROL HORARIO
-- =========================================================

DROP DATABASE IF EXISTS control_horario;
CREATE DATABASE control_horario CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE control_horario;

-- =========================================================
-- TABLA: ROLES
-- =========================================================

CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (nombre) VALUES 
('admin'),
('empleado');

-- =========================================================
-- TABLA: USERS
-- =========================================================

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_users_roles 
        FOREIGN KEY (role_id) 
        REFERENCES roles(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- =========================================================
-- TABLA: FICHAJES (HISTÓRICO INMUTABLE)
-- =========================================================

CREATE TABLE fichajes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tipo ENUM('entrada','salida','inicio_descanso','fin_descanso') NOT NULL,
    fecha_hora DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_fichajes_users 
        FOREIGN KEY (user_id) 
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- =========================================================
-- TABLA: RESUMEN DIARIO (OPTIMIZACIÓN)
-- =========================================================

CREATE TABLE resumen_diario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    fecha DATE NOT NULL,
    horas_trabajadas DECIMAL(5,2) DEFAULT 0,
    horas_descanso DECIMAL(5,2) DEFAULT 0,
    horas_extra DECIMAL(5,2) DEFAULT 0,
    horas_fuera_oficina DECIMAL(5,2) DEFAULT 0,

    UNIQUE KEY unique_user_fecha (user_id, fecha),

    CONSTRAINT fk_resumen_users 
        FOREIGN KEY (user_id) 
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- =========================================================
-- TABLA: CONFIGURACION EMPRESA
-- =========================================================

CREATE TABLE configuracion_empresa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    hora_inicio_jornada TIME NOT NULL,
    hora_fin_jornada TIME NOT NULL,
    horas_jornada_estandar DECIMAL(4,2) DEFAULT 8.00
);

INSERT INTO configuracion_empresa 
(hora_inicio_jornada, hora_fin_jornada, horas_jornada_estandar)
VALUES 
('08:00:00', '16:00:00', 8.00);

-- =========================================================
-- TABLA: EXPORTACIONES (OPCIONAL)
-- =========================================================

CREATE TABLE exportaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    filtro_inicio DATE,
    filtro_fin DATE,

    CONSTRAINT fk_export_admin
        FOREIGN KEY (admin_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE estado_usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    tipo_ultimo ENUM('entrada','salida','inicio_descanso','fin_descanso','ninguno') DEFAULT 'ninguno',
    segundos_actuales INT DEFAULT 0,
    actualizado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_estado_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- =========================================================
-- ÍNDICES PARA RENDIMIENTO
-- =========================================================

CREATE INDEX idx_fichajes_user ON fichajes(user_id);
CREATE INDEX idx_fichajes_fecha ON fichajes(fecha_hora);
CREATE INDEX idx_resumen_fecha ON resumen_diario(fecha);

-- =========================================================
-- USUARIO ADMIN DE PRUEBA
-- password: admin123 (DEBERÁS GENERAR HASH REAL EN PHP)
-- =========================================================

INSERT INTO users (nombre, email, password, role_id)
VALUES ('Administrador', 'admin@empresa.com', '$2y$10$EjemploHashCambiarEnProduccion', 1);

-- =========================================================
-- FIN DEL SCRIPT
-- =========================================================