# Quick Start Guide: Strato MySQL + GitHub Deployment

## 🚀 Quick Setup (5 Steps)

### 1. Set up Strato MySQL Database
1. Log into Strato control panel
2. Create MySQL database: `student_management`
3. Create user with strong password
4. Note connection details (host, username, password)
5. Import `database/schema.sql` via phpMyAdmin

### 2. Deploy Backend API
**Option A: Vercel (Recommended)**
```bash
# Install Vercel CLI
npm install -g vercel

# Deploy backend
vercel --prod

# Set environment variables in Vercel dashboard:
DB_HOST=yourdomain.mysql.db.strato.de
DB_USER=your_username
DB_PASSWORD=your_password
DB_NAME=student_management
JWT_SECRET=your_long_random_secret
NODE_ENV=production
```

**Option B: Railway**
1. Go to [railway.app](https://railway.app)
2. Connect GitHub repository
3. Set environment variables in dashboard
4. Deploy automatically

### 3. Deploy Frontend to GitHub Pages
1. Push code to GitHub repository
2. Go to repository Settings → Pages
3. Source: "GitHub Actions"
4. Your site will be at: `https://yourusername.github.io/lltool`

### 4. Update CORS Configuration
Edit `server/index.ts` line 22-25:
```typescript
origin: process.env.NODE_ENV === 'production' 
  ? [
      'https://yourusername.github.io', // Replace with your GitHub username
      'https://yourusername.github.io/lltool', // Your project pages URL
    ]
  : ['http://localhost:5173', 'http://localhost:3000'],
```

### 5. Set Frontend API URL
Create `.env.production`:
```env
VITE_API_URL=https://your-backend-url.vercel.app/api
```

## 🔧 Environment Variables Summary

### Backend (Vercel/Railway)
```
DB_HOST=yourdomain.mysql.db.strato.de
DB_PORT=3306
DB_USER=your_username
DB_PASSWORD=your_password
DB_NAME=student_management
JWT_SECRET=your_jwt_secret_key
NODE_ENV=production
UPLOAD_DIR=/tmp/uploads
```

### Frontend (GitHub Actions)
```
VITE_API_URL=https://your-backend-url.vercel.app/api
```

## 📁 Deployment Files Created

- `vercel.json` - Vercel deployment config
- `railway.json` - Railway deployment config  
- `.github/workflows/deploy.yml` - GitHub Pages deployment
- `DEPLOYMENT.md` - Detailed deployment guide
- `STRATO_SETUP.md` - Strato-specific setup guide

## 🎯 Final URLs

After deployment, you'll have:
- **Frontend**: `https://yourusername.github.io/lltool`
- **Backend API**: `https://your-backend-url.vercel.app`
- **Database**: Strato MySQL (internal)

## 🔐 Default Login

After importing schema:
- **Email**: admin@example.com
- **Password**: admin123

**⚠️ Change this password immediately!**

## 🆘 Troubleshooting

### Common Issues:
1. **CORS errors**: Check CORS config matches your GitHub Pages URL
2. **Database connection**: Verify Strato credentials and host
3. **Build failures**: Check environment variables are set
4. **SSL errors**: Database config handles SSL automatically

### Quick Tests:
```bash
# Test database connection
npm run migrate

# Test backend locally
npm run server:dev

# Test frontend locally  
npm run dev
```

## 💰 Cost Estimate

- **GitHub Pages**: Free
- **Vercel**: Free tier (100GB bandwidth/month)
- **Strato MySQL**: Included in your hosting plan
- **Total**: ~$0-5/month depending on usage

That's it! Your application will be live with Strato MySQL hosting and GitHub Pages deployment.
