CREATE TABLE INSTRUMENTOS (
    ID_INSTRUMENTO INT PRIMARY KEY AUTO_INCREMENT,
    NOMBRE VARCHAR(50) NOT NULL COMMENT 'Ej: Guitarra, Trompeta, Piano',
    MARCA VARCHAR(50) NOT NULL,
    MODELO VARCHAR(50),
    CATEGORIA VARCHAR(30) COMMENT 'Ej: Cuerda, Viento, Percusión',
    MATERIAL VARCHAR(50) COMMENT 'Ej: Madera de Arce, Latón, Fibra',
    PRECIO DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    STOCK INT NOT NULL DEFAULT 0,
    FECHA_INGRESO DATE
);

CREATE TABLE CLIENTES (
    ID_CLIENTE INT PRIMARY KEY AUTO_INCREMENT,
    DNI VARCHAR(9) NOT NULL,
    NOMBRE VARCHAR(50) NOT NULL,
    APELLIDOS VARCHAR(100) NOT NULL,
    EMAIL VARCHAR(100),
    TELEFONO CHAR(9) NOT NULL,
    DIRECCION VARCHAR(150),
    FECHA_REGISTRO DATE
);

CREATE TABLE RESERVAS (
    ID_RESERVA INT PRIMARY KEY AUTO_INCREMENT,
    ID_CLIENTE INT NOT NULL,
    ID_INSTRUMENTO INT NOT NULL,
    FECHA_RESERVA DATE,
    ADELANTO DECIMAL(10, 2) DEFAULT 0,
    ESTADO VARCHAR(20) DEFAULT 'Pendiente'
);

ALTER TABLE RESERVAS
ADD CONSTRAINT FK_RELACION1 FOREIGN KEY (ID_CLIENTE) REFERENCES CLIENTES (ID_CLIENTE);

ALTER TABLE RESERVAS
ADD CONSTRAINT FK_RELACION2 FOREIGN KEY (ID_INSTRUMENTO) REFERENCES INSTRUMENTOS (ID_INSTRUMENTO);

INSERT INTO
    INSTRUMENTOS (
        NOMBRE,
        MARCA,
        MODELO,
        CATEGORIA,
        MATERIAL,
        PRECIO,
        STOCK,
        FECHA_INGRESO
    )
VALUES (
        'Guitarra Eléctrica',
        'Fender',
        'Stratocaster',
        'Cuerda',
        'Aliso',
        1200.50,
        5,
        '2024-01-15'
    ),
    (
        'Trompeta',
        'Yamaha',
        'YTR-2330',
        'Viento',
        'Latón',
        450.00,
        3,
        '2024-02-10'
    ),
    (
        'Batería Acústica',
        'Pearl',
        'Export',
        'Percusión',
        'Chopo',
        950.00,
        2,
        '2024-03-01'
    ),
    (
        'Piano Digital',
        'Roland',
        'FP-30X',
        'Cuerda/Tecla',
        'Plástico/Metal',
        700.00,
        0,
        '2024-01-20'
    );

CREATE TABLE USUARIO (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    USERNAME VARCHAR(50) UNIQUE NOT NULL,
    PASSWORD_HASH VARCHAR(200) NOT NULL,
    ESTADO TINYINT(1) DEFAULT 1
);

INSERT INTO
    USUARIO (USERNAME, PASSWORD_HASH)
VALUES (
        'James',
        '$2y$10$P7M1.5uXW7kE9Qn7Gz6iueH8yL3vXqf8R5k9O1m2n3p4q5r6s7t8u'
    ), -- la constraseña es james --
    (
        'Rodrigo',
        '$2y$10$7R08NlGfG1XyYfA8J5f8IuV9KzY0G6M6O7Q8R9S0T1U2V3W4X5Y6Z'
    ), -- la constraseña es Rodrigo --
    (
        'Andre',
        '$2y$10$mC.7xV6X.P9fGzE8hR5iOuaK1jL2mN3oP4qR5sT6uV7wX8yZ9aB1C'
    ), -- la constraseña es Andre --
    (
        'Angel',
        '$2y$10$K9pL2mN3oP4qR5sT6uV7wX8yZ9aB1C2dE3fG4hI5jK6lM7n8o9p0q'
    );