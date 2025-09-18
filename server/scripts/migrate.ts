#!/usr/bin/env ts-node

import pool from '../config/database';
import bcrypt from 'bcryptjs';

async function migrate() {
  console.log('Starting database migration...');

  try {
    // Test database connection
    const connection = await pool.getConnection();
    console.log('✓ Database connection successful');
    connection.release();

    // Check if tables exist
    const [tables] = await pool.execute("SHOW TABLES LIKE 'users'");
    if ((tables as any[]).length === 0) {
      console.log('Creating database schema...');
      
      // Read and execute schema file
      const fs = require('fs');
      const path = require('path');
      const schemaPath = path.join(__dirname, '../../database/schema.sql');
      const schema = fs.readFileSync(schemaPath, 'utf8');
      
      // Split by semicolon and execute each statement
      const statements = schema.split(';').filter((stmt: string) => stmt.trim());
      
      for (const statement of statements) {
        if (statement.trim()) {
          await pool.execute(statement);
        }
      }
      
      console.log('✓ Database schema created');
    } else {
      console.log('✓ Database schema already exists');
    }

    // Check if default admin user exists
    const [adminUsers] = await pool.execute(
      "SELECT * FROM users WHERE email = 'admin@example.com'"
    );
    
    if ((adminUsers as any[]).length === 0) {
      console.log('Creating default admin user...');
      
      const hashedPassword = await bcrypt.hash('admin123', 10);
      await pool.execute(
        'INSERT INTO users (id, email, password_hash) VALUES (?, ?, ?)',
        ['admin-uuid-' + Date.now(), 'admin@example.com', hashedPassword]
      );
      
      console.log('✓ Default admin user created');
      console.log('  Email: admin@example.com');
      console.log('  Password: admin123');
      console.log('  ⚠️  Please change this password after first login!');
    } else {
      console.log('✓ Default admin user already exists');
    }

    // Create uploads directory if it doesn't exist
    const fs = require('fs');
    const uploadsDir = process.env.UPLOAD_DIR || './uploads';
    if (!fs.existsSync(uploadsDir)) {
      fs.mkdirSync(uploadsDir, { recursive: true });
      console.log(`✓ Created uploads directory: ${uploadsDir}`);
    } else {
      console.log(`✓ Uploads directory exists: ${uploadsDir}`);
    }

    console.log('\n🎉 Migration completed successfully!');
    console.log('\nNext steps:');
    console.log('1. Start the server: npm run server:dev');
    console.log('2. Start the frontend: npm run dev');
    console.log('3. Visit http://localhost:5173');
    console.log('4. Login with admin@example.com / admin123');

  } catch (error) {
    console.error('❌ Migration failed:', error);
    process.exit(1);
  } finally {
    await pool.end();
  }
}

// Run migration if this script is executed directly
if (require.main === module) {
  migrate();
}

export { migrate };
