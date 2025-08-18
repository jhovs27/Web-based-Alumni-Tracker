-- Admin Access Code Management Tables

-- Table for storing admin access codes
CREATE TABLE IF NOT EXISTS `admin_access_codes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `code_hash` varchar(255) NOT NULL,
    `code_plain` varchar(100) NOT NULL,
    `is_enabled` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_by` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for logging admin access code activities
CREATE TABLE IF NOT EXISTS `admin_access_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `admin_id` int(11) NOT NULL,
    `action` varchar(255) NOT NULL,
    `action_time` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `admin_id` (`admin_id`),
    KEY `action_time` (`action_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin access code
INSERT INTO `admin_access_codes` (`code_hash`, `code_plain`, `is_enabled`, `updated_by`) 
VALUES (
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- hash for 'SLSU-HC_ADMIN_2025'
    'SLSU-HC_ADMIN_2025',
    1,
    1
) ON DUPLICATE KEY UPDATE `id` = `id`;

-- Insert sample log entry
INSERT INTO `admin_access_logs` (`admin_id`, `action`, `action_time`) 
VALUES (1, 'Initial admin access code created', NOW()); 