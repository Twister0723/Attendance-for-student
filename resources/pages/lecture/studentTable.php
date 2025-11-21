<div class="table">
    <table>
        <thead>
            <tr>
                <th style="text-align: left;">Registration No</th>
                <th style="text-align: left;">Name</th>
                <th style="text-align: left;">Course</th>
                <th style="text-align: left;">Unit</th>
                <th style="text-align: left;">Venue</th>
                <th style="text-align: center;">Attendance</th>
                <th style="text-align: right;">Date</th> <!-- CHANGED: Time → Date -->
            </tr>
        </thead>
        <tbody id="studentTableContainer">
            <?php
            if (isset($_POST['courseID']) && isset($_POST['unitID']) && isset($_POST['venueID'])) {

                $courseID = $_POST['courseID'];
                $unitID = $_POST['unitID'];
                $venueID = $_POST['venueID'];

                $sql = "SELECT * FROM tblStudents WHERE courseCode = '$courseID'";
                $result = fetch($sql);

                if ($result) {
                    foreach ($result as $row) {
                        echo "<tr>";
                        $registrationNumber = $row["registrationNumber"];
                        echo "<td style='text-align: left;'>" . $registrationNumber . "</td>";
                        echo "<td style='text-align: left;'>" . $row["firstName"] . " " . $row["lastName"] . "</td>";
                        echo "<td style='text-align: left;'>" . $courseID . "</td>";
                        echo "<td style='text-align: left;'>" . $unitID . "</td>";
                        echo "<td style='text-align: left;'>" . $venueID . "</td>";
                        
                        // Check if attendance already exists in database
                        $attendanceCheck = "SELECT attendanceStatus, timeMarked, dateMarked FROM tblattendance 
                                          WHERE studentRegistrationNumber = '$registrationNumber' 
                                          AND course = '$courseID' 
                                          AND unit = '$unitID' 
                                          AND dateMarked = CURDATE() 
                                          ORDER BY timeMarked DESC LIMIT 1";
                        $attendanceResult = fetch($attendanceCheck);
                        
                        // ATTENDANCE COLUMN - Show Present/Absent (CENTER ALIGNED)
                        if ($attendanceResult && count($attendanceResult) > 0) {
                            $attendance = $attendanceResult[0];
                            $status = $attendance['attendanceStatus'];
                            $statusColor = $status === 'Late' ? 'orange' : 'green';
                            echo "<td style='text-align: center; color: $statusColor; font-weight: bold; padding: 8px 4px; vertical-align: middle;'>$status</td>";
                        } else {
                            echo "<td style='text-align: center; color: red; font-weight: bold; padding: 8px 4px; vertical-align: middle;'>Absent</td>";
                        }
                        
                        // DATE COLUMN - Show only date (RIGHT ALIGNED) - FIXED
                        if ($attendanceResult && count($attendanceResult) > 0) {
                            $attendance = $attendanceResult[0];
                            $dateMarked = date('n/j/y', strtotime($attendance['dateMarked'])); // Only show date
                            echo "<td style='text-align: right; padding: 8px 4px; vertical-align: middle;'>
                                    <div style='color: #333; font-size: 12px;'>$dateMarked</div>
                                  </td>";
                        } else {
                            echo "<td style='text-align: right; color: #666; padding: 10px; vertical-align: middle;'>-</td>";
                        }
                        
                        echo "</tr>";
                    }

                } else {
                    echo "<tr><td colspan='7' style='text-align: center;'>No records found</td></tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>