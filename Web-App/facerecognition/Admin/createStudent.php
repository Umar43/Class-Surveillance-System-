<?php 
error_reporting(0);
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

if (isset($_POST['addStudent'])) {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $registrationNumber = $_POST['registrationNumber'];
    $courseCode = $_POST['course'];
    $faculty = $_POST['faculty'];
    $dateRegistered = date("Y-m-d");
    $capturedImage1 = $_POST['capturedImage1'];
    $capturedImage2 = $_POST['capturedImage2'];
    $base64Data1 = explode(',', $capturedImage1)[1];
    $base64Data2 = explode(',', $capturedImage2)[1];
    $imageData1 = base64_decode($base64Data1);
    $imageData2 = base64_decode($base64Data2);
    $registrationNumber = mysqli_real_escape_string($conn, $_POST['registrationNumber']);
    $folderPath = "../Lecture/labels/{$registrationNumber}/";
    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true);
    }
    file_put_contents($folderPath . '1.png', $imageData1);
    file_put_contents($folderPath . '2.png', $imageData2);

    $query = mysqli_query($conn, "SELECT * FROM tblstudents WHERE registrationNumber ='$registrationNumber'");
    $ret = mysqli_fetch_array($query);

    if ($ret > 0) { 
        $message = "Student with the given Registration No: $registrationNumber Exists!";
    } else {
        $query = mysqli_query($conn, "INSERT INTO tblstudents (firstName, lastName, email, registrationNumber, faculty, courseCode, studentImage1, studentImage2, dateRegistered) 
        VALUES ('$firstName', '$lastName', '$email', '$registrationNumber', '$faculty', '$courseCode', '$registrationNumber" . "_image1.png', '$registrationNumber" . "_image2.png', '$dateRegistered')");

        $message = "Student: $registrationNumber Added Successfully";
    }
}

if (isset($_POST['editStudent'])) {
    $id = $_POST['id'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $registrationNumber = $_POST['registrationNumber'];
    $courseCode = $_POST['course'];
    $faculty = $_POST['faculty'];

    $query = mysqli_query($conn, "UPDATE tblstudents SET firstName='$firstName', lastName='$lastName', email='$email', registrationNumber='$registrationNumber', faculty='$faculty', courseCode='$courseCode' WHERE Id='$id'");

    if ($query) {
        $message = "Student Updated Successfully";
    } else {
        $message = "An error occurred while updating the student.";
    }
}

if (isset($_GET['deleteStudent'])) {
    $id = $_GET['id'];
    $query = mysqli_query($conn, "DELETE FROM tblstudents WHERE Id='$id'");

    if ($query) {
        $message = "Student Deleted Successfully";
    } else {
        $message = "An error occurred while deleting the student.";
    }
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
</head>
<body>
<?php include 'includes/topbar.php';?>

  <section class=main>
      <?php include "Includes/sidebar.php";?>
       
   <div class="main--content"> 
   <div id="overlay"></div>
   <div id="messageDiv" class="messageDiv" style="display:none;"></div>

   <div class="table-container">
      <div class="title" id="addStudent">
          <h2 class="section--title">Students</h2>
          <button class="add"><i class="ri-add-line"></i>Add Student</button>
      </div>
      <div class="table">
          <table>
              <thead>
                  <tr>
                      <th>Registration No</th>
                      <th>Name</th>
                      <th>Faculty</th>
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

   <div class="formDiv--" id="addStudentForm" style="display:none;"> 
       <form method="post">
           <div style="display:flex; justify-content:space-around;">
               <div class="form-title">
                   <p>Add Student</p>
               </div>
               <div>
                   <span class="close">&times;</span>
               </div>
           </div>
           <input type="hidden" name="id" id="editId">
           <div>
               <div>
                   <input type="text" name="firstName" id="firstName" placeholder="First Name"> 
                   <input type="text" name="lastName" id="lastName" placeholder="Last Name">
                   <input type="email" name="email" id="email" placeholder="Email Address">
                   <input type="text" required name="registrationNumber" id="registrationNumber" placeholder="Registration Number">
                   <select required name="faculty" id="faculty">
                       <option value="" selected>Select Faculty</option>
                       <?php
                       $facultyNames = getFacultyNames($conn);
                       foreach ($facultyNames as $faculty) {
                           echo '<option value="' . $faculty["facultyCode"] . '">' . $faculty["facultyName"] . '</option>';
                       }
                       ?>
                   </select>  
                   <br/>
                   <select required name="course" id="course">
                       <option value="" selected>Select Course</option>
                       <?php
                       $courseNames = getCourseNames($conn);
                       foreach ($courseNames as $course) {
                           echo '<option value="' . $course["courseCode"] . '">' . $course["name"] . '</option>';
                       }
                       ?>
                   </select>          
               </div>
               <div>
                   <div class="form-title-image">
                       <p>Take Student Pictures<p>
                   </div>
                   <div class="image-box" onclick="openCamera('button1');">
                       <img src="img/default.png" alt="Default Image" id="button1-captured-image">
                       <div class="edit-icon">
                           <i class="fas fa-camera" onclick="openCamera('button1');"></i>
                       </div>
                       <input type="hidden" id="button1-captured-image-input" name="capturedImage1" />
                   </div>
                   <div class="image-box" onclick="openCamera('button2')">
                       <img src="img/default.png" alt="Default Image" id="button2-captured-image">
                       <div class="edit-icon">
                           <i class="fas fa-camera" onclick="openCamera('button2')"></i>
                       </div>
                       <input type="hidden" id="button2-captured-image-input" name="capturedImage2" />
                   </div>
               </div> 
           </div>
           <input type="submit" class="btn-submit" value="Save Student" name="addStudent" id="saveStudent" />
           <input type="submit" class="btn-submit" value="Update Student" name="editStudent" id="editStudent" style="display:none;" />
       </form>
   </div>
 </section>

 <script src="javascript/main.js"></script>
 <script>
document.addEventListener("DOMContentLoaded", function() {
    const addStudent = document.getElementById('addStudent');
    const addStudentForm = document.getElementById("addStudentForm");
    const overlay = document.getElementById("overlay");
    const closeButtons = document.querySelectorAll('#addStudentForm .close');
    const editButtons = document.querySelectorAll('.edit');
    const deleteButtons = document.querySelectorAll('.delete');

    addStudent.addEventListener("click", function() {
        addStudentForm.style.display = "block";
        overlay.style.display = "block";
        document.body.style.overflow = 'hidden'; 
    });

    closeButtons.forEach(function(closeButton) {
        closeButton.addEventListener('click', function() {
            addStudentForm.style.display = "none";
            overlay.style.display = 'none';
            document.body.style.overflow = 'auto'; 
        });
    });

    editButtons.forEach(function(editButton) {
        editButton.addEventListener('click', function() {
            const id = editButton.getAttribute('data-id');
            fetch(`getStudent.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editId').value = data.Id;
                    document.getElementById('firstName').value = data.firstName;
                    document.getElementById('lastName').value = data.lastName;
                    document.getElementById('email').value = data.email;
                    document.getElementById('registrationNumber').value = data.registrationNumber;
                    document.getElementById('faculty').value = data.faculty;
                    document.getElementById('course').value = data.courseCode;
                    document.getElementById('saveStudent').style.display = 'none';
                    document.getElementById('editStudent').style.display = 'block';
                    addStudentForm.style.display = "block";
                    overlay.style.display = "block";
                    document.body.style.overflow = 'hidden'; 
                });
        });
    });

    deleteButtons.forEach(function(deleteButton) {
        deleteButton.addEventListener('click', function() {
            const id = deleteButton.getAttribute('data-id');
            if (confirm("Are you sure you want to delete this student?")) {
                window.location.href = `createStudent.php?deleteStudent=true&id=${id}`;
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