-- Rental Site Database Structure and Updates
-- Created: 2025-04-21

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

-- Cars table with duration fields
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
    min_rental_days INT NOT NULL DEFAULT 1,
    max_rental_days INT NOT NULL DEFAULT 30,
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

-- User Settings table
CREATE TABLE IF NOT EXISTS user_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_setting (user_id, setting_key)
);

-- Admin Settings table
CREATE TABLE IF NOT EXISTS admin_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Agency Profiles table
CREATE TABLE IF NOT EXISTS agency_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    agency_id INT NOT NULL UNIQUE,
    agency_name VARCHAR(200) NOT NULL,
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    address TEXT,
    business_hours TEXT,
    logo_url VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agency_id) REFERENCES users(id)
);

-- Insert default admin settings
INSERT INTO admin_settings (setting_key, setting_value) VALUES
('site_title', 'RCar Rental'),
('site_description', 'Your trusted car rental platform'),
('minimum_rental_days', '1'),
('tax_rate', '0.15'),
('currency', 'USD'),
('email_from', 'noreply@rcar.com');

-- Insert sample data
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@rcar.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Car Agency 1', 'agency1@rcar.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency'),
('Regular User', 'user@rcar.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert sample cars with duration fields
INSERT INTO cars (title, make, model, year, description, price_per_day, location, image_url, posted_by, min_rental_days, max_rental_days) VALUES
('2023 Toyota Camry SE', 'Toyota', 'Camry', 2023, 'Comfortable sedan with excellent fuel economy', 75.00, 'New York, NY', 'assets/images/cars/camry.jpg', 2, 1, 30),
('2023 Honda CR-V EX', 'Honda', 'CR-V', 2023, 'Spacious SUV perfect for family trips', 85.00, 'Los Angeles, CA', 'assets/images/cars/crv.jpg', 2, 1, 30),
('2023 Tesla Model 3', 'Tesla', 'Model 3', 2023, 'Electric vehicle with autopilot features', 120.00, 'San Francisco, CA', 'assets/images/cars/tesla.jpg', 2, 1, 30);

-- Insert sample bookings
INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price, status) VALUES
(3, 1, '2024-04-20', '2024-04-25', 375.00, 'confirmed'),
(3, 2, '2024-05-01', '2024-05-03', 170.00, 'pending');
