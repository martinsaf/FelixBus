**Note**: This project is currently in development phase. Some features may be incomplete or subject to change.

# FelixBus 🚌 
A PHP/MySQL-based bus travel management system with role-based access for visitors, clients, staff, and admins. It supports route scheduling, ticketing, wallet management, and dynamic alerts, designed for scalability and future expansion.

# Project Status 📌
This project is currently under development. The following features are either incomplete or planned for future implementation:
- Basic website with company details (location, contacts, opening hours, etc.);
- The website must be able to display dynamic alerts/information/promotions, defined by administrators.
- Administration area (accessible only to administrators):
  - Management of alerts/information/promotions;

# Current security 🔒

- SQL: US prepared statements (but lacks full sanitization)
- XSS: Not fully protected (lack of escaping/output encoding)
- Sessions: Basic (no ID renewal or extra validation)

## Prerequisites 📋

- XAMPP installed ([Download XAMPP](https://www.apachefriends.org/download.html))
- PHP 8.0+
- MySQL 5.7+
- Web browser (Chrome, Firefox, etc.)

## Installation & Setup 🛠️

### Database Setup

1. Start XAMPP Control Panel
2. Start Apache and MySQL services
3. Open phpMyAdmin at [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
4. Create a new database by importing the SQL file:
   - Click "New" to create a database
   - Go to "Import" tab
   - Select `basedados/criar_db.sql` from your project folder
   - Click "Go" to execute

### Project Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/martinsaf/FelixBus.git
   ```
2. Move the project folder to your XAMPP htdocs directory:
   ```bash
   C:\xampp\htdocs\FelixBus
3. Acess the application in your browser:
   ```bash
   http://localhost/FelixBus
   ```

## Features ✨

- **User Management**:
  - Passenger registration and profiles
  - Admin access control
- **Ticket Operations**:
  - Ticket booking
  - Ticket cancellation
  - Booking history
- **Bus Management**:
  - Route configuration
  - Schedule management
- **Payment Integration**:
  - Payment processing simulation

## Technologies Used 🛠️

- **Frontend**:
  - HTML, CSS
- **Backend**:
  - PHP
  - MySQL
- **Development Environment**:
  - XAMPP (Apache + MySQL + PHPMyAdmin)
   
