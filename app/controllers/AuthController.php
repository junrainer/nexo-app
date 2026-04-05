<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function showLogin(): void {
        require __DIR__ . '/../views/auth/login.php';
    }

    public function showRegister(): void {
        require __DIR__ . '/../views/auth/register.php';
    }

    public function login(): void {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all fields.';
            header('Location: index.php?url=login');
            exit;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Invalid email or password.';
            header('Location: index.php?url=login');
            exit;
        }

        $_SESSION['user_id']       = $user['id'];
        $_SESSION['username']      = $user['username'];
        $_SESSION['full_name']     = $user['full_name'];
        $_SESSION['profile_image'] = $user['profile_image'];
        header('Location: index.php?url=feed');
        exit;
    }

    public function register(): void {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';

        if (empty($firstName) || empty($lastName) || empty($username) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'All fields are required.';
            header('Location: index.php?url=register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address.';
            header('Location: index.php?url=register');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters.';
            header('Location: index.php?url=register');
            exit;
        }

        if ($this->userModel->findByEmail($email)) {
            $_SESSION['error'] = 'Email is already taken.';
            header('Location: index.php?url=register');
            exit;
        }

        if ($this->userModel->findByUsername($username)) {
            $_SESSION['error'] = 'Username is already taken.';
            header('Location: index.php?url=register');
            exit;
        }

        $fullName = $firstName . ' ' . $lastName;
        $id = $this->userModel->create($username, $email, $password, $fullName);

        $_SESSION['user_id']       = $id;
        $_SESSION['username']      = $username;
        $_SESSION['full_name']     = $fullName;
        $_SESSION['profile_image'] = 'default.png';
        $_SESSION['success']       = 'Account created! Welcome to Nexo.';
        header('Location: index.php?url=feed');
        exit;
    }

    public function logout(): void {
        session_destroy();
        header('Location: index.php?url=login');
        exit;
    }
}