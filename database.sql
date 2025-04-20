-- Create database if not exists
CREATE DATABASE IF NOT EXISTS rental_site;
USE rental_site;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'agency', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cars table
CREATE TABLE IF NOT EXISTS cars (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    make VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    description TEXT,
    price_per_day DECIMAL(10, 2) NOT NULL,
    location VARCHAR(200) NOT NULL,
    image_url VARCHAR(255),
    category VARCHAR(50),
    features TEXT,
    status ENUM('available', 'rented', 'maintenance') NOT NULL DEFAULT 'available',
    posted_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(id)
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (car_id) REFERENCES cars(id)
);

-- Insert sample data
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@rcar.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Car Agency 1', 'agency1@rcar.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency'),
('Regular User', 'user@rcar.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert sample cars
INSERT INTO cars (title, brand, model, year, description, price, location, image, posted_by) VALUES
('2023 Toyota Camry SE', 'Toyota', 'Camry', 2023, 'Comfortable sedan with excellent fuel economy', 75.00, 'New York, NY', 'assets/images/cars/camry.jpg', 2),
('2023 Honda CR-V EX', 'Honda', 'CR-V', 2023, 'Spacious SUV perfect for family trips', 85.00, 'Los Angeles, CA', 'assets/images/cars/crv.jpg', 2),
('2023 Tesla Model 3', 'Tesla', 'Model 3', 2023, 'Electric vehicle with autopilot features', 120.00, 'San Francisco, CA', 'assets/images/cars/tesla.jpg', 2);

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    reference_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert sample bookings
INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price, status) VALUES
(3, 1, '2024-04-20', '2024-04-25', 375.00, 'confirmed'),
(3, 2, '2024-05-01', '2024-05-03', 170.00, 'pending'); 