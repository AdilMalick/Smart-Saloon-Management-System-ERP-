========================================================================
       SMART BARBER APPOINTMENT MANAGEMENT SYSTEM - SETUP GUIDE
========================================================================

Follow these exact steps to deploy and run the "Smart Barber" project 
on your local server environment using XAMPP:

------------------------------------------------------------------------
STEP 1: PLACE THE PROJECT IN THE LOCAL SERVER ROOT
------------------------------------------------------------------------
1. Copy your entire "smart_barber" project folder.
2. Navigate to your XAMPP installation directory (usually located on your C: drive).
3. Open the "htdocs" folder and paste the project directory there.
   
   The path must look precisely like this:
   C:\xampp\htdocs\smart_barber\

------------------------------------------------------------------------
STEP 2: START THE WEB SERVER AND DATABASE ENGINE
------------------------------------------------------------------------
1. Open the "XAMPP Control Panel" app on your computer.
2. Click the "Start" button next to the "Apache" module.
3. Click the "Start" button next to the "MySQL" module.
4. Ensure both modules are highlighted green, indicating they are active.

------------------------------------------------------------------------
STEP 3: INITIALIZE THE RELATIONAL DATABASE ENVIRONMENT
------------------------------------------------------------------------
1. Open your web browser and go to the phpMyAdmin panel:
   http://localhost/phpmyadmin/
2. Click on the "New" tab in the left sidebar menu.
3. Enter the Database Name exactly as: smart_barber
4. Set the collation encoding type to: utf8mb4_general_ci
5. Click the "Create" button.

------------------------------------------------------------------------
STEP 4: EXECUTE THE DATABASE SCHEMA TABLES (MIGRATION)
------------------------------------------------------------------------
1. Select your newly created database "smart_barber" from the left menu.
2. Click on the "SQL" tab located at the top navigation bar.
3. Copy and paste the following structural SQL commands into the text box and click "Go":

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','barber','customer') NOT NULL DEFAULT 'customer',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `services`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in minutes',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `appointments`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `barber_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `status` enum('pending','approved','cancelled') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`customer_id`) REFERENCES users(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`barber_id`) REFERENCES users(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES services(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--------------------------------------------------------
STEP 5: VERIFY CONNECTION VARIABLES (`db.php`)
--------------------------------------------------------
Open the "db.php" file inside your project folder and verify that the database 
parameters match your local engine environment credentials:

<?php
$conn = mysqli_connect("localhost", "root", "", "smart_barber_db");
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}
?>

------------------------------------------------------------------------
STEP 6: COMPILE AND LAUNCH THE APPLICATION
------------------------------------------------------------------------
Open your web browser and execute the server entry-point URL to test:
http://localhost/smart_barber/login.php

- You can now register test accounts using the UI via register.php 
  to evaluate Customer, Barber, and Admin dashboard routing systems.
========================================================================