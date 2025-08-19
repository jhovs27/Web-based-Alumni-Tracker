-- Update alumni table structure to support new registration fields
-- Run this script to add the new columns to your existing alumni table

ALTER TABLE alumni 
ADD COLUMN last_name VARCHAR(100) AFTER fullname,
ADD COLUMN first_name VARCHAR(100) AFTER last_name,
ADD COLUMN middle_name VARCHAR(100) AFTER first_name,
ADD COLUMN sex VARCHAR(10) AFTER middle_name,
ADD COLUMN birthdate DATE AFTER sex,
ADD COLUMN birthplace VARCHAR(255) AFTER birthdate,
ADD COLUMN course VARCHAR(100) AFTER birthplace,
ADD COLUMN date_graduated DATE AFTER course,
ADD COLUMN academic_year VARCHAR(20) AFTER date_graduated,
ADD COLUMN civil_status VARCHAR(20) AFTER academic_year,
ADD COLUMN current_employment_status VARCHAR(50) AFTER employment_status,
ADD COLUMN job_title VARCHAR(100) AFTER current_employment_status,
ADD COLUMN company_address VARCHAR(255) AFTER company_name,
ADD COLUMN location VARCHAR(20) AFTER company_address,
ADD COLUMN industry_type VARCHAR(50) AFTER location,
ADD COLUMN employment_from DATE AFTER industry_type,
ADD COLUMN employment_to DATE AFTER employment_from,
ADD COLUMN job_related_to_degree VARCHAR(5) AFTER employment_to,
ADD COLUMN current_status VARCHAR(50) AFTER job_related_to_degree,
ADD COLUMN engaged_in_applications VARCHAR(5) AFTER current_status,
ADD COLUMN business_type VARCHAR(50) AFTER business_name,
ADD COLUMN business_start_date DATE AFTER business_type,
ADD COLUMN business_address VARCHAR(255) AFTER business_start_date,
ADD COLUMN business_related_to_degree VARCHAR(5) AFTER business_address,
ADD COLUMN educational_level VARCHAR(50) AFTER business_related_to_degree,
ADD COLUMN school_institution VARCHAR(255) AFTER educational_level,
ADD COLUMN program_degree VARCHAR(255) AFTER school_institution;

-- Update existing records to populate new fields from existing data
-- This assumes you have existing data in the old structure
UPDATE alumni SET 
    last_name = SUBSTRING_INDEX(fullname, ',', 1),
    first_name = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(fullname, ',', -1), ' ', 1)),
    middle_name = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(fullname, ',', -1), ' ', -1))
WHERE last_name IS NULL AND fullname IS NOT NULL;

-- Note: You may need to manually update other fields like sex, birthdate, etc.
-- based on your existing data structure and requirements 