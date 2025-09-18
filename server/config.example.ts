// Example configuration file
// Copy this to server/config.ts and update with your values

export const config = {
  database: {
    host: 'localhost',
    port: 3306,
    user: 'student_user',
    password: 'your_password',
    database: 'student_management',
  },
  jwt: {
    secret: 'your_jwt_secret_key_here_make_it_long_and_random',
  },
  server: {
    port: 3001,
    nodeEnv: 'development',
  },
  upload: {
    dir: './uploads',
  },
};
