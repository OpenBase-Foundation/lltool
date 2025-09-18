import { Router, Response } from 'express';
import { DatabaseService } from '../services/database';
import { StudentInsert, StudentUpdate } from '../types/database';
import { AuthenticatedRequest, authenticateToken } from '../middleware/auth';

const router = Router();

// All routes require authentication
router.use(authenticateToken);

// Get students for a cohort
router.get('/cohort/:cohortId', async (req: AuthenticatedRequest, res: Response) => {
  try {
    if (!req.user) {
      return res.status(401).json({ error: 'User not authenticated' });
    }

    const cohortId = req.params.cohortId;
    const hasAccess = await DatabaseService.userHasCohortAccess(req.user.id, cohortId);
    
    if (!hasAccess) {
      return res.status(403).json({ error: 'Access denied' });
    }

    const students = await DatabaseService.getStudentsByCohort(cohortId);
    res.json(students);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// Get a specific student
router.get('/:id', async (req: AuthenticatedRequest, res: Response) => {
  try {
    if (!req.user) {
      return res.status(401).json({ error: 'User not authenticated' });
    }

    const studentId = req.params.id;
    const student = await DatabaseService.getStudentById(studentId);
    
    if (!student) {
      return res.status(404).json({ error: 'Student not found' });
    }

    const hasAccess = await DatabaseService.userHasCohortAccess(req.user.id, student.cohort_id);
    
    if (!hasAccess) {
      return res.status(403).json({ error: 'Access denied' });
    }

    res.json(student);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// Create a new student
router.post('/', async (req: AuthenticatedRequest, res: Response) => {
  try {
    if (!req.user) {
      return res.status(401).json({ error: 'User not authenticated' });
    }

    const cohortId = req.body.cohort_id;
    const hasEditAccess = await DatabaseService.userHasCohortAccess(req.user.id, cohortId, 'edit');
    
    if (!hasEditAccess) {
      return res.status(403).json({ error: 'Edit access denied' });
    }

    const studentData: StudentInsert = {
      name: req.body.name,
      leergroep: req.body.leergroep,
      photo_url: req.body.photo_url || null,
      cohort_id: cohortId
    };

    const student = await DatabaseService.createStudent(studentData);
    res.status(201).json(student);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// Update a student
router.put('/:id', async (req: AuthenticatedRequest, res: Response) => {
  try {
    if (!req.user) {
      return res.status(401).json({ error: 'User not authenticated' });
    }

    const studentId = req.params.id;
    const student = await DatabaseService.getStudentById(studentId);
    
    if (!student) {
      return res.status(404).json({ error: 'Student not found' });
    }

    const hasEditAccess = await DatabaseService.userHasCohortAccess(req.user.id, student.cohort_id, 'edit');
    
    if (!hasEditAccess) {
      return res.status(403).json({ error: 'Edit access denied' });
    }

    const studentData: StudentUpdate = {
      id: studentId,
      name: req.body.name,
      leergroep: req.body.leergroep,
      photo_url: req.body.photo_url
    };

    const updatedStudent = await DatabaseService.updateStudent(studentData);
    res.json(updatedStudent);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// Delete a student
router.delete('/:id', async (req: AuthenticatedRequest, res: Response) => {
  try {
    if (!req.user) {
      return res.status(401).json({ error: 'User not authenticated' });
    }

    const studentId = req.params.id;
    const student = await DatabaseService.getStudentById(studentId);
    
    if (!student) {
      return res.status(404).json({ error: 'Student not found' });
    }

    const hasEditAccess = await DatabaseService.userHasCohortAccess(req.user.id, student.cohort_id, 'edit');
    
    if (!hasEditAccess) {
      return res.status(403).json({ error: 'Edit access denied' });
    }

    await DatabaseService.deleteStudent(studentId);
    res.status(204).send();
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

export default router;
