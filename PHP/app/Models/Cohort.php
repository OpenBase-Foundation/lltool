<?php
declare(strict_types=1);

namespace LLTool\Models;

use LLTool\Database\Database;
use PDO;

final class Cohort
{
    public string $id;
    public string $name;
    public string $owner_id;
    public string $created_at;
    public string $updated_at;

    /**
     * Find cohort by ID.
     */
    public static function find(string $id): ?self
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM cohorts WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return self::fromArray($data);
    }

    /**
     * Find all cohorts for a user.
     */
    public static function findByOwner(string $ownerId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT c.* FROM cohorts c
            WHERE c.owner_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$ownerId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($data) => self::fromArray($data), $results);
    }

    /**
     * Find cohorts accessible by user (owned or shared).
     */
    public static function findAccessibleByUser(string $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT DISTINCT c.* FROM cohorts c
            LEFT JOIN cohort_access ca ON c.id = ca.cohort_id
            WHERE c.owner_id = ? OR ca.user_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$userId, $userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($data) => self::fromArray($data), $results);
    }

    /**
     * Create new cohort.
     */
    public static function create(array $data): self
    {
        $pdo = Database::getConnection();
        
        $id = $data['id'] ?? self::generateId();
        $name = $data['name'];
        $ownerId = $data['owner_id'];
        
        $stmt = $pdo->prepare("
            INSERT INTO cohorts (id, name, owner_id)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$id, $name, $ownerId]);

        return self::find($id);
    }

    /**
     * Update cohort.
     */
    public function update(array $data): self
    {
        $pdo = Database::getConnection();
        
        if (isset($data['name'])) {
            $stmt = $pdo->prepare("UPDATE cohorts SET name = ? WHERE id = ?");
            $stmt->execute([$data['name'], $this->id]);
            $this->name = $data['name'];
        }

        return $this->refresh();
    }

    /**
     * Delete cohort.
     */
    public function delete(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM cohorts WHERE id = ?");
        $stmt->execute([$this->id]);
    }

    /**
     * Check if user is owner.
     */
    public function isOwner(string $userId): bool
    {
        return $this->owner_id === $userId;
    }

    /**
     * Refresh model from database.
     */
    private function refresh(): self
    {
        $updated = self::find($this->id);
        if ($updated) {
            $this->name = $updated->name;
            $this->updated_at = $updated->updated_at;
        }
        return $this;
    }

    /**
     * Create instance from array.
     */
    private static function fromArray(array $data): self
    {
        $cohort = new self();
        $cohort->id = $data['id'];
        $cohort->name = $data['name'];
        $cohort->owner_id = $data['owner_id'];
        $cohort->created_at = $data['created_at'];
        $cohort->updated_at = $data['updated_at'];
        return $cohort;
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

