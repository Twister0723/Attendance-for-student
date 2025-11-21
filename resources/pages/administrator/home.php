<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="resources/images/logo/attnlg.png" rel="icon">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="resources/assets/css/styles.css">
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
        }

        /* Enhanced Main Content */
        .main--content {
            padding: 2rem;
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
            position: relative;
        }

        .dashboard-header p {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 0;
            font-weight: 500;
            position: relative;
        }

        /* Overview Section */
        .overview {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(124, 58, 237, 0.1);
        }

        .overview .title {
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

        /* Enhanced Cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .card {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(124, 58, 237, 0.1);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .card:hover::before {
            opacity: 1;
        }

        .card-1 {
            border-left: 4px solid var(--primary-purple);
        }

        .card-2 {
            border-left: 4px solid var(--accent-teal);
        }

        .card-3 {
            border-left: 4px solid var(--accent-amber);
        }

        .card-4 {
            border-left: 4px solid var(--accent-rose);
        }

        .card--data {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card--content h5 {
            font-size: 0.95rem;
            color: var(--light-slate);
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .card--content h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark-slate);
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .card--stats {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--accent-teal);
            font-weight: 600;
        }

        .card--icon--lg {
            font-size: 3rem;
            opacity: 0.8;
            transition: all 0.3s ease;
        }

        .card:hover .card--icon--lg {
            transform: scale(1.1);
            opacity: 1;
        }

        .card-1 .card--icon--lg {
            color: var(--primary-purple);
        }

        .card-2 .card--icon--lg {
            color: var(--accent-teal);
        }

        .card-3 .card--icon--lg {
            color: var(--accent-amber);
        }

        .card-4 .card--icon--lg {
            color: var(--accent-rose);
        }

        /* Enhanced Table Containers */
        .table-container {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(124, 58, 237, 0.1);
            transition: all 0.3s ease;
        }

        .table-container:hover {
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
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
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
            position: relative;
            overflow: hidden;
        }

        .add::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .add:hover::before {
            left: 100%;
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
        }

        /* Delete Button Styling */
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

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .status-inactive {
            background: rgba(107, 114, 128, 0.1);
            color: #6b7280;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .main--content {
                padding: 1rem;
            }

            .dashboard-header {
                padding: 2rem 1.5rem;
            }

            .dashboard-header h1 {
                font-size: 2rem;
            }

            .dashboard-header p {
                font-size: 1.1rem;
            }

            .overview,
            .table-container {
                padding: 1.5rem;
            }

            .cards {
                grid-template-columns: 1fr;
            }

            .table-container .title {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .section--title {
                font-size: 1.4rem;
            }

            th, td {
                padding: 1rem 0.75rem;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 480px) {
            .dashboard-header h1 {
                font-size: 1.8rem;
            }

            .card--content h1 {
                font-size: 2rem;
            }

            .card--icon--lg {
                font-size: 2.5rem;
            }

            .add {
                width: 100%;
                justify-content: center;
            }
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
                <h1>Admin Dashboard</h1>
                <p>Welcome to your school management system</p>
            </div>

            <!-- Overview Section -->
            <div class="overview">
                <div class="title">
                    <h2 class="section--title">Overview</h2>
                    <select name="date" id="date" class="dropdown">
                        <option value="today">Today</option>
                        <option value="lastweek">Last Week</option>
                        <option value="lastmonth">Last Month</option>
                        <option value="lastyear">Last Year</option>
                        <option value="alltime">All Time</option>
                    </select>
                </div>
                <div class="cards">
                    <div class="card card-1">
                        <div class="card--data">
                            <div class="card--content">
                                <h5 class="card--title">Registered Students</h5>
                                <h1><?php total_rows('tblstudents') ?></h1>
                                <div class="card--stats">
                                    <i class="ri-arrow-up-line"></i>
                                    <span>Active</span>
                                </div>
                            </div>
                            <i class="ri-user-2-line card--icon--lg"></i>
                        </div>
                    </div>
                    <div class="card card-2">
                        <div class="card--data">
                            <div class="card--content">
                                <h5 class="card--title">Units</h5>
                                <h1><?php total_rows("tblunit") ?></h1>
                                <div class="card--stats">
                                    <i class="ri-book-line"></i>
                                    <span>Available</span>
                                </div>
                            </div>
                            <i class="ri-file-text-line card--icon--lg"></i>
                        </div>
                    </div>
                    <div class="card card-3">
                        <div class="card--data">
                            <div class="card--content">
                                <h5 class="card--title">Registered Lectures</h5>
                                <h1><?php total_rows('tbllecture') ?></h1>
                                <div class="card--stats">
                                    <i class="ri-user-line"></i>
                                    <span>Active</span>
                                </div>
                            </div>
                            <i class="ri-user-line card--icon--lg"></i>
                        </div>
                    </div>
                    <div class="card card-4">
                        <div class="card--data">
                            <div class="card--content">
                                <h5 class="card--title">Lecture Rooms</h5>
                                <h1><?php total_rows('tblvenue') ?></h1>
                                <div class="card--stats">
                                    <i class="ri-building-line"></i>
                                    <span>Available</span>
                                </div>
                            </div>
                            <i class="ri-building-line card--icon--lg"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lectures Table -->
            <div class="table-container">
                <div class="title">
                    <h2 class="section--title">Lectures</h2>
                    <a href="manage-lecture" class="add">
                        <i class="ri-add-line"></i>Add Lecture
                    </a>
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
                                <th>Settings</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT l.*, f.facultyName
                                    FROM tbllecture l
                                    LEFT JOIN tblfaculty f ON l.facultyCode = f.facultyCode
                                    ORDER BY l.dateCreated DESC";

                            $stmt = $pdo->query($sql);
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if ($result) {
                                foreach ($result as $row) {
                                    echo "<tr id='rowlecture{$row["Id"]}'>";
                                    echo "<td><strong>" . htmlspecialchars($row["firstName"]) . "</strong></td>";
                                    echo "<td>" . htmlspecialchars($row["emailAddress"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["phoneNo"]) . "</td>";
                                    echo "<td><span class='status-badge status-active'>" . htmlspecialchars($row["facultyName"]) . "</span></td>";
                                    echo "<td>" . htmlspecialchars($row["dateCreated"]) . "</td>";
                                    echo "<td><span><i class='ri-delete-bin-line delete' data-id='{$row["Id"]}' data-name='lecture' title='Delete Lecture'></i></span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'><div class='empty-state'><i class='ri-user-line'></i><p>No lectures found</p></div></td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Students Table -->
            <div class="table-container">
                <div class="title">
                    <h2 class="section--title">Students</h2>
                    <a href="manage-students" class="add">
                        <i class="ri-add-line"></i>Add Student
                    </a>
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
                                <th>Settings</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // FIXED: Removed ORDER BY dateCreated since column doesn't exist
                            $sql = "SELECT * FROM tblstudents";
                            $stmt = $pdo->query($sql);
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if ($result) {
                                foreach ($result as $row) {
                                    echo "<tr id='rowstudents{$row["Id"]}'>";
                                    echo "<td><span class='status-badge status-active'>" . htmlspecialchars($row["registrationNumber"]) . "</span></td>";
                                    echo "<td><strong>" . htmlspecialchars($row["firstName"]) . " " . htmlspecialchars($row["lastName"] ?? '') . "</strong></td>";
                                    echo "<td>" . htmlspecialchars($row["faculty"]) . "</td>";
                                    echo "<td><span class='status-badge status-inactive'>" . htmlspecialchars($row["courseCode"]) . "</span></td>";
                                    echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                    echo "<td><span><i class='ri-delete-bin-line delete' data-id='{$row["Id"]}' data-name='students' title='Delete Student'></i></span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'><div class='empty-state'><i class='ri-user-line'></i><p>No students found</p></div></td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Lecture Rooms Table -->
            <div class="table-container">
                <div class="title">
                    <h2 class="section--title">Lecture Rooms</h2>
                    <a href="create-venue" class="add">
                        <i class="ri-add-line"></i>Add Room
                    </a>
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
                                <th>Settings</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // FIXED: Removed ORDER BY since we don't know the date column
                            $sql = "SELECT * FROM tblvenue";
                            $stmt = $pdo->query($sql);
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if ($result) {
                                foreach ($result as $row) {
                                    $statusClass = ($row["currentStatus"] == 'Available') ? 'status-active' : 'status-inactive';
                                    echo "<tr id='rowvenue{$row["Id"]}'>";
                                    echo "<td><strong>" . htmlspecialchars($row["className"]) . "</strong></td>";
                                    echo "<td>" . htmlspecialchars($row["facultyCode"]) . "</td>";
                                    echo "<td><span class='status-badge $statusClass'>" . htmlspecialchars($row["currentStatus"]) . "</span></td>";
                                    echo "<td>" . htmlspecialchars($row["capacity"]) . " seats</td>";
                                    echo "<td><span class='status-badge status-inactive'>" . htmlspecialchars($row["classification"]) . "</span></td>";
                                    echo "<td><span><i class='ri-delete-bin-line delete' data-id='{$row["Id"]}' data-name='venue' title='Delete Room'></i></span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'><div class='empty-state'><i class='ri-building-line'></i><p>No lecture rooms found</p></div></td></tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Courses Table -->
            <div class="table-container">
                <div class="title">
                    <h2 class="section--title">Courses</h2>
                    <a href="manage-course" class="add">
                        <i class="ri-add-line"></i>Add Course
                    </a>
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
                                    c.name AS course_name,c.Id AS Id,
                                    c.facultyID AS faculty,
                                    f.facultyName AS faculty_name,
                                    COUNT(u.ID) AS total_units,
                                    COUNT(DISTINCT s.Id) AS total_students,
                                    c.dateCreated AS date_created
                                    FROM tblcourse c
                                    LEFT JOIN tblunit u ON c.ID = u.courseID
                                    LEFT JOIN tblstudents s ON c.courseCode = s.courseCode
                                    LEFT JOIN tblfaculty f on c.facultyID=f.Id
                                    GROUP BY c.ID
                                    ORDER BY c.dateCreated DESC";
                            $stmt = $pdo->query($sql);
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if ($result) {
                                foreach ($result as $row) {
                                    echo "<tr id='rowcourse{$row["Id"]}'>";
                                    echo "<td><strong>" . htmlspecialchars($row["course_name"]) . "</strong></td>";
                                    echo "<td><span class='status-badge status-active'>" . htmlspecialchars($row["faculty_name"]) . "</span></td>";
                                    echo "<td><span class='status-badge status-inactive'>" . $row["total_units"] . " units</span></td>";
                                    echo "<td><span class='status-badge status-inactive'>" . $row["total_students"] . " students</span></td>";
                                    echo "<td>" . htmlspecialchars($row["date_created"]) . "</td>";
                                    echo "<td><span><i class='ri-delete-bin-line delete' data-id='{$row["Id"]}' data-name='course' title='Delete Course'></i></span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'><div class='empty-state'><i class='ri-book-line'></i><p>No courses found</p></div></td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <?php js_asset(["active_link", "delete_request"]) ?>
</body>

</html>