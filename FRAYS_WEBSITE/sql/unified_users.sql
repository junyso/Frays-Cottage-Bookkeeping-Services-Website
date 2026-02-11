-- Unified Users Table for Multi-FA Authentication
-- Run this in your main bookkeeping database

CREATE TABLE IF NOT EXISTS unified_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    fa_instance VARCHAR(100) DEFAULT NULL,
    fa_user_id VARCHAR(100) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'client',
    fa_instances JSON DEFAULT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_fa_instance (fa_instance),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data for testing (optional)
-- INSERT INTO unified_users (email, name, password_hash, role, fa_instances) 
-- VALUES ('admin@frayscottage.co.bw', 'Administrator', '$2y$10$...hashed password...', 'admin', '{"frayscottage": {"name": "Frays Cottage", "role": "admin"}}');
