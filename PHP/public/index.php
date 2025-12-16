<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Security.php';
require_once __DIR__ . '/../src/Authorization.php';
require_once __DIR__ . '/../src/Config.php';
require_once __DIR__ . '/../src/SetupController.php';
require_once __DIR__ . '/../src/AuthController.php';
require_once __DIR__ . '/../src/AdminController.php';
require_once __DIR__ . '/../src/CohortController.php';
require_once __DIR__ . '/../src/StudentController.php';
require_once __DIR__ . '/../src/helpers.php';

use App\Database;
use App\Auth;
use App\Security;
use App\Authorization;
use App\Config;
use App\SetupController;
use App\AuthController;
use App\AdminController;
use App\CohortController;
use App\StudentController;

Security::initSession();
Security::setSecurityHeaders();
Security::validateRequest();

$db = new Database();
$pdo = $db->getPdo();
$auth = new Auth($pdo);
$config = new Config($pdo);

// Check if app needs setup
if (!$config->isInitialized()) {
    $setupCtrl = new SetupController($pdo, $config);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'process') {
        $setupCtrl->process();
    } else {
        $setupCtrl->show();
    }
    exit;
}

$page = $_GET['page'] ?? 'home';

if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($auth->signIn($email, $password)) {
        header('Location: /?page=home');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}

if ($page === 'logout') {
    $auth->signOut();
    header('Location: /');
    exit;
}

$cohortCtrl = new CohortController($pdo);
$studentCtrl = new StudentController($pdo);
$authCtrl = new AuthController($pdo, $config);
$adminCtrl = new AdminController($pdo, $config);

// Require authentication for all pages except login/logout/auth
if (!$auth->currentUser() && $page !== 'login' && $page !== 'logout' && $page !== 'auth') {
    $show_register_link = $config->allowUserRegistration();
    include __DIR__ . '/../templates/header.php';
    include __DIR__ . '/../templates/login_form.php';
    include __DIR__ . '/../templates/footer.php';
    exit;
}

// Basic action routing for cohorts and students
$action = $_GET['action'] ?? null;
switch ($page) {
    case 'cohorts':
        if ($action === 'new') {
            $cohortCtrl->createForm();
        } elseif ($action === 'edit' && !empty($_GET['id'])) {
            $cohortCtrl->editForm(intval($_GET['id']));
        } elseif ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $cohortCtrl->save();
        } elseif ($action === 'delete' && !empty($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $cohortCtrl->delete(intval($_GET['id']));
        } else {
            $cohortCtrl->index();
        }
        break;

    case 'students':
        $cohortId = $_GET['cohort_id'] ?? null;
        if ($action === 'new') {
            $studentCtrl->createForm($cohortId);
        } elseif ($action === 'edit' && !empty($_GET['id'])) {
            $studentCtrl->editForm(intval($_GET['id']));
        } elseif ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentCtrl->save();
        } elseif ($action === 'delete' && !empty($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentCtrl->delete(intval($_GET['id']), $_GET['cohort_id'] ?? null);
        } else {
            $studentCtrl->index($cohortId);
        }
        break;

    case 'auth':
        $action = $_GET['action'] ?? null;
        if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $authCtrl->processRegister();
        } elseif ($action === 'register') {
            $authCtrl->register();
        } else {
            header('Location: /?page=login');
        }
        break;

    case 'admin':
        $action = $_GET['action'] ?? 'dashboard';
        if ($action === 'settings') {
            $adminCtrl->settings();
        } elseif ($action === 'update_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminCtrl->updateSettings();
        } elseif ($action === 'users') {
            $adminCtrl->users();
        } elseif ($action === 'delete_user' && !empty($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminCtrl->deleteUser(intval($_GET['id']));
        } else {
            $adminCtrl->dashboard();
        }
        break;

    default:
        include __DIR__ . '/../templates/header.php';
        include __DIR__ . '/../templates/home.php';
        include __DIR__ . '/../templates/footer.php';
}

