-- Create payments table for alumni payments
CREATE TABLE IF NOT EXISTS `alumni_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alumni_id` int(11) NOT NULL,
  `student_no` varchar(50) NOT NULL,
  `payment_type` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','paid','failed','cancelled') DEFAULT 'pending',
  `reference_number` varchar(100) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `notes` text,
  `payment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `alumni_id` (`alumni_id`),
  KEY `student_no` (`student_no`),
  KEY `status` (`status`),
  KEY `payment_date` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample payment data
INSERT INTO `alumni_payments` (`alumni_id`, `student_no`, `payment_type`, `amount`, `payment_method`, `status`, `reference_number`, `notes`, `payment_date`) VALUES
(1, '2025-001', 'Alumni Association Fee', 1000.00, 'GCash', 'paid', 'PAY-001', 'Annual membership fee', '2024-01-15 10:30:00'),
(1, '2025-001', 'Event Registration', 500.00, 'Bank Transfer', 'paid', 'PAY-002', 'Homecoming event registration', '2024-01-10 14:20:00'),
(1, '2025-001', 'Annual Membership', 1000.00, 'GCash', 'paid', 'PAY-003', '2024 membership renewal', '2024-01-05 09:15:00'),
(1, '2025-001', 'Workshop Fee', 500.00, 'Credit Card', 'pending', 'PAY-004', 'Career development workshop', '2024-01-20 16:45:00');

-- Create payment methods table
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `account_number` varchar(100) DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `qr_code_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample payment methods
INSERT INTO `payment_methods` (`name`, `description`, `account_number`, `account_name`, `qr_code_image`, `is_active`) VALUES
('GCash', 'Mobile money transfer via GCash', '09123456789', 'SLSU Alumni Association', '../images/GCash-MyQR-05072025235950.PNG.jpg', 1),
('Bank Transfer', 'Direct bank transfer to SLSU account', '1234-5678-9012-3456', 'SLSU Alumni Association', NULL, 1),
('Credit Card', 'Online credit card payment', NULL, 'SLSU Alumni Association', NULL, 1); 