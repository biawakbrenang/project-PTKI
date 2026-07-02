# Academic E-Learning System - Project Overview

Complete web-based academic management system with database integration, role-based access control, and modern UI.

---

## 📦 Project Contents

### Core Application Files

#### 1. **index.php** (Main Entry Point)
- Router untuk semua request
- Mengecek session user
- Delegate ke auth.php atau api.php sesuai kebutuhan
- Start point: `http://localhost/elearning/`

#### 2. **academic-elearning-system.html** (Frontend UI)
- Complete user interface dengan semua screens
- HTML structure + CSS styling + JavaScript logic
- Responsive design dengan gradient aesthetics
- Interactive animations & SVG icons
- Login form, Dashboard (Admin/Dosen/Mahasiswa)
- Class management, Assignments, Submissions, Attendance, Grades, Announcements

### PHP Backend Files

#### 3. **php/config.php** (Configuration)
- Database connection settings
- Environment constants
- Helper functions (sanitize, sendResponse, logError)
- Session initialization
- Include file ini di semua PHP scripts

**Edit field:**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Ubah sesuai kebutuhan
define('DB_NAME', 'elearning_system');
```

#### 4. **php/auth.php** (Authentication)
- Login handler dengan password verification
- Session management
- Logout functionality
- Role checking
- User info retrieval

**Main Functions:**
- `Auth::login()` - Validate & login user
- `Auth::logout()` - Destroy session
- `Auth::isLoggedIn()` - Check login status
- `Auth::getCurrentUser()` - Get current user info
- `Auth::hasRole()` - Check user role

**Protected by:**
- Prepared statements (SQL injection prevention)
- Password hashing (bcrypt)
- Session validation

#### 5. **php/api.php** (API Endpoints)
Main hub untuk semua data operations. Routes request berdasarkan action parameter.

**Dashboard Data:**
- `getDashboardData` - Statistik dashboard sesuai role

**Classes:**
- `getClasses` - List kelas user

**Assignments:**
- `getAssignments` - List tugas
- `createAssignment` - Buat tugas baru (dosen)

**Submissions:**
- `getSubmissions` - List pengumpulan tugas
- `submitAssignment` - Submit tugas (mahasiswa)
- `gradeSubmission` - Nilai tugas (dosen)

**Attendance:**
- `getAttendance` - List kehadiran
- `markAttendance` - Absen (mahasiswa)
- `recordAttendance` - Catat kehadiran (dosen)

**Announcements:**
- `getAnnouncements` - List pengumuman
- `createAnnouncement` - Buat pengumuman

**Grades:**
- `getGrades` - List nilai

**Admin Functions:**
- `getUsers` - List semua user
- `createUser` - Buat user baru
- `updateUser` - Update data user
- `deleteUser` - Hapus user

### Database Files

#### 6. **sql/elearning_schema.sql** (Database Schema)
Complete SQL script untuk setup database:
- CREATE DATABASE
- CREATE 9 tables with proper relationships
- INSERT sample data (users, courses, classes, assignments, etc.)
- Indexes untuk performance
- Foreign keys untuk data integrity

**Import Method:**
1. phpMyAdmin → Import
2. Upload file
3. Execute

**Result:**
- Database: `elearning_system`
- 9 tables dengan sample data
- Ready to use

### Documentation Files

#### 7. **SETUP_GUIDE.md** (Detailed Setup)
Panduan lengkap step-by-step:
- Prasyarat system
- Extract & copy files
- Create database via phpMyAdmin
- Configure connection
- Create uploads folder
- Default credentials
- Troubleshooting guide
- Security notes

#### 8. **DATABASE_INTEGRATION.md** (Database Details)
Dokumentasi database:
- Architecture overview
- Complete schema definitions
- Sample data details
- API endpoint reference
- Password hashing explanation
- Session management
- File upload handling
- Backup & recovery procedures
- Performance tips
- Security best practices

#### 9. **QUICK_START.md** (Fast Reference)
Quick reference untuk lazy people:
- 5-minute setup
- Test credentials
- File structure
- Features checklist
- Troubleshooting table
- Database connection string

#### 10. **PROJECT_OVERVIEW.md** (This File)
Complete overview of all files & components.

### Additional Folders

#### 11. **uploads/** (File Storage)
Directory untuk menyimpan file yang diupload:
- Assignment files (dari dosen)
- Student submission files
- Naming: `[timestamp]_[filename]`
- Permission: 777 (readable/writable)
- Create manually atau via script

---

## 🔄 Data Flow

### Login Flow
```
1. User mengisi form login
   ↓
2. JavaScript POST ke index.php
   ↓
3. index.php → auth.php
   ↓
4. auth.php query database (users table)
   ↓
5. Verify password dengan password_verify()
   ↓
6. Set $_SESSION variables
   ↓
7. Return success response
   ↓
8. JavaScript redirect ke dashboard
```

### Data Retrieval Flow
```
1. Frontend AJAX POST to api.php
   └─ action=getAssignments
   └─ class_id=1
   ↓
2. api.php::getAssignments()
   ↓
3. Check user role & permissions
   ↓
4. Build SQL query sesuai role
   ↓
5. Execute query ke database
   ↓
6. Fetch results as JSON
   ↓
7. Return JSON response
   ↓
8. JavaScript update DOM
```

### Data Submission Flow
```
1. User submit form
   ↓
2. Frontend validate input
   ↓
3. AJAX POST to api.php
   └─ action=submitAssignment
   └─ file=uploaded_file
   └─ notes=catatan
   ↓
4. api.php::submitAssignment()
   ↓
5. Sanitize input & validate
   ↓
6. Handle file upload
   ↓
7. INSERT into submissions table
   ↓
8. Return success response
   ↓
9. Show toast notification
```

---

## 🗄️ Database Schema Summary

### Users Table
- Stores all users: admin, dosen, mahasiswa
- Password: hashed with bcrypt
- Status: active/inactive
- NIDN: for dosen, NIM: for mahasiswa

### Courses Table
- Mata kuliah / subject information
- Code: TI201, TI305, etc
- SKS: credit units

### Classes Table
- Kelas / section
- Linked to course & lecturer
- Max students, semester, academic year

### Class Enrollments Table
- Student enrollment in classes
- Unique constraint: 1 student per class
- Status: active/dropped

### Assignments Table
- Tugas / homework
- Linked to class
- Deadline, file attachments
- Created by: dosen

### Submissions Table
- Student assignment submissions
- Linked to assignment & student
- Grade & feedback from dosen
- File upload path

### Attendance Table
- Kehadiran / absensi
- Status: hadir/izin/alpha
- Attendance date
- Unique: 1 record per student per day

### Announcements Table
- Pengumuman dari dosen
- Linked to class
- Content & creation timestamp

### Grades Table
- Nilai records
- Linked to assignment & student
- Feedback from lecturer

---

## 🔐 Security Features

### ✅ Implemented
1. **Password Hashing**
   - bcrypt (PASSWORD_BCRYPT)
   - 10 rounds (cost factor)
   
2. **SQL Injection Prevention**
   - Prepared statements (mysqli::prepare)
   - Parameterized queries
   
3. **Input Sanitization**
   - htmlspecialchars()
   - mysqli::real_escape_string()
   - trim()
   
4. **Session Management**
   - $_SESSION validation
   - User role checking
   - Logout with session_destroy()
   
5. **Database**
   - Foreign key constraints
   - Proper data types
   - Indexes for performance

### ⚠️ Recommended for Production
1. HTTPS/SSL Certificate
2. CSRF Token protection
3. Rate limiting on login
4. Audit logging
5. Two-factor authentication
6. Regular security updates
7. WAF (Web Application Firewall)
8. DDoS protection

---

## 📊 Role-Based Features

### Admin Dashboard
```
✓ View all users (admin, dosen, mahasiswa)
✓ Create/Edit/Delete users
✓ Manage courses & classes
✓ View system statistics
✓ Generate reports
✓ User management interface
```

### Dosen Dashboard
```
✓ View assigned classes
✓ Create & manage assignments
✓ View student submissions
✓ Grade submissions & give feedback
✓ Record attendance
✓ Create announcements
✓ View class statistics
```

### Mahasiswa Dashboard
```
✓ View enrolled classes
✓ View assignments
✓ Submit assignments
✓ View grades & feedback
✓ Mark attendance
✓ View announcements
```

---

## 🎨 Frontend Features

### UI Design
- **Background:** Gradient (subtle blue/white)
- **Sidebar:** Dark navy gradient
- **Cards:** White with gradient top border
- **Buttons:** Gradient blue/green/red
- **Icons:** SVG outline style (Feather-like)

### Animations
- **Fade-in:** Page transitions
- **Slide-up:** Cards stagger animation
- **Pop-in:** Dialog boxes
- **Hover effects:** Lift, scale, color shift
- **Loading spinner:** Animated on submit

### Responsive
- Fixed sidebar navigation
- Responsive table design
- Mobile-friendly forms
- Accessible ARIA labels

---

## 📈 Performance Optimizations

1. **Database Indexing**
   - Foreign keys indexed
   - Frequently queried fields indexed
   - Unique constraints

2. **Query Optimization**
   - Only select needed columns
   - Use JOIN efficiently
   - Limit result sets

3. **Frontend**
   - Minimal CSS/JavaScript
   - Lazy loading for images
   - Efficient DOM manipulation
   - Gradient CSS (no images)

---

## 🚀 Deployment Checklist

### Before Going Live
- [ ] Change all default passwords
- [ ] Update database credentials
- [ ] Enable HTTPS/SSL
- [ ] Set proper file permissions
- [ ] Configure firewall rules
- [ ] Setup automated backups
- [ ] Enable error logging
- [ ] Disable debug mode
- [ ] Update PHP to latest version
- [ ] Configure CORS if needed
- [ ] Setup WAF rules
- [ ] Test all features
- [ ] Monitor performance

### After Deployment
- [ ] Monitor server logs
- [ ] Check database performance
- [ ] Backup database regularly
- [ ] Update security patches
- [ ] Review access logs
- [ ] Test backup recovery
- [ ] Update documentation

---

## 🔗 File Dependencies

```
index.php
├── php/config.php
├── php/auth.php
├── php/api.php
└── academic-elearning-system.html

academic-elearning-system.html
├── (no external dependencies, pure HTML/CSS/JS)
└── Makes AJAX calls to api.php

php/auth.php
└── php/config.php

php/api.php
├── php/config.php
└── php/auth.php

sql/elearning_schema.sql
└── (standalone, run in phpMyAdmin)
```

---

## 📞 Support

### Common Issues

**Database Connection Error**
- Check config.php settings
- Verify MySQL is running
- Check user credentials

**Login Fails**
- Verify test credentials
- Check if database imported
- Clear browser cache

**File Upload Fails**
- Check uploads/ folder exists
- Verify permissions (chmod 777)
- Check file size limit

**UI Not Loading**
- Check browser console (F12)
- Verify index.php exists
- Check file paths
- Clear cache

### Getting Help
1. Check SETUP_GUIDE.md
2. Review DATABASE_INTEGRATION.md
3. Check browser console for errors
4. Check server error logs
5. Verify database structure

---

## 📝 Version History

**v1.0 (Initial Release)**
- Complete login & authentication
- All dashboards (Admin/Dosen/Mahasiswa)
- Class management
- Assignment & submission system
- Attendance tracking
- Grades & feedback
- Announcements
- Database integration
- SVG icons & gradient UI
- Interactive animations

---

## 📄 License

This project is created for educational purposes. Free to use and modify.

---

## 🎓 Credits

Academic E-Learning System v1.0
- Database Design: MySQL
- Backend: PHP with mysqli
- Frontend: HTML5, CSS3, JavaScript ES6
- Icons: SVG (Feather style)
- UI: Modern gradient design

---

## 🚀 Ready to Deploy!

**Next Steps:**
1. Read SETUP_GUIDE.md for installation
2. Check QUICK_START.md for testing
3. Review DATABASE_INTEGRATION.md for customization
4. Deploy and enjoy!

**Questions?** Check the documentation files included. All answers are there! 📚

---

Last Updated: July 2, 2026
Status: Production Ready ✅
