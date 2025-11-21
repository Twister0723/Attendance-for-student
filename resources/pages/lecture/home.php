<?php
// ADD THIS AT THE VERY TOP - Set correct timezone
date_default_timezone_set('Asia/Manila');

// ADD DATABASE CONNECTION
$host = "localhost";
$database = "attendance-db";
$user = "root";
$password = "";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// FIXED: Check if session is already started before starting it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendanceData = json_decode(file_get_contents("php://input"), true);
    if ($attendanceData) {
        try {
            // BEST APPROACH: Single query that handles both insert and update
            $sql = "INSERT INTO tblattendance (studentRegistrationNumber, course, unit, attendanceStatus, dateMarked, timeMarked)  
                    VALUES (:studentID, :course, :unit, :attendanceStatus, :date, :time)
                    ON DUPLICATE KEY UPDATE 
                    attendanceStatus = VALUES(attendanceStatus),
                    timeMarked = VALUES(timeMarked)";

            $stmt = $pdo->prepare($sql);

            $successCount = 0;
            $errorCount = 0;

            foreach ($attendanceData as $data) {
                $studentID = $data['studentID'];
                $attendanceStatus = $data['attendanceStatus'];
                $course = $data['course'];
                $unit = $data['unit'];
                $date = date("Y-m-d");
                $time = date("H:i:s");

                // Add tiny delay to ensure unique timestamps in same second
                usleep(1000); // 1 millisecond delay

                try {
                    $stmt->execute([
                        ':studentID' => $studentID,
                        ':course' => $course,
                        ':unit' => $unit,
                        ':attendanceStatus' => $attendanceStatus,
                        ':date' => $date,
                        ':time' => $time
                    ]);
                    
                    $successCount++;
                    error_log("✅ Attendance recorded for $studentID: $attendanceStatus at $time");
                    
                } catch (PDOException $e) {
                    $errorCount++;
                    error_log("❌ Error for $studentID: " . $e->getMessage());
                }
            }

            if ($errorCount === 0) {
                $_SESSION['message'] = "✅ Attendance recorded successfully for $successCount students.";
            } else {
                $_SESSION['message'] = "⚠️ Attendance recorded for $successCount students, $errorCount errors.";
            }
            
        } catch (PDOException $e) {
            $_SESSION['message'] = "❌ Database error: " . $e->getMessage();
            error_log("❌ Attendance system error: " . $e->getMessage());
        }
    } else {
        $_SESSION['message'] = "❌ No attendance data received.";
    }
}

// FIXED Function to convert time to NORMAL format (12-hour with AM/PM)
function toNormalTime($time) {
    if (empty($time) || $time == '00:00:00') return '12:00 AM';
    
    // If it's already in 12-hour format, return as is
    if (strpos($time, 'AM') !== false || strpos($time, 'PM') !== false) {
        return $time;
    }
    
    // Handle different time formats
    $time = trim($time);
    
    // If time has seconds, remove them
    if (strpos($time, ':') !== false) {
        $parts = explode(':', $time);
        if (count($parts) >= 2) {
            $time = $parts[0] . ':' . $parts[1]; // Keep only hours and minutes
        }
    }
    
    return date('g:i A', strtotime($time));
}

// NEW FUNCTION: Get today's attendance for display
function getTodaysAttendance($course, $unit) {
    global $pdo;
    
    $query = $pdo->prepare("
        SELECT studentRegistrationNumber, attendanceStatus, timeMarked, dateMarked 
        FROM tblattendance 
        WHERE course = :course 
        AND unit = :unit 
        AND dateMarked = CURDATE()
    ");
    $query->execute([':course' => $course, ':unit' => $unit]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="resources/images/logo/attnlg.png" rel="icon">
    <title>Lecture Dashboard</title>
    <link rel="stylesheet" href="resources/assets/css/styles.css">
    <script defer src="resources/assets/javascript/face_logics/face-api.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
    <style>
        /* Enhanced Color Scheme */
        :root {
            --primary-purple: #7c3aed;
            --secondary-purple: #8b5cf6;
            --accent-teal: #0d9488;
            --accent-amber: #d97706;
            --accent-rose: #e11d48;
            --dark-slate: #1e293b;
            --light-slate: #475569;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-bg: #ffffff;
            --sidebar-bg: linear-gradient(180deg, #7c3aed 0%, #5b21b6 100%);
            --header-bg: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: var(--dark-slate);
        }

        /* Enhanced Design Styles */
        .dashboard-header {
            background: var(--header-bg);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.3);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-20px, -20px) rotate(360deg); }
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
        }

        .dashboard-header p {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 0;
            font-weight: 500;
            position: relative;
        }

        .control-panel {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(124, 58, 237, 0.1);
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--dark-slate);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group label i {
            color: var(--primary-purple);
            font-size: 1.1rem;
        }

        .form-control {
            padding: 1rem 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1), 0 4px 12px rgba(124, 58, 237, 0.1);
            transform: translateY(-2px);
        }

        .time-status-card {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            color: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.3);
            position: relative;
            overflow: hidden;
        }

        .time-status-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .time-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1rem;
            position: relative;
        }

        .time-item {
            text-align: center;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .time-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.2);
        }

        .time-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .time-value {
            font-size: 1.3rem;
            font-weight: 800;
        }

        .status-indicator {
            text-align: center;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .status-indicator:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 0.25);
        }

        .status-label {
            font-size: 0.95rem;
            opacity: 0.9;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .status-value {
            font-size: 1.4rem;
            font-weight: 800;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .btn {
            padding: 1.25rem 1.5rem;
            border: none;
            border-radius: 15px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--accent-teal) 0%, #0f766e 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(13, 148, 136, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--accent-rose) 0%, #be123c 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(225, 29, 72, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--accent-amber) 0%, #b45309 100%);
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(217, 119, 6, 0.4);
        }

        .video-container {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            display: none;
            border: 1px solid rgba(124, 58, 237, 0.1);
        }

        .video-wrapper {
            position: relative;
            display: inline-block;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
        }

        #video {
            border-radius: 15px;
            max-width: 100%;
            height: auto;
        }

        #overlay {
            position: absolute;
            top: 0;
            left: 0;
            border-radius: 15px;
        }

        /* Enhanced Table Styles */
        .table-container {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            border: 1px solid rgba(124, 58, 237, 0.1);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid rgba(124, 58, 237, 0.1);
        }

        .table-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--dark-slate);
            position: relative;
        }

        .table-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -16px;
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            border-radius: 2px;
        }

        .table {
            overflow-x: auto;
            border-radius: 15px;
            border: 1px solid rgba(124, 58, 237, 0.1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
        }

        th {
            padding: 1.25rem;
            text-align: left;
            font-weight: 700;
            color: white;
            font-size: 0.95rem;
            border: none;
            position: relative;
        }

        th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: rgba(255, 255, 255, 0.3);
        }

        td {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(124, 58, 237, 0.1);
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            transform: translateX(5px);
        }

        /* Enhanced Attendance Badges */
        .attendance-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.85rem;
            text-align: center;
            min-width: 90px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .attendance-present {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
        }
        
        .attendance-late {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
        }
        
        .attendance-absent {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
        }

        .attendance-time {
            font-size: 0.8rem;
            color: var(--light-slate);
            margin-top: 4px;
            font-weight: 600;
        }

        .not-marked {
            color: var(--light-slate);
            font-style: italic;
            font-size: 0.9rem;
            opacity: 0.7;
        }

        /* Message Styles */
        .messageDiv {
            padding: 1.25rem 1.5rem;
            margin: 1rem 0;
            border-radius: 15px;
            font-weight: 700;
            text-align: center;
            display: none;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .time-grid {
                grid-template-columns: 1fr;
            }
            
            .table-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .dashboard-header h1 {
                font-size: 2rem;
            }
            
            .dashboard-header p {
                font-size: 1.1rem;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Fix unit dropdown time alignment */
        #unitSelect option {
            font-family: 'Courier New', monospace;
        }

        /* Statistics Display */
        .table-stats {
            display: flex;
            gap: 1.5rem;
            font-size: 0.95rem;
        }

        .table-stats span {
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 700;
            background: rgba(124, 58, 237, 0.1);
            color: var(--primary-purple);
        }
    </style>
</head>
<body>
    <?php include 'includes/topbar.php'; ?>
    <section class="main">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main--content">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1>Lecture Attendance Dashboard</h1>
                <p>Facial Recognition Attendance System</p>
            </div>

            <!-- Message Display -->
            <div id="messageDiv" class="messageDiv"></div>

            <!-- Control Panel -->
            <div class="control-panel">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="courseSelect"><i class="ri-book-line"></i> Select Course</label>
                        <select required name="course" id="courseSelect" class="form-control" onChange="updateTable()">
                            <option value="" selected>Choose a course...</option>
                            <?php
                            $courseNames = getCourseNames();
                            foreach ($courseNames as $course) {
                                echo '<option value="' . $course["courseCode"] . '">' . $course["name"] . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="unitSelect"><i class="ri-time-line"></i> Select Unit</label>
                        <select required name="unit" id="unitSelect" class="form-control" onChange="updateTable()">
                            <option value="" selected>Choose a unit...</option>
                            <?php
                            $unitNames = getUnitNames();
                            foreach ($unitNames as $unit) {
                                // FIXED: Use raw database times for data attributes
                                $startTimeRaw = $unit["startTime"];
                                $endTimeRaw = $unit["endTime"];
                                $startTimeDisplay = toNormalTime($unit["startTime"]);
                                $endTimeDisplay = toNormalTime($unit["endTime"]);
                                
                                echo '<option value="' . $unit["unitCode"] . '" 
                                         data-start-time="' . $startTimeRaw . '" 
                                         data-end-time="' . $endTimeRaw . '">' . 
                                         $unit["name"] . ' (' . $startTimeDisplay . ' - ' . $endTimeDisplay . ')</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="venueSelect"><i class="ri-building-line"></i> Select Venue</label>
                        <select required name="venue" id="venueSelect" class="form-control" onChange="updateTable()">
                            <option value="" selected>Choose a venue...</option>
                            <?php
                            $venueNames = getVenueNames();
                            foreach ($venueNames as $venue) {
                                echo '<option value="' . $venue["className"] . '">' . $venue["className"] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Time and Status Information -->
            <div class="time-status-card" id="timeInfo" style="display:none;">
                <div class="time-grid">
                    <div class="time-item">
                        <div class="time-label">Unit Time</div>
                        <div class="time-value" id="unitTimeDisplay"></div>
                    </div>
                    <div class="time-item">
                        <div class="time-label">Current Time</div>
                        <div class="time-value" id="currentTimeDisplay"></div>
                    </div>
                    <div class="status-indicator">
                        <div class="status-label">Attendance Status</div>
                        <div class="status-value" id="attendanceStatusDisplay">SELECT UNIT</div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button id="startButton" class="btn btn-primary">
                    <i class="ri-camera-line"></i> Launch Facial Recognition
                </button>
                <button id="endButton" class="btn btn-warning" style="display:none">
                    <i class="ri-stop-line"></i> End Attendance Process
                </button>
                <button id="endAttendance" class="btn btn-success">
                    <i class="ri-check-double-line"></i> END Attendance Taking
                </button>
            </div>

            <!-- Video Container -->
            <div class="video-container">
                <div class="video-wrapper">
                    <video id="video" width="600" height="450" autoplay muted></video>
                    <canvas id="overlay"></canvas>
                </div>
            </div>

            <!-- Student Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2 class="table-title">Student Attendance</h2>
                    <div class="table-stats" id="tableStats">
                        <span style="color: var(--light-slate); font-size: 0.9rem;">Select course and unit to view students</span>
                    </div>
                </div>
                <div id="studentTableContainer">
                    <div style="text-align: center; padding: 3rem; color: var(--light-slate);">
                        <i class="ri-user-search-line" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.5;"></i>
                        <p>Please select a course, unit, and venue to display students</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    let currentUnitStartTime = '';
    let currentUnitEndTime = '';
    let isLateAttendance = false;
    let attendanceRecords = {};
    let isAttendanceMarked = false;

    // Function to get SERVER time
    async function getServerTime() {
        try {
            const response = await fetch('includes/get_time.php');
            const data = await response.json();
            return new Date(data.serverTime);
        } catch (error) {
            console.error('Failed to get server time, using client time:', error);
            return new Date(); // Fallback
        }
    }

    // FIXED Function to convert time to 12-hour format with AM/PM
    function to12HourFormat(timeString) {
        if (!timeString) return '12:00 AM';
        
        console.log("Converting time:", timeString);
        
        // If it's already in 12-hour format, return as is
        if (timeString.includes('AM') || timeString.includes('PM')) {
            return timeString;
        }
        
        // If it's in HH:MM format, convert to 12-hour
        const [hours, minutes] = timeString.split(':');
        const date = new Date();
        date.setHours(parseInt(hours), parseInt(minutes), 0);
        
        return date.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        });
    }

    // Function to format time for display (12-hour with AM/PM)
    function formatTimeForDisplay(date) {
        return date.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        });
    }

    // IMPROVED: Load attendance from server - ALWAYS loads today's attendance
    async function loadAttendanceFromServer() {
        try {
            const course = document.getElementById('courseSelect').value;
            const unit = document.getElementById('unitSelect').value;
            
            if (!course || !unit) return;

            console.log("🔄 Loading attendance for:", course, unit);
            
            const response = await fetch('includes/fetch_attendance.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    course: course,
                    unit: unit,
                    date: new Date().toISOString().split('T')[0] // Today's date
                })
            });
            
            const json = await response.json();
            
            if (json.success && Array.isArray(json.data)) {
                console.log("📥 Loaded", json.data.length, "attendance records for today");
                
                // Reset attendance records
                attendanceRecords = {};
                
                json.data.forEach(row => {
                    const studentID = row.studentID.toString().trim();
                    
                    attendanceRecords[studentID] = {
                        status: row.attendanceStatus || 'Present',
                        time: row.timeMarked || '',
                        date: row.dateMarked || '',
                        showTime: true,
                        fromServer: true
                    };
                    
                    console.log("📝 Student", studentID, ":", row.attendanceStatus, "at", row.timeMarked);
                });
                
                reapplyAttendanceRecords();
                updateTableStats();
            } else {
                console.log("📭 No attendance records found for today");
                // Reset all to not marked if no records found
                resetAttendanceDisplay();
                updateTableStats();
            }
        } catch (error) {
            console.error("❌ Failed to load attendance:", error);
        }
    }

    // Update table statistics
    function updateTableStats() {
        const totalStudents = document.querySelectorAll('#studentTableContainer tbody tr').length;
        const presentCount = Object.values(attendanceRecords).filter(record => record.status === 'Present').length;
        const lateCount = Object.values(attendanceRecords).filter(record => record.status === 'Late').length;
        const absentCount = totalStudents - (presentCount + lateCount);
        
        const statsElement = document.getElementById('tableStats');
        if (totalStudents > 0) {
            statsElement.innerHTML = `
                <div style="display: flex; gap: 1rem; font-size: 0.9rem;">
                    <span style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><strong>${presentCount}</strong> Present</span>
                    <span style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><strong>${lateCount}</strong> Late</span>
                    <span style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><strong>${absentCount}</strong> Absent</span>
                    <span style="background: rgba(124, 58, 237, 0.1); color: #7c3aed;"><strong>${totalStudents}</strong> Total</span>
                </div>
            `;
        }
    }

    // Reset attendance display to "Not Marked"
    function resetAttendanceDisplay() {
        const tables = document.querySelectorAll('#studentTableContainer table');
        tables.forEach(table => {
            const rows = table.getElementsByTagName('tr');
            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                const studentID = cells[0].textContent.trim();
                
                if (!attendanceRecords[studentID]) {
                    const attendanceCell = cells[5];
                    const timeCell = cells[6];
                    
                    if (attendanceCell && timeCell) {
                        attendanceCell.innerHTML = `
                            <div style="text-align: center; padding: 8px 4px; vertical-align: middle;">
                                <span class="not-marked">
                                    Not Marked
                                </span>
                            </div>
                        `;
                        
                        timeCell.innerHTML = `
                            <div style="text-align: right; padding: 8px 4px; vertical-align: middle;">
                                <div class="not-marked">
                                    Not Marked
                                </div>
                            </div>
                        `;
                    }
                }
            }
        });
    }

    // Update current time with NORMAL time format
    async function updateCurrentTime() {
        const serverTime = await getServerTime();
        const timeString = formatTimeForDisplay(serverTime);
        document.getElementById('currentTimeDisplay').textContent = timeString;
        await checkAttendanceStatus(serverTime);
    }

    // FIXED Check attendance status with proper time handling
    async function checkAttendanceStatus(serverTime = null) {
        if (!currentUnitStartTime) {
            document.getElementById('attendanceStatusDisplay').textContent = 'SELECT UNIT';
            document.getElementById('attendanceStatusDisplay').style.color = 'rgba(255,255,255,0.7)';
            return;
        }
        
        const currentTime = serverTime || await getServerTime();
        const today = new Date();
        const currentDateStr = today.toISOString().split('T')[0];
        
        // Parse unit times correctly (handle HH:MM format)
        const [startHours, startMinutes] = currentUnitStartTime.split(':');
        const unitStart = new Date(today);
        unitStart.setHours(parseInt(startHours), parseInt(startMinutes), 0, 0);
        
        const lateThreshold = new Date(unitStart.getTime() + (15 * 60 * 1000));
        
        // Proper status calculation
        if (currentTime > lateThreshold) {
            document.getElementById('attendanceStatusDisplay').textContent = 'LATE ATTENDANCE PERIOD';
            document.getElementById('attendanceStatusDisplay').style.color = '#fbbf24';
        } else if (currentTime >= unitStart) {
            document.getElementById('attendanceStatusDisplay').textContent = 'ON TIME PERIOD';
            document.getElementById('attendanceStatusDisplay').style.color = '#34d399';
        } else {
            document.getElementById('attendanceStatusDisplay').textContent = 'BEFORE CLASS TIME';
            document.getElementById('attendanceStatusDisplay').style.color = '#93c5fd';
        }
        document.getElementById('attendanceStatusDisplay').style.fontWeight = '800';
    }

    // FIXED Update time info when unit is selected
    document.getElementById('unitSelect').addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        currentUnitStartTime = selectedOption.getAttribute('data-start-time');
        currentUnitEndTime = selectedOption.getAttribute('data-end-time');
        
        if (currentUnitStartTime && currentUnitEndTime) {
            document.getElementById('timeInfo').style.display = 'block';
            // Display unit time in 12-hour format
            const startTimeDisplay = to12HourFormat(currentUnitStartTime);
            const endTimeDisplay = to12HourFormat(currentUnitEndTime);
            document.getElementById('unitTimeDisplay').textContent = startTimeDisplay + ' - ' + endTimeDisplay;
            await updateCurrentTime();
            
            // ALWAYS load attendance when unit changes
            setTimeout(() => {
                loadAttendanceFromServer();
            }, 500);
        } else {
            document.getElementById('timeInfo').style.display = 'none';
        }
    });

    // MARK ATTENDANCE FUNCTION with normal time format
    async function markAttendance(studentID, studentName) {
        const serverTime = await getServerTime();
        const course = document.getElementById('courseSelect').value;
        const unit = document.getElementById('unitSelect').value;
        
        if (!course || !unit) {
            alert('Please select both course and unit first.');
            return;
        }

        // Determine attendance status
        let attendanceStatus = 'Present';
        
        if (currentUnitStartTime) {
            const today = new Date();
            const [startHours, startMinutes] = currentUnitStartTime.split(':');
            const unitStart = new Date(today);
            unitStart.setHours(parseInt(startHours), parseInt(startMinutes), 0, 0);
            
            const lateThreshold = new Date(unitStart.getTime() + (15 * 60 * 1000));
            
            if (serverTime > lateThreshold) {
                attendanceStatus = 'Late';
            }
        }

        // Use normal time format (12-hour with AM/PM)
        const time = formatTimeForDisplay(serverTime);
        const date = serverTime.toLocaleDateString('en-US', {
            month: 'numeric',
            day: 'numeric', 
            year: '2-digit'
        });

        isAttendanceMarked = true;

        attendanceRecords[studentID] = {
            status: attendanceStatus,
            time: time,
            date: date,
            showTime: true,
            fromServer: false
        };

        const attendanceData = [{
            studentID: studentID,
            attendanceStatus: attendanceStatus,
            course: course,
            unit: unit
        }];

        fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(attendanceData)
        })
        .then(response => response.text())
        .then(data => {
            updateStudentTableStatus(studentID, attendanceStatus, time, date, true);
            updateTableStats();
            showMessage(`✅ ${studentName}: ${attendanceStatus} at ${time}`, 'success');
        })
        .catch(error => {
            showMessage('❌ Error marking attendance', 'error');
        });
    }

    // IMPROVED Update student table status - shows both status and time
    function updateStudentTableStatus(studentID, status, time, date, showTime = false) {
        const tables = document.querySelectorAll('#studentTableContainer table');
        let studentFound = false;
        
        tables.forEach(table => {
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                
                if (cells.length > 0 && cells[0].textContent.trim() === studentID) {
                    studentFound = true;
                    const attendanceCell = cells[5];
                    const timeCell = cells[6];
                    
                    if (attendanceCell && timeCell) {
                        const statusClass = status === 'Late' ? 'attendance-late' : 'attendance-present';
                        
                        // Update attendance cell
                        attendanceCell.innerHTML = `
                            <div style="text-align: center; padding: 8px 4px; vertical-align: middle;">
                                <span class="attendance-badge ${statusClass}">
                                    ${status}
                                </span>
                            </div>
                        `;
                        
                        // Update time cell with both status and time/date
                        let displayContent;
                        if (showTime && time && date) {
                            displayContent = `
                                <div style="text-align: right; padding: 8px 4px; vertical-align: middle;">
                                    <div class="attendance-badge ${statusClass}" style="margin-bottom: 4px;">
                                        ${status}
                                    </div>
                                    <div class="attendance-time">
                                        ${time} ${date}
                                    </div>
                                </div>
                            `;
                        } else {
                            displayContent = `
                                <div style="text-align: right; padding: 8px 4px; vertical-align: middle;">
                                    <div class="not-marked">
                                        Not Marked
                                    </div>
                                </div>
                            `;
                        }
                        
                        timeCell.innerHTML = displayContent;
                    }
                    break;
                }
            }
        });
        
        if (!studentFound) {
            setTimeout(() => {
                updateStudentTableStatus(studentID, status, time, date, showTime);
            }, 500);
        }
    }

    // FIXED: END Attendance button with INDIVIDUAL timestamps
    document.getElementById('endAttendance').addEventListener('click', async () => {
        isAttendanceMarked = true;

        let endAttendanceStatus = 'Present';
        
        // Determine status based on current time
        const serverTime = await getServerTime();
        if (currentUnitStartTime) {
            const today = new Date();
            const [startHours, startMinutes] = currentUnitStartTime.split(':');
            const unitStart = new Date(today);
            unitStart.setHours(parseInt(startHours), parseInt(startMinutes), 0, 0);
            
            const lateThreshold = new Date(unitStart.getTime() + (15 * 60 * 1000));
            
            if (serverTime > lateThreshold) {
                endAttendanceStatus = 'Late';
            }
        }

        const tables = document.querySelectorAll('#studentTableContainer table');
        let markedCount = 0;
        const studentsToMark = [];
        
        tables.forEach(table => {
            const rows = table.getElementsByTagName('tr');
            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                const studentID = cells[0].textContent.trim();
                
                if (!attendanceRecords[studentID]) {
                    studentsToMark.push(studentID);
                }
            }
        });

        for (const studentID of studentsToMark) {
            // FIX: Generate INDIVIDUAL timestamp for each student
            const individualTime = await getServerTime();
            const time = formatTimeForDisplay(individualTime);
            const date = individualTime.toLocaleDateString('en-US', { 
                month: 'numeric', 
                day: 'numeric', 
                year: '2-digit' 
            });

            attendanceRecords[studentID] = { 
                status: endAttendanceStatus, 
                time: time,  // ← INDIVIDUAL TIME!
                date: date,  // ← INDIVIDUAL DATE!
                showTime: true,
                fromServer: false
            };
            updateStudentTableStatus(studentID, endAttendanceStatus, time, date, true);
            markedCount++;

            const course = document.getElementById('courseSelect').value;
            const unit = document.getElementById('unitSelect').value;
            
            const attendanceData = [{
                studentID: studentID,
                attendanceStatus: endAttendanceStatus,
                course: course,
                unit: unit
            }];

            await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(attendanceData)
            });
        }
        
        updateTableStats();
        showMessage(`✅ Attendance finalized. Marked ${markedCount} students as ${endAttendanceStatus}.`, 'success');
    });

    // IMPROVED REAPPLY ATTENDANCE RECORDS - More robust
    function reapplyAttendanceRecords() {
        console.log("🔄 Reapplying", Object.keys(attendanceRecords).length, "attendance records");
        
        const tables = document.querySelectorAll('#studentTableContainer table');
        
        if (tables.length === 0) {
            console.log("❌ No tables found, retrying...");
            setTimeout(() => reapplyAttendanceRecords(), 200);
            return;
        }
        
        let appliedCount = 0;
        
        tables.forEach(table => {
            const rows = table.getElementsByTagName('tr');
            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                if (cells.length > 0) {
                    const studentID = cells[0].textContent.trim();
                    
                    if (attendanceRecords[studentID]) {
                        const record = attendanceRecords[studentID];
                        updateStudentTableStatus(studentID, record.status, record.time, record.date, true);
                        appliedCount++;
                    } else {
                        // Reset to "Not Marked" if no record exists
                        const attendanceCell = cells[5];
                        const timeCell = cells[6];
                        
                        if (attendanceCell && timeCell) {
                            attendanceCell.innerHTML = `
                                <div style="text-align: center; padding: 8px 4px; vertical-align: middle;">
                                    <span class="not-marked">
                                        Not Marked
                                    </span>
                                </div>
                            `;
                            
                            timeCell.innerHTML = `
                                <div style="text-align: right; padding: 8px 4px; vertical-align: middle;">
                                    <div class="not-marked">
                                        Not Marked
                                    </div>
                                </div>
                            `;
                        }
                    }
                }
            }
        });
        
        console.log("✅ Applied", appliedCount, "attendance records");
        updateTableStats();
    }

    function showMessage(message, type) {
        const messageDiv = document.getElementById('messageDiv');
        messageDiv.innerHTML = message;
        messageDiv.style.display = 'block';
        messageDiv.style.backgroundColor = type === 'success' ? '#d1fae5' : '#fee2e2';
        messageDiv.style.color = type === 'success' ? '#065f46' : '#991b1b';
        messageDiv.style.padding = '1.25rem 1.5rem';
        messageDiv.style.margin = '1rem 0';
        messageDiv.style.borderRadius = '15px';
        messageDiv.style.border = type === 'success' ? '1px solid #a7f3d0' : '1px solid #fecaca';
        messageDiv.style.fontSize = '1rem';
        messageDiv.style.textAlign = 'center';
        messageDiv.style.fontWeight = '700';
        
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 5000);
    }

    // IMPROVED UPDATE TABLE FUNCTION with immediate attendance reapplication
    function updateTable() {
        const course = document.getElementById('courseSelect').value;
        const unit = document.getElementById('unitSelect').value;
        const venue = document.getElementById('venueSelect').value;

        if (course && unit && venue) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'includes/fetch_students.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status === 200) {
                    document.getElementById('studentTableContainer').innerHTML = this.responseText;
                    
                    const unitSelect = document.getElementById('unitSelect');
                    if (unitSelect.value) {
                        const selectedOption = unitSelect.options[unitSelect.selectedIndex];
                        currentUnitStartTime = selectedOption.getAttribute('data-start-time');
                        currentUnitEndTime = selectedOption.getAttribute('data-end-time');
                        
                        if (currentUnitStartTime && currentUnitEndTime) {
                            document.getElementById('timeInfo').style.display = 'block';
                            const startTimeDisplay = to12HourFormat(currentUnitStartTime);
                            const endTimeDisplay = to12HourFormat(currentUnitEndTime);
                            document.getElementById('unitTimeDisplay').textContent = startTimeDisplay + ' - ' + endTimeDisplay;
                            updateCurrentTime();
                        }
                    }
                    
                    // REAPPLY ATTENDANCE RECORDS IMMEDIATELY after table loads
                    setTimeout(() => {
                        reapplyAttendanceRecords();
                    }, 100);
                }
            };
            xhr.send('course=' + course + '&unit=' + unit + '&venue=' + venue);
        } else {
            document.getElementById('studentTableContainer').innerHTML = `
                <div style="text-align: center; padding: 3rem; color: var(--light-slate);">
                    <i class="ri-user-search-line" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.5;"></i>
                    <p>Please select a course, unit, and venue to display students</p>
                </div>
            `;
            document.getElementById('timeInfo').style.display = 'none';
            document.getElementById('tableStats').innerHTML = '<span style="color: var(--light-slate); font-size: 0.9rem;">Select course and unit to view students</span>';
        }
    }

    // Real-time ticking every second
    setInterval(async () => {
        await updateCurrentTime();
    }, 1000);

    window.markAttendance = markAttendance;

    // Load attendance when page loads if course and unit are selected
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            const course = document.getElementById('courseSelect').value;
            const unit = document.getElementById('unitSelect').value;
            if (course && unit) {
                console.log("🚀 Page loaded, loading attendance...");
                loadAttendanceFromServer();
            }
        }, 1000);
    });
    </script>

    <?php js_asset(["active_link", 'face_logics/script']) ?>
</body>
</html>