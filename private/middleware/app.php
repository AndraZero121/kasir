<?php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Include the configuration file
include __DIR__ . '/../config/config.php';

// Define the connection function
function connection()
{
  try {
    // Create a new PDO connection
    $pdo = new PDO("mysql:host=" . HOST . ";port=" . PORT . ";dbname=" . DATABASE, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo; // Return the PDO connection
  } catch (PDOException $e) {
    // Handle connection error
    error_log('Connection failed: ' . $e->getMessage()); // Log the actual error
    header("Location: ../500.php"); // Redirect to 500 error page
    exit();
  }
}

// Call the connection function and assign it to $pdo
$pdo = connection();
