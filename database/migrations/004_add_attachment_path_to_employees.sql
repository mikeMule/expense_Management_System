-- Migration: Add attachment_path to employees table
-- Date: 2024-12-19

ALTER TABLE employees ADD COLUMN attachment_path VARCHAR(255) DEFAULT NULL AFTER hire_date; 