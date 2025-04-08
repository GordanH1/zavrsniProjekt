<?php
// Database connection
$host = 'localhost';
$dbname = 'formula1';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required = ['IDvozac', 'IDutrka', 'start_pozicija', 'br_bodova', 'prosjecna_brzina', 'najbrzi_krug', 'ukupno_vrijeme', 'pozicija'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            die("Error: Missing required field '$field'");
        }
    }

    $stmt = $conn->prepare("INSERT INTO statistika (IDvozac, IDutrka, start_pozicija, br_bodova, prosjecna_brzina, najbrzi_krug, ukupno_vrijeme, pozicija) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "iiiiiddi", 
        $_POST['IDvozac'], 
        $_POST['IDutrka'], 
        $_POST['start_pozicija'], 
        $_POST['br_bodova'], 
        $_POST['prosjecna_brzina'], 
        $_POST['najbrzi_krug'], 
        $_POST['ukupno_vrijeme'], 
        $_POST['pozicija']
    );
    
    if ($stmt->execute()) {
        header("Location: index.php?success=1");
        exit();
    } else {
        die("Error adding statistic: " . $conn->error);
    }
}
$conn->close();
?>