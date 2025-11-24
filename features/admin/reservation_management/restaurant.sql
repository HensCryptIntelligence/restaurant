-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2025 at 10:00 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: restaurant
--

CREATE DATABASE IF NOT EXISTS restaurant DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE restaurant;

-- --------------------------------------------------------

--
-- Table structure for table reservation
--

CREATE TABLE reservation (
  id_reservation int(11) NOT NULL,
  id_user int(11) DEFAULT NULL,
  customer_name varchar(255) DEFAULT NULL,
  id_reservation_room varchar(10) NOT NULL,
  seats int(11) NOT NULL,
  reservation_start datetime NOT NULL,
  reservation_time int(11) DEFAULT 120,
  reservation_date date NOT NULL,
  phone_number varchar(20) DEFAULT NULL,
  email_address varchar(255) DEFAULT NULL,
  status enum('pending','confirmed','seated','cancelled','deleted') DEFAULT 'pending',
  created_by enum('admin','customer') DEFAULT 'admin',
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table reservation_rooms
--

CREATE TABLE reservation_rooms (
  id_reservation_room varchar(10) NOT NULL,
  seats int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table users
--

CREATE TABLE users (
  id_user int(11) NOT NULL,
  fullname varchar(255) NOT NULL,
  password_hash varchar(255) NOT NULL,
  role enum('admin','customer') DEFAULT 'customer',
  created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE reservation:
--   id_user
--       users -> id_user
--   id_reservation_room
--       reservation_rooms -> id_reservation_room
--

-- --------------------------------------------------------

--
-- Dumping data for table reservation_rooms
--

INSERT INTO reservation_rooms (id_reservation_room, seats) VALUES
('A1', 4), ('A2', 4),
('B1', 6), ('B2', 6), ('B3', 6),
('C1', 8), ('C2', 8),
('D1', 4), ('D2', 4), ('D3', 4),
('E1', 6), ('E2', 6), ('E3', 6),
('F1', 8), ('F2', 8),
('G1', 4), ('G2', 4), ('G3', 4),
('H1', 6), ('H2', 6), ('H3', 6),
('I1', 8), ('I2', 8),
('Bar', 4);

-- --------------------------------------------------------

--
-- Indexes for table reservation
--
ALTER TABLE reservation
  ADD PRIMARY KEY (id_reservation),
  ADD KEY id_user (id_user),
  ADD KEY id_reservation_room (id_reservation_room);

--
-- Indexes for table reservation_rooms
--
ALTER TABLE reservation_rooms
  ADD PRIMARY KEY (id_reservation_room);

--
-- Indexes for table users
--
ALTER TABLE users
  ADD PRIMARY KEY (id_user);

--
-- AUTO_INCREMENT for table reservation
--
ALTER TABLE reservation
  MODIFY id_reservation int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table users
--
ALTER TABLE users
  MODIFY id_user int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for table reservation
--
ALTER TABLE reservation
  ADD CONSTRAINT reservation_ibfk_1 FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE SET NULL,
  ADD CONSTRAINT reservation_ibfk_2 FOREIGN KEY (id_reservation_room) REFERENCES reservation_rooms (id_reservation_room);

COMMIT;

ALTER TABLE reservation 
ADD COLUMN created_by VARCHAR(50) DEFAULT 'system' AFTER status,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER created_by,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;