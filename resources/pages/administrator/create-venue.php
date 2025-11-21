<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST["addVenue"])) {
    // Sanitize and validate inputs
    $className = htmlspecialchars(trim($_POST['className']));
    $facultyCode = htmlspecialchars(trim($_POST['faculty']));
    $currentStatus = htmlspecialchars(trim($_POST['currentStatus']));
    $capacity = filter_var($_POST['capacity'], FILTER_VALIDATE_INT);
    $classification = htmlspecialchars(trim($_POST['classification']));

    // Check for required fields
    if (empty($className) || empty($facultyCode) || empty($currentStatus) || $capacity === false || empty($classification)) {
        $_SESSION['message'] = "All fields are required and must be valid.";
        $_SESSION['message_type'] = "error";
    } elseif ($capacity < 1) {
        $_SESSION['message'] = "Please enter a valid capacity (minimum 1).";
        $_SESSION['message_type'] = "error";
    } else {
        $dateRegistered = date("Y-m-d");

        // Prepare database operations using PDO
        try {
            // Check if venue already exists
            $stmt = $pdo->prepare("SELECT * FROM tblvenue WHERE className = :className");
            $stmt->bindParam(':className', $className);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = "Venue '$className' Already Exists";
                $_SESSION['message_type'] = "error";
            } else {
                // Insert the new venue
                $stmt = $pdo->prepare(
                    "INSERT INTO tblvenue (className, facultyCode, currentStatus, capacity, classification, dateCreated)
                    VALUES (:className, :facultyCode, :currentStatus, :capacity, :classification, :dateCreated)"
                );
                $stmt->bindParam(':className', $className);
                $stmt->bindParam(':facultyCode', $facultyCode);
                $stmt->bindParam(':currentStatus', $currentStatus);
                $stmt->bindParam(':capacity', $capacity, PDO::PARAM_INT);
                $stmt->bindParam(':classification', $classification);
                $stmt->bindParam(':dateCreated', $dateRegistered);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "Venue '$className' added successfully!";
                    $_SESSION['message_type'] = "success";
                    
                    // Redirect to clear POST data
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $_SESSION['message'] = "Failed to insert venue. Please try again.";
                    $_SESSION['message_type'] = "error";
                }
            }
        } catch (PDOException $e) {
            $_SESSION['message'] = "Database Error: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
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
    <title>Venue Management</title>
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

        /* Rooms Section */
        .rooms {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(124, 58, 237, 0.1);
        }

        .rooms .title {
            display: flex;
            align-items: center;
            justify-content: space-between;
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
            width: 50px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            border-radius: 2px;
        }

        .rooms--right--btns {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .dropdown {
            padding: 0.75rem 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            color: var(--dark-slate);
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            outline: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .dropdown:focus {
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
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

        /* Rooms Cards */
        .rooms--cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .room--card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 1.5rem;
            text-decoration: none;
            color: var(--dark-slate);
            transition: all 0.3s ease;
            border: 1px solid rgba(124, 58, 237, 0.1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .room--card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
        }

        .room--card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-purple);
        }

        .img--box--cover {
            margin-bottom: 1rem;
        }

        .img--box {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            overflow: hidden;
            margin: 0 auto;
            border: 3px solid rgba(124, 58, 237, 0.2);
            transition: all 0.3s ease;
        }

        .room--card:hover .img--box {
            border-color: var(--primary-purple);
            transform: scale(1.05);
        }

        .img--box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.3s ease;
        }

        .room--card:hover .img--box img {
            transform: scale(1.1);
        }

        .room--card p {
            font-weight: 600;
            margin: 0;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .room--card .free {
            color: var(--accent-teal);
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

        .table-container .section--title {
            font-size: 1.5rem;
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
            text-transform: capitalize;
        }

        .status-available {
            background: rgba(16, 185, 129, 0.15);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-scheduled {
            background: rgba(245, 158, 11, 0.15);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.3);
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
        .formDiv-venue {
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

        .formDiv-venue form {
            padding: 2rem;
        }

        .formDiv-venue input, .formDiv-venue select {
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

        .formDiv-venue input:focus, .formDiv-venue select:focus {
            border-color: var(--primary-purple);
            outline: none;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
            transform: translateY(-2px);
        }

        /* ENHANCED SUBMIT BUTTON - LIKE YOUR COURSE MANAGEMENT */
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

            .rooms,
            .table-container {
                padding: 1.5rem;
            }

            .rooms--cards {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .table-container .title,
            .rooms .title {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .rooms--right--btns {
                width: 100%;
                justify-content: space-between;
            }

            .formDiv-venue {
                min-width: 95vw;
            }

            table {
                min-width: 600px;
            }
        }

        @media (max-width: 480px) {
            .dashboard-header h1 {
                font-size: 1.8rem;
            }

            .rooms--cards {
                grid-template-columns: 1fr 1fr;
            }

            .add {
                width: 100%;
                justify-content: center;
            }

            .submit {
                padding: 0.875rem 1.5rem;
                font-size: 0.95rem;
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
                <h1>Venue Management</h1>
                <p>Manage lecture rooms, classrooms, and other venues</p>
            </div>

            <!-- Rooms Section -->
            <div class="rooms">
                <div class="title">
                    <h2 class="section--title">Available Rooms</h2>
                    <div class="rooms--right--btns">
                        <select name="date" id="date" class="dropdown room--filter">
                            <option>Filter</option>
                            <option value="free">Free</option>
                            <option value="scheduled">Scheduled</option>
                        </select>
                        <button id="addClass1" class="add show-form"><i class="ri-add-line"></i>Add Venue</button>
                    </div>
                </div>
                <div class="rooms--cards">
                    <a href="#" class="room--card">
                        <div class="img--box--cover">
                            <div class="img--box">
                                <img src="resources/images/office image.jpeg" alt="Office">
                            </div>
                        </div>
                        <p class="free">Office</p>
                    </a>
                    <a href="#" class="room--card">
                        <div class="img--box--cover">
                            <div class="img--box">
                                <img src="resources/images/class.jpeg" alt="Classroom">
                            </div>
                        </div>
                        <p class="free">Classroom</p>
                    </a>
                    <a href="#" class="room--card">
                        <div class="img--box--cover">
                            <div class="img--box">
                                <img src="resources/images/lecture hall.jpeg" alt="Lecture Hall">
                            </div>
                        </div>
                        <p class="free">Lecture Hall</p>
                    </a>
                    <a href="#" class="room--card">
                        <div class="img--box--cover">
                            <div class="img--box">
                                <img src="resources/images/computer lab.jpeg" alt="Computer Lab">
                            </div>
                        </div>
                        <p class="free">Computer Lab</p>
                    </a>
                    <a href="#" class="room--card">
                        <div class="img--box--cover">
                            <div class="img--box">
                                <img src="resources/images/laboratory.jpeg" alt="Science Lab">
                            </div>
                        </div>
                        <p class="free">Science Lab</p>
                    </a>
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

            <!-- Venues Table -->
            <div class="table-container">
                <div class="title" id="addClass2">
                    <h2 class="section--title">Lecture Rooms</h2>
                    <button class="add show-form"><i class="ri-add-line"></i>Add Venue</button>
                </div>

                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Class Name</th>
                                <th>Faculty</th>
                                <th>Current Status</th>
                                <th>Capacity</th>
                                <th>Classification</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $sql = "SELECT * FROM tblvenue ORDER BY dateCreated DESC";
                                $stmt = $pdo->query($sql);
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if ($result && count($result) > 0) {
                                    foreach ($result as $row) {
                                        $statusClass = ($row["currentStatus"] == 'available') ? 'status-available' : 'status-scheduled';
                                        echo "<tr id='rowvenue{$row["Id"]}'>";
                                        echo "<td><strong>" . htmlspecialchars($row["className"]) . "</strong></td>";
                                        echo "<td>" . htmlspecialchars($row["facultyCode"]) . "</td>";
                                        echo "<td><span class='status-badge {$statusClass}'>" . htmlspecialchars($row["currentStatus"]) . "</span></td>";
                                        echo "<td>" . htmlspecialchars($row["capacity"]) . " seats</td>";
                                        echo "<td>" . htmlspecialchars($row["classification"]) . "</td>";
                                        echo "<td><i class='ri-delete-bin-line delete' data-id='{$row["Id"]}' data-name='venue' title='Delete Venue'></i></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' style='text-align: center; color: #6c757d; padding: 2rem;'>No venues found. Add your first venue above.</td></tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='6' style='text-align: center; color: #e11d48; padding: 2rem;'>Error loading venues: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Venue Form -->
            <div class="formDiv-venue" id="addClassForm" style="display:none">
                <form method="POST" action="" name="addVenue">
                    <div style="display:flex; justify-content:space-between; align-items:center; padding: 0 2rem;">
                        <div class="form-title" style="flex: 1;">
                            <p>Add New Venue</p>
                        </div>
                        <div>
                            <span class="close">&times;</span>
                        </div>
                    </div>
                    <div style="padding: 2rem;">
                        <input type="text" name="className" placeholder="Class Name (e.g., Room 101)" required>
                        
                        <select name="currentStatus" required>
                            <option value="" selected disabled>-- Current Status --</option>
                            <option value="available">Available</option>
                            <option value="scheduled">Scheduled</option>
                        </select>
                        
                        <input type="number" name="capacity" placeholder="Capacity (e.g., 50)" min="1" required>
                        
                        <select required name="classification">
                            <option value="" selected disabled>-- Select Venue Type --</option>
                            <option value="laboratory">Laboratory</option>
                            <option value="computerLab">Computer Lab</option>
                            <option value="lectureHall">Lecture Hall</option>
                            <option value="class">Classroom</option>
                            <option value="office">Office</option>
                        </select>
                        
                        <select required name="faculty">
                            <option value="" selected disabled>Select Faculty</option>
                            <?php
                            $facultyNames = getFacultyNames();
                            foreach ($facultyNames as $faculty) {
                                echo '<option value="' . htmlspecialchars($faculty["facultyCode"]) . '">' . htmlspecialchars($faculty["facultyName"]) . '</option>';
                            }
                            ?>
                        </select>
                        
                        <!-- ENHANCED SUBMIT BUTTON WITH CLEAR "SAVE VENUE" TEXT -->
                        <button type="submit" class="submit" name="addVenue">
                            <i class="ri-check-line"></i>
                            SAVE VENUE
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <?php js_asset(["active_link", "delete_request"]) ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const showForms = document.querySelectorAll(".show-form");
            const addClassForm = document.getElementById('addClassForm');
            const overlay = document.getElementById('overlay');
            const closeButtons = document.querySelectorAll('.close');

            console.log('Script loaded - forms:', showForms.length);

            // Show form function
            function showForm() {
                console.log('Showing form');
                addClassForm.style.display = 'block';
                overlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }

            // Hide form function
            function hideForm() {
                console.log('Hiding form');
                addClassForm.style.display = 'none';
                overlay.style.display = 'none';
                document.body.style.overflow = 'auto';
            }

            // Add event listeners to show form buttons
            showForms.forEach((button) => {
                button.addEventListener('click', showForm);
                console.log('Added click listener to button:', button);
            });

            // Add event listeners to close buttons
            closeButtons.forEach(function(closeButton) {
                closeButton.addEventListener('click', hideForm);
            });

            // Close form when clicking on overlay
            overlay.addEventListener('click', hideForm);

            // Prevent form from closing when clicking inside it
            addClassForm.addEventListener('click', function(e) {
                e.stopPropagation();
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

            // Form validation
            const form = document.querySelector('form[name="addVenue"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const className = this.querySelector('input[name="className"]').value;
                    const capacity = this.querySelector('input[name="capacity"]').value;
                    
                    if (!className.trim()) {
                        e.preventDefault();
                        alert('Please enter a venue name');
                        return false;
                    }
                    
                    if (!capacity || capacity < 1) {
                        e.preventDefault();
                        alert('Please enter a valid capacity (minimum 1)');
                        return false;
                    }
                    
                    console.log('Form submitted successfully');
                });
            }

            // Delete confirmation
            const deleteButtons = document.querySelectorAll('.delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const itemName = this.closest('tr').querySelector('td:first-child').textContent;
                    
                    if (confirm(`Are you sure you want to delete venue: "${itemName}"? This action cannot be undone.`)) {
                        console.log(`Delete ${name} with ID: ${id}`);
                        // Add your delete logic here
                    }
                });
            });
        });
    </script>
</body>
</html>