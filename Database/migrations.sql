-- ===========================
-- AfyaCall Database Schema
-- ===========================

-- ===========================
-- 1. users table
-- ===========================
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `phone` varchar(20),
  `address` varchar(255),
  `gender` enum('Male','Female','Other'),
  `password` varchar(255) NOT NULL,
  `role` enum('patient','doctor','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `force_password_change` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `role` (`role`),
  KEY `created_at` (`created_at`)
);

-- ===========================
-- 2. doctors table
-- ===========================
CREATE TABLE `doctors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `specialty_id` int NOT NULL,
  `status` varchar(20) DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `specialty_id` (`specialty_id`),
  CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `doctors_ibfk_2` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`)
);

-- ===========================
-- 3. doctor_slots table
-- ===========================
CREATE TABLE `doctor_slots` (
  `id` int NOT NULL AUTO_INCREMENT,
  `doctor_id` int NOT NULL,
  `slot_datetime` datetime NOT NULL,
  `end_datetime` datetime,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `doctor_slots_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
);

-- ===========================
-- 4. appointments table
-- ===========================
CREATE TABLE `appointments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `slot_id` int NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  KEY `slot_id` (`slot_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`slot_id`) REFERENCES `doctor_slots` (`id`) ON DELETE CASCADE
);

-- ===========================
-- 5. medical_history table
-- ===========================
CREATE TABLE `medical_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `details` text NOT NULL,
  `attachment` varchar(255),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `medical_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medical_history_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

-- ===========================
-- 6. feedback table
-- ===========================
CREATE TABLE `feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('approved','unapproved') NOT NULL DEFAULT 'unapproved',
  `profile_pic` varchar(255) NOT NULL DEFAULT 'assets/images/user_placeholder.png',
  `rating` tinyint(1) DEFAULT 5,
  PRIMARY KEY (`id`)
);

-- ===========================
-- 7. testimonials table
-- ===========================
CREATE TABLE `testimonials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

-- ===========================
-- 8. faqs table
-- ===========================
CREATE TABLE `faqs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

-- ===========================
-- 9. messages table
-- ===========================
CREATE TABLE `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `sender` enum('user','admin') NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

-- ===========================
-- 10. notifications table
-- ===========================
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

-- ===========================
-- 11. password_resets table
-- ===========================
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role` enum('doctor','patient','admin') NOT NULL,
  `token` varchar(255) NOT NULL UNIQUE,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
);

-- ===========================
-- 12. specialties table
-- ===========================
CREATE TABLE `specialties` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL UNIQUE,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
);

-- ===========================
-- 13. system_settings table
-- ===========================
CREATE TABLE `system_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `system_name` varchar(100) NOT NULL DEFAULT 'AfyaCall',
  `logo` varchar(255),
  `contact_email` varchar(100),
  `contact_phone` varchar(50),
  `appointment_duration` int DEFAULT 30,
  `working_hours` varchar(50) DEFAULT '08:00-17:00',
  `max_appointments_per_day` int DEFAULT 10,
  `file_upload_limit_mb` int DEFAULT 5,
  `theme_color` varchar(20) DEFAULT '#127137',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
);

-- ===========================
-- 14. user_logs table
-- ===========================
CREATE TABLE `user_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `role` enum('admin','doctor','patient') NOT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(45),
  `user_agent` varchar(255),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `login_time` datetime,
  `logout_time` datetime,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

-- =========================== END ===========================