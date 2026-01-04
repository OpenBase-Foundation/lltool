<?php
declare(strict_types=1);

namespace LLTool\Models;

use LLTool\Database\Database;
use PDO;

final class CohortAccess
{
    public string $id;
    public string $cohort_id;
    public string $user_id;
    public string $permissions;
    public string $created_at;

    /**
     * Find access by ID.
     */
    public static function find(string $id): ?self
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM cohort_access WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return self::fromArray($data);
    }

    /**
     * Find access for a cohort and user.
     */
    public static function findByCohortAndUser(string $cohortId, string $userId): ?self
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM cohort_access
            WHERE cohort_id = ? AND user_id = ?
        ");
        $stmt->execute([$cohortId, $userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return self::fromArray($data);
    }

    /**
     * Find all access records for a cohort.
     */
    public static function findByCohort(string $cohortId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM cohort_access
            WHERE cohort_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$cohortId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($data) => self::fromArray($data), $results);
    }

    /**
     * Create new access record.
     */
    public static function create(array $data): self
    {
        $pdo = Database::getConnection();
        
        $id = $data['id'] ?? self::generateId();
        $cohortId = $data['cohort_id'];
        $userId = $data['user_id'];
        $permissions = $data['permissions'];
        
        $stmt = $pdo->prepare("
            INSERT INTO cohort_access (id, cohort_id, user_id, permissions)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$id, $cohortId, $userId, $permissions]);

        return self::find($id);
    }

    /**
     * Update permissions.
     */
    public function updatePermissions(string $permissions): self
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE cohort_access
            SET permissions = ?
            WHERE id = ?
        ");
        $stmt->execute([$permissions, $this->id]);
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Delete access record.
     */
    public function delete(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM cohort_access WHERE id = ?");
        $stmt->execute([$this->id]);
    }

    /**
     * Check if user has edit permission.
     */
    public function canEdit(): bool
    {
        return $this->permissions === 'edit';
    }

    /**
     * Create instance from array.
     */
    private static function fromArray(array $data): self
    {
        $access = new self();
        $access->id = $data['id'];
        $access->cohort_id = $data['cohort_id'];
        $access->user_id = $data['user_id'];
        $access->permissions = $data['permissions'];
        $access->created_at = $data['created_at'];
        return $access;
    }

    /**
     * Generate UUID v4.
     */
    private static function generateId(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

