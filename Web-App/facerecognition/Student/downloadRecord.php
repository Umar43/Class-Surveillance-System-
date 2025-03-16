<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';


function getCourseNames($conn) {
    $sql = "SELECT courseCode,name FROM tblcourse";
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



function fetchStudentRecordsFromDatabase($conn, $courseCode) {
    $studentRows = array();

    $query = "SELECT * FROM tblattendance WHERE course = '$courseCode'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $studentRows[] = $row;
        }
    }

    return $studentRows;
}

$courseCode = isset($_GET['course']) ? $_GET['course'] : '';

$studentRows = fetchStudentRecordsFromDatabase($conn, $courseCode);

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
  <title>lecture Dashboard</title>
  <link rel="stylesheet" href="css/styles.css">
 <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>



<body>
<?php include 'includes/topbar.php';?>
    <section class="main">
        <?php include 'includes/sidebar.php';?>
    <div class="main--content">
    <form class="lecture-options" id="selectForm">
    <select required name="course" id="courseSelect"  onChange="updateTable()">
        <option value="" selected>Select Course</option>
        <?php
        $courseNames = getCourseNames($conn);
        foreach ($courseNames as $course) {
            echo '<option value="' . $course["courseCode"] . '">' . $course["name"] . '</option>';
        }
        ?>
    </select>

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
                    <?php
                    $distinctDatesQuery = "SELECT DISTINCT dateMarked FROM tblattendance where course='$courseCode'";
                    $distinctDatesResult = mysqli_query($conn, $distinctDatesQuery);

                    if ($distinctDatesResult) {
                        while ($dateRow = mysqli_fetch_assoc($distinctDatesResult)) {
                            echo "<th>" . $dateRow['dateMarked'] . "</th>";
                        }
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($studentRows as $row) {
                    echo "<tr>";
                    echo "<td>" . $row["studentRegistrationNumber"] . "</td>";
                    $distinctDatesResult = mysqli_query($conn, $distinctDatesQuery);
                    if ($distinctDatesResult) {
                        while ($dateRow = mysqli_fetch_assoc($distinctDatesResult)) {
                            $date = $dateRow['dateMarked'];
                            $attendanceQuery = "SELECT attendanceStatus FROM tblattendance WHERE studentRegistrationNumber = '" . $row['studentRegistrationNumber'] . "' AND dateMarked = '$date'";
                            $attendanceResult = mysqli_query($conn, $attendanceQuery);
                            
                            if ($attendanceResult && mysqli_num_rows($attendanceResult) > 0) {
                                $attendanceData = mysqli_fetch_assoc($attendanceResult);
                                echo "<td>" . $attendanceData['attendanceStatus'] . "</td>";
                            } else {
                                echo "<td>Absent</td>";
                            }
                        }
                    }
                    
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        
    </div>
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
function updateTable(){
    var courseSelect = document.getElementById("courseSelect");

    
    var selectedCourse = courseSelect.value;

    
    var url = "downloadrecord.php";
    if (selectedCourse && selectedUnit) {
        url += "?course=" + encodeURIComponent(selectedCourse) + "&unit=" + encodeURIComponent(selectedUnit);
        window.location.href = url;

    }}
    function exportTableToExcel(tableId, filename = '', courseCode = '', unitCode = '') {
    var table = document.getElementById(tableId);
    var currentDate = new Date();
    var formattedDate = currentDate.toLocaleDateString(); // Format the date as needed

    var headerContent = '<p style="font-weight:700;"> Attendance for : ' + courseCode + ' Unit name : ' + unitCode + ' On: ' + formattedDate + '</p>';
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