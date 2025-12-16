<?php
// Seed script: creates an admin user and some sample cohorts + students
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

use App\Database;
use App\Auth;

$db = new Database();
$pdo = $db->getPdo();

function createUser($pdo, $email, $password) {
    $hash = Auth::hashPassword($password);
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
    try {
        $stmt->execute([$email, $hash]);
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        echo "User creation failed: " . $e->getMessage() . "\n";
        return null;
    }
}

function createCohort($pdo, $name, $desc = null) {
    $stmt = $pdo->prepare('INSERT INTO cohorts (name, description) VALUES (?, ?)');
    $stmt->execute([$name, $desc]);
    return $pdo->lastInsertId();
}

function createStudent($pdo, $cohortId, $first, $last, $email = null) {
    $stmt = $pdo->prepare('INSERT INTO students (cohort_id, first_name, last_name, email) VALUES (?, ?, ?, ?)');
    $stmt->execute([$cohortId, $first, $last, $email]);
    return $pdo->lastInsertId();
}

echo "Seeding database...\n";

// create admin user
$adminEmail = 'admin@example.com';
$adminPass = 'password123';
$uid = createUser($pdo, $adminEmail, $adminPass);
if ($uid) echo "Created admin user: $adminEmail (password: $adminPass)\n";

// sample cohorts
$c1 = createCohort($pdo, 'Cohort Alpha', 'First sample cohort');
$c2 = createCohort($pdo, 'Cohort Beta', 'Second sample cohort');

// sample students
createStudent($pdo, $c1, 'Alice', 'Anderson', 'alice@example.com');
createStudent($pdo, $c1, 'Bob', 'Brown', 'bob@example.com');
createStudent($pdo, $c2, 'Carol', 'Clark', 'carol@example.com');

echo "Seeding complete.\n";
