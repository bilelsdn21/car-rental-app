-- Add duration fields to cars table
ALTER TABLE cars
ADD COLUMN min_rental_days INT NOT NULL DEFAULT 1,
ADD COLUMN max_rental_days INT NOT NULL DEFAULT 30;

-- Update existing cars with default values
UPDATE cars SET min_rental_days = 1, max_rental_days = 30;
