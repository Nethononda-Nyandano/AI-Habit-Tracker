<?php
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updateHabit'])) {
    $habitId = intval($_POST['habit_id']);
    $userId = intval($_POST['userId']);
    $habitName = trim($_POST['habitName']);
    $habitDescription = trim($_POST['habitDescription']);
    $habitFrequency = trim($_POST['habitFrequency']);

    $allowedFrequencies = ['daily','weekly','monthly'];
    if(!in_array($habitFrequency, $allowedFrequencies)){
        die("Invalid frequency selected.");
    }

    $conn = new mysqli('localhost', 'root', '', '');
    if($conn->connect_error) die("DB connection failed: " . $conn->connect_error);

    $stmt = $conn->prepare("UPDATE habits SET habit_name=?, description=?, frequency=? WHERE habit_id=? AND user_id=?");
    $stmt->bind_param("sssii", $habitName, $habitDescription, $habitFrequency, $habitId, $userId);

    if($stmt->execute()){
        header("Location: index.php?habit_id=$habitId&success=1");
        exit;
    } else {
        die("Failed to update habit: " . $stmt->error);
    }
}