# Deployment Guide for Strato MySQL + GitHub

This guide will help you deploy your application with MySQL hosted on Strato and the frontend on GitHub Pages.

## Architecture Overview

- **Frontend**: GitHub Pages (Static React app)
- **Backend API**: Vercel/Netlify/Railway (Node.js server)
- **Database**: Strato MySQL
- **File Storage**: Backend server storage or cloud storage

## Step 1: Strato MySQL Setup

### 1.1 Create MySQL Database on Strato

1. Log into your Strato hosting panel
2. Go to "Databases" → "MySQL"
3. Create a new MySQL database:
   - Database name: `your_database_name`
   - Username: `your_username`
   - Password: `your_secure_password`
4. Note down the connection details:
   - Host: Usually `yourdomain.mysql.db.strato.de` or similar
   - Port: `3306`
   - Database name
   - Username
   - Password

### 1.2 Import Database Schema

1. Access your Strato MySQL database through phpMyAdmin or MySQL client
2. Run the schema from `database/schema.sql`:
```sql
-- Copy and paste the contents of database/schema.sql
-- This will create all necessary tables
```

## Step 2: Backend Deployment (Choose One)

### Option A: Vercel (Recommended)

1. **Install Vercel CLI**:
```bash
npm install -g vercel
```

2. **Create vercel.json** (already created for you):
```json
{
  "version": 2,
  "builds": [
    {
      "src": "server/index.ts",
      "use": "@vercel/node"
    }
  ],
  "routes": [
    {
      "src": "/api/(.*)",
      "dest": "server/index.ts"
    }
  ]
}
```

3. **Deploy**:
```bash
vercel --prod
```

4. **Set Environment Variables** in Vercel dashboard:
```
DB_HOST=yourdomain.mysql.db.strato.de
DB_PORT=3306
DB_USER=your_username
DB_PASSWORD=your_password
DB_NAME=your_database_name
JWT_SECRET=your_long_random_jwt_secret
NODE_ENV=production
```

### Option B: Railway

1. Go to [Railway.app](https://railway.app)
2. Connect your GitHub repository
3. Set environment variables in Railway dashboard
4. Deploy automatically

### Option C: Netlify Functions

1. Go to [Netlify](https://netlify.com)
2. Connect your GitHub repository
3. Set build command: `npm run build:server`
4. Set publish directory: `dist`
5. Set environment variables in Netlify dashboard

## Step 3: Frontend Deployment (GitHub Pages)

### 3.1 Update API Configuration

1. Update `src/lib/api.ts` with your backend URL:
```typescript
const API_BASE_URL = import.meta.env.VITE_API_URL || 'https://your-backend-url.vercel.app/api';
```

### 3.2 Configure GitHub Pages

1. **Install gh-pages**:
```bash
npm install --save-dev gh-pages
```

2. **Update package.json scripts**:
```json
{
  "scripts": {
    "predeploy": "npm run build",
    "deploy": "gh-pages -d dist",
    "build:server": "tsc server/index.ts --outDir dist/server"
  }
}
```

3. **Create .github/workflows/deploy.yml**:
```yaml
name: Deploy to GitHub Pages

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
        cache: 'npm'
    
    - name: Install dependencies
      run: npm ci
    
    - name: Build
      run: npm run build
      env:
        VITE_API_URL: https://your-backend-url.vercel.app/api
    
    - name: Deploy to GitHub Pages
      uses: peaceiris/actions-gh-pages@v3
      with:
        github_token: ${{ secrets.GITHUB_TOKEN }}
        publish_dir: ./dist
```

### 3.3 Enable GitHub Pages

1. Go to your repository Settings
2. Scroll to "Pages" section
3. Source: "GitHub Actions"
4. Your site will be available at: `https://yourusername.github.io/lltool`

## Step 4: Environment Configuration

### 4.1 Frontend Environment Variables

Create `.env.production`:
```env
VITE_API_URL=https://your-backend-url.vercel.app/api
```

### 4.2 Backend Environment Variables

Set these in your hosting platform (Vercel/Railway/Netlify):

**Strato MySQL Connection**:
```
DB_HOST=yourdomain.mysql.db.strato.de
DB_PORT=3306
DB_USER=your_username
DB_PASSWORD=your_password
DB_NAME=your_database_name
```

**Security**:
```
JWT_SECRET=your_very_long_and_random_jwt_secret_key_here
NODE_ENV=production
```

**Server Configuration**:
```
PORT=3001
UPLOAD_DIR=/tmp/uploads
```

## Step 5: File Storage for Production

Since serverless functions have limited file storage, consider:

### Option A: Cloud Storage (Recommended)
- AWS S3
- Cloudinary
- Supabase Storage (ironically!)

### Option B: External File Hosting
- Upload files to a separate file hosting service
- Store only URLs in database

### Option C: Temporary Storage
- Files stored temporarily on serverless function
- Not persistent across deployments

## Step 6: Domain Configuration (Optional)

If you want a custom domain:

1. **Backend**: Add custom domain in Vercel/Railway
2. **Frontend**: Configure custom domain in GitHub Pages
3. **Update CORS**: Add your custom domain to allowed origins

## Step 7: SSL and Security

1. **SSL Certificates**: Automatically handled by hosting platforms
2. **Environment Variables**: Never commit secrets to GitHub
3. **Database Security**: Use strong passwords and limit access
4. **API Rate Limiting**: Consider implementing rate limiting

## Troubleshooting

### Common Issues:

1. **CORS Errors**: Check CORS configuration matches your frontend URL
2. **Database Connection**: Verify Strato MySQL credentials and host
3. **Environment Variables**: Ensure all required variables are set
4. **Build Failures**: Check Node.js version compatibility

### Debugging:

1. **Backend Logs**: Check hosting platform logs (Vercel/Railway)
2. **Frontend Console**: Check browser console for errors
3. **Network Tab**: Verify API calls are reaching backend

## Cost Considerations

- **GitHub Pages**: Free
- **Vercel**: Free tier available (100GB bandwidth/month)
- **Railway**: $5/month for hobby plan
- **Strato MySQL**: Depends on your hosting plan

## Monitoring

1. **Uptime Monitoring**: Use services like UptimeRobot
2. **Error Tracking**: Consider Sentry for error monitoring
3. **Analytics**: Google Analytics for usage tracking

## Backup Strategy

1. **Database**: Regular exports from Strato MySQL
2. **Code**: GitHub provides automatic backups
3. **Files**: Backup uploaded files to cloud storage

This setup gives you a scalable, cost-effective solution with your data hosted on Strato and your application on GitHub!
