<?php
// Database connection
$host = 'localhost';
$dbname = 'formula1';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("INSERT INTO utrka (naziv, lokacija, duzina_staze, br_krugova) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $_POST['naziv'], $_POST['lokacija'], $_POST['duzina_staze'], $_POST['br_krugova']);
    
    if ($stmt->execute()) {
        header("Location: index.php?success=1");
        exit();
    } else {
        die("Error adding race: " . $conn->error);
    }
}
$conn->close();
?>