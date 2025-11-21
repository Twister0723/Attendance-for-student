<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST["addLecture"])) {
    // Securely handle input
    $firstName = htmlspecialchars(trim($_POST["firstName"]));
    $lastName = htmlspecialchars(trim($_POST["lastName"]));
    $email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);
    $phoneNumber = htmlspecialchars(trim($_POST["phoneNumber"]));
    $faculty = htmlspecialchars(trim($_POST["faculty"]));
    $dateRegistered = date("Y-m-d H:i:s");
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Strong password validation
    $passwordErrors = [];
    
    if (strlen($password) < 8) {
        $passwordErrors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $passwordErrors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $passwordErrors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $passwordErrors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
        $passwordErrors[] = "Password must contain at least one special character (!@#$%^&*()-_=+{};:,<.>)";
    }
    if ($password !== $confirmPassword) {
        $passwordErrors[] = "Passwords do not match";
    }

    if ($email && $firstName && $lastName && $phoneNumber && $faculty) {
        if (!empty($passwordErrors)) {
            $_SESSION['message'] = "Password requirements not met:<br>" . implode("<br>", $passwordErrors);
            $_SESSION['message_type'] = "error";
        } else {
            try {
                // Check if lecture already exists
                $query = $pdo->prepare("SELECT * FROM tbllecture WHERE emailAddress = :email");
                $query->bindParam(':email', $email);
                $query->execute();

                if ($query->rowCount() > 0) {
                    $_SESSION['message'] = "Lecture with this email already exists";
                    $_SESSION['message_type'] = "error";
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT); // Secure password hashing

                    // Insert new lecture
                    $query = $pdo->prepare("INSERT INTO tbllecture 
                        (firstName, lastName, emailAddress, password, phoneNo, facultyCode, dateCreated) 
                        VALUES (:firstName, :lastName, :email, :password, :phoneNumber, :faculty, :dateCreated)");
                    $query->bindParam(':firstName', $firstName);
                    $query->bindParam(':lastName', $lastName);
                    $query->bindParam(':email', $email);
                    $query->bindParam(':password', $hashedPassword);
                    $query->bindParam(':phoneNumber', $phoneNumber);
                    $query->bindParam(':faculty', $faculty);
                    $query->bindParam(':dateCreated', $dateRegistered);

                    if ($query->execute()) {
                        $_SESSION['message'] = "Lecture '$firstName $lastName' added successfully!";
                        $_SESSION['message_type'] = "success";
                        
                        // Redirect to clear POST data
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit;
                    } else {
                        $_SESSION['message'] = "Failed to add lecture. Please try again.";
                        $_SESSION['message_type'] = "error";
                    }
                }
            } catch (PDOException $e) {
                $_SESSION['message'] = "Database Error: " . $e->getMessage();
                $_SESSION['message_type'] = "error";
            }
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
    <title>Lecture Management</title>
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

        .formDiv-- form {
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
            background: linear-gradient(135deg, #6d28d9 0%, #4c1d95 100%);
        }

        .submit:active {
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

        /* Password Field Styles */
        .password-field {
            position: relative;
            margin-bottom: 1rem;
        }

        .password-requirements {
            display: none;
            position: absolute;
            background: #fff;
            border: 2px solid var(--primary-purple);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            z-index: 1000;
            width: 320px;
            top: 100%;
            left: 0;
            margin-top: 8px;
        }

        .password-match {
            display: none;
            position: absolute;
            background: #fff;
            border: 2px solid var(--primary-purple);
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            z-index: 1000;
            width: 220px;
            top: 100%;
            left: 0;
            margin-top: 8px;
        }

        .password-requirements h4 {
            margin: 0 0 12px 0;
            color: var(--dark-slate);
            font-size: 14px;
            font-weight: 600;
        }

        .password-requirements ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .password-requirements li {
            padding: 6px 0;
            font-size: 13px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .password-requirements li.valid {
            color: #059669;
        }

        .password-requirements li.invalid {
            color: #dc3545;
        }

        .password-requirements li::before {
            content: '●';
            font-size: 8px;
        }

        .password-requirements li.valid::before {
            content: '✓';
            font-size: 12px;
        }

        #matchText.match {
            color: #059669;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        #matchText.match::before {
            content: '✓';
        }

        #matchText.no-match {
            color: #dc3545;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        #matchText.no-match::before {
            content: '✗';
        }

        #matchText.neutral {
            color: #6c757d;
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

            .table-container {
                padding: 1.5rem;
            }

            .table-container .title {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .formDiv-- {
                min-width: 95vw;
            }

            table {
                min-width: 600px;
            }

            .password-requirements {
                width: 280px;
                right: 0;
                left: auto;
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
    </style>
</head>

<body>
    <?php include "Includes/topbar.php"; ?>
    <section class="main">
        <?php include "Includes/sidebar.php"; ?>
        <div class="main--content">
            <div id="overlay"></div>

            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1>Lecture Management</h1>
                <p>Manage and organize academic lectures</p>
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

            <!-- Lectures Table -->
            <div class="table-container">
                <div class="title" id="showButton">
                    <h2 class="section--title">Lectures</h2>
                    <button class="add show-form"><i class="ri-add-line"></i>Add Lecture</button>
                </div>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email Address</th>
                                <th>Phone No</th>
                                <th>Faculty</th>
                                <th>Date Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $sql = "SELECT l.*, f.facultyName 
                                        FROM tbllecture l 
                                        LEFT JOIN tblfaculty f ON l.facultyCode = f.facultyCode 
                                        ORDER BY l.dateCreated DESC";
                                $result = fetch($sql);
                                
                                if ($result && count($result) > 0) {
                                    foreach ($result as $row) {
                                        echo "<tr id='rowlecture{$row["Id"]}'>";
                                        echo "<td><strong>" . htmlspecialchars($row["firstName"] . " " . $row["lastName"]) . "</strong></td>";
                                        echo "<td>" . htmlspecialchars($row["emailAddress"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["phoneNo"]) . "</td>";
                                        echo "<td><span class='status-badge status-active'>" . htmlspecialchars($row["facultyName"] ?? $row["facultyCode"]) . "</span></td>";
                                        echo "<td>" . date('M j, Y', strtotime($row["dateCreated"])) . "</td>";
                                        echo "<td><i class='ri-delete-bin-line delete' data-id='{$row["Id"]}' data-name='lecture' title='Delete Lecture'></i></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6'><div class='empty-state'><i class='ri-user-star-line'></i><p>No lectures found. Add your first lecture above.</p></div></td></tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='6' style='text-align: center; color: #e11d48; padding: 2rem;'>Error loading lectures: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Lecture Form -->
            <div class="formDiv--" id="form" style="display:none">
                <form method="POST" action="" name="addLecture" id="lectureForm">
                    <div style="display:flex; justify-content:space-between; align-items:center; padding: 0 2rem;">
                        <div class="form-title" style="flex: 1;">
                            <p>Add New Lecture</p>
                        </div>
                        <div>
                            <span class="close">&times;</span>
                        </div>
                    </div>
                    <div style="padding: 2rem;">
                        <input type="text" name="firstName" placeholder="First Name" required>
                        <input type="text" name="lastName" placeholder="Last Name" required>
                        <input type="email" name="email" placeholder="Email Address" required>
                        <input type="text" name="phoneNumber" placeholder="Phone Number" required>
                        
                        <div class="password-field">
                            <input type="password" name="password" id="password" placeholder="Enter strong password" required 
                                   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>])[A-Za-z\d!@#$%^&*()\-_=+{};:,<.>]{8,}$"
                                   title="Must contain at least 8 characters, one uppercase, one lowercase, one number and one special character">
                            
                            <div class="password-requirements" id="passwordRequirements">
                                <h4>Password Requirements:</h4>
                                <ul>
                                    <li id="reqLength">At least 8 characters</li>
                                    <li id="reqUppercase">One uppercase letter (A-Z)</li>
                                    <li id="reqLowercase">One lowercase letter (a-z)</li>
                                    <li id="reqNumber">One number (0-9)</li>
                                    <li id="reqSpecial">One special character (!@#$%^&*()-_=+{};:,<.>)</li>
                                </ul>
                            </div>
                        </div>

                        <div class="password-field">
                            <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm password" required>
                            <div class="password-match" id="passwordMatch">
                                <span id="matchText"></span>
                            </div>
                        </div>

                        <select required name="faculty">
                            <option value="" selected disabled>Select Faculty</option>
                            <?php
                            $facultyNames = getFacultyNames();
                            foreach ($facultyNames as $faculty) {
                                echo '<option value="' . htmlspecialchars($faculty["facultyCode"]) . '">' . htmlspecialchars($faculty["facultyName"]) . '</option>';
                            }
                            ?>
                        </select>
                        
                        <!-- COPIED SUBMIT BUTTON FROM VENUE MANAGEMENT -->
                        <button type="submit" class="submit" name="addLecture" id="submitBtn">
                            <i class="ri-check-line"></i>
                            SAVE LECTURE
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php js_asset(["admin_functions", "active_link", "delete_request", "script"]) ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const showForms = document.querySelectorAll(".show-form");
        const addLectureForm = document.getElementById('form');
        const overlay = document.getElementById('overlay');
        const closeButtons = document.querySelectorAll('.close');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const requirements = document.getElementById('passwordRequirements');
        const matchIndicator = document.getElementById('passwordMatch');
        const matchText = document.getElementById('matchText');
        const submitBtn = document.getElementById('submitBtn');
        
        let passwordValid = false;
        let passwordsMatch = false;
        let passwordFocused = false;
        let confirmPasswordFocused = false;
        
        // Show form function
        function showForm() {
            addLectureForm.style.display = 'block';
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        // Hide form function
        function hideForm() {
            addLectureForm.style.display = 'none';
            overlay.style.display = 'none';
            document.body.style.overflow = 'auto';
            // Reset password indicators when closing form
            requirements.style.display = 'none';
            matchIndicator.style.display = 'none';
            passwordFocused = false;
            confirmPasswordFocused = false;
        }

        // Add event listeners to show form buttons
        showForms.forEach((button) => {
            button.addEventListener('click', showForm);
        });

        // Add event listeners to close buttons
        closeButtons.forEach(function(closeButton) {
            closeButton.addEventListener('click', hideForm);
        });

        // Close form when clicking on overlay
        overlay.addEventListener('click', hideForm);

        // Prevent form from closing when clicking inside it
        addLectureForm.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // Show requirements when password field is focused
        passwordInput.addEventListener('focus', function() {
            passwordFocused = true;
            requirements.style.display = 'block';
        });
        
        // Show match indicator when confirm password field is focused
        confirmPasswordInput.addEventListener('focus', function() {
            confirmPasswordFocused = true;
            matchIndicator.style.display = 'block';
        });
        
        // Hide requirements when password field loses focus (with delay to allow clicking on requirements)
        passwordInput.addEventListener('blur', function() {
            setTimeout(() => {
                if (!requirements.matches(':hover') && !passwordFocused) {
                    requirements.style.display = 'none';
                }
            }, 200);
        });
        
        // Hide match indicator when confirm password field loses focus
        confirmPasswordInput.addEventListener('blur', function() {
            setTimeout(() => {
                if (!matchIndicator.matches(':hover') && !confirmPasswordFocused) {
                    matchIndicator.style.display = 'none';
                }
            }, 200);
        });
        
        // Track mouse events for password requirements
        requirements.addEventListener('mouseenter', function() {
            passwordFocused = true;
        });
        
        requirements.addEventListener('mouseleave', function() {
            passwordFocused = false;
            if (!passwordInput.matches(':focus')) {
                requirements.style.display = 'none';
            }
        });
        
        // Track mouse events for password match indicator
        matchIndicator.addEventListener('mouseenter', function() {
            confirmPasswordFocused = true;
        });
        
        matchIndicator.addEventListener('mouseleave', function() {
            confirmPasswordFocused = false;
            if (!confirmPasswordInput.matches(':focus')) {
                matchIndicator.style.display = 'none';
            }
        });
        
        // Real-time password validation
        function validatePassword() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            // Check each requirement
            const hasLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*()\-_=+{};:,<.>]/.test(password);
            
            // Update requirement indicators
            document.getElementById('reqLength').className = hasLength ? 'valid' : 'invalid';
            document.getElementById('reqUppercase').className = hasUppercase ? 'valid' : 'invalid';
            document.getElementById('reqLowercase').className = hasLowercase ? 'valid' : 'invalid';
            document.getElementById('reqNumber').className = hasNumber ? 'valid' : 'invalid';
            document.getElementById('reqSpecial').className = hasSpecial ? 'valid' : 'invalid';
            
            // Check if passwords match
            const doPasswordsMatch = password === confirmPassword && password !== '';
            
            // Update match indicator
            if (confirmPassword === '') {
                matchText.textContent = 'Confirm your password';
                matchText.className = 'neutral';
            } else if (doPasswordsMatch) {
                matchText.textContent = 'Passwords match';
                matchText.className = 'match';
            } else {
                matchText.textContent = 'Passwords do not match';
                matchText.className = 'no-match';
            }
            
            // Update validation flags
            passwordValid = hasLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;
            passwordsMatch = doPasswordsMatch;
            
            // Enable/disable submit button based on all requirements
            submitBtn.disabled = !(passwordValid && passwordsMatch);
        }
        
        // Add event listeners for both password fields
        passwordInput.addEventListener('input', validatePassword);
        confirmPasswordInput.addEventListener('input', validatePassword);
        
        // Form submission validation
        document.getElementById('lectureForm').addEventListener('submit', function(e) {
            if (!(passwordValid && passwordsMatch)) {
                e.preventDefault();
                alert('Please meet all password requirements and ensure passwords match before submitting.');
                if (!passwordValid) {
                    passwordInput.focus();
                } else {
                    confirmPasswordInput.focus();
                }
            }
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
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const itemName = this.closest('tr').querySelector('td:first-child').textContent;
                
                if (confirm(`Are you sure you want to delete lecture: "${itemName}"? This action cannot be undone.`)) {
                    console.log(`Delete ${name} with ID: ${id}`);
                    // Add your delete logic here
                }
            });
        });
    });
    </script>
</body>
</html>