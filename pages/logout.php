<?php
require '../config.php';
require '../classes/User.php';
require '../classes/Auth.php';

$db = new Database();
$pdo = $db->getConnection();
$auth = new Auth($pdo);

session_start();

// Log the user out
$auth->logout();

// Redirect to login page
header("Location: login.php");
exit;