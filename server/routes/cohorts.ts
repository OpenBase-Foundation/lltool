import { Router, Response } from 'express';
import { DatabaseService } from '../services/database.js';
import { CohortInsert, CohortUpdate, CohortAccessInsert } from '../types/database.js';
import { AuthenticatedRequest, authenticateToken } from '../middleware/auth.js';

const router = Router();

// All routes require authentication
router.use(authenticateToken);

// Get all cohorts for the authenticated user
router.get('/', async (req: AuthenticatedRequest, res: Response) => {
  try {
    if (!req.user) {
      return res.status(401).json({ error: 'User not authenticated' });
    }

    const cohorts = await DatabaseService.getCohortsByUser(req.user.id);
    res.json(cohorts);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// Get a specific cohort
router.get('/:id', async (req: AuthenticatedRequest, res: Response) => {
  try {
    if (!req.user) {
      return res.status(401).json({ error: 'User not authenticated' });
    }

    const cohortId = req.params.id;
    const hasAccess = await DatabaseService.userHasCohortAccess(req.user.id, cohortId);
    
    if (!hasAccess) {
      return res.status(403).json({ error: 'Access denied' });
    }

    const cohort = await DatabaseService.getCohortById(cohortId);
    
    if (!cohort) {
      return res.status(404).json({ error: 'Cohort not found' });
    }

    res.json(cohort);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// Create a new cohort
router.post('/', async (req: AuthenticatedRequest, res: Response) => {
  try {
    if (!req.user) {
      return res.status(401).json({ error: 'User not authenticated' });
    }

    const cohortData: CohortInsert = {
      name: req.body.name,
      owner_id: req.user.id
    };

    const cohort = await DatabaseService.createCohort(cohortData);
    res.status(201).json(cohort);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// Update a cohort
router.put('/:id', async (req: AuthenticatedRequest, res: Response) => {
  try {
    if (!req.user) {
      return res.status(401).json({ error: 'User not authenticated' });
    }

    const cohortId = req.params.id;
    const hasEditAccess = await DatabaseService.userHasCohortAccess(req.user.id, cohortId, 'edit');
    
    if (!hasEditAccess) {
      return res.status(403).json({ error: 'Edit access denied' });
    }

    const cohortData: CohortUpdate = {
      id: cohortId,
      name: req.body.name
    };

    const cohort = await DatabaseService.updateCohort(cohortData);
    res.json(cohort);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// Delete a cohort
router.delete('/:id', async (req: AuthenticatedRequest, res: Response) => {
  try {
    if (!req.user) {
      return res.status(401).json({ error: 'User not authenticated' });
    }

    const cohortId = req.params.id;
    const cohort = await DatabaseService.getCohortById(cohortId);
    
    if (!cohort) {
      return res.status(404).json({ error: 'Cohort not found' });
    }

    if (cohort.owner_id !== req.user.id) {
      return res.status(403).json({ error: 'Only the owner can delete a cohort' });
    }

    await DatabaseService.deleteCohort(cohortId);
    res.status(204).send();
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// Share a cohort
router.post('/:id/share', async (req: AuthenticatedRequest, res: Response) => {
  try {
    if (!req.user) {
      return res.status(401).json({ error: 'User not authenticated' });
    }

    const cohortId = req.params.id;
    const cohort = await DatabaseService.getCohortById(cohortId);
    
    if (!cohort) {
      return res.status(404).json({ error: 'Cohort not found' });
    }

    if (cohort.owner_id !== req.user.id) {
      return res.status(403).json({ error: 'Only the owner can share a cohort' });
    }

    const accessData: CohortAccessInsert = {
      cohort_id: cohortId,
      user_id: req.body.user_id,
      permissions: req.body.permissions || 'view'
    };

    const cohortAccess = await DatabaseService.createCohortAccess(accessData);
    res.status(201).json(cohortAccess);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// Get cohort access list
router.get('/:id/access', async (req: AuthenticatedRequest, res: Response) => {
  try {
    if (!req.user) {
      return res.status(401).json({ error: 'User not authenticated' });
    }

    const cohortId = req.params.id;
    const hasAccess = await DatabaseService.userHasCohortAccess(req.user.id, cohortId);
    
    if (!hasAccess) {
      return res.status(403).json({ error: 'Access denied' });
    }

    const accessList = await DatabaseService.getCohortAccess(cohortId);
    res.json(accessList);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// Remove cohort access
router.delete('/access/:accessId', async (req: AuthenticatedRequest, res: Response) => {
  try {
    if (!req.user) {
      return res.status(401).json({ error: 'User not authenticated' });
    }

    const accessId = req.params.accessId;
    // TODO: Add validation to ensure user is the owner of the cohort
    // For now, we'll allow any authenticated user to remove access
    
    await DatabaseService.deleteCohortAccess(accessId);
    res.status(204).send();
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

export default router;
