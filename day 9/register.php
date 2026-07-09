<?php
// Include your database connection file (e.g., db.php or config.php)
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $course = $_POST['course'];
    $address = $_POST['address'];
    
    // Handling the Photo Upload column (saving filename as requested)
    $photoName = null;
    if (isset($_FILES['photoUpload']) && $_FILES['photoUpload']['error'] == 0) {
        $photoName = time() . '_' . basename($_FILES['photoUpload']['name']);
        $targetDir = "uploads/";
        
        // Ensure uploads directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        move_uploaded_file($_FILES['photoUpload']['tmp_name'], $targetDir . $photoName);
    }

    // Insert statement with the new columns
    $sql = "INSERT INTO students (name, email, gender, course, address, photo) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $fullName, $email, $gender, $course, $address, $photoName);

    if ($stmt->execute()) {
        header("Location: dashboard.php?success=1");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>