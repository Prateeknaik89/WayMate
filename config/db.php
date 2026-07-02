<?php
// WayMate/config/db.php

$host = 'localhost';
$db   = 'waymte2'; 
$user = 'root';    
$pass = '';        
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// CORRECTED OPTIONS ARRAY BELOW
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Separated ATTR and ERRMODE
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Connection failed: " . $e->getMessage());
}
?>