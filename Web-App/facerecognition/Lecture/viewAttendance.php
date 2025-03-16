<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

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

function fetchStudentRecordsFromDatabase($conn, $courseCode, $dateMarked) {
    $studentRows = array();

    // Join tblattendance with tblstudents to get student details
    $query = "SELECT a.studentRegistrationNumber, a.attendanceStatus, a.availabilityTime, a.mobileUsage, a.sleepingTime, s.firstName, s.lastName, s.email 
              FROM tblattendance a 
              JOIN tblstudents s ON a.studentRegistrationNumber = s.registrationNumber 
              WHERE a.course = '$courseCode' AND a.dateMarked = '$dateMarked'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $studentRows[] = $row;
        }
    }

    return $studentRows;
}

$courseCode = isset($_GET['course']) ? $_GET['course'] : '';
$dateMarked = isset($_GET['date']) ? $_GET['date'] : '';

$studentRows = fetchStudentRecordsFromDatabase($conn, $courseCode, $dateMarked);

$coursename = "";
if (!empty($courseCode)) {
    $coursename_query = "SELECT name FROM tblcourse WHERE courseCode = '$courseCode'";
    $result = mysqli_query($conn, $coursename_query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $coursename = $row['name'];
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
  <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
</head>

<body>
<?php include 'includes/topbar.php';?>
    <section class="main">
        <?php include 'includes/sidebar.php';?>
    <div class="main--content">
    <form class="lecture-options" id="selectForm">
    <select required name="course" id="courseSelect">
        <option value="" selected>Select Course</option>
        <?php
        $courseNames = getCourseNames($conn);
        foreach ($courseNames as $course) {
            echo '<option value="' . $course["courseCode"] . '">' . $course["name"] . '</option>';
        }
        ?>
    </select>
    <input type="date" name="date" id="dateSelect" required>
    <button type="button" onclick="updateTable()">Filter</button>
    </form>

    <button class="add" onclick="exportTableToExcel('attendaceTable', '<?php echo date('Y-m-d'); ?>','<?php echo $coursename ?>')">Export Attendance As Excel</button>

    <div class="table-container">
    <div class="title">
        <h2 class="section--title">Attendance Preview</h2>
    </div>
    <div class="table attendance-table" id="attendaceTable">
    <table>
    <thead>
        <tr>
            <th>Registration No</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <?php
            if (!empty($courseCode)) {
                echo "<th>Attendance Status</th>";
                echo "<th>Availability Time</th>";
                echo "<th>Mobile Usage</th>";
                echo "<th>Sleeping Time</th>";
            }
            ?>
        </tr>
    </thead>
    <tbody>
    <?php
    if (!empty($studentRows)) {
        foreach ($studentRows as $row) {
            echo "<tr>";
            echo "<td>" . $row['studentRegistrationNumber'] . "</td>";
            echo "<td>" . $row['firstName'] . "</td>";
            echo "<td>" . $row['lastName'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['attendanceStatus'] . "</td>";
            echo "<td>" . $row['availabilityTime'] . "</td>";
            echo "<td>" . $row['mobileUsage'] . "</td>";
            echo "<td>" . $row['sleepingTime'] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='8'>No attendance records available for the selected date.</td></tr>";
    }
    ?>
    </tbody>
</table>
    </div>
</div>
</div>
</section>
<div>
</body>

<script src="./min/js/filesaver.js"></script>
<script src="./min/js/xlsx.js"></script>
<script  src="../admin/javascript/main.js"></script>

<script>
function updateTable() {
    var courseSelect = document.getElementById("courseSelect");
    var dateSelect = document.getElementById("dateSelect");
    var selectedCourse = courseSelect.value;
    var selectedDate = dateSelect.value;
    if (selectedCourse && selectedDate) {
        window.location.href = "viewAttendance.php?course=" + encodeURIComponent(selectedCourse) + "&date=" + encodeURIComponent(selectedDate);
    }
}

function exportTableToExcel(tableId, filename = '', courseCode = '') {
    var table = document.getElementById(tableId);
    var currentDate = new Date().toLocaleDateString();
    var headerContent = '<p style="font-weight:700;"> Attendance for : ' + courseCode + ' On: ' + currentDate + '</p>';
    var tbody = document.createElement('tbody');
    var additionalRow = tbody.insertRow(0);
    var additionalCell = additionalRow.insertCell(0);
    additionalCell.innerHTML = headerContent;
    table.insertBefore(tbody, table.firstChild);
    var wb = XLSX.utils.table_to_book(table, { sheet: "Attendance" });
    var wbout = XLSX.write(wb, { bookType: 'xlsx', bookSST: true, type: 'binary' });
    var blob = new Blob([s2ab(wbout)], { type: 'application/octet-stream' });
    if (!filename.toLowerCase().endsWith('.xlsx')) {
        filename += '.xlsx'; 
    }
    saveAs(blob, filename);
}

function s2ab(s) {
    var buf = new ArrayBuffer(s.length);
    var view = new Uint8Array(buf);
    for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
    return buf;
}
</script>

</html>