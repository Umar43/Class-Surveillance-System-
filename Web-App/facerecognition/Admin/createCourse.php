<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

function getFacultyNames($conn) {
    $sql = "SELECT Id, facultyName FROM tblfaculty";
    $result = $conn->query($sql);

    $facultyNames = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $facultyNames[] = $row;
        }
    }

    return $facultyNames;
}
function getLectureNames($conn) {
    $sql = "SELECT Id, firstName, lastName FROM tbllecture";
    $result = $conn->query($sql);

    $lectureNames = array();  
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $lectureNames[] = $row;
        }
    }

    return $lectureNames;
}
function getCourseNames($conn) {
    $sql = "SELECT ID,name FROM tblcourse";
    $result = $conn->query($sql);

    $courseNames = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $courseNames[] = $row;
        }
    }

    return $courseNames;
}

if (isset($_POST["addCourse"])) {
    $courseName = $_POST["courseName"];
    $courseCode = $_POST["courseCode"];
    $facultyID = $_POST["faculty"];
    $dateRegistered = date("Y-m-d");

    $query=mysqli_query($conn,"select * from tblcourse where courseCode='$courseCode'");
    $ret=mysqli_fetch_array($query);
        if($ret > 0){ 
            $message = " Course Already Exists";
    }
    else{
            $query=mysqli_query($conn,"insert into tblcourse(name,courseCode,facultyID,dateCreated) 
        value('$courseName','$courseCode','$facultyID','$dateRegistered')");
        $message = " Course Inserted Successfully";

    }
   
}

if (isset($_POST["addFaculty"])) {
    $facultyName = $_POST["facultyName"];
    $facultyCode = $_POST["facultyCode"];
    $dateRegistered = date("Y-m-d");

    $query=mysqli_query($conn,"select * from tblfaculty where facultyCode='$facultyCode'");
    $ret=mysqli_fetch_array($query);
        if($ret > 0){ 

            $message = " Faculty already Exists";}
    else{
        $query=mysqli_query($conn,"insert into tblfaculty(facultyName,facultyCode,dateRegistered) 
        value('$facultyName','$facultyCode','$dateRegistered')");
        $message = " Faculty Inserted Successfully";

    }
   
   
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.png" rel="icon">
  <title>Dashboard</title>
  <link rel="stylesheet" href="css/styles.css">
 <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
 <script src="./javascript/confirmation.js" defer></script>
</head>
<body>
<?php include 'includes/topbar.php'?>
<section class="main">
<?php include 'includes/sidebar.php';?>
 <div class="main--content">
    <div id="overlay"></div>
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
                    <div id="addCourse" class="card card-1">
                        <?php 
                        $query1=mysqli_query($conn,"SELECT * from tblcourse");                       
                        $courses = mysqli_num_rows($query1);
                        ?>
                        <div class="card--data">
                            <div class="card--content">
                            <button class="add"><i class="ri-add-line"></i>Add Course</button>
                                <h1><?php echo $courses;?> Courses</h1>
                            </div>
                            <i class="ri-user-2-line card--icon--lg"></i>
                        </div>
                       
                    </div>
                   
                    <div class="" id="addUnit">
                        <?php 
                        #$query1=mysqli_query($conn,"SELECT * from tblunit");                       
                        #$unit = mysqli_num_rows($query1);
                        ?>
                       
                        
                    </div>

                    <div class="card card-1" id="addFaculty">
                        <?php 
                        $query1=mysqli_query($conn,"SELECT * from tblfaculty");                       
                        $faculty = mysqli_num_rows($query1);
                        ?>
                        <div class="card--data">
                            <div class="card--content">
                            <button class="add"><i class="ri-add-line"></i>Add Department</button>
                                <h1><?php echo $faculty;?> Departments </h1> 
                            </div>
                            <i class="ri-user-line card--icon--lg"></i>
                        </div>
                       
                    </div>
                </div>
            </div>
            <div id="messageDiv" class="messageDiv" style="display:none;"></div>

            <div class="table-container">
                <div class="title">
                    <h2 class="section--title">Course</h2>
                </div>
                </a>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Departments</th>
                                <th>Total Students</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $sql = "SELECT 
                        c.name AS course_name,
                        c.facultyID AS faculty,
                        f.facultyName AS faculty_name,
                        COUNT(DISTINCT s.Id) AS total_students,
                        c.dateCreated AS date_created
                        FROM tblcourse c
                        LEFT JOIN tblstudents s ON c.courseCode = s.courseCode
                        LEFT JOIN tblfaculty f on c.facultyID=f.Id
                        GROUP BY c.ID";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["course_name"] . "</td>";
                            echo "<td>" . $row["faculty_name"] . "</td>";
                            echo "<td>" . $row["total_students"] . "</td>";
                            echo "<td>" . $row["date_created"] . "</td>";
                            echo "<td><span><i class='ri-edit-line edit'></i><i class='ri-delete-bin-line delete'></i></span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No records found</td></tr>";
                    }

                    ?>  
                        </tbody>
                    </table>
                </div>
                
            </div> 
          
            <div class="table-container">
                <div class="title">
                    <h2 class="section--title">Department</h2>
                </div>
                </a>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Total Courses</th>
                                <th>Total Students</th>
                                <th>Total Lectures</th>
                                <th>Date Created</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                           $sql = "SELECT 
                           f.facultyName AS faculty_name,
                           f.facultyCode AS faculty_code,
                           f.dateRegistered AS date_created,
                           COUNT(DISTINCT c.ID) AS total_courses,
                           COUNT(DISTINCT s.Id) AS total_students,
                           COUNT(DISTINCT l.Id) AS total_lectures
                       FROM tblfaculty f
                       LEFT JOIN tblcourse c ON f.Id = c.facultyID
                       LEFT JOIN tblstudents s ON f.facultyCode = s.faculty
                       LEFT JOIN tbllecture l ON f.facultyCode = l.facultyCode
                       GROUP BY f.Id";
                                     
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                           while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["faculty_code"] . "</td>";
                            echo "<td>" . $row["faculty_name"] . "</td>";
                            echo "<td>" . $row["total_courses"] . "</td>";
                            echo "<td>" . $row["total_students"] . "</td>";
                            echo "<td>" . $row["total_lectures"] . "</td>";
                            echo "<td>" . $row["date_created"] . "</td>";
                            echo "<td><span><i class='ri-edit-line edit'></i><i class='ri-delete-bin-line delete'></i></span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No records found</td></tr>";
                    }

                    ?>  
                        </tbody>
                    </table>
                </div>
                
            </div> 
          
  </div> 
  <div class="formDiv" id="addCourseForm"  style="display:none; ">
        
        <form method="POST" action="" name="addCourse" enctype="multipart/form-data">
            <div style="display:flex; justify-content:space-around;">
                <div class="form-title">
                    <p>Add Course</p>
                </div>
                <div>
                    <span class="close">&times;</span>
                </div>
            </div>
        
            <input type="text" name="courseName" placeholder="Course Name" required>
            <input type="text" name="courseCode" placeholder="Course Code" required>
        
        
            <select required name="faculty">
                <option value="" selected>Select Faculty</option>
                <?php
                $facultyNames = getFacultyNames($conn);
                foreach ($facultyNames as $faculty) {
                    echo '<option value="' . $faculty["Id"] . '">' . $faculty["facultyName"] . '</option>';
                }
                ?>
            </select>
        
            <input type="submit" class="submit" value="Save Course" name="addCourse">
        </form>		  
            </div>
        
        <div class="formDiv1" id="addUnitForm"  style="display:none; ">
                  
         </div>
            
        <div class="formDiv" id="addFacultyForm"  style="display:none; ">
        <form method="POST" action="" name="addFaculty" enctype="multipart/form-data">
            <div style="display:flex; justify-content:space-around;">
                <div class="form-title">
                    <p>Add Department</p>
                </div>
                <div>
                    <span class="close">&times;</span>
                </div>
            </div>
            <input type="text" name="facultyName" placeholder="Department Name" required>
            <input type="text" name="facultyCode" placeholder="Department Code" required>
            <input type="submit" class="submit" value="Save Department" name="addFaculty">
        </form>		  
        </div>


</section>
<script src="javascript/main.js"></script>
<script src="javascript/addCourse.js"></script>
<script src="javascript/confirmation.js"></script>
<?php if(isset($message)){
    echo "<script>showMessage('" . $message . "');</script>";
} 
?>
</body>
</html>
