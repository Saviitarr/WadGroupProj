-- Accommodation Booking System - schema
DROP DATABASE IF EXISTS accommodation;
CREATE DATABASE accommodation CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE accommodation;

-- 1. USERS - students and admins
CREATE TABLE users (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    full_name      VARCHAR(100) NOT NULL,
    student_number VARCHAR(20)  UNIQUE,
    email          VARCHAR(150) NOT NULL UNIQUE,
    phone          VARCHAR(20),
    password_hash  VARCHAR(255) NOT NULL,
    role           ENUM('student','admin') NOT NULL DEFAULT 'student',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- 2. PROPERTIES - a residence/building
CREATE TABLE properties (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    address     VARCHAR(255) NOT NULL,
    area        VARCHAR(100) NOT NULL,  -- e.g. "Windhoek West"
    distance_to_campus_km  DECIMAL(5,2) NOT NULL CHECK (distance_to_campus_km >= 0),
    description TEXT,
    has_wifi    TINYINT(1) NOT NULL DEFAULT 0,  -- TODO: yes/no, default no
    has_parking TINYINT(1) NOT NULL DEFAULT 0,  -- TODO: yes/no, default no
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. ROOMS - a bookable room inside a property
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id      INT NOT NULL,
    room_number      VARCHAR(20) NOT NULL,
    room_type        ENUM('single', 'double') NOT NULL,
    capacity         INT NOT NULL CHECK (capacity > 0),
    price_per_month  DECIMAL(10,2) NOT NULL CHECK (price_per_month >= 0),
    is_available     TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE (property_id, room_number),
    
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- 4. PROPERTY_IMAGES - photos of a property
CREATE TABLE property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
    -- TODO: foreign key - same question as rooms
);

-- 5. BOOKINGS - which student booked which room, when
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    room_id     INT NOT NULL,
    start_date  DATE NOT NULL,
    end_date    DATE NOT NULL,
    status      ENUM ('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    notes       TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- TODO: two foreign keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT,
   
    -- TODO: CHECK that end_date is after start_date
    CHECK (end_date > start_date)
);

-- 6. REVIEWS (optional, extra marks)
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- 7. CONTACT_MESSAGES - from the contact page
CREATE TABLE contact (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL,
email VARCHAR(150) NOT NULL,
subject VARCHAR(255) NOT NULL,
message TEXT NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);