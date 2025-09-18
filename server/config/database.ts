import mysql from 'mysql2/promise';

export interface DatabaseConfig {
  host: string;
  port: number;
  user: string;
  password: string;
  database: string;
}

export const dbConfig: DatabaseConfig = {
  host: process.env.DB_HOST || 'localhost',
  port: parseInt(process.env.DB_PORT || '3306'),
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'student_management',
};

// Create connection pool
export const pool = mysql.createPool({
  ...dbConfig,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  acquireTimeout: 60000,
  timeout: 60000,
  // SSL configuration for Strato and other external hosts
  ssl: process.env.NODE_ENV === 'production' && dbConfig.host !== 'localhost' 
    ? { rejectUnauthorized: false } 
    : false,
  // Additional options for external MySQL hosts
  reconnect: true,
  charset: 'utf8mb4',
});

export default pool;
