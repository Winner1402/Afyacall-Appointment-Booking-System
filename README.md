# 🏥 Afyacall Appointment Booking System

## 📌 Project Overview
The **Afyacall Appointment Booking System** is a web-based platform designed to simplify healthcare service delivery by enabling patients to book consultations with doctors, doctors to manage their schedules, and administrators to oversee the system.  

This project is built for **AfyaCall Health Services Limited** and aims to provide practical, real-world problem-solving experience while delivering a useful solution in the health domain.  

The system is developed using **PHP, MySQL, HTML, CSS, and JavaScript**.



## 🎯 System Features
1. A fully functional web application (PHP, HTML, CSS, JavaScript, MySQL).  
2. User roles with different capabilities (**Patient, Doctor, Admin**).  
3. Complete appointment booking flow: Request → Approval → Confirmation.  
4. Notification system (Email/SMS) for patients and doctors.  
5. Dashboards tailored for patients, doctors, and administrators.  
6. Database schema & migrations.  
7. Documentation (README + system overview).  
 
 
## 🧩 Core Modules

### 1. Authentication & Roles
- **Patient:** Register, login, and book appointments.  
- **Doctor:** Manage availability, accept/reject appointments.  
- **Admin:** Manage doctors, specialties, and overall system configuration.  

### 2. Doctor & Specialty Management
- Admins can add doctors and assign specialties (e.g., Psychiatry, Orthopaedics).  
- Doctors can define their available time slots.  

### 3. Appointment Booking System
- Patients can browse available doctors by specialty.  
- Patients can request appointments by selecting doctor, date, and time.  
- Doctors can **accept/reject** requests.  
- Patients can **cancel or reschedule** appointments before a set cutoff time.  
- Doctors can mark unavailable slots.  
- System automatically updates availability.  

### 4. Notifications & Alerts
- Patients receive confirmation or rejection notifications.  
- Automated reminders sent via **Email/SMS** before appointments.  
- Doctors notified of upcoming schedules.  

### 5. Dashboards
- **Patient Dashboard:** Upcoming appointments & booking history.  
- **Doctor Dashboard:** Pending requests & confirmed appointments.  
- **Admin Dashboard:** Overview of doctors, specialties, and system statistics.  

### 6. Reports & Analytics
- Number of appointments per doctor/specialty.  
- Daily/weekly/monthly booking reports.  
- Most requested specialties.  
- Data displayed via charts/graphs on the Admin Dashboard.  

### 7. Deployment
- Code hosted in a public GitHub repository.  
- Working application deployed locally (Linux preferred) or on a cloud server.  
- Documentation provided for setup and usage.  

 

## ⚙️ Tech Stack
- **Backend:** PHP  
- **Frontend:** HTML, CSS, JavaScript  
- **Database:** MySQL  
- **Notifications:** Email & SMS APIs  
- **Deployment:** Localhost (XAMPP/WAMP/LAMP) or Cloud Server  

 

## 🚀 Getting Started

### Prerequisites
- PHP >= 7.4  
- MySQL >= 5.7  
- Apache/Nginx server (XAMPP, WAMP, or LAMP stack)  
- Composer (optional, if dependencies added)  

### Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/Winner1402/Afyacall-Appointment-Booking-System.git
   cd Afyacall 
2. Import the database:

Create a new database named 'afyacall' in MySQL.

Import the provided migrations.sql file into the database.

3. Configure database credentials in:

config/db.php


4. Start your local server (XAMPP/WAMP/LAMP).

Access the system via:

http://localhost/Afyacall 


 ## Login Credentials

### Admin
- **Email:** admin@afyacall.com
- **Password:** Admin@2025

### Doctor
- **Email:** doctor@afyacall.com
- **Password:** Doctor@2025

### Patient
- **Email:** patient@gmail.com
- **Password:** Patient@2025
