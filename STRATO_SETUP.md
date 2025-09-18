# Strato MySQL Setup Guide

This guide will help you set up your MySQL database on Strato hosting.

## Step 1: Access Strato Control Panel

1. Log into your Strato hosting account
2. Navigate to your hosting package
3. Look for "Databases" or "MySQL" section

## Step 2: Create MySQL Database

### 2.1 Database Creation
1. Click "Create Database" or "New Database"
2. Choose MySQL version (8.0 recommended)
3. Set database name: `student_management` (or your preferred name)
4. Create database user:
   - Username: `student_user` (or your preferred name)
   - Password: Generate a strong password (save this!)

### 2.2 Connection Details
Strato will provide you with connection details like:
- **Host**: `yourdomain.mysql.db.strato.de` or `mysql.strato.de`
- **Port**: `3306`
- **Database Name**: `your_database_name`
- **Username**: `your_username`
- **Password**: `your_password`

## Step 3: Access Database

### Option A: phpMyAdmin (Recommended)
1. In Strato control panel, find "phpMyAdmin" or "Database Management"
2. Click to open phpMyAdmin
3. Select your database from the left sidebar
4. Go to "SQL" tab

### Option B: External MySQL Client
Use MySQL Workbench, DBeaver, or command line:
```bash
mysql -h yourdomain.mysql.db.strato.de -u your_username -p your_database_name
```

## Step 4: Import Database Schema

1. Copy the contents of `database/schema.sql`
2. Paste into phpMyAdmin SQL tab or MySQL client
3. Execute the SQL to create tables

## Step 5: Test Connection

You can test the connection using our migration script:

1. Create `.env` file with Strato credentials:
```env
DB_HOST=yourdomain.mysql.db.strato.de
DB_PORT=3306
DB_USER=your_username
DB_PASSWORD=your_password
DB_NAME=your_database_name
JWT_SECRET=your_jwt_secret_key
PORT=3001
NODE_ENV=production
UPLOAD_DIR=./uploads
```

2. Run migration:
```bash
npm run migrate
```

## Step 6: Strato-Specific Considerations

### 6.1 Connection Limits
- Strato typically allows 20-50 concurrent connections
- Our connection pool is set to 10 connections max
- This should work fine with Strato limits

### 6.2 SSL/TLS
- Strato MySQL usually requires SSL connections
- Our MySQL2 client supports SSL by default
- If you encounter SSL issues, you can disable it by adding `ssl: false` to database config

### 6.3 Firewall
- Strato may have firewall rules
- Ensure port 3306 is accessible from your backend hosting
- Some hosting providers (Vercel, Railway) use specific IP ranges

### 6.4 Database Size Limits
- Check your Strato plan for database size limits
- Monitor usage through Strato control panel

## Step 7: Security Best Practices

### 7.1 Database Security
- Use strong passwords
- Limit database user permissions to only necessary operations
- Regularly backup your database

### 7.2 Connection Security
- Use SSL connections (enabled by default)
- Store credentials in environment variables
- Never commit database credentials to version control

## Step 8: Backup Strategy

### 8.1 Regular Backups
1. Use Strato's backup feature if available
2. Export database regularly through phpMyAdmin
3. Store backups securely

### 8.2 Automated Backups
Consider setting up automated backups:
```bash
# Example backup script
mysqldump -h yourdomain.mysql.db.strato.de -u your_username -p your_database_name > backup_$(date +%Y%m%d).sql
```

## Troubleshooting

### Common Issues:

1. **Connection Refused**
   - Check host and port
   - Verify firewall settings
   - Ensure database is active

2. **Access Denied**
   - Verify username and password
   - Check user permissions
   - Ensure user has access to the database

3. **SSL Connection Errors**
   - Try disabling SSL: `ssl: false` in database config
   - Check if Strato requires SSL certificates

4. **Timeout Issues**
   - Increase connection timeout in database config
   - Check network latency

### Debug Connection:

Test connection with a simple script:
```javascript
const mysql = require('mysql2/promise');

async function testConnection() {
  try {
    const connection = await mysql.createConnection({
      host: 'yourdomain.mysql.db.strato.de',
      port: 3306,
      user: 'your_username',
      password: 'your_password',
      database: 'your_database_name',
      ssl: false // Try with and without SSL
    });
    
    console.log('Connection successful!');
    await connection.end();
  } catch (error) {
    console.error('Connection failed:', error);
  }
}

testConnection();
```

## Environment Variables for Production

Set these in your backend hosting platform (Vercel/Railway):

```env
DB_HOST=yourdomain.mysql.db.strato.de
DB_PORT=3306
DB_USER=your_username
DB_PASSWORD=your_password
DB_NAME=your_database_name
JWT_SECRET=your_very_long_random_jwt_secret
NODE_ENV=production
UPLOAD_DIR=/tmp/uploads
```

## Support

If you encounter issues:
1. Check Strato's documentation
2. Contact Strato support for database-specific issues
3. Check our deployment logs for connection errors
4. Test connection locally before deploying

This setup should work seamlessly with Strato's MySQL hosting!
