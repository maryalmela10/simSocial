-- Crear la base de datos
CREATE DATABASE red_social;
USE red_social;

-- Tabla de usuarios (modificada)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(50) DEFAULT 'ROLE_USER',
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE usuarios
ADD COLUMN fecha_nacimiento DATE,
ADD COLUMN localidad VARCHAR(100),
ADD COLUMN biografia TEXT,
ADD COLUMN activacion_token VARCHAR(255),
ADD COLUMN verificado BOOLEAN DEFAULT FALSE;

-- -- Renombrar la columna 'clave' a 'password'
-- ALTER TABLE usuarios CHANGE COLUMN clave password VARCHAR(255) NOT NULL;

-- Tabla de posts
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contenido TEXT NOT NULL,
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NOT NULL,
    likes int DEFAULT 0,
    dislikes int DEFAULT 0
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- -- Columnas de likes en post
-- ALTER TABLE posts ADD COLUMN likes INT DEFAULT 0;
-- ALTER TABLE posts ADD COLUMN dislikes INT DEFAULT 0;

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

-- Tabla para almacenar la foto de perfil
CREATE TABLE fotos_perfil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    url_imagen VARCHAR(255) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla para almacenar las reacciones
CREATE TABLE reacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    post_id INT NOT NULL,
    tipo ENUM('me_gusta', 'me_divierte') NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (post_id) REFERENCES posts(id),
    UNIQUE (usuario_id, post_id, tipo)
);

-- Tabla para almacenar las fotos de post
    CREATE TABLE foto_post (
    id int AUTO_INCREMENT PRIMARY KEY,
    post_id  int NOT NULL,
    urlImagen  varchar(255) NOT NULL
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    );


-- Insertar usuarios
INSERT INTO usuarios (password, nombre, apellido, email)
VALUES ('1234', 'pablo', 'pozo', 'pablo@email.com');

INSERT INTO usuarios (password, rol, nombre, apellido, email)
VALUES ('1234', 'ROLE_ADMIN', 'mary', 'almela', 'mary@email.com');

INSERT INTO usuarios (password, nombre, apellido, email)
VALUES ('1234', 'lucas', 'fernandez', 'lucas@email.com');

-- Insertar posts
INSERT INTO posts (contenido, usuario_id) VALUES ('Este es mi primer post!', 1);
INSERT INTO posts (contenido, usuario_id) VALUES ('Hola a todos, este es un nuevo post.', 2);
INSERT INTO posts (contenido, usuario_id) VALUES ('Me encanta esta red social!', 3);

-- Insertar comentarios
INSERT INTO comentarios (contenido, post_id, usuario_id) VALUES ('¡Muy buen post!', 1, 2);
INSERT INTO comentarios (contenido, post_id, usuario_id) VALUES ('Me gusta tu opinión.', 2, 3);
INSERT INTO comentarios (contenido, post_id, usuario_id) VALUES ('Interesante perspectiva.', 3, 1);

-- Insertar reacciones
INSERT INTO reacciones (usuario_id, post_id, tipo) VALUES (1, 2, 'me_gusta');
INSERT INTO reacciones (usuario_id, post_id, tipo) VALUES (2, 3, 'me_divierte');
INSERT INTO reacciones (usuario_id, post_id, tipo) VALUES (3, 1, 'me_gusta');
