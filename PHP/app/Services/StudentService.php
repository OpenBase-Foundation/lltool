<?php
declare(strict_types=1);

namespace LLTool\Services;

use LLTool\Models\Student;
use LLTool\Models\Cohort;
use LLTool\Services\CohortService;

final class StudentService
{
    private CohortService $cohortService;

    public function __construct()
    {
        $this->cohortService = new CohortService();
    }

    /**
     * Get students in cohort with permission check.
     */
    public function getStudents(string $cohortId, string $userId, ?int $leergroep = null): array
    {
        $cohort = Cohort::find($cohortId);
        
        if (!$cohort || !$this->cohortService->hasAccess($cohort, $userId)) {
            return [];
        }

        return Student::findByCohort($cohortId, $leergroep);
    }

    /**
     * Get student by ID with permission check.
     */
    public function getStudent(string $id, string $userId): ?Student
    {
        $student = Student::find($id);
        
        if (!$student) {
            return null;
        }

        $cohort = Cohort::find($student->cohort_id);
        
        if (!$cohort || !$this->cohortService->hasAccess($cohort, $userId)) {
            return null;
        }

        return $student;
    }

    /**
     * Create new student.
     */
    public function createStudent(array $data, string $userId): ?Student
    {
        $cohort = Cohort::find($data['cohort_id']);
        
        if (!$cohort || !$this->cohortService->canEdit($cohort, $userId)) {
            return null;
        }

        return Student::create($data);
    }

    /**
     * Update student.
     */
    public function updateStudent(string $id, array $data, string $userId): ?Student
    {
        $student = Student::find($id);
        
        if (!$student) {
            return null;
        }

        $cohort = Cohort::find($student->cohort_id);
        
        if (!$cohort || !$this->cohortService->canEdit($cohort, $userId)) {
            return null;
        }

        return $student->update($data);
    }

    /**
     * Delete student.
     */
    public function deleteStudent(string $id, string $userId): bool
    {
        $student = Student::find($id);
        
        if (!$student) {
            return false;
        }

        $cohort = Cohort::find($student->cohort_id);
        
        if (!$cohort || !$this->cohortService->canEdit($cohort, $userId)) {
            return false;
        }

        $student->delete();
        return true;
    }
}

