# Quick Start Guide - Academic E-Learning System

## 5-Minute Setup

### 1. Copy Files (1 min)
```
Copy semua file ke folder project:
- php/
- sql/
- uploads/ (create if not exist)
- index.php
- academic-elearning-system.html
```

### 2. Import Database (2 min)
- Buka http://localhost/phpmyadmin
- Login dengan username `root`
- Import file `sql/elearning_schema.sql`
- Database otomatis terbuat dengan sample data

### 3. Configure (1 min)
Edit `php/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Kosong atau password Anda
define('DB_NAME', 'elearning_system');
```

### 4. Run & Test (1 min)
- Akses http://localhost/elearning/
- Login dengan credential di bawah
- Done! 🎉

---

## Test Credentials

### Role: Admin
```
Username: admin
Email:    admin@univ.ac.id
Password: admin123
```

### Role: Dosen (Lecturer)
```
Username: rina_hartati
Email:    rina.hartati@univ.ac.id
Password: admin123
NIDN:     0012038501
```

Dosen lainnya:
- `sutanto` / `sutanto@univ.ac.id`
- `yuni_kartika` / `yuni.kartika@univ.ac.id`

### Role: Mahasiswa (Student)
```
Username: ahmad_fauzi
Email:    ahmad.fauzi@univ.ac.id
Password: admin123
NIM:      2155201101
```

Mahasiswa lainnya:
- `siti_nurhaliza` / NIM 2155201102
- `budi_santoso` / NIM 2155201103
- `dewi_lestari` / NIM 2155201104

---

## File Structure

```
elearning/
├── index.php                      ← Main entry point
├── academic-elearning-system.html ← UI/Frontend
│
├── php/
│   ├── config.php                ← Database config
│   ├── auth.php                  ← Login handler
│   └── api.php                   ← All API endpoints
│
├── sql/
│   └── elearning_schema.sql      ← Database schema
│
├── uploads/                      ← File uploads (create this)
│
└── Documentation:
    ├── SETUP_GUIDE.md            ← Full setup guide
    ├── DATABASE_INTEGRATION.md   ← Database details
    └── QUICK_START.md            ← This file
```

---

## Features Ready

### ✅ For Admin
- User Management (Create, Read, Update, Delete)
- View all Dosen & Mahasiswa
- Manage Courses & Classes
- View Statistics & Reports

### ✅ For Dosen
- View assigned classes
- Create & manage assignments
- Grade student submissions
- Record attendance
- Create announcements
- View submission history

### ✅ For Mahasiswa
- View my classes
- View assignments
- Submit assignments with file upload
- View grades & feedback
- Mark attendance
- View announcements

---

## Database Tables

| Table | Purpose |
|-------|---------|
| `users` | Store all users (admin, dosen, mahasiswa) |
| `courses` | Course information (mata kuliah) |
| `classes` | Class/section information (kelas) |
| `class_enrollments` | Student enrollment in classes |
| `assignments` | Assignment/task information |
| `submissions` | Student assignment submissions |
| `attendance` | Attendance/kehadiran records |
| `announcements` | Course announcements |
| `grades` | Grade records |

---

## Key Features

### Authentication
- Login with username/email
- Password hashing (bcrypt)
- Session management
- Role-based access control

### Data Management
- Dynamic data loading from database
- AJAX requests for smooth UX
- File upload support
- Real-time updates

### User Experience
- Clean, modern interface
- Responsive design
- Gradient aesthetics
- Interactive animations
- SVG icons

### Security
- SQL injection prevention (prepared statements)
- Input sanitization
- Password encryption
- Session validation

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Database connection error | Check config.php settings, ensure MySQL is running |
| Login fails | Verify credentials, check if database is imported |
| Files not uploading | Check uploads/ folder permissions (chmod 777) |
| UI not loading | Clear browser cache, check if index.php exists |
| 404 errors | Verify file structure and paths |

---

## Next Steps

1. **Change default passwords** - For security
2. **Add more courses** - Via admin panel
3. **Enroll students** - Add class enrollments
4. **Create assignments** - Start assigning tasks
5. **Customize branding** - Update colors/logos

---

## Support Resources

- **Setup Issues?** → Read `SETUP_GUIDE.md`
- **Database Questions?** → Check `DATABASE_INTEGRATION.md`
- **API Reference?** → See DATABASE_INTEGRATION.md section "API Endpoints"
- **Password Reset?** → Update directly in phpMyAdmin users table

---

## Important Notes

⚠️ **Before Production:**
- Change all default passwords
- Set proper file permissions
- Enable HTTPS
- Setup database backups
- Implement rate limiting on login
- Add CSRF protection

✅ **Already Implemented:**
- Password hashing (bcrypt)
- Input sanitization
- SQL injection prevention
- Role-based access control
- Database indexes for performance

---

## Database Connection String

```php
// If needed for other tools
Host:     localhost
Port:     3306
Database: elearning_system
User:     root
Password: (empty or your password)
```

---

## PhpMyAdmin Access

- URL: http://localhost/phpmyadmin
- Username: root
- Password: (empty by default)
- Database: elearning_system

---

**🎓 Ready to use! Happy learning!**

Last updated: July 2, 2026
Version: 1.0 (Beta)
