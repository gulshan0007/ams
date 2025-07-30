# Lab Asset Management System

A comprehensive web-based system for managing laboratory equipment and assets across different departments.

## Features

- **Multi-Department Support**: Separate equipment management for different departments (Civil, Mechanical, Electrical, Computer Science, Chemistry, Physics, Mathematics, Biology)
- **Equipment Booking System**: Users can book equipment with time slots
- **User Authentication**: Secure login system with department-based access
- **Equipment Tracking**: Real-time availability status and usage tracking
- **Image Upload**: Support for equipment photos
- **Detailed Equipment Information**: Specifications, descriptions, purchase details, and more

## Database Setup

### Prerequisites
- XAMPP installed and running
- MySQL server running on localhost
- PHP 7.4 or higher

### Installation Steps

1. **Start XAMPP Services**
   - Start Apache and MySQL services in XAMPP Control Panel

2. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `ugacademics_lab_assets`

3. **Run Database Setup**
   - Navigate to your project directory
   - Open `http://localhost/ams/direct_setup.php` in your browser
   - This will create all necessary tables and insert sample data

4. **Access the Application**
   - Go to `http://localhost/ams/login.php`
   - Use the sample credentials provided after setup

## Sample Login Credentials

After running the setup script, you can use these credentials:

| Department | Username | Password |
|------------|----------|----------|
| Civil | admin | admin123 |
| Mechanical | admin_mech | admin123 |
| Electrical | admin_elec | admin123 |
| Computer Science | admin_cs | admin123 |
| Chemistry | admin_chem | admin123 |
| Physics | admin_phy | admin123 |
| Mathematics | admin_math | admin123 |
| Biology | admin_bio | admin123 |

## Database Structure

### Core Tables

1. **users** - Basic user authentication
2. **userdetails** - Extended user information
3. **bookings** - Equipment booking records

### Department Tables

Each department has its own equipment table:
- civil
- mechanical
- electrical
- computer_science
- chemistry
- physics
- mathematics
- biology

### Equipment Table Structure

Each department table contains:
- Equipment details (name, specifications, description)
- Availability status
- Current and last user information
- Purchase details (year, supplier, amount, fund)
- Department incharge information

## File Structure

```
ams/
├── connections.php          # Database connection
├── login.php               # Main login page
├── dashboard.php           # Main dashboard
├── book_instrument.php     # Equipment booking
├── user_signup.php        # User registration
├── user_login.php         # User login
├── setup_database.php     # Database setup script
├── create_tables.sql      # SQL schema file
├── uploads/               # Equipment images
├── images/                # System images
└── vendor/                # Composer dependencies
```

## Usage

1. **Admin Login**: Use department-specific admin credentials
2. **Add Equipment**: Navigate to dashboard and add new equipment
3. **Book Equipment**: Users can book available equipment
4. **Track Usage**: Monitor equipment usage and availability
5. **Manage Users**: Register new users for the system

## Security Features

- Password hashing for user accounts
- Session-based authentication
- Department-based access control
- Input validation and sanitization

## Technical Requirements

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx web server
- PHPMailer for email functionality

## Support

For technical support or questions, please contact the system administrator.

## License

This system is developed for internal use at the institution. 