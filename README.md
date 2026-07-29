# 💈 Smart Saloon Management System (ERP)

A comprehensive **web-based Enterprise Resource Planning (ERP)** solution developed to automate and streamline the daily operations of modern salons and barbershops. The system integrates appointment scheduling, customer management, employee administration, and business analytics into a centralized platform.

Designed using **PHP, MySQL, Bootstrap 5, and JavaScript**, the application follows a **Role-Based Access Control (RBAC)** architecture, providing separate dashboards and permissions for **Administrators**, **Barbers**, and **Customers** while ensuring data security and operational efficiency.

---

# 📌 Project Objectives

- Digitize the appointment booking process.
- Eliminate scheduling conflicts through intelligent booking validation.
- Provide secure Role-Based Access Control (RBAC).
- Enable administrators to manage users and appointments efficiently.
- Allow barbers to manage assigned appointments.
- Offer customers a seamless online booking experience.
- Protect user credentials using secure password hashing.
- Improve salon productivity through automation and real-time analytics.

---

# 🚀 Features

## 👨‍💼 Administrator Panel

- Dashboard with business statistics
- User Management (Customers & Barbers)
- Create, View, Update & Delete Users
- Appointment Monitoring
- Booking History Viewer (AJAX)
- Employee Management
- Search & Filter Users
- Performance Analytics

---

## ✂️ Barber Panel

- View Assigned Appointments
- Update Appointment Status
- Manage Daily Schedule
- Customer Information Access
- Appointment History
- Status Tracking
- Dashboard Statistics

---

## 👤 Customer Panel

- Online Appointment Booking
- Appointment History
- Upcoming Appointments
- Booking Status Tracking
- Search & Filter Bookings
- Cancel Pending Appointments
- Personal Dashboard

---

# 🛠️ Technology Stack

| Component | Technology |
|------------|------------|
| **Frontend** | HTML5, CSS3 |
| **UI Framework** | Bootstrap 5 |
| **Client-Side Scripting** | JavaScript (ES6) |
| **Icons** | Font Awesome 6 |
| **Backend** | PHP 8+ |
| **Database** | MySQL |
| **Server** | Apache (XAMPP) |

---

# 🏗️ System Architecture

```
                Smart Saloon ERP
                       │
        ┌──────────────┼──────────────┐
        │              │              │
        ▼              ▼              ▼
     Admin         Barber        Customer
        │              │              │
        ▼              ▼              ▼
 User Mgmt     Appointment Mgmt   Appointment Booking
        │              │              │
        └──────────────┼──────────────┘
                       ▼
                  MySQL Database
```

---

# 📂 Core Modules

## 🔐 Authentication & Authorization

The authentication module ensures secure user access using modern security standards.

### Features

- Secure User Registration
- Login Authentication
- Role-Based Access Control (RBAC)
- Session Management
- Duplicate Email Prevention
- Password Encryption

### Security

Passwords are encrypted using:

```php
password_hash(
    $password,
    PASSWORD_DEFAULT
);
```

After successful authentication, users are redirected automatically according to their assigned role:

- Administrator
- Barber
- Customer

---

## 👨‍💼 Administration Module

The Administrator has complete control over the system.

### Functionalities

- Dashboard Analytics
- User Management
- Barber Management
- Customer Management
- Delete User Accounts
- Appointment Monitoring
- Booking History Viewer
- AJAX-Based User Details
- Search & Filtering

---

## ✂️ Barber Module

Each barber can only manage appointments assigned to them.

### Features

- View Assigned Appointments
- Update Appointment Status
- Approve Bookings
- Complete Services
- Cancel Appointments
- Daily Appointment Dashboard

Supported appointment statuses:

- Pending
- Approved
- Completed
- Cancelled

Completed and cancelled appointments automatically disable unnecessary actions to prevent accidental updates.

---

## 👤 Customer Module

Customers can book appointments and monitor their booking history.

### Features

- Online Booking
- Appointment History
- Upcoming Bookings
- Booking Statistics
- Search & Filter
- Cancel Pending Appointments

---

# 📅 Smart Appointment Scheduling

The system prevents duplicate bookings by checking existing appointments before confirming a new reservation.

Workflow:

```
Customer
      │
      ▼
Choose Barber
      │
      ▼
Select Date & Time
      │
      ▼
Availability Check
      │
 ┌────┴────┐
 │         │
Available  Booked
 │         │
 ▼         ▼
Confirm    Display Error
Booking
```

This ensures that no two customers can book the same barber at the same date and time.

---

# 📊 Dashboard Analytics

The system provides real-time statistics including:

### Administrator

- Total Users
- Total Customers
- Total Barbers
- Total Appointments

---

### Barber

- Today's Appointments
- Pending Jobs
- Completed Services

---

### Customer

- Total Appointments
- Upcoming Bookings
- Completed Services

---

# ⚡ AJAX Integration

The application utilizes the **JavaScript Fetch API (AJAX)** to enhance user experience.

Features include:

- Booking History Modal
- User Information Retrieval
- Instant Data Loading
- No Page Refresh Required

---

# 🔒 Security Features

The system incorporates several security mechanisms.

### Password Security

- PHP `password_hash()`
- Secure password verification

---

### Session Management

- PHP Sessions
- Unauthorized access prevention
- Automatic login validation

---

### Role-Based Access Control (RBAC)

Separate access permissions for:

- Administrator
- Barber
- Customer

Users can only access resources permitted for their role.

---

### Duplicate User Prevention

The system verifies email addresses before registration to prevent duplicate accounts.

---

# 🎨 User Interface Features

- Responsive Bootstrap 5 Design
- Mobile-Friendly Layout
- Dynamic Search
- Instant Table Filtering
- Bootstrap Badges
- Font Awesome Icons
- Interactive Modals
- Clean Dashboard Interface

---

# 🗄️ Database Overview

Main database entities include:

```
Database
│
├── Users
├── Appointments
├── Services
└── Roles
```

These relational tables ensure efficient data storage and retrieval while maintaining data integrity.

---

# ⚙️ Installation

## Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/Smart-Saloon-Management-System.git
```

Move into the project directory:

```bash
cd Smart-Saloon-Management-System
```

---

## Requirements

Install and configure:

- XAMPP
- Apache Server
- MySQL
- PHP 8+

---

## Database Setup

1. Start Apache and MySQL.
2. Open phpMyAdmin.
3. Create a new database.
4. Import the provided SQL file.

---

## Run the Application

Place the project folder inside:

```
xampp/htdocs/
```

Open your browser:

```
http://localhost/Smart-Saloon-Management-System
```

---

# 📁 Project Structure

```
Smart-Saloon-Management-System/
│
├── admin/
├── barber/
├── customer/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── includes/
├── database/
├── login.php
├── register.php
├── customer_dashboard.php
├── manage_users.php
├── manage_appointments.php
├── book_appointment.php
├── README.md
└── LICENSE
```

---

# 🚀 Future Enhancements

Potential future improvements include:

- Online Payment Integration
- SMS & Email Notifications
- QR Code Appointment Check-in
- Loyalty Rewards Program
- Inventory Management
- Employee Payroll Module
- Service Reviews & Ratings
- Multi-Branch Salon Support
- Mobile Application
- Cloud Deployment

---

# 🤝 Contributing

Contributions are welcome!

To contribute:

1. Fork the repository.
2. Create a new feature branch.
3. Commit your changes.
4. Push your branch.
5. Submit a Pull Request.

Bug reports, suggestions, and feature requests are greatly appreciated.

---

# 👨‍💻 Author

**Muhammad Adil Ehtisham Malick**

Full Stack Web Developer | PHP Developer | Database Designer | ERP Systems Developer

---

# 📄 License

This project is licensed under the **MIT License**.

Feel free to use, modify, and distribute this project for educational and commercial purposes.

---

## ⭐ Support

If you found this project useful, please consider giving it a **Star ⭐** on GitHub.
