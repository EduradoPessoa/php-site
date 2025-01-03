-- Add status column to products table if it doesn't exist
ALTER TABLE products ADD COLUMN status INTEGER DEFAULT 1;
