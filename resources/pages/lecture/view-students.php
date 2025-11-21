<?php

$courseCode = isset($_GET['course']) ? $_GET['course'] : '';
$unitCode = isset($_GET['unit']) ? $_GET['unit'] : '';

$studentRows = fetchStudentRecordsFromDatabase($courseCode, $unitCode);

$coursename = "";
if (!empty($courseCode)) {
    $coursename_query = "SELECT name FROM tblcourse WHERE courseCode = '$courseCode'";
    $result = fetch($coursename_query);
    foreach ($result as $row) {
        $coursename = $row['name'];
    }
}
$unitname = "";
if (!empty($unitCode)) {
    $unitname_query = "SELECT name FROM tblunit WHERE unitCode = '$unitCode'";
    $result = fetch($unitname_query);
    foreach ($result as $row) {
        $unitname = $row['name'];
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
    <title>Student Management - View Students</title>
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
        }

        /* Enhanced Main Content */
        .main--content {
            padding: 2rem;
        }

        /* Header Section */
        .page-header {
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

        .page-header::before {
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

        .page-header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.95;
            margin-bottom: 0;
            font-weight: 500;
            position: relative;
        }

        /* Control Panel */
        .control-panel {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(124, 58, 237, 0.1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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

        /* Selected Course Info */
        .selection-info {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.3);
            display: <?php echo (!empty($courseCode)) ? 'block' : 'none'; ?>;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            text-align: center;
        }

        .info-item {
            padding: 1rem;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .info-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 700;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
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

        .btn-refresh {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }

        .btn-refresh:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
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

        .table-stats {
            display: flex;
            gap: 1rem;
            font-size: 0.95rem;
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

        /* Student Avatar */
        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            margin-right: 1rem;
        }

        .student-info {
            display: flex;
            align-items: center;
        }

        .student-name {
            font-weight: 600;
            color: var(--dark-slate);
        }

        .student-email {
            font-size: 0.85rem;
            color: var(--light-slate);
            margin-top: 2px;
        }

        /* Registration Number Styling */
        .reg-number {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: var(--primary-purple);
            background: rgba(124, 58, 237, 0.1);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--light-slate);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
            display: block;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--dark-slate);
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

            .page-header {
                padding: 2rem 1.5rem;
            }

            .page-header h1 {
                font-size: 1.8rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .table-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .control-panel,
            .table-container {
                padding: 1.5rem;
            }

            .action-buttons {
                justify-content: center;
            }

            .btn {
                width: 100%;
                max-width: 200px;
                justify-content: center;
            }

            .student-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .student-avatar {
                margin-right: 0;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.5rem;
            }

            .page-header p {
                font-size: 1rem;
            }

            .btn {
                padding: 0.875rem 1.25rem;
                font-size: 0.9rem;
            }

            th, td {
                padding: 1rem 0.75rem;
                font-size: 0.85rem;
            }

            .table-stats {
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        /* Search and Filter */
        .search-filter {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #ffffff;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--light-slate);
        }
    </style>
</head>

<body>
    <?php include 'includes/topbar.php'; ?>
    <section class="main">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main--content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Student Management</h1>
                <p>View and manage student information</p>
            </div>

            <!-- Control Panel -->
            <div class="control-panel">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="courseSelect"><i class="ri-book-line"></i> Select Course</label>
                        <select required name="course" id="courseSelect" class="form-control">
                            <option value="" selected>Choose a course...</option>
                            <?php
                            $courseNames = getCourseNames();
                            foreach ($courseNames as $course) {
                                $selected = ($course["courseCode"] == $courseCode) ? 'selected' : '';
                                echo '<option value="' . $course["courseCode"] . '" ' . $selected . '>' . $course["name"] . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="unitSelect"><i class="ri-time-line"></i> Select Unit</label>
                        <select required name="unit" id="unitSelect" class="form-control">
                            <option value="" selected>Choose a unit...</option>
                            <?php
                            $unitNames = getUnitNames();
                            foreach ($unitNames as $unit) {
                                $selected = ($unit["unitCode"] == $unitCode) ? 'selected' : '';
                                echo '<option value="' . $unit["unitCode"] . '" ' . $selected . '>' . $unit["name"] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons - Only Load Students button remains -->
                <div class="action-buttons">
                    <button class="btn btn-refresh" onclick="updateTable()">
                        <i class="ri-refresh-line"></i>
                        Load Students
                    </button>
                </div>
            </div>

            <!-- Selected Course Information -->
            <?php if (!empty($courseCode)): ?>
            <div class="selection-info" id="selectionInfo">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Selected Course</div>
                        <div class="info-value"><?php echo htmlspecialchars($coursename); ?></div>
                    </div>
                    <?php if (!empty($unitCode)): ?>
                    <div class="info-item">
                        <div class="info-label">Selected Unit</div>
                        <div class="info-value"><?php echo htmlspecialchars($unitname); ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <div class="info-label">Total Students</div>
                        <div class="info-value">
                            <?php 
                            $studentCount = 0;
                            if (!empty($courseCode)) {
                                $countQuery = "SELECT COUNT(*) as total FROM tblstudents WHERE courseCode = '$courseCode'";
                                $countResult = fetch($countQuery);
                                $studentCount = $countResult[0]['total'] ?? 0;
                            }
                            echo $studentCount . ' Students';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Students Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2 class="table-title">Students List</h2>
                    <?php if (!empty($courseCode)): ?>
                    <div class="table-stats">
                        <span><?php echo $studentCount; ?> Students</span>
                        <span><?php echo !empty($unitCode) ? '1 Unit' : 'All Units'; ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Search and Filter -->
                <?php if (!empty($courseCode)): ?>
                <div class="search-filter">
                    <div class="search-box">
                        <i class="ri-search-line search-icon"></i>
                        <input type="text" id="searchInput" class="search-input" placeholder="Search students by name, registration number, or email...">
                    </div>
                </div>
                <?php endif; ?>

                <div class="table attendance-table" id="attendaceTable">
                    <?php if (!empty($courseCode)): 
                        $query = "SELECT * FROM tblstudents WHERE courseCode = '$courseCode'";
                        $result = fetch($query);
                        if ($result && count($result) > 0): 
                    ?>
                    <table id="studentsTable">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Registration No</th>
                                <th>Email</th>
                                <th>Course</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($result as $row) {
                                $firstName = $row['firstName'];
                                $lastName = $row['lastName'];
                                $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                                
                                echo "<tr>";
                                echo "<td>";
                                echo "<div class='student-info'>";
                                echo "<div class='student-avatar'>" . $initials . "</div>";
                                echo "<div>";
                                echo "<div class='student-name'>" . htmlspecialchars($firstName . ' ' . $lastName) . "</div>";
                                echo "<div class='student-email'>" . htmlspecialchars($row['email']) . "</div>";
                                echo "</div>";
                                echo "</div>";
                                echo "</td>";
                                echo "<td><span class='reg-number'>" . htmlspecialchars($row['registrationNumber']) . "</span></td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td><strong>" . htmlspecialchars($coursename) . "</strong></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="ri-user-search-line"></i>
                        <h3>No Students Found</h3>
                        <p>No students are enrolled in the selected course.</p>
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="ri-book-open-line"></i>
                        <h3>Select a Course</h3>
                        <p>Please select a course to view the student list.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </section>

    <?php js_asset(["active_link"]) ?>
</body>

<script>
    function updateTable() {
        console.log("Loading students...");
        const courseSelect = document.getElementById("courseSelect");
        const unitSelect = document.getElementById("unitSelect");

        const selectedCourse = courseSelect.value;
        const selectedUnit = unitSelect.value;

        if (selectedCourse) {
            let url = "view-students?course=" + encodeURIComponent(selectedCourse);
            if (selectedUnit) {
                url += "&unit=" + encodeURIComponent(selectedUnit);
            }
            window.location.href = url;
            console.log("Redirecting to:", url);
        } else {
            alert("Please select a course first.");
        }
    }

    // Search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const table = document.getElementById('studentsTable');
                if (!table) return;

                const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                
                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        }
    });
</script>

</html>