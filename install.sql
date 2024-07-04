CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') NOT NULL,
    phone_number VARCHAR(20),
    email VARCHAR(255) NOT NULL UNIQUE
);

INSERT INTO users (username, password, role, phone_number, email)
VALUES 
('admin', '$2a$12$qjcuKmzc5GLcBedri3C8beg3r/JMlrDdYyynyG8qo77MU1Rfd9sie', 'admin', '123-456-7890', 'admin@example.com'),
('editor', '$2a$12$yaWrsgSmNv.dtjQd70ckUuNYs2rwXIjdFmuBC2acophV//pY5aFIK', 'editor', '234-567-8901', 'editor@example.com');

CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(255) NOT NULL,
    status ENUM('online', 'offline', 'maintenance', 'degraded') DEFAULT 'online',
    last_checked DATETIME DEFAULT NULL
);

CREATE TABLE uptimes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT,
    status ENUM('online', 'offline', 'degraded'),
    checked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id)
);

CREATE TABLE incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT,
    description TEXT NOT NULL,
    status ENUM('open', 'resolved') DEFAULT 'open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id)
);