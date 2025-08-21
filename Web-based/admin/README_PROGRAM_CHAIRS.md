# Program Chair Management System

This document describes the Program Chair management functionality for the Southern Leyte State University - Hinunangan Campus Alumni System.

## Features

### ✅ Create Program Chair Account Form
- Full Name (required)
- Email (required, unique)
- Username (required, unique)
- Password (required, hashed)
- Designated Program/Department (dropdown with all SLSU-HC programs)
- Profile Picture (optional upload)

### ✅ Display of Existing Program Chair Accounts
- Responsive table with all chair information
- Profile picture display
- Status indicators (Active/Suspended)
- Action buttons for each chair

### ✅ View Profile Modal
- Complete chair details display
- Profile image preview
- Program designation
- Account creation date

### ✅ Edit Functionality
- Update all chair information
- Optional password change
- Profile picture replacement
- Form validation

### ✅ Suspend/Activate Functionality
- Toggle button for status updates
- Confirmation dialogs
- Visual status indicators

### ✅ Search and Filter
- Search by name, email, or username
- Filter by program
- Filter by status (Active/Suspended)
- Clear filters option

### ✅ Professional UI/UX
- Modern Tailwind CSS design
- Responsive layout
- Modal dialogs
- Success/error alerts
- Loading states
- Mobile-friendly interface

## Setup Instructions

### 1. Database Setup
Run the database creation script:
```bash
# Navigate to admin/config/
php create_program_chairs_table.php
```

This will:
- Create the `program_chairs` table
- Add necessary indexes for performance
- Insert sample data for testing

### 2. File Structure
Ensure the following directory structure exists:
```
admin/
├── manage-users-chair.php          # Main management page
├── chair-uploads/                  # Profile picture uploads
├── config/
│   ├── database.php               # Database connection
│   └── create_program_chairs_table.php  # Table creation script
└── includes/
    ├── header.php                 # Page header
    ├── footer.php                 # Page footer
    └── sidebar.php                # Navigation sidebar
```

### 3. Permissions
Make sure the `chair-uploads/` directory is writable:
```bash
chmod 755 admin/chair-uploads/
```

## Database Schema

### program_chairs Table
```sql
CREATE TABLE program_chairs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    program ENUM('BSA', 'BSAB', 'BSED', 'BSIT', 'BSES', 'BTLED', 'BAT') NOT NULL,
    profile_picture VARCHAR(255) NULL,
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Programs Available
- **BSA** - Bachelor of Science in Accountancy
- **BSAB** - Bachelor of Science in Agribusiness
- **BSED** - Bachelor of Secondary Education
- **BSIT** - Bachelor of Science in Information Technology
- **BSES** - Bachelor of Science in Environmental Science
- **BTLED** - Bachelor of Technology and Livelihood Education
- **BAT** - Bachelor of Agricultural Technology

## Usage

### Accessing the System
1. Log in to the admin panel
2. Navigate to "Manage Users" → "Program Chair" in the sidebar
3. The system will display all existing Program Chair accounts

### Creating a New Program Chair
1. Click "Add New Chair" button
2. Fill in all required fields
3. Select the appropriate program
4. Optionally upload a profile picture
5. Click "Create Chair"

### Managing Existing Chairs
- **View Profile**: Click the eye icon to see complete details
- **Edit**: Click the edit icon to modify chair information
- **Suspend/Activate**: Click the ban/check icon to toggle status

### Searching and Filtering
- Use the search bar to find chairs by name, email, or username
- Use the program dropdown to filter by specific programs
- Use the status dropdown to filter by active/suspended accounts
- Click "Clear" to reset all filters

## Security Features

- **Password Hashing**: All passwords are hashed using PHP's `password_hash()`
- **Input Validation**: All form inputs are validated and sanitized
- **File Upload Security**: Profile pictures are validated for type and size
- **Session Management**: Admin authentication required
- **SQL Injection Prevention**: Prepared statements used throughout

## File Upload Specifications

- **Supported Formats**: JPG, PNG, GIF
- **Maximum Size**: 5MB
- **Storage Location**: `admin/chair-uploads/`
- **File Naming**: Unique filenames generated using `uniqid()`

## Error Handling

The system includes comprehensive error handling:
- Database connection errors
- File upload errors
- Validation errors
- Duplicate email/username detection
- Success/error message display

## Browser Compatibility

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Mobile browsers (responsive design)

## Troubleshooting

### Common Issues

1. **Upload Directory Not Writable**
   - Ensure `chair-uploads/` directory exists and has proper permissions
   - Run: `chmod 755 admin/chair-uploads/`

2. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running

3. **Table Not Found**
   - Run the table creation script: `php create_program_chairs_table.php`

4. **Profile Pictures Not Displaying**
   - Check file permissions on uploaded images
   - Verify the upload directory path is correct

### Support
For technical support, contact the system administrator or refer to the main system documentation. 