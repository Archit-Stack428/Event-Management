-- MMDU Event Hub Database Schema
-- Tables inferred from application code

USE id13212736_event;

-- Users table (sign up)
CREATE TABLE IF NOT EXISTS sign_up (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(100) GENERATED ALWAYS AS (email) STORED,
    full_name VARCHAR(200) GENERATED ALWAYS AS (CONCAT(first_name, ' ', last_name)) STORED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE IF NOT EXISTS create_event (
    Event_ID INT AUTO_INCREMENT PRIMARY KEY,
    organizer_name VARCHAR(200),
    event_title VARCHAR(255) NOT NULL,
    event_desc TEXT,
    category VARCHAR(100),
    eventtype VARCHAR(100),
    min_team INT DEFAULT 0,
    max_team INT DEFAULT 0,
    event_rules TEXT,
    startdate DATE,
    enddate DATE,
    event_venue VARCHAR(255),
    time VARCHAR(50),
    event_price INT DEFAULT 0,
    event_thumbnail VARCHAR(255),
    event_sponsors TEXT,
    event_prizes TEXT,
    publish_event ENUM('yes','no') DEFAULT 'no',
    open_closed VARCHAR(20) DEFAULT 'open'
);

-- Single event registration
CREATE TABLE IF NOT EXISTS singleevent_registration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    name VARCHAR(200),
    roll_no VARCHAR(100),
    college_name VARCHAR(200),
    dept_name VARCHAR(200),
    email VARCHAR(150),
    mobile_no VARCHAR(50),
    paid_amount VARCHAR(50),
    paid_amount_currency VARCHAR(20),
    txn_id VARCHAR(255),
    payment_status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Team event registration
CREATE TABLE IF NOT EXISTS teamevent_registration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    team_name VARCHAR(200),
    college_name VARCHAR(200),
    student_name TEXT,
    emails TEXT,
    mobile_no TEXT,
    paid_amount VARCHAR(50),
    paid_amount_currency VARCHAR(20),
    txn_id VARCHAR(255),
    payment_status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Gallery
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(255),
    organizer_name VARCHAR(200),
    image VARCHAR(255),
    date DATE,
    category VARCHAR(100)
);

-- Feedback
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    name VARCHAR(200),
    feedback TEXT,
    stars INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Razorpay Event Orders (UPI Payments)
CREATE TABLE IF NOT EXISTS event_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    razorpay_order_id VARCHAR(100) NOT NULL,
    razorpay_payment_id VARCHAR(100) DEFAULT NULL,
    event_id INT NOT NULL,
    registration_type ENUM('individual', 'team') NOT NULL,
    registration_data LONGTEXT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'INR',
    status ENUM('pending', 'paid', 'failed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uq_razorpay_order_id (razorpay_order_id),
    UNIQUE KEY uq_razorpay_payment_id (razorpay_payment_id),
    INDEX idx_event_orders_event (event_id),
    INDEX idx_event_orders_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

