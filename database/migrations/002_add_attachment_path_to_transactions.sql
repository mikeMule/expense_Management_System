-- Add attachment_path to transactions table
ALTER TABLE transactions
ADD COLUMN attachment_path VARCHAR(255) DEFAULT NULL; 