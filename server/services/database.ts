import pool from '../config/database';
import { 
  User, Cohort, CohortAccess, Student,
  UserInsert, CohortInsert, CohortAccessInsert, StudentInsert,
  UserUpdate, CohortUpdate, CohortAccessUpdate, StudentUpdate
} from '../types/database';

export class DatabaseService {
  // User operations
  static async createUser(userData: UserInsert): Promise<User> {
    const [result] = await pool.execute(
      'INSERT INTO users (email, password_hash) VALUES (?, ?)',
      [userData.email, userData.password_hash]
    );
    
    const [users] = await pool.execute(
      'SELECT * FROM users WHERE id = ?',
      [(result as any).insertId]
    );
    
    return (users as User[])[0];
  }

  static async getUserByEmail(email: string): Promise<User | null> {
    const [rows] = await pool.execute(
      'SELECT * FROM users WHERE email = ?',
      [email]
    );
    
    return (rows as User[])[0] || null;
  }

  static async getUserById(id: string): Promise<User | null> {
    const [rows] = await pool.execute(
      'SELECT * FROM users WHERE id = ?',
      [id]
    );
    
    return (rows as User[])[0] || null;
  }

  // Cohort operations
  static async getCohortsByOwner(ownerId: string): Promise<Cohort[]> {
    const [rows] = await pool.execute(
      'SELECT * FROM cohorts WHERE owner_id = ? ORDER BY created_at DESC',
      [ownerId]
    );
    
    return rows as Cohort[];
  }

  static async getCohortsByUser(userId: string): Promise<Cohort[]> {
    const [rows] = await pool.execute(`
      SELECT DISTINCT c.* FROM cohorts c
      LEFT JOIN cohort_access ca ON c.id = ca.cohort_id
      WHERE c.owner_id = ? OR ca.user_id = ?
      ORDER BY c.created_at DESC
    `, [userId, userId]);
    
    return rows as Cohort[];
  }

  static async createCohort(cohortData: CohortInsert): Promise<Cohort> {
    const [result] = await pool.execute(
      'INSERT INTO cohorts (name, owner_id) VALUES (?, ?)',
      [cohortData.name, cohortData.owner_id]
    );
    
    const [cohorts] = await pool.execute(
      'SELECT * FROM cohorts WHERE id = ?',
      [(result as any).insertId]
    );
    
    return (cohorts as Cohort[])[0];
  }

  static async updateCohort(cohortData: CohortUpdate): Promise<Cohort | null> {
    const fields = [];
    const values = [];
    
    if (cohortData.name !== undefined) {
      fields.push('name = ?');
      values.push(cohortData.name);
    }
    if (cohortData.owner_id !== undefined) {
      fields.push('owner_id = ?');
      values.push(cohortData.owner_id);
    }
    
    if (fields.length === 0) {
      return this.getCohortById(cohortData.id);
    }
    
    values.push(cohortData.id);
    
    await pool.execute(
      `UPDATE cohorts SET ${fields.join(', ')} WHERE id = ?`,
      values
    );
    
    return this.getCohortById(cohortData.id);
  }

  static async getCohortById(id: string): Promise<Cohort | null> {
    const [rows] = await pool.execute(
      'SELECT * FROM cohorts WHERE id = ?',
      [id]
    );
    
    return (rows as Cohort[])[0] || null;
  }

  static async deleteCohort(id: string): Promise<void> {
    await pool.execute('DELETE FROM cohorts WHERE id = ?', [id]);
  }

  // Cohort Access operations
  static async createCohortAccess(accessData: CohortAccessInsert): Promise<CohortAccess> {
    const [result] = await pool.execute(
      'INSERT INTO cohort_access (cohort_id, user_id, permissions) VALUES (?, ?, ?)',
      [accessData.cohort_id, accessData.user_id, accessData.permissions]
    );
    
    const [accesses] = await pool.execute(
      'SELECT * FROM cohort_access WHERE id = ?',
      [(result as any).insertId]
    );
    
    return (accesses as CohortAccess[])[0];
  }

  static async getCohortAccess(cohortId: string): Promise<CohortAccess[]> {
    const [rows] = await pool.execute(
      'SELECT * FROM cohort_access WHERE cohort_id = ?',
      [cohortId]
    );
    
    return rows as CohortAccess[];
  }

  static async deleteCohortAccess(id: string): Promise<void> {
    await pool.execute('DELETE FROM cohort_access WHERE id = ?', [id]);
  }

  // Student operations
  static async getStudentsByCohort(cohortId: string): Promise<Student[]> {
    const [rows] = await pool.execute(
      'SELECT * FROM students WHERE cohort_id = ? ORDER BY name',
      [cohortId]
    );
    
    return rows as Student[];
  }

  static async createStudent(studentData: StudentInsert): Promise<Student> {
    const [result] = await pool.execute(
      'INSERT INTO students (name, leergroep, photo_url, cohort_id) VALUES (?, ?, ?, ?)',
      [studentData.name, studentData.leergroep, studentData.photo_url || null, studentData.cohort_id]
    );
    
    const [students] = await pool.execute(
      'SELECT * FROM students WHERE id = ?',
      [(result as any).insertId]
    );
    
    return (students as Student[])[0];
  }

  static async updateStudent(studentData: StudentUpdate): Promise<Student | null> {
    const fields = [];
    const values = [];
    
    if (studentData.name !== undefined) {
      fields.push('name = ?');
      values.push(studentData.name);
    }
    if (studentData.leergroep !== undefined) {
      fields.push('leergroep = ?');
      values.push(studentData.leergroep);
    }
    if (studentData.photo_url !== undefined) {
      fields.push('photo_url = ?');
      values.push(studentData.photo_url);
    }
    if (studentData.cohort_id !== undefined) {
      fields.push('cohort_id = ?');
      values.push(studentData.cohort_id);
    }
    
    if (fields.length === 0) {
      return this.getStudentById(studentData.id);
    }
    
    values.push(studentData.id);
    
    await pool.execute(
      `UPDATE students SET ${fields.join(', ')} WHERE id = ?`,
      values
    );
    
    return this.getStudentById(studentData.id);
  }

  static async getStudentById(id: string): Promise<Student | null> {
    const [rows] = await pool.execute(
      'SELECT * FROM students WHERE id = ?',
      [id]
    );
    
    return (rows as Student[])[0] || null;
  }

  static async deleteStudent(id: string): Promise<void> {
    await pool.execute('DELETE FROM students WHERE id = ?', [id]);
  }

  // Check if user has access to cohort
  static async userHasCohortAccess(userId: string, cohortId: string, permission: 'view' | 'edit' = 'view'): Promise<boolean> {
    const [rows] = await pool.execute(`
      SELECT 1 FROM cohorts c
      LEFT JOIN cohort_access ca ON c.id = ca.cohort_id
      WHERE c.id = ? AND (c.owner_id = ? OR (ca.user_id = ? AND ca.permissions = ?))
      LIMIT 1
    `, [cohortId, userId, userId, permission]);
    
    return (rows as any[]).length > 0;
  }
}
