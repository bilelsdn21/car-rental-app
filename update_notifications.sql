-- Add booking status confirmation column
ALTER TABLE bookings ADD COLUMN confirmed BOOLEAN DEFAULT FALSE;

-- Add notification types enum
ALTER TABLE notifications ADD COLUMN type ENUM('booking_request', 'booking_confirmed', 'booking_rejected', 'booking_cancelled') DEFAULT 'booking_request';

-- Add notification settings for users
INSERT INTO admin_settings (setting_key, setting_value) VALUES
('notification_email_enabled', 'true'),
('notification_email_template', 'Dear [user_name],\n\n[notification_message]\n\nBest regards,\nRCar Team'),
('notification_sms_enabled', 'false');
