<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    error_log('Raw input: ' . $input); // Log the raw input data
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON decode error: ' . json_last_error_msg());
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
        exit();
    }

    // Log the decoded data
    error_log('Decoded data: ' . print_r($data, true));

    if (isset($data['clinics']) && !empty($data['clinics']) && isset($_SESSION['user_id'])) {
        $clinics = $data['clinics'];
        $user_id = $_SESSION['user_id'];

        // Connect to the database
        $conn = new mysqli("localhost", "root", "", "queue_db");

        if ($conn->connect_error) {
            error_log('Database connection failed: ' . $conn->connect_error);
            echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
            exit();
        }

        // Optional: Delete old clinics for the user (if needed)
        // $conn->query("DELETE FROM user_clinics WHERE user_id = '$user_id'");

        // Save new clinics
        foreach ($clinics as $clinic) {
            $sql = "INSERT INTO user_clinics (user_id, clinic) VALUES ('$user_id', '$clinic')";
            if (!$conn->query($sql)) {
                error_log('Failed to save clinic: ' . $clinic . ' for user: ' . $user_id);
                echo json_encode(['status' => 'error', 'message' => 'Failed to save clinics']);
                exit();
            }
        }

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No clinics selected or user_id missing']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
