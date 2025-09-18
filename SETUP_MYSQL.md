# MySQL Database Setup Guide

This guide will help you set up your own MySQL database to replace Supabase.

## Prerequisites

1. **MySQL Server** - Install MySQL 8.0 or later
2. **Node.js** - Version 18 or later
3. **npm** - Package manager

## Step 1: Install MySQL

### Windows:
1. Download MySQL Installer from https://dev.mysql.com/downloads/installer/
2. Install MySQL Server and MySQL Workbench
3. During installation, set a root password

### macOS:
```bash
brew install mysql
brew services start mysql
```

### Linux (Ubuntu/Debian):
```bash
sudo apt update
sudo apt install mysql-server
sudo mysql_secure_installation
```

## Step 2: Create Database and User

1. Connect to MySQL as root:
```bash
mysql -u root -p
```

2. Create the database:
```sql
CREATE DATABASE student_management;
```

3. Create a user (replace with your credentials):
```sql
CREATE USER 'student_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON student_management.* TO 'student_user'@'localhost';
FLUSH PRIVILEGES;
```

## Step 3: Set Up Database Schema

1. Run the schema file:
```bash
mysql -u student_user -p student_management < database/schema.sql
```

## Step 4: Environment Configuration

1. Copy the environment template:
```bash
cp .env.template .env
```

2. Edit `.env` with your MySQL credentials:
```env
DB_HOST=localhost
DB_PORT=3306
DB_USER=student_user
DB_PASSWORD=your_password
DB_NAME=student_management

JWT_SECRET=your_jwt_secret_key_here

PORT=3001
NODE_ENV=development

UPLOAD_DIR=./uploads
```

## Step 5: Install Dependencies

```bash
npm install
```

## Step 6: Create Uploads Directory

```bash
mkdir uploads
```

## Step 7: Start the Application

1. Start the backend server:
```bash
npm run server:dev
```

2. In another terminal, start the frontend:
```bash
npm run dev
```

## Step 8: Create Your First User

1. Go to http://localhost:5173
2. Use the registration form to create your first admin account
3. Or use the default admin user (if you ran the schema):
   - Email: admin@example.com
   - Password: admin123

## Default Admin User

The schema includes a default admin user:
- **Email**: admin@example.com
- **Password**: admin123

**Important**: Change this password after first login!

## API Endpoints

The backend server provides the following API endpoints:

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `GET /api/auth/verify` - Verify token

### Cohorts
- `GET /api/cohorts` - Get user's cohorts
- `POST /api/cohorts` - Create cohort
- `GET /api/cohorts/:id` - Get specific cohort
- `PUT /api/cohorts/:id` - Update cohort
- `DELETE /api/cohorts/:id` - Delete cohort
- `POST /api/cohorts/:id/share` - Share cohort
- `GET /api/cohorts/:id/access` - Get cohort access list

### Students
- `GET /api/students/cohort/:cohortId` - Get students in cohort
- `POST /api/students` - Create student
- `GET /api/students/:id` - Get specific student
- `PUT /api/students/:id` - Update student
- `DELETE /api/students/:id` - Delete student

### File Upload
- `POST /api/upload` - Upload photo

## Troubleshooting

### Connection Issues
1. Check MySQL is running: `brew services list | grep mysql` (macOS) or `systemctl status mysql` (Linux)
2. Verify credentials in `.env`
3. Check firewall settings

### Permission Issues
1. Ensure the MySQL user has proper privileges
2. Check file permissions for uploads directory

### Port Conflicts
1. Change PORT in `.env` if 3001 is occupied
2. Update frontend API_URL accordingly

## Security Notes

1. Change default admin password
2. Use strong JWT_SECRET
3. Set up proper firewall rules
4. Use HTTPS in production
5. Regular database backups

## Production Deployment

For production deployment:

1. Use environment-specific configurations
2. Set up SSL certificates
3. Configure reverse proxy (nginx/Apache)
4. Set up database backups
5. Use PM2 or similar for process management
6. Configure proper logging

## Data Migration from Supabase

If you have existing data in Supabase, you can export it and import to MySQL:

1. Export data from Supabase dashboard
2. Convert UUID format if needed
3. Import using MySQL Workbench or command line tools
