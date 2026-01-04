<?php
declare(strict_types=1);

namespace LLTool\Models;

use LLTool\Database\Database;
use PDO;

final class Student
{
    public string $id;
    public string $name;
    public int $leergroep;
    public ?string $photo_url;
    public string $cohort_id;
    public string $created_at;
    public string $updated_at;

    /**
     * Find student by ID.
     */
    public static function find(string $id): ?self
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return self::fromArray($data);
    }

    /**
     * Find all students in a cohort.
     */
    public static function findByCohort(string $cohortId, ?int $leergroep = null): array
    {
        $pdo = Database::getConnection();
        
        if ($leergroep !== null) {
            $stmt = $pdo->prepare("
                SELECT * FROM students
                WHERE cohort_id = ? AND leergroep = ?
                ORDER BY name
            ");
            $stmt->execute([$cohortId, $leergroep]);
        } else {
            $stmt = $pdo->prepare("
                SELECT * FROM students
                WHERE cohort_id = ?
                ORDER BY name
            ");
            $stmt->execute([$cohortId]);
        }
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($data) => self::fromArray($data), $results);
    }

    /**
     * Create new student.
     */
    public static function create(array $data): self
    {
        $pdo = Database::getConnection();
        
        $id = $data['id'] ?? self::generateId();
        $name = $data['name'];
        $leergroep = (int)$data['leergroep'];
        $cohortId = $data['cohort_id'];
        $photoUrl = $data['photo_url'] ?? null;
        
        $stmt = $pdo->prepare("
            INSERT INTO students (id, name, leergroep, cohort_id, photo_url)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$id, $name, $leergroep, $cohortId, $photoUrl]);

        return self::find($id);
    }

    /**
     * Update student.
     */
    public function update(array $data): self
    {
        $pdo = Database::getConnection();
        
        $updates = [];
        $params = [];
        
        if (isset($data['name'])) {
            $updates[] = "name = ?";
            $params[] = $data['name'];
            $this->name = $data['name'];
        }
        
        if (isset($data['leergroep'])) {
            $updates[] = "leergroep = ?";
            $params[] = (int)$data['leergroep'];
            $this->leergroep = (int)$data['leergroep'];
        }
        
        if (isset($data['photo_url'])) {
            $updates[] = "photo_url = ?";
            $params[] = $data['photo_url'];
            $this->photo_url = $data['photo_url'];
        }
        
        if (!empty($updates)) {
            $params[] = $this->id;
            $sql = "UPDATE students SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        return $this->refresh();
    }

    /**
     * Delete student.
     */
    public function delete(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$this->id]);
    }

    /**
     * Refresh model from database.
     */
    private function refresh(): self
    {
        $updated = self::find($this->id);
        if ($updated) {
            $this->name = $updated->name;
            $this->leergroep = $updated->leergroep;
            $this->photo_url = $updated->photo_url;
            $this->updated_at = $updated->updated_at;
        }
        return $this;
    }

    /**
     * Create instance from array.
     */
    private static function fromArray(array $data): self
    {
        $student = new self();
        $student->id = $data['id'];
        $student->name = $data['name'];
        $student->leergroep = (int)$data['leergroep'];
        $student->photo_url = $data['photo_url'];
        $student->cohort_id = $data['cohort_id'];
        $student->created_at = $data['created_at'];
        $student->updated_at = $data['updated_at'];
        return $student;
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

