-- Complete Alumni Table Update Script
-- This script updates the alumni table with all required fields for the refined registration system

-- First, ensure the alumni table exists with basic structure
CREATE TABLE IF NOT EXISTS alumni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumni_id VARCHAR(20) NOT NULL UNIQUE,
    fullname VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(30),
    address VARCHAR(255),
    password_hash VARCHAR(255),
    program VARCHAR(100),
    year_graduated INT,
    employment_status VARCHAR(30),
    company_name VARCHAR(100),
    position VARCHAR(100),
    business_name VARCHAR(100),
    business_location VARCHAR(100),
    study_level VARCHAR(50),
    study_type VARCHAR(100),
    student_no VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Add new columns for detailed personal information
ALTER TABLE alumni 
ADD COLUMN IF NOT EXISTS last_name VARCHAR(100) AFTER fullname,
ADD COLUMN IF NOT EXISTS first_name VARCHAR(100) AFTER last_name,
ADD COLUMN IF NOT EXISTS middle_name VARCHAR(100) AFTER first_name,
ADD COLUMN IF NOT EXISTS sex VARCHAR(10) AFTER middle_name,
ADD COLUMN IF NOT EXISTS birthdate DATE AFTER sex,
ADD COLUMN IF NOT EXISTS birthplace VARCHAR(255) AFTER birthdate,
ADD COLUMN IF NOT EXISTS course VARCHAR(100) AFTER birthplace,
ADD COLUMN IF NOT EXISTS date_graduated DATE AFTER course,
ADD COLUMN IF NOT EXISTS academic_year VARCHAR(20) AFTER date_graduated,
ADD COLUMN IF NOT EXISTS civil_status VARCHAR(20) AFTER academic_year;

-- Add employment-related columns
ALTER TABLE alumni 
ADD COLUMN IF NOT EXISTS current_employment_status VARCHAR(50) AFTER employment_status,
ADD COLUMN IF NOT EXISTS job_title VARCHAR(100) AFTER current_employment_status,
ADD COLUMN IF NOT EXISTS company_address VARCHAR(255) AFTER company_name,
ADD COLUMN IF NOT EXISTS location VARCHAR(20) AFTER company_address,
ADD COLUMN IF NOT EXISTS industry_type VARCHAR(50) AFTER location,
ADD COLUMN IF NOT EXISTS employment_from DATE AFTER industry_type,
ADD COLUMN IF NOT EXISTS employment_to DATE AFTER employment_from,
ADD COLUMN IF NOT EXISTS job_related_to_degree VARCHAR(5) AFTER employment_to,
ADD COLUMN IF NOT EXISTS employment_proof_document VARCHAR(255) AFTER job_related_to_degree;

-- Add unemployed-related columns
ALTER TABLE alumni 
ADD COLUMN IF NOT EXISTS current_status VARCHAR(50) AFTER employment_proof_document,
ADD COLUMN IF NOT EXISTS engaged_in_applications VARCHAR(5) AFTER current_status;

-- Add self-employed-related columns
ALTER TABLE alumni 
ADD COLUMN IF NOT EXISTS business_type VARCHAR(50) AFTER business_name,
ADD COLUMN IF NOT EXISTS business_start_date DATE AFTER business_type,
ADD COLUMN IF NOT EXISTS business_address VARCHAR(255) AFTER business_start_date,
ADD COLUMN IF NOT EXISTS business_related_to_degree VARCHAR(5) AFTER business_address,
ADD COLUMN IF NOT EXISTS business_permit_document VARCHAR(255) AFTER business_related_to_degree;

-- Add further studying-related columns
ALTER TABLE alumni 
ADD COLUMN IF NOT EXISTS educational_level VARCHAR(50) AFTER business_permit_document,
ADD COLUMN IF NOT EXISTS school_institution VARCHAR(255) AFTER educational_level,
ADD COLUMN IF NOT EXISTS program_degree VARCHAR(255) AFTER school_institution;

-- Add payment-related columns
ALTER TABLE alumni 
ADD COLUMN IF NOT EXISTS payment_method ENUM('gcash', 'cash_on_hand') AFTER program_degree,
ADD COLUMN IF NOT EXISTS gcash_name VARCHAR(100) AFTER payment_method,
ADD COLUMN IF NOT EXISTS gcash_number VARCHAR(20) AFTER gcash_name,
ADD COLUMN IF NOT EXISTS payment_status ENUM('pending', 'paid', 'verified') DEFAULT 'pending' AFTER gcash_number,
ADD COLUMN IF NOT EXISTS payment_date DATETIME NULL AFTER payment_status,
ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(100) AFTER payment_date;

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_alumni_id ON alumni(alumni_id);
CREATE INDEX IF NOT EXISTS idx_email ON alumni(email);
CREATE INDEX IF NOT EXISTS idx_employment_status ON alumni(employment_status);
CREATE INDEX IF NOT EXISTS idx_payment_status ON alumni(payment_status);
CREATE INDEX IF NOT EXISTS idx_payment_method ON alumni(payment_method);
CREATE INDEX IF NOT EXISTS idx_employment_proof ON alumni(employment_proof_document);
CREATE INDEX IF NOT EXISTS idx_business_permit ON alumni(business_permit_document);

-- Insert sample alumni data for testing (if table is empty)
INSERT IGNORE INTO alumni (
    alumni_id, fullname, last_name, first_name, middle_name, sex, birthdate, birthplace,
    email, phone, course, date_graduated, academic_year, program, year_graduated
) VALUES 
('2025-001', 'Santos, Juan Dela Cruz', 'Santos', 'Juan', 'Dela Cruz', 'M', '1995-03-15', 'Hinunangan, Southern Leyte',
 'juan.santos@example.com', '09123456789', 'Bachelor of Science in Information Technology', '2025-03-20', '2024-2025',
 'Bachelor of Science in Information Technology', 2025),
('2025-002', 'Garcia, Maria Santos', 'Garcia', 'Maria', 'Santos', 'F', '1996-07-22', 'Sogod, Southern Leyte',
 'maria.garcia@example.com', '09234567890', 'Bachelor of Science in Business Administration', '2025-03-20', '2024-2025',
 'Bachelor of Science in Business Administration', 2025),
('2025-003', 'Reyes, Pedro Martinez', 'Reyes', 'Pedro', 'Martinez', 'M', '1994-11-08', 'Maasin City, Southern Leyte',
 'pedro.reyes@example.com', '09345678901', 'Bachelor of Science in Education', '2025-03-20', '2024-2025',
 'Bachelor of Science in Education', 2025);

-- Create admin access codes table if it doesn't exist
CREATE TABLE IF NOT EXISTS admin_access_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code_hash VARCHAR(255) NOT NULL,
    code_plain VARCHAR(100) NOT NULL,
    is_enabled BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT DEFAULT NULL
);

-- Insert default admin access code
INSERT IGNORE INTO admin_access_codes (code_hash, code_plain, is_enabled, updated_by) 
VALUES (
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- hash for 'SLSU-HC_ADMIN_2025'
    'SLSU-HC_ADMIN_2025',
    1,
    1
);

-- Create admin access logs table
CREATE TABLE IF NOT EXISTS admin_access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    action_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX admin_id (admin_id),
    INDEX action_time (action_time)
);

-- Insert sample log entry
INSERT IGNORE INTO admin_access_logs (admin_id, action, action_time) 
VALUES (1, 'Initial admin access code created', NOW());

SELECT 'Alumni table structure updated successfully!' as message; 