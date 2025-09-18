import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import { DatabaseService } from './database.js';
import { User, UserInsert } from '../types/database.js';

export interface AuthUser {
  id: string;
  email: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterCredentials {
  email: string;
  password: string;
}

export class AuthService {
  private static readonly JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key';
  private static readonly SALT_ROUNDS = 10;

  static async register(credentials: RegisterCredentials): Promise<{ user: AuthUser; token: string }> {
    // Check if user already exists
    const existingUser = await DatabaseService.getUserByEmail(credentials.email);
    if (existingUser) {
      throw new Error('User with this email already exists');
    }

    // Hash password
    const password_hash = await bcrypt.hash(credentials.password, this.SALT_ROUNDS);

    // Create user
    const userData: UserInsert = {
      email: credentials.email,
      password_hash
    };

    const user = await DatabaseService.createUser(userData);
    const token = this.generateToken(user);

    return {
      user: { id: user.id, email: user.email },
      token
    };
  }

  static async login(credentials: LoginCredentials): Promise<{ user: AuthUser; token: string }> {
    // Find user by email
    const user = await DatabaseService.getUserByEmail(credentials.email);
    if (!user) {
      throw new Error('Invalid email or password');
    }

    // Verify password
    const isValidPassword = await bcrypt.compare(credentials.password, user.password_hash);
    if (!isValidPassword) {
      throw new Error('Invalid email or password');
    }

    const token = this.generateToken(user);

    return {
      user: { id: user.id, email: user.email },
      token
    };
  }

  static async verifyToken(token: string): Promise<AuthUser | null> {
    try {
      const decoded = jwt.verify(token, this.JWT_SECRET) as any;
      const user = await DatabaseService.getUserById(decoded.userId);
      
      if (!user) {
        return null;
      }

      return { id: user.id, email: user.email };
    } catch (error) {
      return null;
    }
  }

  static generateToken(user: User): string {
    return jwt.sign(
      { userId: user.id, email: user.email },
      this.JWT_SECRET,
      { expiresIn: '7d' }
    );
  }

  static async hashPassword(password: string): Promise<string> {
    return bcrypt.hash(password, this.SALT_ROUNDS);
  }

  static async comparePassword(password: string, hash: string): Promise<boolean> {
    return bcrypt.compare(password, hash);
  }
}
