CREATE DATABASE habitos_db;

USE habitos_db;

-- Creo las tablas que considero pertinentes --
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    correo VARCHAR(100) UNIQUE, -- Para evitar correos repetidos, de igual manera se verificara en el php --
    password VARCHAR(255),
    id_rol INT,
    fecha_registro DATETIME,
    FOREIGN KEY (id_rol) REFERENCES roles(id)
) ENGINE = InnoDB;

-- Estos tokens son necesarios para implementar la recuperacion de contraseña por correo --
CREATE TABLE recuperacion_password (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    token VARCHAR(255),
    expira_token DATETIME,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
    ON DELETE CASCADE
) ENGINE = InnoDB;

-- Las frecuencias comunes, como diaria, semanal, mensual y personalizada --
CREATE TABLE frecuencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL
) ENGINE = InnoDB;

-- Esta tabla entra en funcionamiento cada que se selecciona el ID = 4 (personalizada) de la tabla frecuencia. En esta tabla están todos los días de la semana.
CREATE TABLE dias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE habitos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    nombre VARCHAR(100),
    descripcion TEXT,
    id_frecuencia INT,
    cada_cuantos_dias INT DEFAULT NULL, -- En caso de que no le sirvan los días de la semana se usa esto, para repetir el habito según la cantidad de días que indique --
    fecha_creacion DATE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
    ON DELETE CASCADE,
    FOREIGN KEY (id_frecuencia) REFERENCES frecuencias(id)
) ENGINE = InnoDB;

-- Esta es una tabla intermediaria entre dias y habitos, se va a usar para cuando se se quiera hacer un habito de frecuencia personalizada, como lunes y miercoles, por dar un ejemplo --
CREATE TABLE dias_habito (
    id_habito INT,
    id_dia INT,
    PRIMARY KEY (id_habito, id_dia),
    FOREIGN KEY (id_habito) REFERENCES habitos(id)
    ON DELETE CASCADE,
    FOREIGN KEY (id_dia) REFERENCES dias(id)
) ENGINE = InnoDB;

-- Para poder llevar un seguimiento (historial) en los habitos, ademas de ver si se completo o no --
CREATE TABLE seguimiento_habito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_habito INT,
    fecha DATE,
    cumplido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_habito) REFERENCES habitos(id)
    ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE metas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_habito INT,
    cantidad_objetivo INT,
    periodo ENUM('diario', 'semanal', 'mensual'),
    FOREIGN KEY (id_habito) REFERENCES habitos(id)
    ON DELETE CASCADE
) ENGINE = InnoDB;

-- Inserto los datos necesarios por el momento para el funcionamiento de la pagina --
INSERT INTO roles (nombre) VALUES
('Administrador'),
('Usuario');

-- Inserto los días de la semana -- 
INSERT INTO dias (nombre) VALUES
('Lunes'),
('Martes'),
('Miercoles'),
('Jueves'),
('Viernes'),
('Sabado'),
('Domingo');

-- Inserto las frecuencias -- 
INSERT INTO frecuencias (nombre) VALUES
('Diaria'),
('Semanal'),
('Mensual'),
('Personalizada');