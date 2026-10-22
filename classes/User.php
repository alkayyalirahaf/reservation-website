<?php

class User
{
    private $pdo;

    // Constructor receives the PDO connection (Dependency Injection)
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Register a new user
    public function register($name, $email, $password)
    {
        $errors = [];

        // 1. Validation
        if (empty(trim($name))) {
            $errors[] = "Name is required.";
        }
        if (empty(trim($email)) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "A valid email is required.";
        }
        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }

        // Check if email already exists
        if (empty($errors) && $this->findByEmail($email)) {
            $errors[] = "Email is already registered.";
        }

        // If there are errors, return them and stop
        if (!empty($errors)) {
            return $errors;
        }

        // 2. Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert into database
        $sql = "INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([$name, $email, $hashedPassword]);
            return []; // Empty array means success
        } catch (PDOException $e) {
            return ["Database error during registration."];
        }
    }

    // Find a user by email (used for login)
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(); // Returns user array or false if not found
    }

    // Find a user by ID (used for sessions)
    public function findById($id)
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}