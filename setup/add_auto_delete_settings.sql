-- Migration: Add auto-delete settings for old recordings
-- This script adds settings for automatically deleting recordings older than X months

USE radiobeere;

-- Add auto_delete_enabled setting (default: disabled)
INSERT INTO settings (name, wert) VALUES ('auto_delete_enabled', '0')
ON DUPLICATE KEY UPDATE name=name;

-- Add auto_delete_months setting (default: 3 months)
INSERT INTO settings (name, wert) VALUES ('auto_delete_months', '3')
ON DUPLICATE KEY UPDATE name=name;
