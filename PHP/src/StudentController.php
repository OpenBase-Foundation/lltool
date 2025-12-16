<?php

namespace App;

class StudentController {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index($cohortId = null) {
        if (!$cohortId) {
            echo "<p>Please select a cohort.</p>";
            return;
        }
        $stmt = $this->pdo->prepare('SELECT * FROM students WHERE cohort_id = ? ORDER BY created_at DESC');
        $stmt->execute([$cohortId]);
        $students = $stmt->fetchAll();
        include __DIR__ . '/../templates/header.php';
        include __DIR__ . '/../templates/student_list.php';
        include __DIR__ . '/../templates/footer.php';
    }

    public function createForm($cohortId = null, $errors = [], $old = []) {
        $student = null;
        $cohort_id = $cohortId ?? ($old['cohort_id'] ?? null);
        include __DIR__ . '/../templates/header.php';
        include __DIR__ . '/../templates/student_form.php';
        include __DIR__ . '/../templates/footer.php';
    }

    public function editForm($id, $errors = [], $old = []) {
        $stmt = $this->pdo->prepare('SELECT * FROM students WHERE id = ?');
        $stmt->execute([$id]);
        $student = $stmt->fetch();
        $cohort_id = $student['cohort_id'] ?? null;
        include __DIR__ . '/../templates/header.php';
        include __DIR__ . '/../templates/student_form.php';
        include __DIR__ . '/../templates/footer.php';
    }

    public function save() {
        $first = \App\sanitize_string($_POST['first_name'] ?? '');
        $last = \App\sanitize_string($_POST['last_name'] ?? '');
        $email = \App\sanitize_email($_POST['email'] ?? '');
        $cohort_id = intval($_POST['cohort_id'] ?? 0);
        $csrf = $_POST['_csrf'] ?? '';
        if (!\App\verify_csrf($csrf)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }
        $errors = [];
        if (strlen($first) === 0) $errors[] = 'First name is required';
        if (strlen($last) === 0) $errors[] = 'Last name is required';
        if (!$cohort_id) $errors[] = 'Cohort is required';
        if (!empty($email) && !$email) $errors[] = 'Invalid email format';
        if (strlen($first) > 255) $errors[] = 'First name is too long';
        if (strlen($last) > 255) $errors[] = 'Last name is too long';
        if (!empty($errors)) {
            return $this->createForm($cohort_id, $errors, $_POST);
        }

        try {
            if (!empty($_POST['id'])) {
                $stmt = $this->pdo->prepare('UPDATE students SET first_name = ?, last_name = ?, email = ? WHERE id = ?');
                $stmt->execute([$first, $last, $email, intval($_POST['id'])]);
                \App\log_event('INFO', 'Student updated', ['student_id' => $_POST['id'], 'user_id' => $_SESSION['user_id'] ?? null]);
            } else {
                $stmt = $this->pdo->prepare('INSERT INTO students (cohort_id, first_name, last_name, email) VALUES (?, ?, ?, ?)');
                $stmt->execute([$cohort_id, $first, $last, $email]);
                \App\log_event('INFO', 'Student created', ['user_id' => $_SESSION['user_id'] ?? null]);
            }
        } catch (Exception $e) {
            \App\log_security('Student save failed', ['error' => $e->getMessage()]);
            $errors[] = 'Database error. Please try again.';
            return $this->createForm($cohort_id, $errors, $_POST);
        }
        header('Location: /?page=students&cohort_id=' . $cohort_id);
        exit;
    }

    public function delete($id, $cohortId = null) {
        $csrf = $_POST['_csrf'] ?? '';
        if (!\App\verify_csrf($csrf)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }
        try {
            $stmt = $this->pdo->prepare('DELETE FROM students WHERE id = ?');
            $stmt->execute([intval($id)]);
            \App\log_event('INFO', 'Student deleted', ['student_id' => $id, 'user_id' => $_SESSION['user_id'] ?? null]);
        } catch (Exception $e) {
            \App\log_security('Student delete failed', ['error' => $e->getMessage()]);
        }
        $redirect = '/?page=cohorts';
        if ($cohortId) $redirect = '/?page=students&cohort_id=' . intval($cohortId);
        header('Location: ' . $redirect);
        exit;
    }
}
