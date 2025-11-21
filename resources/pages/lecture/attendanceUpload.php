<?php
// Use PDO instead of mysqli to match your main system
session_start();
require_once '../includes/config.php'; // Your PDO config file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendanceData = json_decode(file_get_contents("php://input"), true);

    if (!empty($attendanceData)) {
        try {
            $sql = "INSERT INTO tblattendance (studentRegistrationNumber, course, unit, attendanceStatus, dateMarked, timeMarked)  
                    VALUES (:studentID, :course, :unit, :attendanceStatus, :date, :time)";

            $stmt = $pdo->prepare($sql);

            foreach ($attendanceData as $data) {
                $studentID = $data['studentID'] ?? '';
                $course = $data['course'] ?? '';
                $unitCode = $data['unit'] ?? '';
                $date = date("Y-m-d");
                $currentTime = date("H:i:s");
                
                // GET UNIT SCHEDULE FROM DATABASE
                $unitQuery = $pdo->prepare("SELECT startTime FROM tblunit WHERE unitCode = :unitCode");
                $unitQuery->execute([':unitCode' => $unitCode]);
                $unit = $unitQuery->fetch(PDO::FETCH_ASSOC);
                
                $attendanceStatus = 'Present'; // Default
                
                if ($unit && isset($unit['startTime'])) {
                    $unitStartTime = $unit['startTime'];
                    
                    // Calculate late threshold (unit start + 15 minutes)
                    $lateThreshold = date('H:i:s', strtotime($unitStartTime) + (15 * 60));
                    
                    // If current time is after late threshold, mark as Late
                    if ($currentTime > $lateThreshold) {
                        $attendanceStatus = 'Late';
                    }
                    
                    echo "🔍 Unit: $unitCode, Start: $unitStartTime, Late After: $lateThreshold, Current: $currentTime, Status: $attendanceStatus<br>";
                } else {
                    echo "⚠️ No schedule found for unit: $unitCode<br>";
                }

                // Save to database using PDO
                $stmt->execute([
                    ':studentID' => $studentID,
                    ':course' => $course,
                    ':unit' => $unitCode,
                    ':attendanceStatus' => $attendanceStatus,
                    ':date' => $date,
                    ':time' => $currentTime
                ]);
                
                echo "✅ Attendance: $studentID - $attendanceStatus<br>";
            }
        } catch (PDOException $e) {
            echo "❌ Database error: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "No attendance data received.<br>";
    }
} else {
    echo "Invalid request method.<br>";
}
?>