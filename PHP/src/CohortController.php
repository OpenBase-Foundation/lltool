<?php

namespace App;

class CohortController {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index() {
        $stmt = $this->pdo->query('SELECT * FROM cohorts ORDER BY created_at DESC');
        $cohorts = $stmt->fetchAll();
        include __DIR__ . '/../templates/header.php';
        include __DIR__ . '/../templates/cohort_list.php';
        include __DIR__ . '/../templates/footer.php';
    }

    public function createForm($errors = [], $old = []) {
        $cohort = null;
        include __DIR__ . '/../templates/header.php';
        include __DIR__ . '/../templates/cohort_form.php';
        include __DIR__ . '/../templates/footer.php';
    }

    public function editForm($id, $errors = [], $old = []) {
        $stmt = $this->pdo->prepare('SELECT * FROM cohorts WHERE id = ?');
        $stmt->execute([$id]);
        $cohort = $stmt->fetch();
        include __DIR__ . '/../templates/header.php';
        include __DIR__ . '/../templates/cohort_form.php';
        include __DIR__ . '/../templates/footer.php';
    }

    public function save() {
        $name = \App\sanitize_string($_POST['name'] ?? '');
        $description = \App\sanitize_string($_POST['description'] ?? '');
        $csrf = $_POST['_csrf'] ?? '';
        if (!\App\verify_csrf($csrf)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }
        $errors = [];
        if (strlen($name) === 0) $errors[] = 'Name is required';
        if (strlen($name) > 255) $errors[] = 'Name is too long';
        if (!empty($errors)) {
            return $this->createForm($errors, $_POST);
        }

        try {
            if (!empty($_POST['id'])) {
                $stmt = $this->pdo->prepare('UPDATE cohorts SET name = ?, description = ? WHERE id = ?');
                $stmt->execute([$name, $description, intval($_POST['id'])]);
                \App\log_event('INFO', 'Cohort updated', ['cohort_id' => $_POST['id'], 'user_id' => $_SESSION['user_id'] ?? null]);
            } else {
                $stmt = $this->pdo->prepare('INSERT INTO cohorts (name, description) VALUES (?, ?)');
                $stmt->execute([$name, $description]);
                \App\log_event('INFO', 'Cohort created', ['user_id' => $_SESSION['user_id'] ?? null]);
            }
        } catch (Exception $e) {
            \App\log_security('Cohort save failed', ['error' => $e->getMessage()]);
            $errors[] = 'Database error. Please try again.';
            return $this->createForm($errors, $_POST);
        }
        header('Location: /?page=cohorts');
        exit;
    }

    public function delete($id) {
        $csrf = $_POST['_csrf'] ?? '';
        if (!\App\verify_csrf($csrf)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }
        try {
            $stmt = $this->pdo->prepare('DELETE FROM cohorts WHERE id = ?');
            $stmt->execute([intval($id)]);
            \App\log_event('INFO', 'Cohort deleted', ['cohort_id' => $id, 'user_id' => $_SESSION['user_id'] ?? null]);
        } catch (Exception $e) {
            \App\log_security('Cohort delete failed', ['error' => $e->getMessage()]);
        }
        header('Location: /?page=cohorts');
        exit;
    }
}
