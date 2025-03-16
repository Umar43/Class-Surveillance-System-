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
  <title>Lecture Dashboard</title>
  <link rel="stylesheet" href="css/styles.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
</head>

<body>
<?php include 'includes/topbar.php';?>
    <section class="main">
        <?php include 'includes/sidebar.php';?>
    <div class="main--content">
    <form class="lecture-options" id="selectForm" method="GET" action="">
    <select required name="course" id="courseSelect" onChange="updateTable()">
        <option value="" selected>Select Course</option>
        <?php
        $courseNames = getCourseNames($conn);
        foreach ($courseNames as $course) {
            echo '<option value="' . $course["courseCode"] . '">' . $course["name"] . '</option>';
        }
        ?>
    </select>
    </form>

    <div class="table-container">
    <div class="title">
        <h2 class="section--title">Students List</h2>
    </div>
    <div class="table attendance-table" id="attendaceTable">
        <table>
        <thead>
                <tr>
                    <th>Registration No</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
      <?php  
      if (!empty($courseCode)) {
          $query = "SELECT * FROM tblstudents WHERE courseCode = '$courseCode'";
          $result = mysqli_query($conn, $query);

          if (mysqli_num_rows($result) > 0) {
              while ($row = mysqli_fetch_assoc($result)) {
                  echo "<tr>";
                  echo "<td>" . $row['registrationNumber'] . "</td>";
                  echo "<td>" . $row['firstName'] . "</td>";
                  echo "<td>" . $row['lastName'] . "</td>";
                  echo "<td>" . $row['email'] . "</td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='4'>No students found for this course.</td></tr>";
          }
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
<script>
function updateTable(){
    document.getElementById("selectForm").submit();
}
</script>

</html>