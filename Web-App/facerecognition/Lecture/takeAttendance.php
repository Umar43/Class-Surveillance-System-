<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Function to get course names
function getCourseNames($conn) {
    $sql = "SELECT courseCode, name FROM tblcourse";
    $result = $conn->query($sql);

    $courseNames = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $courseNames[] = $row;
        }
    }

    return $courseNames;
}

// Function to get venue names
function getVenueNames($conn) {
    $sql = "SELECT className FROM tblvenue";
    $result = $conn->query($sql);

    $venueNames = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $venueNames[] = $row;
        }
    }

    return $venueNames;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the JSON payload
    $attendanceData = json_decode(file_get_contents("php://input"), true);

    // Check if the JSON payload is valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        die(json_encode(["status" => "error", "message" => "Invalid JSON data received."]));
    }

    // Check if the required data is present
    if (!empty($attendanceData) && isset($attendanceData['students']) && isset($attendanceData['course']) && isset($attendanceData['venue'])) {
        $course = $attendanceData['course']; // Get the course from the JSON payload
        $venue = $attendanceData['venue']; // Get the venue from the JSON payload

        // Loop through each student's attendance data
        foreach ($attendanceData['students'] as $data) {
            // Validate required fields
            if (!isset($data['studentRegistrationNumber']) || !isset($data['attendanceStatus']) ||
                !isset($data['availabilityTime']) || !isset($data['mobileUsage']) || !isset($data['sleepingTime'])) {
                echo json_encode(["status" => "error", "message" => "Missing required fields in student data."]);
                continue; // Skip this student and proceed to the next one
            }

            $studentRegistrationNumber = $data['studentRegistrationNumber'];
            $attendanceStatus = $data['attendanceStatus'];
            $date = date("Y-m-d"); // Current date
            $availabilityTime = $data['availabilityTime'];
            $mobileUsage = $data['mobileUsage'];
            $sleepingTime = $data['sleepingTime'];

            // Insert the data into the tblattendance table
            $sql = "INSERT INTO tblattendance (
                        studentRegistrationNumber, 
                        course, 
                        venue, 
                        attendanceStatus, 
                        dateMarked, 
                        availabilityTime, 
                        mobileUsage, 
                        sleepingTime
                    ) VALUES (
                        '$studentRegistrationNumber', 
                        '$course', 
                        '$venue', 
                        '$attendanceStatus', 
                        '$date', 
                        '$availabilityTime', 
                        '$mobileUsage', 
                        '$sleepingTime'
                    )";
            
            // Execute the query
            if ($conn->query($sql)) {
                $message = "Attendance recorded successfully for student: $studentRegistrationNumber.";
            } else {
                echo json_encode(["status" => "error", "message" => "Error inserting attendance data for student $studentRegistrationNumber: " . $conn->error]);
            }
        }

        echo json_encode(["status" => "success", "message" => "Attendance recorded successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "No valid attendance data received."]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="../admin/img/logo/attnlg.png" rel="icon">
  <title>Lecture Dashboard</title>
  <link rel="stylesheet" href="css/styles.css">
  <script defer src="face-api.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
  <style>
    /* Add this CSS to style the video container */
    #videoContainer {
      width: 600px; /* Fixed width */
      height: 400px; /* Fixed height */
      border: 2px solid #ccc;
      margin: 20px auto;
      position: relative;
      overflow: hidden;
      display: none; /* Initially hidden */
    }

    #videoFeed {
      width: 100%;
      height: 100%;
      object-fit: cover; /* Ensure video fits within the container */
    }

    .attendance-button {
      margin: 20px 0;
      text-align: center;
    }

    .attendance-button button {
      margin: 0 10px;
    }

    .messageDiv {
      padding: 10px;
      margin: 10px 0;
      background-color: #f1f1f1;
      border: 1px solid #ccc;
      border-radius: 5px;
      text-align: center;
      display: none;
    }
  </style>
</head>

<body>

<?php include 'includes/topbar.php';?>
<section class="main">
    <?php include 'includes/sidebar.php';?>
    <div class="main--content">
        <div id="messageDiv" class="messageDiv"></div>

        <!-- Form for selecting course and venue -->
        <form class="lecture-options" id="selectForm">
            <select required name="course" id="courseSelect" onChange="updateTable()">
                <option value="" selected>Select Course</option>
                <?php
                $courseNames = getCourseNames($conn);
                foreach ($courseNames as $course) {
                    echo '<option value="' . $course["courseCode"] . '">' . $course["name"] . '</option>';
                }
                ?>
            </select>
            
            <select required name="venue" id="venueSelect" onChange="updateTable()">
                <option value="" selected>Select Venue</option>
                <?php
                $venueNames = getVenueNames($conn);
                foreach ($venueNames as $venue) {
                    echo '<option value="' . $venue["className"] . '">' . $venue["className"] . '</option>';
                }
                ?>
            </select>
        </form>

        <!-- Buttons for attendance process -->
        <div class="attendance-button">
            <button id="startButton" class="add" disabled>Launch Attendance Process</button>
            <button id="endButton" class="add" style="display:none">Launch Attendance Process</button>
            <button id="endAttendance" class="add">END Attendance Taking</button>
        </div>

        <!-- Video container for displaying the video feed -->
        <div id="videoContainer">
            <img id="videoFeed" src="" alt="Video Feed">
        </div>

        <!-- Container for displaying the student table -->
        <div class="table-container">
            <div id="studentTableContainer"></div>
        </div>
    </div>
</section>

<!-- Include JavaScript files -->
<script src="script.js"></script>
<script src="../admin/javascript/main.js"></script>

</body>
</html>