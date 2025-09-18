import { Router, Request, Response } from 'express';
import { AuthService, LoginCredentials, RegisterCredentials } from '../services/auth';

const router = Router();

// Register endpoint
router.post('/register', async (req: Request, res: Response) => {
  try {
    const credentials: RegisterCredentials = req.body;
    
    if (!credentials.email || !credentials.password) {
      return res.status(400).json({ error: 'Email and password are required' });
    }

    const result = await AuthService.register(credentials);
    
    res.status(201).json({
      message: 'User registered successfully',
      user: result.user,
      token: result.token
    });
  } catch (error: any) {
    res.status(400).json({ error: error.message });
  }
});

// Login endpoint
router.post('/login', async (req: Request, res: Response) => {
  try {
    const credentials: LoginCredentials = req.body;
    
    if (!credentials.email || !credentials.password) {
      return res.status(400).json({ error: 'Email and password are required' });
    }

    const result = await AuthService.login(credentials);
    
    res.json({
      message: 'Login successful',
      user: result.user,
      token: result.token
    });
  } catch (error: any) {
    res.status(401).json({ error: error.message });
  }
});

// Verify token endpoint
router.get('/verify', async (req: Request, res: Response) => {
  try {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (!token) {
      return res.status(401).json({ error: 'No token provided' });
    }

    const user = await AuthService.verifyToken(token);
    
    if (!user) {
      return res.status(403).json({ error: 'Invalid token' });
    }

    res.json({ user });
  } catch (error: any) {
    res.status(403).json({ error: 'Invalid token' });
  }
});

export default router;
