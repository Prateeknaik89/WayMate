<?php
// WayMate/config/db.php

define('GEOAPIFY_API_KEY', 'db1860f6b2bd44a29e66ce1c8bf445fd');

$host = 'localhost';
$db   = 'waymte2'; // Your database name from the SQL file
$user = 'root';    // Default XAMPP user
$pass = '';        // Default XAMPP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ATTR_ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // In a real app, don't echo the error, but for debugging your project, it's helpful
     die("Connection failed: " . $e->getMessage());
}
?>