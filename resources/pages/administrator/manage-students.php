<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Delete Student functionality
if (isset($_GET['deleteStudent'])) {
    $studentId = $_GET['deleteStudent'];
    
    try {
        // First, get the registration number to delete the image folder
        $getStudentQuery = $pdo->prepare("SELECT registrationNumber FROM tblstudents WHERE Id = :id");
        $getStudentQuery->execute([':id' => $studentId]);
        $student = $getStudentQuery->fetch(PDO::FETCH_ASSOC);
        
        if ($student) {
            $registrationNumber = $student['registrationNumber'];
            
            // Delete student from database
            $deleteQuery = $pdo->prepare("DELETE FROM tblstudents WHERE Id = :id");
            $deleteQuery->execute([':id' => $studentId]);
            
            // Delete student images folder
            $folderPath = "resources/labels/{$registrationNumber}/";
            if (file_exists($folderPath)) {
                // Delete all files in the folder
                $files = glob($folderPath . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                // Remove the folder
                rmdir($folderPath);
            }
            
            $_SESSION['message'] = "Student deleted successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Student not found!";
            $_SESSION['message_type'] = "error";
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error deleting student: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
    
    // Redirect to clear GET parameters
    header("Location: " . str_replace('?deleteStudent=' . $studentId, '', $_SERVER['REQUEST_URI']));
    exit;
}

if (isset($_POST['addStudent'])) {
    // Securely handle input
    $firstName = htmlspecialchars(trim($_POST['firstName']));
    $lastName = htmlspecialchars(trim($_POST['lastName']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $registrationNumber = htmlspecialchars(trim($_POST['registrationNumber']));
    $courseCode = htmlspecialchars(trim($_POST['course']));
    $faculty = htmlspecialchars(trim($_POST['faculty']));
    $dateRegistered = date("Y-m-d H:i:s");

    $imageFileNames = []; // Array to hold image file names

    // Validate registration number format
    $invalidCharacters = '/[\\/:*?"<>|]/';
    if (preg_match($invalidCharacters, $registrationNumber)) {
        $_SESSION['message'] = "Registration number contains invalid characters!";
        $_SESSION['message_type'] = "error";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($firstName && $lastName && $email && $registrationNumber && $courseCode && $faculty) {
        try {
            // Check for duplicate registration number
            $checkQuery = $pdo->prepare("SELECT COUNT(*) FROM tblstudents WHERE registrationNumber = :registrationNumber");
            $checkQuery->execute([':registrationNumber' => $registrationNumber]);
            $count = $checkQuery->fetchColumn();

            if ($count > 0) {
                $_SESSION['message'] = "Student with the given Registration No: $registrationNumber already exists!";
                $_SESSION['message_type'] = "error";
            } else {
                // Process and save images
                $folderPath = "resources/labels/{$registrationNumber}/";
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0777, true);
                }

                $imagesSaved = false;
                $capturedImagesCount = 0;
                
                for ($i = 1; $i <= 5; $i++) {
                    if (isset($_POST["capturedImage$i"]) && !empty($_POST["capturedImage$i"])) {
                        $base64Image = $_POST["capturedImage$i"];
                        
                        // Check if it's a valid base64 image
                        if (strpos($base64Image, 'data:image/png;base64,') === 0) {
                            $base64Data = str_replace('data:image/png;base64,', '', $base64Image);
                            $base64Data = str_replace(' ', '+', $base64Data);
                            $imageData = base64_decode($base64Data);
                            
                            if ($imageData !== false) {
                                $fileName = "{$registrationNumber}_image{$i}.png";
                                $labelName = "{$i}.png";
                                
                                if (file_put_contents("{$folderPath}{$labelName}", $imageData)) {
                                    $imageFileNames[] = $fileName;
                                    $imagesSaved = true;
                                    $capturedImagesCount++;
                                }
                            }
                        }
                    }
                }

                // Convert image file names to JSON
                $imagesJson = json_encode($imageFileNames);

                // Insert new student with images stored as JSON
                $insertQuery = $pdo->prepare("
                    INSERT INTO tblstudents 
                    (firstName, lastName, email, registrationNumber, faculty, courseCode, studentImage, dateRegistered) 
                    VALUES 
                    (:firstName, :lastName, :email, :registrationNumber, :faculty, :courseCode, :studentImage, :dateRegistered)
                ");

                $insertQuery->execute([
                    ':firstName' => $firstName,
                    ':lastName' => $lastName,
                    ':email' => $email,
                    ':registrationNumber' => $registrationNumber,
                    ':faculty' => $faculty,
                    ':courseCode' => $courseCode,
                    ':studentImage' => $imagesJson,
                    ':dateRegistered' => $dateRegistered
                ]);

                $_SESSION['message'] = "Student: $registrationNumber added successfully! ($capturedImagesCount images saved)";
                $_SESSION['message_type'] = "success";
                
                // Redirect to clear POST data
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['message'] = "Database Error: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Please fill all required fields correctly.";
        $_SESSION['message_type'] = "error";
    }
    
    // If we get here, there was an error - redirect to clear POST data
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="resources/images/logo/attnlg.png" rel="icon">
    <title>Student Management - AMS</title>
    <link rel="stylesheet" href="resources/assets/css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
            --card-bg: #ffffff;
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
            background: linear-gradient(135deg, var(--primary-purple) 0%, #5b21b6 100%);
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

        /* Table Container */
        .table-container {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(124, 58, 237, 0.1);
        }

        .table-container .title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
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
            width: 50px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            border-radius: 2px;
        }

        .add {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            color: white;
            border: none;
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }

        .add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
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

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.15);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        /* Delete Button */
        .delete {
            color: var(--accent-rose);
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .delete:hover {
            background: rgba(225, 29, 72, 0.1);
            transform: scale(1.1);
        }

        /* Form Styles */
        .formDiv-- {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 0;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            min-width: 800px;
            max-width: 90vw;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid rgba(124, 58, 237, 0.2);
        }

        .form-title {
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            font-weight: 600;
            font-size: 1.2rem;
            flex: 1;
        }

        .form-title-image {
            padding: 1rem;
            background: linear-gradient(135deg, var(--accent-teal) 0%, #0f766e 100%);
            color: white;
            border-radius: 12px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 1rem;
        }

        .formDiv-- form {
            padding: 0;
        }

        .formDiv-- form > div:first-child {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            margin: 0;
        }

        .formDiv-- form > div:nth-child(2) {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            padding: 2rem;
        }

        .formDiv-- input, .formDiv-- select {
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

        .formDiv-- input:focus, .formDiv-- select:focus {
            border-color: var(--primary-purple);
            outline: none;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
            transform: translateY(-2px);
        }

        /* COPIED SUBMIT BUTTON DESIGN FROM VENUE MANAGEMENT */
        .btn-submit {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            margin: 1rem 2rem 2rem;
            width: calc(100% - 4rem);
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
            transition: all 0.3s ease;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
            background: linear-gradient(135deg, #6d28d9 0%, #4c1d95 100%);
        }

        .btn-submit:active {
            transform: translateY(0);
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

        /* Image Capture Styles */
        .camera-btn {
            width: 100%;
            height: 200px;
            border: 3px dashed #cbd5e1;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8fafc;
            margin-bottom: 1rem;
            text-align: center;
        }

        .camera-btn:hover {
            border-color: var(--primary-purple);
            background: rgba(124, 58, 237, 0.05);
            transform: translateY(-2px);
        }

        .camera-btn i {
            font-size: 3rem;
            color: var(--primary-purple);
            margin-bottom: 0.5rem;
        }

        .camera-btn span {
            color: var(--dark-slate);
            font-weight: 600;
        }

        .camera-btn p {
            color: var(--light-slate);
            font-size: 0.9rem;
            margin: 0.25rem 0 0 0;
        }

        #multiple-images {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .image-preview-container {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #e2e8f0;
        }

        .image-preview {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }

        .image-number {
            position: absolute;
            top: 8px;
            left: 8px;
            background: var(--primary-purple);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Registration Number Validation */
        .validation-message {
            display: none;
            padding: 0.5rem;
            margin: 0.5rem 0;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .validation-error {
            background: rgba(225, 29, 72, 0.1);
            color: #e11d48;
            border: 1px solid rgba(225, 29, 72, 0.2);
        }

        .validation-success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main--content {
                margin-left: 0;
                padding: 1rem;
            }

            .formDiv-- {
                min-width: 95vw;
            }

            .formDiv-- form > div:nth-child(2) {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 2rem 1.5rem;
            }

            .dashboard-header h1 {
                font-size: 2rem;
            }

            .table-container {
                padding: 1.5rem;
            }

            .table-container .title {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            table {
                min-width: 600px;
            }

            #multiple-images {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .dashboard-header h1 {
                font-size: 1.8rem;
            }

            .add {
                width: 100%;
                justify-content: center;
            }

            .formDiv-- form > div:first-child {
                padding: 0 1rem;
            }

            .formDiv-- form > div:nth-child(2) {
                padding: 1rem;
            }

            .btn-submit {
                margin: 1rem 1rem 2rem;
                width: calc(100% - 2rem);
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--light-slate);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
            display: block;
        }

        .empty-state p {
            font-size: 1rem;
            opacity: 0.8;
        }

        /* Camera Modal Styles */
        .camera-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .camera-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 90vw;
            max-height: 90vh;
            position: relative;
            text-align: center;
        }

        .camera-preview {
            width: 100%;
            max-width: 500px;
            border-radius: 12px;
            margin-bottom: 1rem;
            border: 2px solid var(--primary-purple);
        }

        .camera-controls {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .capture-btn {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .capture-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
        }

        .retake-btn {
            background: var(--accent-amber);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .retake-btn:hover {
            background: #b45309;
            transform: translateY(-2px);
        }

        .close-camera {
            position: absolute;
            top: 1rem;
            right: 1rem;
            color: white;
            background: rgba(0, 0, 0, 0.5);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .close-camera:hover {
            background: rgba(0, 0, 0, 0.7);
        }

        .image-status {
            text-align: center;
            padding: 0.5rem;
            font-size: 0.9rem;
            color: var(--light-slate);
            margin-bottom: 1rem;
        }

        .image-status.has-images {
            color: var(--accent-teal);
            font-weight: 600;
        }

        .camera-instruction {
            text-align: center;
            color: white;
            margin-top: 1rem;
            font-size: 1rem;
        }

        .camera-counter {
            text-align: center;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <?php include 'includes/topbar.php'; ?>

    <section class="main">
        <?php include "Includes/sidebar.php"; ?>

        <div class="main--content">
            <div id="overlay"></div>

            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1>Student Management</h1>
                <p>Manage and organize student records</p>
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

            <!-- Students Table -->
            <div class="table-container">
                <div class="title" id="showButton">
                    <h2 class="section--title">Students</h2>
                    <button class="add show-form"><i class="ri-add-line"></i>Add Student</button>
                </div>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Registration No</th>
                                <th>Name</th>
                                <th>Faculty</th>
                                <th>Course</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $sql = "SELECT * FROM tblstudents ORDER BY dateRegistered DESC";
                                $result = fetch($sql);
                                
                                if ($result && count($result) > 0) {
                                    foreach ($result as $row) {
                                        echo "<tr id='rowstudents{$row["Id"]}'>";
                                        echo "<td><strong>" . htmlspecialchars($row["registrationNumber"]) . "</strong></td>";
                                        echo "<td>" . htmlspecialchars($row["firstName"] . " " . $row["lastName"]) . "</td>";
                                        echo "<td><span class='status-badge status-active'>" . htmlspecialchars($row["faculty"]) . "</span></td>";
                                        echo "<td>" . htmlspecialchars($row["courseCode"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                        echo "<td><i class='ri-delete-bin-line delete' data-id='{$row["Id"]}' data-name='students' title='Delete Student'></i></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6'><div class='empty-state'><i class='ri-user-line'></i><p>No students found. Add your first student above.</p></div></td></tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='6' style='text-align: center; color: #e11d48; padding: 2rem;'>Error loading students: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Student Form -->
            <div class="formDiv--" id="form" style="display:none">
                <form method="POST" action="" name="addStudent" id="studentForm">
                    <div>
                        <div class="form-title">
                            <p>Add New Student</p>
                        </div>
                        <div>
                            <span class="close">&times;</span>
                        </div>
                    </div>
                    <div>
                        <div>
                            <input type="text" name="firstName" placeholder="First Name" required>
                            <input type="text" name="lastName" placeholder="Last Name" required>
                            <input type="email" name="email" placeholder="Email Address" required>
                            <input type="text" required id="registrationNumber" name="registrationNumber" placeholder="Registration Number">
                            <div id="validationMessage" class="validation-message"></div>
                            
                            <select required name="faculty">
                                <option value="" selected disabled>Select Faculty</option>
                                <?php
                                $facultyNames = getFacultyNames();
                                foreach ($facultyNames as $faculty) {
                                    echo '<option value="' . htmlspecialchars($faculty["facultyCode"]) . '">' . htmlspecialchars($faculty["facultyName"]) . '</option>';
                                }
                                ?>
                            </select>

                            <select required name="course">
                                <option value="" selected disabled>Select Course</option>
                                <?php
                                $courseNames = getCourseNames();
                                foreach ($courseNames as $course) {
                                    echo '<option value="' . htmlspecialchars($course["courseCode"]) . '">' . htmlspecialchars($course["name"]) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <div class="form-title-image">
                                <p>Take Student Pictures</p>
                            </div>
                            <button type="button" id="open_camera" class="camera-btn">
                                <i class="ri-camera-line"></i>
                                <span>Open Camera</span>
                                <p>Click to capture student images</p>
                            </button>
                            <div id="imageStatus" class="image-status">No images captured yet</div>
                            <div id="multiple-images">
                                <!-- Image previews will be added here -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- COPIED SUBMIT BUTTON FROM VENUE MANAGEMENT -->
                    <button type="submit" class="btn-submit" name="addStudent">
                        <i class="ri-check-line"></i>
                        SAVE STUDENT
                    </button>
                </form>
            </div>

            <!-- Camera Modal -->
            <div class="camera-modal" id="cameraModal">
                <div class="camera-container">
                    <button class="close-camera">&times;</button>
                    <div class="camera-counter">Image <span id="currentImageCount">1</span> of 5</div>
                    <video id="video" class="camera-preview" autoplay playsinline></video>
                    <canvas id="canvas" style="display: none;"></canvas>
                    <div class="camera-controls">
                        <button class="capture-btn" id="captureBtn">
                            <i class="ri-camera-line"></i>Capture Image
                        </button>
                        <button class="retake-btn" id="retakeBtn" style="display: none;">
                            <i class="ri-refresh-line"></i>Retake
                        </button>
                    </div>
                    <div class="camera-instruction">
                        Position the student's face in the frame and click "Capture Image"
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php js_asset(["admin_functions", "delete_request", "script", "active_link"]) ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const showForms = document.querySelectorAll(".show-form");
            const addStudentForm = document.getElementById('form');
            const overlay = document.getElementById('overlay');
            const closeButtons = document.querySelectorAll('.close, .close-camera');
            const registrationNumberInput = document.getElementById('registrationNumber');
            const validationMessage = document.getElementById('validationMessage');
            const cameraModal = document.getElementById('cameraModal');
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const captureBtn = document.getElementById('captureBtn');
            const retakeBtn = document.getElementById('retakeBtn');
            const submitBtn = document.getElementById('submitBtn');
            const imageStatus = document.getElementById('imageStatus');
            const currentImageCount = document.getElementById('currentImageCount');
            const openCameraBtn = document.getElementById('open_camera');
            
            let currentImageIndex = 1;
            const maxImages = 5;
            const capturedImages = new Array(maxImages).fill(null);
            let currentStream = null;
            
            // Invalid characters regex
            const invalidCharacters = /[\\/:*?"<>|]/g;
            
            // Show form function
            function showForm() {
                addStudentForm.style.display = 'block';
                overlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }

            // Hide form function
            function hideForm() {
                addStudentForm.style.display = 'none';
                overlay.style.display = 'none';
                document.body.style.overflow = 'auto';
                resetForm();
            }

            // Reset form function
            function resetForm() {
                document.getElementById('studentForm').reset();
                validationMessage.style.display = 'none';
                validationMessage.className = 'validation-message';
                currentImageIndex = 1;
                capturedImages.fill(null);
                updateImagePreviews();
            }

            // Add event listeners to show form buttons
            showForms.forEach((button) => {
                button.addEventListener('click', showForm);
            });

            // Add event listeners to close buttons
            closeButtons.forEach(function(closeButton) {
                closeButton.addEventListener('click', function() {
                    if (this.classList.contains('close-camera')) {
                        closeCamera();
                    } else {
                        hideForm();
                    }
                });
            });

            // Close form when clicking on overlay
            overlay.addEventListener('click', hideForm);

            // Prevent form from closing when clicking inside it
            addStudentForm.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            // Open camera when camera button is clicked
            openCameraBtn.addEventListener('click', function() {
                takeMultipleImages();
            });

            // Registration number validation
            registrationNumberInput.addEventListener('input', function() {
                const value = this.value;
                const hasInvalidChars = invalidCharacters.test(value);
                
                if (hasInvalidChars) {
                    const sanitizedValue = value.replace(invalidCharacters, '');
                    this.value = sanitizedValue;
                    
                    validationMessage.textContent = 'Invalid characters removed from registration number.';
                    validationMessage.className = 'validation-message validation-error';
                    validationMessage.style.display = 'block';
                    
                    // Hide message after 3 seconds
                    setTimeout(() => {
                        validationMessage.style.display = 'none';
                    }, 3000);
                } else if (value.length > 0) {
                    validationMessage.textContent = 'Registration number format is valid.';
                    validationMessage.className = 'validation-message validation-success';
                    validationMessage.style.display = 'block';
                } else {
                    validationMessage.style.display = 'none';
                }
            });

            // Camera functionality
            function takeMultipleImages() {
                currentImageIndex = 1;
                openCamera();
            }

            function openCamera() {
                cameraModal.style.display = 'flex';
                currentImageCount.textContent = currentImageIndex;
                captureBtn.style.display = 'flex';
                retakeBtn.style.display = 'none';
                
                // Stop any existing stream
                if (currentStream) {
                    currentStream.getTracks().forEach(track => track.stop());
                }
                
                navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }, 
                    audio: false 
                })
                .then(function(stream) {
                    currentStream = stream;
                    video.srcObject = stream;
                    video.play().catch(e => console.error('Error playing video:', e));
                })
                .catch(function(err) {
                    console.error("Error accessing camera: ", err);
                    alert("Error accessing camera. Please make sure you have granted camera permissions.");
                });
            }

            function closeCamera() {
                cameraModal.style.display = 'none';
                if (currentStream) {
                    currentStream.getTracks().forEach(track => track.stop());
                    currentStream = null;
                }
            }

            captureBtn.addEventListener('click', function() {
                // Ensure canvas has proper dimensions
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                
                const context = canvas.getContext('2d');
                
                // Draw the current video frame to canvas
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                // Convert canvas to base64 image
                const imageData = canvas.toDataURL('image/png');
                
                console.log('Captured image data length:', imageData.length);
                
                // Store the image data
                capturedImages[currentImageIndex - 1] = imageData;
                
                // Create or update hidden input for the captured image
                let hiddenInput = document.getElementById(`capturedImage${currentImageIndex}`);
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `capturedImage${currentImageIndex}`;
                    hiddenInput.id = `capturedImage${currentImageIndex}`;
                    document.getElementById('studentForm').appendChild(hiddenInput);
                }
                hiddenInput.value = imageData;
                
                console.log(`Hidden input ${currentImageIndex} created with value length:`, hiddenInput.value.length);
                
                updateImagePreviews();
                
                if (currentImageIndex < maxImages) {
                    currentImageIndex++;
                    currentImageCount.textContent = currentImageIndex;
                    // Continue capturing more images
                } else {
                    closeCamera();
                    alert('Maximum of 5 images captured. You can review them below.');
                }
            });

            function updateImagePreviews() {
                const multipleImagesContainer = document.getElementById('multiple-images');
                multipleImagesContainer.innerHTML = '';
                
                let capturedCount = 0;
                
                capturedImages.forEach((imageData, index) => {
                    if (imageData && imageData.length > 1000) { // Ensure it's a valid image
                        capturedCount++;
                        const container = document.createElement('div');
                        container.className = 'image-preview-container';
                        
                        const img = document.createElement('img');
                        img.src = imageData;
                        img.className = 'image-preview';
                        img.alt = `Student image ${index + 1}`;
                        
                        const number = document.createElement('div');
                        number.className = 'image-number';
                        number.textContent = index + 1;
                        
                        container.appendChild(img);
                        container.appendChild(number);
                        multipleImagesContainer.appendChild(container);
                    }
                });
                
                // Update image status
                if (capturedCount > 0) {
                    imageStatus.textContent = `${capturedCount} image(s) captured successfully`;
                    imageStatus.className = 'image-status has-images';
                    openCameraBtn.innerHTML = `
                        <i class="ri-camera-line"></i>
                        <span>Capture More Images</span>
                        <p>Click to capture additional images</p>
                    `;
                } else {
                    imageStatus.textContent = 'No images captured yet';
                    imageStatus.className = 'image-status';
                }
                
                console.log('Total captured images:', capturedCount);
            }

            // Form validation before submission
            document.getElementById('studentForm').addEventListener('submit', function(e) {
                const registrationNumber = registrationNumberInput.value.trim();
                
                if (!registrationNumber) {
                    e.preventDefault();
                    validationMessage.textContent = 'Registration number is required.';
                    validationMessage.className = 'validation-message validation-error';
                    validationMessage.style.display = 'block';
                    registrationNumberInput.focus();
                    return;
                }
                
                if (invalidCharacters.test(registrationNumber)) {
                    e.preventDefault();
                    validationMessage.textContent = 'Registration number contains invalid characters.';
                    validationMessage.className = 'validation-message validation-error';
                    validationMessage.style.display = 'block';
                    registrationNumberInput.focus();
                    return;
                }
                
                // Check if at least one image is captured
                const hasImages = capturedImages.some(img => img !== null && img.length > 1000);
                if (!hasImages) {
                    if (!confirm('No student images captured. Are you sure you want to continue without images?')) {
                        e.preventDefault();
                        return;
                    }
                }
                
                // Debug: Log captured images before submission
                console.log('Form submission - captured images:', capturedImages.filter(img => img !== null && img.length > 1000).length);
            });

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

            // Delete confirmation
            const deleteButtons = document.querySelectorAll('.delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const itemName = this.closest('tr').querySelector('td:first-child').textContent;
                    
                    if (confirm(`Are you sure you want to delete student: "${itemName}"? This action cannot be undone.`)) {
                        // Redirect to delete the student
                        window.location.href = `?deleteStudent=${id}`;
                    }
                });
            });
        });
    </script>
</body>
</html>