-- Add settings table
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

-- Add admin settings table
CREATE TABLE IF NOT EXISTS admin_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add agency profile table
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

-- Add default admin settings
INSERT INTO admin_settings (setting_key, setting_value) VALUES
('site_title', 'RCar Rental'),
('site_description', 'Your trusted car rental platform'),
('minimum_rental_days', '1'),
('tax_rate', '0.15'),
('currency', 'USD'),
('email_from', 'noreply@rcar.com');
