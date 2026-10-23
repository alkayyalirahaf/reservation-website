<?php

class Auth
{
    private $pdo;
    private $userModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        // Auth needs User class to fetch user data
        $this->userModel = new User($pdo);
    }

    // Attempt to log the user in
    public function login($email, $password)
    {
        // 1. Find user by email
        $user = $this->userModel->findByEmail($email);

        // 2. Check if user exists and password is correct
        // We use one generic error for both to prevent email enumeration
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ["Invalid email or password."];
        }

        // 3. Check if account is active
        if ($user['is_active'] == 0) {
            return ["Your account has been deactivated. Please contact support."];
        }

        // 4. Success! Save user info in Session
        // We regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);
        
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role']      = $user['role'];

        return []; // Empty array = success
    }

    // Log the user out
    public function logout()
    {
        // Clear all session variables
        $_SESSION = [];
        // Destroy the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    // Check if someone is logged in
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    // Check if the logged-in user is an admin
    public function isAdmin()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    // Guard for user pages: redirect to login if not logged in
    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            header("Location: ../pages/login.php");
            exit;
        }

        // Re-check is_active on every request (takes effect immediately)
        $user = $this->userModel->findById($_SESSION['user_id']);
        if (!$user || $user['is_active'] == 0) {
            $this->logout();
            header("Location: ../pages/login.php");
            exit;
        }
    }

    // Guard for admin pages: redirect guests to login, block non-admins
    public function requireAdmin()
    {
        if (!$this->isLoggedIn()) {
            // Admin pages are in /admin, so path is ../pages/login.php
            header("Location: ../pages/login.php");
            exit;
        }

        if (!$this->isAdmin()) {
            // Logged in but not admin -> 403 Forbidden
            http_response_code(403);
            die("403 Forbidden: You do not have permission to access this page.");
        }
    }


    
}