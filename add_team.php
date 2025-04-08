<?php
// Database connection
$host = 'localhost';
$dbname = 'formula1';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("INSERT INTO tim (naziv, br_bodova) VALUES (?, ?)");
    $stmt->bind_param("si", $_POST['naziv'], $_POST['br_bodova']);
    
    if ($stmt->execute()) {
        header("Location: index.php?success=1");
        exit();
    } else {
        die("Error adding team: " . $conn->error);
    }
}
$conn->close();
?>