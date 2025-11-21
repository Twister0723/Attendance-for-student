<?php
if (isset($_POST["addCourse"])) {
    $courseName = htmlspecialchars(trim($_POST["courseName"]));
    $courseCode = htmlspecialchars(trim($_POST["courseCode"]));
    $facultyID = filter_var($_POST["faculty"], FILTER_VALIDATE_INT);
    $dateRegistered = date("Y-m-d");

    if ($courseName && $courseCode && $facultyID) {
        $query = $pdo->prepare("SELECT * FROM tblcourse WHERE courseCode = :courseCode");
        $query->bindParam(':courseCode', $courseCode);
        $query->execute();

        if ($query->rowCount() > 0) {
            $_SESSION['message'] = "Course Already Exists";
            $_SESSION['message_type'] = "error";
        } else {
            $query = $pdo->prepare("INSERT INTO tblcourse (name, courseCode, facultyID, dateCreated) 
                                     VALUES (:name, :courseCode, :facultyID, :dateCreated)");
            $query->bindParam(':name', $courseName);
            $query->bindParam(':courseCode', $courseCode);
            $query->bindParam(':facultyID', $facultyID);
            $query->bindParam(':dateCreated', $dateRegistered);
            $query->execute();

            $_SESSION['message'] = "Course Inserted Successfully";
            $_SESSION['message_type'] = "success";
        }
    } else {
        $_SESSION['message'] = "Invalid input for course";
        $_SESSION['message_type'] = "error";
    }
}

if (isset($_POST["addUnit"])) {
    $unitName = htmlspecialchars(trim($_POST["unitName"]));
    $unitCode = htmlspecialchars(trim($_POST["unitCode"]));
    $courseID = filter_var($_POST["course"], FILTER_VALIDATE_INT);
    $startTime = $_POST["startTime"];
    $endTime = $_POST["endTime"];
    $dateRegistered = date("Y-m-d");

    // ENSURE PROPER MILITARY TIME FORMAT FOR STORAGE
    if ($startTime) {
        $startTimeMilitary = date("H:i", strtotime($startTime));
    }
    if ($endTime) {
        $endTimeMilitary = date("H:i", strtotime($endTime));
    }

    // VALIDATE TIME FORMAT
    if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $startTimeMilitary)) {
        $_SESSION['message'] = "Invalid start time format";
        $_SESSION['message_type'] = "error";
    } elseif (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $endTimeMilitary)) {
        $_SESSION['message'] = "Invalid end time format";
        $_SESSION['message_type'] = "error";
    } elseif (strtotime($endTimeMilitary) <= strtotime($startTimeMilitary)) {
        $_SESSION['message'] = "End time must be after start time";
        $_SESSION['message_type'] = "error";
    } elseif ($unitName && $unitCode && $courseID && $startTimeMilitary && $endTimeMilitary) {
        $query = $pdo->prepare("SELECT * FROM tblunit WHERE unitCode = :unitCode");
        $query->bindParam(':unitCode', $unitCode);
        $query->execute();

        if ($query->rowCount() > 0) {
            $_SESSION['message'] = "Unit Already Exists";
            $_SESSION['message_type'] = "error";
        } else {
            $query = $pdo->prepare("INSERT INTO tblunit (name, unitCode, courseID, startTime, endTime, dateCreated) 
                                     VALUES (:name, :unitCode, :courseID, :startTime, :endTime, :dateCreated)");
            $query->bindParam(':name', $unitName);
            $query->bindParam(':unitCode', $unitCode);
            $query->bindParam(':courseID', $courseID);
            $query->bindParam(':startTime', $startTimeMilitary);
            $query->bindParam(':endTime', $endTimeMilitary);
            $query->bindParam(':dateCreated', $dateRegistered);
            
            if ($query->execute()) {
                $_SESSION['message'] = "Unit Inserted Successfully - " . 
                                       formatTimeForDisplay($startTimeMilitary) . " to " . 
                                       formatTimeForDisplay($endTimeMilitary);
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error inserting unit";
                $_SESSION['message_type'] = "error";
            }
        }
    } else {
        $_SESSION['message'] = "Invalid input for unit";
        $_SESSION['message_type'] = "error";
    }
}

if (isset($_POST["addFaculty"])) {
    $facultyName = htmlspecialchars(trim($_POST["facultyName"]));
    $facultyCode = htmlspecialchars(trim($_POST["facultyCode"]));
    $dateRegistered = date("Y-m-d");

    if ($facultyName && $facultyCode) {
        $query = $pdo->prepare("SELECT * FROM tblfaculty WHERE facultyCode = :facultyCode");
        $query->bindParam(':facultyCode', $facultyCode);
        $query->execute();

        if ($query->rowCount() > 0) {
            $_SESSION['message'] = "Faculty Already Exists";
            $_SESSION['message_type'] = "error";
        } else {
            $query = $pdo->prepare("INSERT INTO tblfaculty (facultyName, facultyCode, dateRegistered) 
                                     VALUES (:facultyName, :facultyCode, :dateRegistered)");
            $query->bindParam(':facultyName', $facultyName);
            $query->bindParam(':facultyCode', $facultyCode);
            $query->bindParam(':dateRegistered', $dateRegistered);
            $query->execute();

            $_SESSION['message'] = "Faculty Inserted Successfully";
            $_SESSION['message_type'] = "success";
        }
    } else {
        $_SESSION['message'] = "Invalid input for faculty";
        $_SESSION['message_type'] = "error";
    }
}

// IMPROVED TIME FORMATTING FUNCTION - DISPLAY IN 12-HOUR FORMAT
function formatTimeForDisplay($time) {
    if (!$time || $time == '00:00:00' || $time == '0000-00-00 00:00:00') {
        return '--:--';
    }
    
    // Convert to 12-hour format for user-friendly display
    try {
        $time = trim($time);
        
        // If it's already in 24-hour format (HH:MM)
        if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
            return date("g:i A", strtotime($time));
        }
        // If it includes seconds (HH:MM:SS)
        elseif (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $time)) {
            return date("g:i A", strtotime($time));
        }
        // Fallback
        else {
            $formatted = date("g:i A", strtotime($time));
            return $formatted === '12:00 AM' ? '--:--' : $formatted;
        }
    } catch (Exception $e) {
        return '--:--';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="resources/images/logo/attnlg.png" rel="icon">
    <title>Dashboard - Manage Courses & Units</title>
    <link rel="stylesheet" href="resources/assets/css/admin_styles.css">
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
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Enhanced Main Content */
        .main--content {
            padding: 2rem;
            margin-left: 280px;
        }

        /* Dashboard Header */
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
        }

        .dashboard-header p {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 0;
            font-weight: 500;
        }

        /* Cards Section */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(124, 58, 237, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(124, 58, 237, 0.2);
        }

        .card--data {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card--content .add {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }

        .card--content .add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
        }

        .card--content h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark-slate);
            margin: 0;
        }

        .card--icon--lg {
            font-size: 3rem;
            color: var(--primary-purple);
            opacity: 0.7;
        }

        /* Time Fields */
        .time-fields {
            margin: 15px 0;
            padding: 15px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 10px;
            border: 1px solid rgba(124, 58, 237, 0.1);
        }
        
        .time-fields label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: var(--dark-slate);
            font-size: 0.95rem;
        }
        
        .time-fields input[type="time"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #ffffff;
        }

        .time-fields input[type="time"]:focus {
            outline: none;
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        
        .time-format-note {
            font-size: 12px;
            color: var(--light-slate);
            margin-top: -10px;
            margin-bottom: 15px;
            font-style: italic;
        }

        /* Enhanced Form Styling */
        .formDiv {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 0;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            min-width: 450px;
            max-width: 90vw;
            border: 1px solid rgba(124, 58, 237, 0.2);
        }

        .form-title {
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .formDiv form {
            padding: 2rem;
        }

        .formDiv input[type="text"],
        .formDiv select {
            width: 100%;
            padding: 1rem;
            margin: 0.75rem 0;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            background: #ffffff;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .formDiv input[type="text"]:focus,
        .formDiv select:focus {
            outline: none;
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
            transform: translateY(-2px);
        }

        /* Enhanced Submit Button - LIKE YOUR EXAMPLE */
        .submit {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 1rem;
            width: 100%;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
        }

        .close {
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: scale(1.1);
        }

        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
            backdrop-filter: blur(5px);
        }

        /* Message Styling */
        .message {
            padding: 1rem 1.5rem;
            margin: 1rem 0;
            border-radius: 12px;
            font-weight: 500;
            border: 1px solid transparent;
            animation: slideIn 0.3s ease;
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

        .message.success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border-color: rgba(16, 185, 129, 0.2);
        }

        .message.error {
            background: rgba(225, 29, 72, 0.1);
            color: #e11d48;
            border-color: rgba(225, 29, 72, 0.2);
        }

        /* Enhanced Table Styling */
        .table-container {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
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

        .section--title {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--dark-slate);
            position: relative;
        }

        .section--title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -12px;
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            border-radius: 2px;
        }

        .table-stats span {
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 700;
            background: rgba(124, 58, 237, 0.1);
            color: var(--primary-purple);
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
            min-width: 800px;
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
            white-space: nowrap;
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

        /* Delete Button Styling */
        .delete {
            color: #ef4444;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 8px;
            border-radius: 6px;
            background: rgba(239, 68, 68, 0.1);
        }

        .delete:hover {
            color: #dc2626;
            background: rgba(239, 68, 68, 0.2);
            transform: scale(1.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main--content {
                padding: 1rem;
                margin-left: 0;
            }

            .dashboard-header {
                padding: 2rem 1.5rem;
            }

            .dashboard-header h1 {
                font-size: 2rem;
            }

            .cards {
                grid-template-columns: 1fr;
            }

            .card {
                padding: 1.5rem;
            }

            .table-container {
                padding: 1.5rem;
            }

            th, td {
                padding: 1rem 0.75rem;
                font-size: 0.85rem;
            }

            .formDiv {
                min-width: 95vw;
            }
        }

        @media (max-width: 480px) {
            .dashboard-header h1 {
                font-size: 1.8rem;
            }

            .card--content .add {
                width: 100%;
                justify-content: center;
            }

            


        }
    </style>
</head>
<body>
    <?php include 'includes/topbar.php' ?>
    <section class="main">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main--content">
            <div id="overlay"></div>

            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1>Course & Unit Management</h1>
                <p>Manage courses, units, and faculties efficiently</p>
            </div>

            <!-- Management Cards -->
            <div class="cards">
                <div id="addCourse" class="card card-1">
                    <div class="card--data">
                        <div class="card--content">
                            <button class="add show-form"><i class="ri-add-line"></i>Add Course</button>
                            <h1><?php total_rows('tblcourse') ?> Courses</h1>
                        </div>
                        <i class="ri-book-2-line card--icon--lg"></i>
                    </div>
                </div>
                
                <div class="card card-1" id="addUnit">
                    <div class="card--data">
                        <div class="card--content">
                            <button class="add show-form"><i class="ri-add-line"></i>Add Units</button>
                            <h1><?php total_rows('tblunit') ?> Units</h1>
                        </div>
                        <i class="ri-file-text-line card--icon--lg"></i>
                    </div>
                </div>

                <div class="card card-1" id="addFaculty">
                    <div class="card--data">
                        <div class="card--content">
                            <button class="add show-form"><i class="ri-add-line"></i>Add Faculty</button>
                            <h1><?php total_rows("tblfaculty") ?> Faculties</h1>
                        </div>
                        <i class="ri-building-2-line card--icon--lg"></i>
                    </div>
                </div>
            </div>

            <!-- Message Display -->
            <?php 
            if (isset($_SESSION['message'])) {
                $messageType = $_SESSION['message_type'] ?? 'info';
                echo '<div class="message ' . $messageType . '">' . $_SESSION['message'] . '</div>';
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>
            
            <!-- Courses Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2 class="section--title">Courses</h2>
                    <div class="table-stats">
                        <span><?php total_rows('tblcourse') ?> Total</span>
                    </div>
                </div>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Faculty</th>
                                <th>Total Units</th>
                                <th>Total Students</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT 
                        c.name AS course_name,
                        c.facultyID AS faculty,
                        f.facultyName AS faculty_name,
                        c.Id AS Id,
                        COUNT(u.Id) AS total_units,
                        COUNT(DISTINCT s.Id) AS total_students,
                        c.dateCreated AS date_created
                        FROM tblcourse c
                        LEFT JOIN tblunit u ON c.Id = u.courseID
                        LEFT JOIN tblstudents s ON c.courseCode = s.courseCode
                        LEFT JOIN tblfaculty f on c.facultyID=f.Id
                        GROUP BY c.Id";

                            $result = fetch($sql);

                            if ($result) {
                                foreach ($result as $row) {
                                    echo "<tr id='rowcourse{$row["Id"]}'>";
                                    echo "<td><strong>" . $row["course_name"] . "</strong></td>";
                                    echo "<td>" . $row["faculty_name"] . "</td>";
                                    echo "<td><span style='background: rgba(124, 58, 237, 0.1); color: var(--primary-purple); padding: 4px 8px; border-radius: 6px; font-weight: 700;'>" . $row["total_units"] . "</span></td>";
                                    echo "<td><span style='background: rgba(13, 148, 136, 0.1); color: var(--accent-teal); padding: 4px 8px; border-radius: 6px; font-weight: 700;'>" . $row["total_students"] . "</span></td>";
                                    echo "<td>" . $row["date_created"] . "</td>";
                                    echo "<td><span><i class='ri-delete-bin-line delete' data-id='{$row["Id"]}' data-name='course' title='Delete Course'></i></span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align: center; padding: 2rem; color: var(--light-slate);'>No courses found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Units Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2 class="section--title">Units</h2>
                    <div class="table-stats">
                        <span><?php total_rows('tblunit') ?> Total</span>
                    </div>
                </div>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Unit Code</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Schedule</th>
                                <th>Total Students</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // FIXED QUERY: Count students from attendance records for accurate count
                            $sql = "SELECT 
                                c.name AS course_name,
                                u.unitCode AS unit_code,
                                u.name AS unit_name, 
                                u.Id as Id,
                                u.startTime AS start_time,
                                u.endTime AS end_time,
                                u.dateCreated AS date_created,
                                COUNT(DISTINCT a.studentRegistrationNumber) AS total_students
                            FROM tblunit u
                            LEFT JOIN tblcourse c ON u.courseID = c.Id
                            LEFT JOIN tblattendance a ON u.unitCode = a.unit AND c.courseCode = a.course
                            GROUP BY u.Id";
                            
                            $result = fetch($sql);
                            if ($result) {
                                foreach ($result as $row) {
                                    echo "<tr id='rowunit{$row["Id"]}'>";
                                    echo "<td><strong>" . $row["unit_code"] . "</strong></td>";
                                    echo "<td>" . $row["unit_name"] . "</td>";
                                    echo "<td>" . $row["course_name"] . "</td>";
                                    echo "<td><small>" . formatTimeForDisplay($row["start_time"]) . " - " . formatTimeForDisplay($row["end_time"]) . "</small></td>";
                                    echo "<td><span style='background: rgba(13, 148, 136, 0.1); color: var(--accent-teal); padding: 4px 8px; border-radius: 6px; font-weight: 700;'>" . $row["total_students"] . "</span></td>";
                                    echo "<td>" . $row["date_created"] . "</td>";
                                    echo "<td><span><i class='ri-delete-bin-line delete' data-id='{$row["Id"]}' data-name='unit' title='Delete Unit'></i></span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' style='text-align: center; padding: 2rem; color: var(--light-slate);'>No units found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Faculties Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2 class="section--title">Faculties</h2>
                    <div class="table-stats">
                        <span><?php total_rows('tblfaculty') ?> Total</span>
                    </div>
                </div>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Total Courses</th>
                                <th>Total Students</th>
                                <th>Total Lectures</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT 
                           f.facultyName AS faculty_name,
                           f.facultyCode AS faculty_code,
                           f.Id as Id,
                           f.dateRegistered AS date_created,
                           COUNT(DISTINCT c.Id) AS total_courses,
                           COUNT(DISTINCT s.Id) AS total_students,
                           COUNT(DISTINCT l.Id) AS total_lectures
                       FROM tblfaculty f
                       LEFT JOIN tblcourse c ON f.Id = c.facultyID
                       LEFT JOIN tblstudents s ON f.facultyCode = s.faculty
                       LEFT JOIN tbllecture l ON f.facultyCode = l.facultyCode
                       GROUP BY f.Id";

                            $result = fetch($sql);
                            if ($result) {
                                foreach ($result as $row) {
                                    echo "<tr id='rowfaculty{$row["Id"]}'>";
                                    echo "<td><strong>" . $row["faculty_code"] . "</strong></td>";
                                    echo "<td>" . $row["faculty_name"] . "</td>";
                                    echo "<td><span style='background: rgba(124, 58, 237, 0.1); color: var(--primary-purple); padding: 4px 8px; border-radius: 6px; font-weight: 700;'>" . $row["total_courses"] . "</span></td>";
                                    echo "<td><span style='background: rgba(13, 148, 136, 0.1); color: var(--accent-teal); padding: 4px 8px; border-radius: 6px; font-weight: 700;'>" . $row["total_students"] . "</span></td>";
                                    echo "<td><span style='background: rgba(217, 119, 6, 0.1); color: var(--accent-amber); padding: 4px 8px; border-radius: 6px; font-weight: 700;'>" . $row["total_lectures"] . "</span></td>";
                                    echo "<td>" . $row["date_created"] . "</td>";
                                    echo "<td><span><i class='ri-delete-bin-line delete' data-id='{$row["Id"]}' data-name='faculty' title='Delete Faculty'></i></span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' style='text-align: center; padding: 2rem; color: var(--light-slate);'>No faculties found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Enhanced Forms -->
        <!-- Add Course Form -->
        <div class="formDiv" id="addCourseForm" style="display:none;">
            <form method="POST" action="" name="addCourse" enctype="multipart/form-data">
                <div style="display:flex; justify-content:space-between; align-items:center; padding: 0 2rem;">
                    <div class="form-title" style="flex: 1;">
                        <p>Add New Course</p>
                    </div>
                    <div>
                        <span class="close">&times;</span>
                    </div>
                </div>
                <div style="padding: 2rem;">
                    <input type="text" name="courseName" placeholder="Course Name (e.g., Computer Science)" required>
                    <input type="text" name="courseCode" placeholder="Course Code (e.g., CS101)" required>

                    <select required name="faculty">
                        <option value="" selected disabled>Select Faculty</option>
                        <?php
                        $facultyNames = getFacultyNames();
                        foreach ($facultyNames as $faculty) {
                            echo '<option value="' . $faculty["Id"] . '">' . $faculty["facultyName"] . '</option>';
                        }
                        ?>
                    </select>

                    <input type="submit" class="submit" value="Save Course" name="addCourse">
                </div>
            </form>
        </div>

        <!-- Add Unit Form -->
        <div class="formDiv" id="addUnitForm" style="display:none;">
            <form method="POST" action="" name="addUnit" enctype="multipart/form-data">
                <div style="display:flex; justify-content:space-between; align-items:center; padding: 0 2rem;">
                    <div class="form-title" style="flex: 1;">
                        <p>Add New Unit</p>
                    </div>
                    <div>
                        <span class="close">&times;</span>
                    </div>
                </div>
                <div style="padding: 2rem;">
                    <input type="text" name="unitName" placeholder="Unit Name (e.g., Database Management)" required>
                    <input type="text" name="unitCode" placeholder="Unit Code (e.g., DBMS301)" required>

                    <select required name="course">
                        <option value="" selected disabled>Select Course</option>
                        <?php
                        $courseNames = getCourseNames();
                        foreach ($courseNames as $course) {
                            echo '<option value="' . $course["Id"] . '">' . $course["name"] . ' (' . $course["courseCode"] . ')</option>';
                        }
                        ?>
                    </select>

                    <!-- Enhanced Time Fields -->
                    <div class="time-fields">
                        <label><i class="ri-time-line"></i> Class Schedule</label>
                        <input type="time" name="startTime" required>
                        <div class="time-format-note">Start time of the class</div>
                        
                        <input type="time" name="endTime" required>
                        <div class="time-format-note">End time of the class</div>
                    </div>

                    <input type="submit" class="submit" value="Save Unit" name="addUnit">
                </div>
            </form>
        </div>

        <!-- Add Faculty Form -->
        <div class="formDiv" id="addFacultyForm" style="display:none;">
            <form method="POST" action="" name="addFaculty" enctype="multipart/form-data">
                <div style="display:flex; justify-content:space-between; align-items:center; padding: 0 2rem;">
                    <div class="form-title" style="flex: 1;">
                        <p>Add New Faculty</p>
                    </div>
                    <div>
                        <span class="close">&times;</span>
                    </div>
                </div>
                <div style="padding: 2rem;">
                    <input type="text" name="facultyName" placeholder="Faculty Name (e.g., Faculty of Computing)" required>
                    <input type="text" name="facultyCode" placeholder="Faculty Code (e.g., FOC)" required>
                    
                    <input type="submit" class="submit" value="Save Faculty" name="addFaculty">
                </div>
            </form>
        </div>

    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const showForms = document.querySelectorAll(".show-form");
            const addCourseForm = document.getElementById('addCourseForm');
            const addUnitForm = document.getElementById('addUnitForm');
            const addFacultyForm = document.getElementById('addFacultyForm');
            const overlay = document.getElementById('overlay');
            const closeButtons = document.querySelectorAll('.close');

            // Show form functions
            function showCourseForm() {
                addCourseForm.style.display = 'block';
                overlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }

            function showUnitForm() {
                addUnitForm.style.display = 'block';
                overlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }

            function showFacultyForm() {
                addFacultyForm.style.display = 'block';
                overlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }

            // Hide form function
            function hideForms() {
                addCourseForm.style.display = 'none';
                addUnitForm.style.display = 'none';
                addFacultyForm.style.display = 'none';
                overlay.style.display = 'none';
                document.body.style.overflow = 'auto';
            }

            // Add event listeners to show form buttons
            document.getElementById('addCourse').addEventListener('click', showCourseForm);
            document.getElementById('addUnit').addEventListener('click', showUnitForm);
            document.getElementById('addFaculty').addEventListener('click', showFacultyForm);

            // Add event listeners to close buttons
            closeButtons.forEach(function(closeButton) {
                closeButton.addEventListener('click', hideForms);
            });

            // Close form when clicking on overlay
            overlay.addEventListener('click', hideForms);

            // Prevent form from closing when clicking inside it
            const forms = [addCourseForm, addUnitForm, addFacultyForm];
            forms.forEach(form => {
                if (form) {
                    form.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                }
            });

            // Unit form validation
            const unitForm = document.querySelector('form[name="addUnit"]');
            if (unitForm) {
                unitForm.addEventListener('submit', function(e) {
                    const startTime = this.querySelector('input[name="startTime"]').value;
                    const endTime = this.querySelector('input[name="endTime"]').value;
                    
                    if (startTime && endTime) {
                        if (startTime >= endTime) {
                            e.preventDefault();
                            alert('Error: End time must be after start time');
                            return false;
                        }
                    }
                });
            }

            // Auto-hide messages after 5 seconds
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.opacity = '0';
                    message.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => {
                        if (message.parentNode) {
                            message.parentNode.removeChild(message);
                        }
                    }, 500);
                }, 5000);
            });

            // Enhanced card hover effects
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>

    <?php js_asset(["delete_request", "addCourse", "active_link"]) ?>
</body>
</html>