<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "queue_db");

    if ($conn->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit();
    }

    $sql = "SELECT id, role, window_number FROM queue_users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['windowNumber'] = $user['window_number'];
        $role = $user['role'];

        // if($user['role' === 'admin']){
        //     header('Location: ../services.php');
        //     exit();
        // }

        echo json_encode(['status' => 'success', 'role' => $role]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
