<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

error_reporting(0);

function getFacultyNames($conn) {
    $sql = "SELECT facultyCode, facultyName FROM tblfaculty";
    $result = $conn->query($sql);

    $facultyNames = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $facultyNames[] = $row;
        }
    }

    return $facultyNames;
}

if (isset($_POST["addLecture"])) {
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $email = $_POST["email"];
    $phoneNumber = $_POST["phoneNumber"];
    $faculty = $_POST["faculty"];
    $dateRegistered = date("Y-m-d");
    $password = $_POST["password"];

    $query = mysqli_query($conn, "SELECT * FROM tbllecture WHERE emailAddress='$email'");
    $ret = mysqli_fetch_array($query);
    if ($ret > 0) { 
        $message = "Teacher Already Exists";
    } else {
        $query = mysqli_query($conn, "INSERT INTO tbllecture (firstName, lastName, emailAddress, password, phoneNo, facultyCode, dateCreated) 
        VALUES ('$firstName', '$lastName', '$email', '$password', '$phoneNumber', '$faculty', '$dateRegistered')");
        $message = "Teacher Added Successfully";
    }
}

if (isset($_POST["editLecture"])) {
    $id = $_POST["id"];
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $email = $_POST["email"];
    $phoneNumber = $_POST["phoneNumber"];
    $faculty = $_POST["faculty"];
    $password = $_POST["password"];

    $query = mysqli_query($conn, "UPDATE tbllecture SET firstName='$firstName', lastName='$lastName', emailAddress='$email', password='$password', phoneNo='$phoneNumber', facultyCode='$faculty' WHERE Id='$id'");
    $message = "Teacher Updated Successfully";
}

if (isset($_GET["deleteLecture"])) {
    $id = $_GET["id"];
    $query = mysqli_query($conn, "DELETE FROM tbllecture WHERE Id='$id'");
    $message = "Teacher Deleted Successfully";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.png" rel="icon">
  <title>AMS - Dashboard</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
  <script src="./javascript/addStudent.js"></script>   
  <script src="./javascript/addStudent.js"></script>
  <script src="https://unpkg.com/face-api.js"></script>
</head>
<body>
<?php include "Includes/topbar.php";?>

  <section class=main>
      <?php include "Includes/sidebar.php";?>
       
   <div class="main--content"> 
   <div id="overlay"></div>
   <div id="messageDiv" class="messageDiv" style="display:none;"></div>

   <div class="table-container">
            <a href="#add-form" style="text-decoration:none;"> <div class="title" id="addLecture">
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
                                <th>Faculty</th>
                                <th>Date Registered</th>
                                <th>Settings</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM tbllecture";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row["firstName"] . "</td>";
                                    echo "<td>" . $row["emailAddress"] . "</td>";
                                    echo "<td>" . $row["phoneNo"] . "</td>";
                                    echo "<td>" . $row["facultyCode"] . "</td>";
                                    echo "<td>" . $row["dateCreated"] . "</td>";
                                    echo "<td><span><i class='ri-edit-line edit' data-id='" . $row["Id"] . "'></i><i class='ri-delete-bin-line delete' data-id='" . $row["Id"] . "'></i></span></td>";
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

<div id="addLectureForm" style="display:none;">
    <form method="POST" action="" name="addLecture" enctype="multipart/form-data">
        <div style="display:flex; justify-content:space-around;">
            <div class="form-title">
                <p>Add Lecture</p>
            </div>
            <div>
                <span class="close">&times;</span>
            </div>
        </div>
        <input type="hidden" name="id" id="editId">
        <input type="text" name="firstName" id="firstName" placeholder="First Name" required>
        <input type="text" name="lastName" id="lastName" placeholder="Last Name" required>
        <input type="email" name="email" id="email" placeholder="Email Address" required>
        <input type="text" name="phoneNumber" id="phoneNumber" placeholder="Phone Number" required>
        <input type="password" name="password" id="password" placeholder="**********" required>
        <select required name="faculty" id="faculty">
            <option value="" selected>Select Faculty</option>
            <?php
            $facultyNames = getFacultyNames($conn);
            foreach ($facultyNames as $faculty) {
                echo '<option value="' . $faculty["facultyCode"] . '">' . $faculty["facultyName"] . '</option>';
            }
            ?>
        </select>
        <input type="submit" class="submit" value="Save Lecture" name="addLecture">
        <input type="submit" class="submit" value="Update Lecture" name="editLecture" style="display:none;">
    </form>		  
</div>
      
</section>

<script src="javascript/main.js"></script>
<script src="javascript/addLecture.js"></script>
<script src="./javascript/confirmation.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const addLecture = document.getElementById('addLecture');
    const addLectureForm = document.getElementById("addLectureForm");
    const overlay = document.getElementById("overlay");
    const closeButtons = document.querySelectorAll('#addLectureForm .close');
    const editButtons = document.querySelectorAll('.edit');
    const deleteButtons = document.querySelectorAll('.delete');

    addLecture.addEventListener("click", function() {
        addLectureForm.style.display = "block";
        overlay.style.display = "block";
        document.body.style.overflow = 'hidden'; 
    });

    closeButtons.forEach(function(closeButton) {
        closeButton.addEventListener('click', function() {
            addLectureForm.style.display = "none";
            overlay.style.display = 'none';
            document.body.style.overflow = 'auto'; 
        });
    });

    editButtons.forEach(function(editButton) {
        editButton.addEventListener('click', function() {
            const id = editButton.getAttribute('data-id');
            fetch(`getLecture.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editId').value = data.Id;
                    document.getElementById('firstName').value = data.firstName;
                    document.getElementById('lastName').value = data.lastName;
                    document.getElementById('email').value = data.emailAddress;
                    document.getElementById('phoneNumber').value = data.phoneNo;
                    document.getElementById('password').value = data.password;
                    document.getElementById('faculty').value = data.facultyCode;
                    document.querySelector('input[name="editLecture"]').style.display = 'block';
                    document.querySelector('input[name="addLecture"]').style.display = 'none';
                    addLectureForm.style.display = "block";
                    overlay.style.display = "block";
                    document.body.style.overflow = 'hidden'; 
                });
        });
    });

    deleteButtons.forEach(function(deleteButton) {
        deleteButton.addEventListener('click', function() {
            const id = deleteButton.getAttribute('data-id');
            if (confirm("Are you sure you want to delete this teacher?")) {
                window.location.href = `createteacher.php?deleteLecture=true&id=${id}`;
            }
        });
    });
});

<?php if(isset($message)): ?>
    showMessage('<?php echo $message; ?>');
<?php endif; ?>
</script>
</body>
</html>