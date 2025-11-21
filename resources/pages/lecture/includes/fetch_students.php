<?php
// includes/fetch_students.php

// FIXED: Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Function to format time for display (12-hour format with AM/PM)
function formatTimeForDisplay($time) {
    if (!$time || $time == '00:00:00' || $time == '0000-00-00 00:00:00') {
        return '--:--';
    }
    
    return date("g:i A", strtotime($time));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course = $_POST['course'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $venue = $_POST['venue'] ?? '';

    if ($course && $unit && $venue) {
        try {
            $query = $pdo->prepare("
                SELECT s.registrationNumber, s.firstName, s.lastName, s.email, s.phoneNumber 
                FROM tblstudents s 
                WHERE s.courseCode = :course 
                AND s.className = :venue
                ORDER BY s.firstName, s.lastName
            ");
            $query->execute([
                ':course' => $course,
                ':venue' => $venue
            ]);
            $students = $query->fetchAll(PDO::FETCH_ASSOC);

            if ($students) {
                echo '<div class="table">';
                echo '<table>';
                echo '<thead>';
                echo '<tr>';
                echo '<th>Registration No</th>';
                echo '<th>Name</th>';
                echo '<th>Course</th>';
                echo '<th>Unit</th>';
                echo '<th>Venue</th>';
                echo '<th style="text-align:center;">Attendance</th>';
                echo '<th style="text-align:right;">Time & Date</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                foreach ($students as $student) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($student['registrationNumber']) . '</td>';
                    echo '<td>' . htmlspecialchars($student['firstName'] . ' ' . $student['lastName']) . '</td>';
                    echo '<td>' . htmlspecialchars($course) . '</td>';
                    echo '<td>' . htmlspecialchars($unit) . '</td>';
                    echo '<td>' . htmlspecialchars($venue) . '</td>';

                    // ATTENDANCE COLUMN - ALWAYS show "Not Marked" (JavaScript will update from database)
                    echo '<td style="text-align:center; vertical-align:middle;">';
                    echo '<div style="text-align: center; padding: 8px 4px; vertical-align: middle;">';
                    echo '<span class="not-marked">Not Marked</span>';
                    echo '</div>';
                    echo '</td>';

                    // TIME & DATE COLUMN - ALWAYS show "Not Marked" (JavaScript will update from database)
                    echo '<td style="text-align:right; vertical-align:middle;">';
                    echo '<div style="text-align: right; padding: 8px 4px; vertical-align: middle;">';
                    echo '<div class="not-marked">Not Marked</div>';
                    echo '</div>';
                    echo '</td>';

                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
                echo '</div>';
            } else {
                echo '<div style="text-align: center; padding: 3rem; color: var(--light-slate);">';
                echo '<i class="ri-user-search-line" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.5;"></i>';
                echo '<p>No students found for the selected course and venue.</p>';
                echo '</div>';
            }
        } catch (PDOException $e) {
            echo '<div style="text-align: center; padding: 2rem; color: #e11d48;">';
            echo '<i class="ri-error-warning-line" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>';
            echo '<p>Error fetching students: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '</div>';
        }
    } else {
        echo '<div style="text-align: center; padding: 3rem; color: var(--light-slate);">';
        echo '<i class="ri-alert-line" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.5;"></i>';
        echo '<p>Please select course, unit, and venue.</p>';
        echo '</div>';
    }
} else {
    echo '<div style="text-align: center; padding: 2rem; color: #e11d48;">';
    echo '<p>Invalid request method.</p>';
    echo '</div>';
}
?>