<?php
// includes/fetch_attendance.php

// FIXED: Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ADD DATABASE CONNECTION
$host = "localhost";
$database = "attendance-db";
$user = "root";
$password = "";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // FIXED: Proper JSON response for connection error
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
    exit;
}

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // FIXED: Better JSON input handling
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data received'
        ]);
        exit;
    }
    
    $course = $input['course'] ?? '';
    $unit = $input['unit'] ?? '';
    $date = $input['date'] ?? date('Y-m-d'); // Support custom dates

    if (empty($course) || empty($unit)) {
        error_log("❌ Missing parameters - Course: $course, Unit: $unit");
        echo json_encode([
            'success' => false,
            'message' => 'Course and unit parameters are required'
        ]);
        exit;
    }

    try {
        $query = $pdo->prepare("
            SELECT 
                studentRegistrationNumber as studentID,
                attendanceStatus,
                TIME_FORMAT(timeMarked, '%h:%i %p') as timeMarked,
                DATE_FORMAT(dateMarked, '%m/%d/%y') as dateMarked
            FROM tblattendance 
            WHERE course = :course 
            AND unit = :unit 
            AND dateMarked = :date
            ORDER BY timeMarked DESC
        ");
        $query->execute([
            ':course' => $course,
            ':unit' => $unit,
            ':date' => $date
        ]);
        $attendanceData = $query->fetchAll(PDO::FETCH_ASSOC);

        // Log for debugging
        error_log("📊 Fetched " . count($attendanceData) . " attendance records for $course/$unit on $date");

        echo json_encode([
            'success' => true,
            'data' => $attendanceData,
            'count' => count($attendanceData),
            'course' => $course,
            'unit' => $unit,
            'date' => $date
        ]);

    } catch (PDOException $e) {
        error_log("❌ Attendance fetch error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching attendance data',
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed'
    ]);
}
?>