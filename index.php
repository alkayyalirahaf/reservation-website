<?php
// 1. Require config and classes
require 'config.php';
require 'classes/User.php';
require 'classes/Auth.php';

// 2. DB Connection & Auth instance
$db = new Database();
$pdo = $db->getConnection();
$auth = new Auth($pdo);

// 3. Start Session
session_start();

// 4. Route the user based on their status
if (!$auth->isLoggedIn()) {
    // Guest -> go to login
    header("Location: pages/login.php");
    exit;
} 
elseif ($auth->isAdmin()) {
    // Admin -> go to admin dashboard (we'll create it later)
    header("Location: admin/dashboard.php");
    exit;
} 
else {
    // Normal user -> go to available slots
    header("Location: pages/slots.php");
    exit;
}