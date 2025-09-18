-- Student Management System MySQL Database Schema
-- This schema replicates the functionality of the original Supabase schema

-- Create database (run this separately if needed)
-- CREATE DATABASE student_management;
-- USE student_management;

-- Users table (replaces Supabase auth.users)
CREATE TABLE users (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cohorts table
CREATE TABLE cohorts (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(255) NOT NULL,
    owner_id VARCHAR(36) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Cohort access table for sharing
CREATE TABLE cohort_access (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    cohort_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    permissions ENUM('view', 'edit') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cohort_user (cohort_id, user_id)
);

-- Students table
CREATE TABLE students (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(255) NOT NULL,
    leergroep TINYINT NOT NULL CHECK (leergroep IN (1, 2, 3)),
    photo_url VARCHAR(500) NULL,
    cohort_id VARCHAR(36) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_cohorts_owner_id ON cohorts(owner_id);
CREATE INDEX idx_cohort_access_cohort_id ON cohort_access(cohort_id);
CREATE INDEX idx_cohort_access_user_id ON cohort_access(user_id);
CREATE INDEX idx_students_cohort_id ON students(cohort_id);
CREATE INDEX idx_users_email ON users(email);

-- Insert a default admin user (password: admin123)
-- You should change this password after first login
INSERT INTO users (id, email, password_hash) VALUES 
('admin-uuid-here', 'admin@example.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
