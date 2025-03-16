<?php
include '../Includes/dbcon.php';
session_start();

if (!isset($_SESSION['userId'])) {
    echo "<script>alert('Unauthorized Access! Please login.'); window.location.href='../index.php';</script>";
    exit();
}

$userId = $_SESSION['userId'];

// Fetch student's registration number correctly
$query = "SELECT registrationNumber, firstName, lastName FROM tblstudents WHERE Id = '$userId'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $student = mysqli_fetch_assoc($result);
    $registrationNumber = $student['registrationNumber'];  // Ensure this matches tblattendance column
    $studentName = $student['firstName'] . ' ' . $student['lastName'];
} else {
    echo "<script>alert('Student record not found!'); window.location.href='../index.php';</script>";
    exit();
}

// Fetch attendance data
$attendanceQuery = "SELECT course, dateMarked, attendanceStatus, mobileUsage, sleepingTime FROM tblattendance WHERE studentRegistrationNumber = '$registrationNumber' ORDER BY dateMarked ASC";
$attendanceResult = mysqli_query($conn, $attendanceQuery);

// Get distinct attendance dates for table headers
$dateQuery = "SELECT DISTINCT dateMarked FROM tblattendance WHERE studentRegistrationNumber = '$registrationNumber' ORDER BY dateMarked ASC";
$dateResult = mysqli_query($conn, $dateQuery);

$dates = [];
if ($dateResult) {
    while ($row = mysqli_fetch_assoc($dateResult)) {
        $dates[] = $row['dateMarked'];
    }
}

// Calculate attendance stats
$totalAttendance = 30; // Assuming total attendance is 30 days
$present = 0;
$absent = 0;
$upcoming = $totalAttendance - count($dates); // Upcoming attendance

if ($attendanceResult) {
    while ($row = mysqli_fetch_assoc($attendanceResult)) {
        if ($row['attendanceStatus'] === 'Present') {
            $present++;
        } else {
            $absent++;
        }
    }
}

// Calculate mobile usage and sleeping time stats
$totalMobileUsage = 0;
$totalSleepingTime = 0;

$attendanceResult = mysqli_query($conn, $attendanceQuery); // Re-fetch data
if ($attendanceResult) {
    while ($row = mysqli_fetch_assoc($attendanceResult)) {
        $totalMobileUsage += (int)$row['mobileUsage'];
        $totalSleepingTime += (int)$row['sleepingTime'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Student Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Styling for better graph visibility */
        .graph-container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .graph-container h3 {
            margin-bottom: 15px;
            font-size: 20px;
            color: #333;
        }
        .graph-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        canvas {
            width: 100% !important;
            height: 300px !important;
        }
        @media (max-width: 768px) {
            .graph-container {
                width: 100%;
            }
        }
        .main--content {
            padding: 20px;
        }
        .table-container {
            width: 100%;
            margin-top: 20px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <?php include 'includes/topbar.php'; ?>
    <section class="main">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main--content">
            <h2 style="text-align: center;">Attendance Record for <?php echo $studentName; ?></h2>
            <div class="graph-row">
                <div class="graph-container">
                    <h3>Attendance Overview</h3>
                    <canvas id="attendanceChart"></canvas>
                </div>
                <div class="graph-container">
                    <h3>Mobile Usage & Sleeping Time</h3>
                    <canvas id="usageChart"></canvas>
                </div>
            </div>
            
            <!-- Attendance Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Course</th>
                            <?php foreach ($dates as $date) {
                                echo "<th>" . $date . "</th>";
                            } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $attendanceData = [];
                        $attendanceResult = mysqli_query($conn, $attendanceQuery); // Re-fetch data
                        if ($attendanceResult) {
                            while ($row = mysqli_fetch_assoc($attendanceResult)) {
                                $attendanceData[$row['course']][$row['dateMarked']] = $row['attendanceStatus'];
                            }
                        }

                        foreach ($attendanceData as $course => $attendance) {
                            echo "<tr>";
                            echo "<td>$course</td>";
                            foreach ($dates as $date) {
                                echo "<td>" . ($attendance[$date] ?? 'Absent') . "</td>";
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script>
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        };
        
        const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(attendanceCtx, {
            type: 'bar',
            data: {
                labels: ['Total Attendance', 'Present', 'Absent', 'Upcoming'],
                datasets: [{
                    label: 'Attendance Overview',
                    data: [<?php echo $totalAttendance; ?>, <?php echo $present; ?>, <?php echo $absent; ?>, <?php echo $upcoming; ?>],
                    backgroundColor: ['rgba(54, 162, 235, 0.5)', 'rgba(75, 192, 192, 0.5)', 'rgba(255, 99, 132, 0.5)', 'rgba(153, 102, 255, 0.5)'],
                    borderColor: ['rgba(54, 162, 235, 1)', 'rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)', 'rgba(153, 102, 255, 1)'],
                    borderWidth: 1
                }]
            },
            options: chartOptions
        });
        
        const usageCtx = document.getElementById('usageChart').getContext('2d');
        new Chart(usageCtx, {
            type: 'pie',
            data: {
                labels: ['Mobile Usage', 'Sleeping Time', 'Active Time'],
                datasets: [{
                    data: [<?php echo $totalMobileUsage; ?>, <?php echo $totalSleepingTime; ?>, <?php echo 30 - ($totalMobileUsage + $totalSleepingTime); ?>],
                    backgroundColor: ['rgba(255, 99, 132, 0.5)', 'rgba(54, 162, 235, 0.5)', 'rgba(75, 192, 192, 0.5)'],
                    borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(75, 192, 192, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } }
            }
        });
    </script>
</body>
</html>