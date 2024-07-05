

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL
);

INSERT INTO users (username, password, phone)
VALUES 
('admin', '$2a$12$qjcuKmzc5GLcBedri3C8beg3r/JMlrDdYyynyG8qo77MU1Rfd9sie', '123-456-7890');

CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL
);

CREATE TABLE incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE service_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id)
);