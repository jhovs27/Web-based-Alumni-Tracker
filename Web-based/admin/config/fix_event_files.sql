-- Fix alumni_events table structure for file uploads
-- This script ensures the poster_image and event_document columns exist and are properly configured

-- Check if table exists, if not create it
CREATE TABLE IF NOT EXISTS alumni_events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_title VARCHAR(255) NOT NULL,
    event_description TEXT,
    event_type ENUM('Reunion', 'Seminar', 'Webinar', 'Career Fair', 'Outreach', 'Other') NOT NULL,
    event_category ENUM('Academic', 'Social', 'Career', 'Other') NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    physical_address TEXT NULL,
    online_link VARCHAR(255) NULL,
    timezone VARCHAR(50) NOT NULL DEFAULT 'Asia/Manila',
    poster_image VARCHAR(255) NULL,
    event_document VARCHAR(255) NULL,
    registration_required BOOLEAN DEFAULT TRUE,
    max_attendees INT NULL,
    allow_guests BOOLEAN DEFAULT FALSE,
    auto_confirm BOOLEAN DEFAULT FALSE,
    contact_person VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20) NULL,
    visibility ENUM('public', 'private') DEFAULT 'public',
    status ENUM('Draft', 'Published', 'Cancelled', 'Completed') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add poster_image column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'alumni_events' 
     AND COLUMN_NAME = 'poster_image') = 0,
    'ALTER TABLE alumni_events ADD COLUMN poster_image VARCHAR(255) NULL AFTER timezone',
    'SELECT "poster_image column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add event_document column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'alumni_events' 
     AND COLUMN_NAME = 'event_document') = 0,
    'ALTER TABLE alumni_events ADD COLUMN event_document VARCHAR(255) NULL AFTER poster_image',
    'SELECT "event_document column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add timezone column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'alumni_events' 
     AND COLUMN_NAME = 'timezone') = 0,
    'ALTER TABLE alumni_events ADD COLUMN timezone VARCHAR(50) NOT NULL DEFAULT "Asia/Manila" AFTER online_link',
    'SELECT "timezone column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update status enum values
ALTER TABLE alumni_events MODIFY COLUMN status ENUM('Draft', 'Published', 'Cancelled', 'Completed') DEFAULT 'Draft';

-- Update visibility enum values
ALTER TABLE alumni_events MODIFY COLUMN visibility ENUM('public', 'private') DEFAULT 'public';

-- Show the current table structure
DESCRIBE alumni_events;

-- Show any existing events
SELECT id, event_title, poster_image, event_document, status FROM alumni_events LIMIT 10; 