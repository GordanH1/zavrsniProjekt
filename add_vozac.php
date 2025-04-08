<?php
// Database connection
$host = 'localhost';
$dbname = 'formula1';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("INSERT INTO vozac (ime, prezime, br_bodova, br_utrka, timID) VALUES (?, ?, ?, ?, ?)");
    $timID = !empty($_POST['timID']) ? $_POST['timID'] : null;
    $stmt->bind_param("ssiii", $_POST['ime'], $_POST['prezime'], $_POST['br_bodova'], $_POST['br_utrka'], $timID);
    
    if ($stmt->execute()) {
        header("Location: index.php?success=1");
        exit();
    } else {
        die("Error adding driver: " . $conn->error);
    }
}
$conn->close();
?>