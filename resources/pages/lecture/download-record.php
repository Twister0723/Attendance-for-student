<?php
// ADD DATABASE CONNECTION AT THE TOP
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

$courseCode = isset($_GET['course']) ? $_GET['course'] : '';
$unitCode = isset($_GET['unit']) ? $_GET['unit'] : '';

$coursename = "";
if (!empty($courseCode)) {
    $coursename_query = "SELECT name FROM tblcourse WHERE courseCode = '$courseCode'";
    $result = fetch($coursename_query);
    if ($result) {
        foreach ($result as $row) {
            $coursename = $row['name'];
        }
    }
}
$unitname = "";
if (!empty($unitCode)) {
    $unitname_query = "SELECT name FROM tblunit WHERE unitCode = '$unitCode'";
    $result = fetch($unitname_query);
    if ($result) {
        foreach ($result as $row)
            $unitname = $row['name'];
    }
}

// NEW FUNCTION: Fetch student records with names
function fetchStudentRecordsWithNames($courseCode, $unitCode) {
    global $pdo;
    
    // Try multiple approaches to get student names
    try {
        // First attempt: Get students from attendance records joined with student names
        $query = "SELECT DISTINCT
                    a.studentRegistrationNumber,
                    COALESCE(CONCAT(s.firstName, ' ', s.lastName), a.studentRegistrationNumber) as studentName
                  FROM tblattendance a
                  LEFT JOIN tblstudents s ON a.studentRegistrationNumber = s.registrationNumber
                  WHERE a.course = :courseCode 
                  AND a.unit = :unitCode
                  ORDER BY a.studentRegistrationNumber";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':courseCode' => $courseCode,
            ':unitCode' => $unitCode
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("First query failed: " . $e->getMessage());
        
        // Fallback: Just get registration numbers from attendance records
        try {
            $query = "SELECT DISTINCT
                        studentRegistrationNumber,
                        studentRegistrationNumber as studentName
                      FROM tblattendance 
                      WHERE course = :courseCode 
                      AND unit = :unitCode
                      ORDER BY studentRegistrationNumber";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':courseCode' => $courseCode,
                ':unitCode' => $unitCode
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e2) {
            error_log("All queries failed: " . $e2->getMessage());
            return [];
        }
    }
}

// FIXED: Get accurate student count for the selected course and unit
$studentCount = 0;
$studentRows = [];

if (!empty($courseCode) && !empty($unitCode)) {
    // Count unique students for this specific course and unit
    $countQuery = "SELECT COUNT(DISTINCT studentRegistrationNumber) as total 
                   FROM tblattendance 
                   WHERE course = :courseCode AND unit = :unitCode";
    $stmtCount = $pdo->prepare($countQuery);
    $stmtCount->execute([
        ':courseCode' => $courseCode,
        ':unitCode' => $unitCode,
    ]);
    $countResult = $stmtCount->fetch(PDO::FETCH_ASSOC);
    $studentCount = $countResult['total'] ?? 0;
    
    // Get the actual student records for display WITH NAMES
    $studentRows = fetchStudentRecordsWithNames($courseCode, $unitCode);
}

// FIXED: Get distinct dates for the selected course and unit
$distinctDatesResult = [];
if (!empty($courseCode) && !empty($unitCode)) {
    $distinctDatesQuery = "SELECT DISTINCT dateMarked 
                           FROM tblattendance 
                           WHERE course = :courseCode AND unit = :unitCode 
                           ORDER BY dateMarked DESC";
    $stmtDates = $pdo->prepare($distinctDatesQuery);
    $stmtDates->execute([
        ':courseCode' => $courseCode,
        ':unitCode' => $unitCode,
    ]);
    $distinctDatesResult = $stmtDates->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="resources/images/logo/attnlg.png" rel="icon">
    <title>Download Attendance Records</title>
    <link rel="stylesheet" href="resources/assets/css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <!-- ADD EXCEL LIBRARIES -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
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
            display: <?php echo (!empty($courseCode) && !empty($unitCode)) ? 'block' : 'none'; ?>;
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
            justify-content: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1.25rem 2rem;
            border: none;
            border-radius: 15px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
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

        .btn-export {
            background: linear-gradient(135deg, var(--accent-teal) 0%, #0f766e 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(13, 148, 136, 0.3);
        }

        .btn-export:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(13, 148, 136, 0.4);
        }

        .btn-view {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }

        .btn-view:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.4);
        }

        .btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn:disabled:hover {
            transform: none;
            box-shadow: none;
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
        }

        /* Student Info Cells */
        .student-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .student-reg {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: var(--primary-purple);
            background: rgba(124, 58, 237, 0.1);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        .student-name {
            font-size: 0.85rem;
            color: var(--light-slate);
            font-weight: 500;
            margin-top: 4px;
        }

        /* Attendance Status Badges */
        .attendance-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.8rem;
            text-align: center;
            min-width: 70px;
        }

        .status-present {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .status-late {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .status-absent {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        /* Time display */
        .attendance-time {
            font-size: 0.75rem;
            color: #666;
            margin-top: 2px;
            font-weight: 600;
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
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
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
                padding: 1rem 1.5rem;
                font-size: 1rem;
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
    </style>
</head>

<body>
    <?php include 'includes/topbar.php'; ?>
    <section class="main">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main--content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Download Attendance Records</h1>
                <p>Export attendance data for courses and units</p>
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

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn btn-view" onclick="updateTable()">
                        <i class="ri-eye-line"></i>
                        View Attendance
                    </button>
                    <button class="btn btn-export" onclick="exportToExcel()" <?php echo (empty($courseCode) || empty($unitCode)) ? 'disabled' : ''; ?>>
                        <i class="ri-download-line"></i>
                        Export to Excel
                    </button>
                </div>
            </div>

            <!-- Selected Course Information -->
            <?php if (!empty($courseCode) && !empty($unitCode)): ?>
            <div class="selection-info" id="selectionInfo">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Selected Course</div>
                        <div class="info-value"><?php echo htmlspecialchars($coursename); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Selected Unit</div>
                        <div class="info-value"><?php echo htmlspecialchars($unitname); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Records Found</div>
                        <div class="info-value"><?php echo $studentCount; ?> Students</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Attendance Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2 class="table-title">Attendance Preview</h2>
                    <?php if (!empty($courseCode) && !empty($unitCode)): ?>
                    <div class="table-stats">
                        <span><?php echo $studentCount; ?> Students</span>
                        <span><?php echo count($distinctDatesResult); ?> Dates</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="table attendance-table" id="attendaceTable">
                    <?php if (!empty($courseCode) && !empty($unitCode) && $studentCount > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Student Information</th>
                                <?php
                                // Display each distinct date as a column header
                                if ($distinctDatesResult) {
                                    foreach ($distinctDatesResult as $dateRow) {
                                        echo "<th>" . $dateRow['dateMarked'] . "</th>";
                                    }
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Display each student's attendance row
                            foreach ($studentRows as $row) {
                                echo "<tr>";
                                // Display student registration number and name
                                echo "<td>";
                                echo "<div class='student-info'>";
                                echo "<div class='student-reg'>" . $row['studentRegistrationNumber'] . "</div>";
                                echo "<div class='student-name'>" . htmlspecialchars($row['studentName'] ?? 'N/A') . "</div>";
                                echo "</div>";
                                echo "</td>";

                                // Loop through each date and fetch the attendance status for the student
                                foreach ($distinctDatesResult as $dateRow) {
                                    $date = $dateRow['dateMarked'];

                                    // Fetch attendance for the current student and date WITH TIME
                                    $attendanceQuery = "SELECT attendanceStatus, timeMarked FROM tblattendance 
                                    WHERE studentRegistrationNumber = :studentRegistrationNumber 
                                    AND dateMarked = :date 
                                    AND course = :courseCode 
                                    AND unit = :unitCode";
                                    $stmtAttendance = $pdo->prepare($attendanceQuery);
                                    $stmtAttendance->execute([
                                        ':studentRegistrationNumber' => $row['studentRegistrationNumber'],
                                        ':date' => $date,
                                        ':courseCode' => $courseCode,
                                        ':unitCode' => $unitCode,
                                    ]);
                                    $attendanceResult = $stmtAttendance->fetch(PDO::FETCH_ASSOC);

                                    // Display attendance status with appropriate badge and TIME
                                    if ($attendanceResult) {
                                        $status = $attendanceResult['attendanceStatus'];
                                        $time = $attendanceResult['timeMarked'];
                                        $badgeClass = 'status-' . strtolower($status);
                                        echo "<td>
                                                <span class='attendance-badge $badgeClass'>" . $status . "</span>
                                                <div class='attendance-time'>" . date('g:i A', strtotime($time)) . "</div>
                                              </td>";
                                    } else {
                                        echo "<td>
                                                <span class='attendance-badge status-absent'>Absent</span>
                                                <div class='attendance-time'>--:--</div>
                                              </td>";
                                    }
                                }

                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php elseif (!empty($courseCode) && !empty($unitCode)): ?>
                    <div class="empty-state">
                        <i class="ri-file-search-line"></i>
                        <h3>No Attendance Records</h3>
                        <p>No attendance records found for the selected course and unit.</p>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="ri-file-search-line"></i>
                        <h3>No Data Selected</h3>
                        <p>Please select a course and unit, then click "View Attendance" to see the data</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </section>

    <script>
        function updateTable() {
            console.log("Loading attendance data...");
            const courseSelect = document.getElementById("courseSelect");
            const unitSelect = document.getElementById("unitSelect");

            const selectedCourse = courseSelect.value;
            const selectedUnit = unitSelect.value;

            if (selectedCourse && selectedUnit) {
                const url = "download-record?course=" + encodeURIComponent(selectedCourse) + "&unit=" + encodeURIComponent(selectedUnit);
                window.location.href = url;
                console.log("Redirecting to:", url);
            } else {
                alert("Please select both a course and a unit.");
            }
        }

        // UPDATED: Function to export to Excel with student names and date/time
        function exportToExcel() {
            const courseCode = document.getElementById('courseSelect').value;
            const unitCode = document.getElementById('unitSelect').value;
            const courseName = "<?php echo $coursename; ?>";
            const unitName = "<?php echo $unitname; ?>";
            
            if (!courseCode || !unitCode) {
                alert('Please select both course and unit first.');
                return;
            }

            // Create workbook
            const wb = XLSX.utils.book_new();
            
            // Prepare data for Excel
            const excelData = [];
            
            // Add header row with course info
            excelData.push(['Attendance Report']);
            excelData.push(['Course:', courseName]);
            excelData.push(['Unit:', unitName]);
            excelData.push(['Generated on:', new Date().toLocaleString()]);
            excelData.push([]); // Empty row
            
            // Get table data
            const table = document.getElementById('attendaceTable').getElementsByTagName('table')[0];
            const rows = table.getElementsByTagName('tr');
            
            // Process header row - include "Student Name" column
            const headerRow = ['Registration No', 'Student Name'];
            const headerCells = rows[0].getElementsByTagName('th');
            for (let i = 1; i < headerCells.length; i++) { // Start from 1 to skip "Student Information" header
                headerRow.push(headerCells[i].textContent || headerCells[i].innerText);
            }
            excelData.push(headerRow);
            
            // Process data rows
            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                const rowData = [];
                
                // Get registration number and student name from first cell
                const firstCell = cells[0];
                const regNumber = firstCell.querySelector('.student-reg').textContent;
                const studentName = firstCell.querySelector('.student-name').textContent;
                
                rowData.push(regNumber);
                rowData.push(studentName);
                
                // Process attendance data for each date
                for (let j = 1; j < cells.length; j++) {
                    const statusBadge = cells[j].querySelector('.attendance-badge');
                    const timeDiv = cells[j].querySelector('.attendance-time');
                    
                    if (statusBadge && timeDiv) {
                        const status = statusBadge.textContent || statusBadge.innerText;
                        const time = timeDiv.textContent || timeDiv.innerText;
                        rowData.push(`${status} (${time})`);
                    } else {
                        rowData.push('Absent (--:--)');
                    }
                }
                excelData.push(rowData);
            }
            
            // Add summary row
            excelData.push([]);
            excelData.push(['Summary Information']);
            excelData.push(['Total Students:', <?php echo $studentCount; ?>]);
            excelData.push(['Total Dates:', <?php echo count($distinctDatesResult); ?>]);
            excelData.push(['Report Period:', '<?php echo !empty($distinctDatesResult) ? $distinctDatesResult[0]['dateMarked'] : 'N/A'; ?> to <?php echo !empty($distinctDatesResult) ? end($distinctDatesResult)['dateMarked'] : 'N/A'; ?>']);
            
            // Create worksheet
            const ws = XLSX.utils.aoa_to_sheet(excelData);
            
            // Set column widths
            const colWidths = [
                { wch: 15 }, // Registration No
                { wch: 25 }, // Student Name
            ];
            // Add widths for date columns
            for (let i = 0; i < (headerRow.length - 2); i++) {
                colWidths.push({ wch: 20 });
            }
            ws['!cols'] = colWidths;
            
            // Add some basic styling to header rows
            if (!ws['!merges']) ws['!merges'] = [];
            ws['!merges'].push({ s: { r: 0, c: 0 }, e: { r: 0, c: headerRow.length - 1 } });
            
            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Attendance');
            
            // Generate filename
            const filename = `Attendance_${courseCode}_${unitName}_${new Date().toISOString().split('T')[0]}.xlsx`;
            
            // Export to Excel
            XLSX.writeFile(wb, filename);
            
            console.log('Excel file exported successfully:', filename);
        }

        // Update export button state based on selection
        document.addEventListener('DOMContentLoaded', function() {
            const courseSelect = document.getElementById('courseSelect');
            const unitSelect = document.getElementById('unitSelect');
            const exportBtn = document.querySelector('.btn-export');

            function updateExportButton() {
                if (courseSelect.value && unitSelect.value) {
                    exportBtn.disabled = false;
                } else {
                    exportBtn.disabled = true;
                }
            }

            courseSelect.addEventListener('change', updateExportButton);
            unitSelect.addEventListener('change', updateExportButton);
            updateExportButton(); // Initial check
        });
    </script>

</html>