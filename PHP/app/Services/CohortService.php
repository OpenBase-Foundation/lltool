<?php
declare(strict_types=1);

namespace LLTool\Services;

use LLTool\Models\Cohort;
use LLTool\Models\CohortAccess;

final class CohortService
{
    /**
     * Get all cohorts accessible by user.
     */
    public function getAccessibleCohorts(string $userId): array
    {
        return Cohort::findAccessibleByUser($userId);
    }

    /**
     * Get cohort by ID with permission check.
     */
    public function getCohort(string $id, string $userId): ?Cohort
    {
        $cohort = Cohort::find($id);
        
        if (!$cohort) {
            return null;
        }

        // Check if user has access
        if (!$this->hasAccess($cohort, $userId)) {
            return null;
        }

        return $cohort;
    }

    /**
     * Create new cohort.
     */
    public function createCohort(string $name, string $ownerId): Cohort
    {
        return Cohort::create([
            'name' => $name,
            'owner_id' => $ownerId,
        ]);
    }

    /**
     * Update cohort (only owner can update).
     */
    public function updateCohort(string $id, string $name, string $userId): ?Cohort
    {
        $cohort = Cohort::find($id);
        
        if (!$cohort || !$cohort->isOwner($userId)) {
            return null;
        }

        return $cohort->update(['name' => $name]);
    }

    /**
     * Delete cohort (only owner can delete).
     */
    public function deleteCohort(string $id, string $userId): bool
    {
        $cohort = Cohort::find($id);
        
        if (!$cohort || !$cohort->isOwner($userId)) {
            return false;
        }

        $cohort->delete();
        return true;
    }

    /**
     * Share cohort with user.
     */
    public function shareCohort(string $cohortId, string $userId, string $permissions, string $ownerId): bool
    {
        $cohort = Cohort::find($cohortId);
        
        if (!$cohort || !$cohort->isOwner($ownerId)) {
            return false;
        }

        // Don't share with owner
        if ($cohort->owner_id === $userId) {
            return false;
        }

        // Check if access already exists
        $existing = CohortAccess::findByCohortAndUser($cohortId, $userId);
        
        if ($existing) {
            $existing->updatePermissions($permissions);
        } else {
            CohortAccess::create([
                'cohort_id' => $cohortId,
                'user_id' => $userId,
                'permissions' => $permissions,
            ]);
        }

        return true;
    }

    /**
     * Remove access to cohort.
     */
    public function removeAccess(string $cohortId, string $userId, string $ownerId): bool
    {
        $cohort = Cohort::find($cohortId);
        
        if (!$cohort || !$cohort->isOwner($ownerId)) {
            return false;
        }

        $access = CohortAccess::findByCohortAndUser($cohortId, $userId);
        
        if ($access) {
            $access->delete();
            return true;
        }

        return false;
    }

    /**
     * Get shared users for a cohort.
     */
    public function getSharedUsers(string $cohortId): array
    {
        return CohortAccess::findByCohort($cohortId);
    }

    /**
     * Check if user has access to cohort.
     */
    public function hasAccess(Cohort $cohort, string $userId): bool
    {
        // Owner always has access
        if ($cohort->isOwner($userId)) {
            return true;
        }

        // Check shared access
        $access = CohortAccess::findByCohortAndUser($cohort->id, $userId);
        return $access !== null;
    }

    /**
     * Check if user can edit cohort.
     */
    public function canEdit(Cohort $cohort, string $userId): bool
    {
        if ($cohort->isOwner($userId)) {
            return true;
        }

        $access = CohortAccess::findByCohortAndUser($cohort->id, $userId);
        return $access !== null && $access->canEdit();
    }
}

