USE accommodation;

-- USERS: 1 admin + 3 students
-- Password hashes: generate with
--   C:\xampp\php\php.exe -r "echo password_hash('Admin@123', PASSWORD_DEFAULT);"
INSERT INTO users (full_name, student_number, email, phone, password_hash, role) VALUES
('System Admin', NULL, 'admin@nust.na', '0811234567', '$2y$10$kd39mCT2XtoQH5V5Ci7gheUtnvYwdUpi9pXmowcc3cQ/EcU2yZlhS', 'admin'),
('John Doe', '224056781','224056781@nust.na', '0816690321', '$2y$10$2t9GuxPMW0Eb3fSkbo.OXuWC90YNFF2X.bbGazAqVSe8TX.K5wpTO', 'student'),
('Jane Smith', '224056782','224056782@nust.na', '08183025584', '$2y$10$2t9GuxPMW0Eb3fSkbo.OXuWC90YNFF2X.bbGazAqVSe8TX.K5wpTO', 'student'),
('Alice Johnson', '224056783','224056783@nust.na', '0811234567', '$2y$10$2t9GuxPMW0Eb3fSkbo.OXuWC90YNFF2X.bbGazAqVSe8TX.K5wpTO', 'student')
;

-- PROPERTIES: 4-5 real-sounding Windhoek places
INSERT INTO properties (name, address, area, distance_to_campus_km, description, has_wifi, has_parking) VALUES
('Campus View Residence', '12 Brahms Street', 'Windhoek West', 0.8, 'Quiet residence 10 minutes walk from NUST.', 1, 0),
('Sunset Apartments', '45 Sunset Avenue', 'Klein Windhoek', 2.5, 'Modern apartments with great views.', 1, 1),
('Eros Heights', '78 Eros Street', 'Eros', 3.0, 'Spacious rooms with easy access to public transport.', 0, 1),
('Khomasdal Flats', '23 Khomasdal Road', 'Khomasdal', 4.2, 'Affordable flats in a friendly neighborhood.', 1, 0)
;

-- ROOMS: 2-3 per property. property_id = the property's id
INSERT INTO rooms (property_id, room_number, room_type, capacity, price_per_month) VALUES
(1, 'A1', 'single', 1, 3200.00),
(1, 'A2', 'double', 2, 4500.00),
(1, 'B1', 'single', 1, 3300.00),
(2, 'C1', 'double', 2, 4800.00),
(2, 'C2', 'single', 1, 3500.00),
(3, 'D1', 'single', 1, 3000.00),
(3, 'D2', 'double', 2, 4700.00),
(4, 'E1', 'single', 1, 3100.00)
;

-- BOOKINGS: a mix of statuses so the dashboard and admin pages have something to show
INSERT INTO bookings (user_id, room_id, start_date, end_date, status) VALUES
(2, 1, '2027-02-01', '2027-06-30', 'approved'),
(2, 2, '2027-03-01', '2027-07-31', 'pending'),
(3, 3, '2027-04-01', '2027-08-31', 'rejected'),
(4, 4, '2027-05-01', '2027-09-30', 'approved')
;

INSERT INTO reviews (user_id, property_id, rating, comment) VALUES
(2, 1, 5, 'Great place to stay!'),
(3, 2, 4, 'Nice apartments but a bit pricey.'),
(4, 3, 3, 'Average experience. Could be better.');

INSERT INTO contact(name, email, subject, message) VALUES
('John Doe', '224056781@nust.na', 'Inquiry about availability', 'Hi, I would like to know if the room is still available.');

