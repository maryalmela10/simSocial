-- Crear la base de datos
CREATE DATABASE red_social;
USE red_social;

-- Tabla de usuarios (modificada)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(255) NOT NULL,
    rol VARCHAR(50) DEFAULT 'ROLE_USER',
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de posts
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contenido TEXT NOT NULL,
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de comentarios
CREATE TABLE comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contenido TEXT NOT NULL,
    fecha_comentario TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    post_id INT NOT NULL,
    usuario_id INT NOT NULL,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de amistades (relación entre usuarios)
CREATE TABLE amistades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_a_id INT NOT NULL,
    usuario_b_id INT NOT NULL,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'aceptado', 'rechazado') DEFAULT 'pendiente',
    FOREIGN KEY (usuario_a_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_b_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla para almacenar la foto de perfil (si un usuario tiene foto)
CREATE TABLE fotos_perfil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    url_imagen VARCHAR(255) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Insertar un usuario regular
INSERT INTO usuarios (clave, nombre, apellido, email)
VALUES ('1234', 'pablo', 'pozo', 'pablo@email.com');

-- Insertar un usuario administrador
INSERT INTO usuarios (clave, rol, nombre, apellido, email)
VALUES ('1234', 'ROLE_ADMIN', 'mary', 'almela', 'mary@email.com');

-- Tabla para almacenar la foto de perfil (si un usuario tiene foto)
CREATE TABLE reacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    post_id INT NOT NULL,
    tipo ENUM('me_gusta', 'me_divierte') NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (post_id) REFERENCES posts(id),
    UNIQUE (usuario_id, post_id, tipo) -- Evita que un usuario reaccione dos veces con el mismo tipo
);
