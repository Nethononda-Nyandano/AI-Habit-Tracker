<?php
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['saveHabit'])) {
    $habitName = trim($_POST['habitName']);
    $habitDescription = trim($_POST['habitDescription']);
    $habitFrequency = trim($_POST['habitFrequency']);
    $userId = intval($_POST['userId']);

    $conn = new mysqli('localhost', 'root', '', '');
    if ($conn->connect_error) die("DB connection failed: " . $conn->connect_error);

    $stmt = $conn->prepare("INSERT INTO habits (habit_name, description, frequency, user_id, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssi", $habitName, $habitDescription, $habitFrequency, $userId);

    if($stmt->execute()){
        // Redirect back to form page with success flag
        header("Location: index.php?success=1");
        exit;
    } else {
        die("Failed to save habit: " . $stmt->error);
    }
}
