<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';
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
</head>
<body>
<?php include 'includes/topbar.php';?>
    <section class="main">
        <?php include 'includes/sidebar.php';?>
    <div class="main--content">
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
                    <div class="card card-1">
                        <?php 
                        $query1 = mysqli_query($conn, "SELECT * FROM tblstudents");                       
                        $students = mysqli_num_rows($query1);
                        ?>
                        <div class="card--data">
                            <div class="card--content">
                                <h5 class="card--title">Registered Students</h5>
                                <h1><?php echo $students;?></h1>
                            </div>
                            <i class="ri-user-2-line card--icon--lg"></i>
                        </div>
                    </div>
                    <div class="card card-1">
                        <?php 
                        $query1 = mysqli_query($conn, "SELECT * FROM tbllecture");                       
                        $totalLecture = mysqli_num_rows($query1);
                        ?>
                        <div class="card--data">
                            <div class="card--content">
                                <h5 class="card--title">Registered Teachers</h5>
                                <h1><?php echo $totalLecture;?></h1>
                            </div>
                            <i class="ri-user-line card--icon--lg"></i>
                        </div>
                    </div>
                </div>
            </div>
           
            <div class="table-container">
                <a href="createteacher.php" style="text-decoration:none;">
                    <div class="title">
                        <h2 class="section--title">Teachers</h2>
                        <button class="add"><i class="ri-add-line"></i>Add Teacher</button>
                    </div>
                </a>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email Address</th>
                                <th>Phone No</th>
                                <th>Departments</th>
                                <th>Date Registered</th>
                                <th>Settings</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT l.*, f.facultyName
                            FROM tbllecture l
                            LEFT JOIN tblfaculty f ON l.facultyCode = f.facultyCode";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row["firstName"] . "</td>";
                                    echo "<td>" . $row["emailAddress"] . "</td>";
                                    echo "<td>" . $row["phoneNo"] . "</td>";
                                    echo "<td>" . $row["facultyName"] . "</td>";
                                    echo "<td>" . $row["dateCreated"] . "</td>";
                                    echo "<td><span><i class='ri-edit-line edit' data-id='" . $row["Id"] . "' data-type='teacher'></i><i class='ri-delete-bin-line delete' data-id='" . $row["Id"] . "' data-type='teacher'></i></span></td>";
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
                <a href="createStudent.php" style="text-decoration:none;">
                    <div class="title">
                        <h2 class="section--title">Students</h2>
                        <button class="add"><i class="ri-add-line"></i>Add Student</button>
                    </div>
                </a>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Registration No</th>
                                <th>Name</th>
                                <th>Departments</th>
                                <th>Course</th>
                                <th>Email</th>
                                <th>Settings</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM tblstudents";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row["registrationNumber"] . "</td>";
                                    echo "<td>" . $row["firstName"] . "</td>";
                                    echo "<td>" . $row["faculty"] . "</td>";
                                    echo "<td>" . $row["courseCode"] . "</td>";
                                    echo "<td>" . $row["email"] . "</td>";
                                    echo "<td><span><i class='ri-edit-line edit' data-id='" . $row["Id"] . "' data-type='student'></i><i class='ri-delete-bin-line delete' data-id='" . $row["Id"] . "' data-type='student'></i></span></td>";
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
                <a href="createVenue.php" style="text-decoration:none;">
                    <div class="title">
                        <h2 class="section--title">Lecture Rooms</h2>
                        <button class="add"><i class="ri-add-line"></i>Add room</button>
                    </div>
                </a>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Class Name</th>
                                <th>Departments</th>
                                <th>Current Status</th>
                                <th>Capacity</th>
                                <th>Classification</th>
                                <th>Settings</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM tblvenue";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row["className"] . "</td>";
                                    echo "<td>" . $row["facultyCode"] . "</td>";
                                    echo "<td>" . $row["currentStatus"] . "</td>";
                                    echo "<td>" . $row["capacity"] . "</td>";
                                    echo "<td>" . $row["classification"] . "</td>";
                                    echo "<td><span><i class='ri-edit-line edit' data-id='" . $row["Id"] . "' data-type='venue'></i><i class='ri-delete-bin-line delete' data-id='" . $row["Id"] . "' data-type='venue'></i></span></td>";
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
                <a href="createCourse.php" style="text-decoration:none;">
                    <div class="title">
                        <h2 class="section--title">Courses</h2>
                        <button class="add"><i class="ri-add-line"></i>Add Course</button>
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
        </div>
    </section>
    <script src="javascript/main.js"></script>
    <script src="javascript/confirmation.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const editButtons = document.querySelectorAll('.edit');
        const deleteButtons = document.querySelectorAll('.delete');

        // Edit Functionality
        editButtons.forEach(function(editButton) {
            editButton.addEventListener('click', function() {
                const id = editButton.getAttribute('data-id');
                const type = editButton.getAttribute('data-type');
                switch (type) {
                    case 'teacher':
                        window.location.href = `createteacher.php?edit=true&id=${id}`;
                        break;
                    case 'student':
                        window.location.href = `createStudent.php?edit=true&id=${id}`;
                        break;
                    case 'venue':
                        window.location.href = `createVenue.php?edit=true&id=${id}`;
                        break;
                    case 'course':
                        window.location.href = `createCourse.php?edit=true&id=${id}`;
                        break;
                }
            });
        });

        // Delete Functionality
        deleteButtons.forEach(function(deleteButton) {
            deleteButton.addEventListener('click', function() {
                const id = deleteButton.getAttribute('data-id');
                const type = deleteButton.getAttribute('data-type');
                if (confirm("Are you sure you want to delete this record?")) {
                    switch (type) {
                        case 'teacher':
                            window.location.href = `createteacher.php?delete=true&id=${id}`;
                            break;
                        case 'student':
                            window.location.href = `createStudent.php?delete=true&id=${id}`;
                            break;
                        case 'venue':
                            window.location.href = `createVenue.php?delete=true&id=${id}`;
                            break;
                        case 'course':
                            window.location.href = `createCourse.php?delete=true&id=${id}`;
                            break;
                    }
                }
            });
        });
    });
    </script>
    <?php include 'includes/footer.php';?>
</body>
</html>