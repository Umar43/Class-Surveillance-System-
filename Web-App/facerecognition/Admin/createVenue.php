<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

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

if (isset($_POST["addVenue"])) {
    $className = $_POST["className"];
    $facultyCode = $_POST["faculty"];
    $currentStatus = $_POST["currentStatus"];
    $capacity = $_POST["capacity"];
    $classification = $_POST["classification"];
    $dateRegistered = date("Y-m-d");

    $query = mysqli_query($conn, "SELECT * FROM tblvenue WHERE className='$className'");
    $ret = mysqli_fetch_array($query);
    if ($ret > 0) { 
        $message = "Venue Already Exists";
    } else {
        $query = mysqli_query($conn, "INSERT INTO tblvenue (className, facultyCode, currentStatus, capacity, classification, dateCreated) 
        VALUES ('$className', '$facultyCode', '$currentStatus', '$capacity', '$classification', '$dateRegistered')");
        $message = "Venue Inserted Successfully";
    }
}

if (isset($_POST["editVenue"])) {
    $id = $_POST["id"];
    $className = $_POST["className"];
    $facultyCode = $_POST["faculty"];
    $currentStatus = $_POST["currentStatus"];
    $capacity = $_POST["capacity"];
    $classification = $_POST["classification"];

    $query = mysqli_query($conn, "UPDATE tblvenue SET className='$className', facultyCode='$facultyCode', currentStatus='$currentStatus', capacity='$capacity', classification='$classification' WHERE Id='$id'");
    $message = "Venue Updated Successfully";
}

if (isset($_GET["deleteVenue"])) {
    $id = $_GET["id"];
    $query = mysqli_query($conn, "DELETE FROM tblvenue WHERE Id='$id'");
    $message = "Venue Deleted Successfully";
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
</head>
<body>
<?php include 'includes/topbar.php';?>
<section class="main">
<?php include 'includes/sidebar.php';?>
 <div class="main--content">

 <div id="overlay"></div>

 <div class="rooms">
    <div class="title">
        <h2 class="section--title">Locations</h2>
        <div class="rooms--right--btns">
            <select name="date" id="date" class="dropdown room--filter">
                <option>Filter</option>
                <option value="free">Free</option>
                <option value="scheduled">Scheduled</option>
            </select>
            <button id="addClass1" class="add"><i class="ri-add-line"></i>Add lecture room</button>
        </div>
    </div>
    <div class="rooms--cards">
        <a href="#" class="room--card">
            <div class="img--box--cover">
                <div class="img--box">
                <img src="img/class.jpeg" alt="">
                </div>
            </div>
            <p class="free">Ground Floor</p>
        </a>
        <a href="#" class="room--card">
            <div class="img--box--cover">
                <div class="img--box">
                <img src="img/class.jpeg" alt="">
                </div>
            </div>
            <p class="free">First Floor</p>
        </a>
        <a href="#" class="room--card">
            <div class="img--box--cover">
                <div class="img--box">
                    <img src="img/class.jpeg" alt="">
                </div>
            </div>
            <p class="free">Second Floor</p>
        </a>
        <a href="#" class="room--card">
            <div class="img--box--cover">
                <div class="img--box">
                <img src="img/class.jpeg" alt="">
                </div>
            </div>
            <p class="free">Third Floor</p>
        </a>
        <a href="#" class="room--card">
            <div class="img--box--cover">
                <div class="img--box">
                <img src="img/class.jpeg" alt="">
                </div>
            </div>
            <p class="free">Girls Block</p>
        </a>
    </div>
 </div>

 <div id="messageDiv" class="messageDiv" style="display:none;"></div>

 <div class="table-container">
    <div class="title" id="addClass2">
        <h2 class="section--title">Lecture Rooms</h2>
    </div>
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

 <div class="formDiv-venue" id="addClassForm" style="display:none;">
    <form method="POST" action="" name="addVenue" enctype="multipart/form-data">
        <div style="display:flex; justify-content:space-around;">
            <div class="form-title">
                <p>Add Venue</p>
            </div>
            <div>
                <span class="close">&times;</span>
            </div>
        </div>
        <input type="hidden" name="id" id="editId">
        <input type="text" name="className" id="className" placeholder="Class Name" required>
        <select name="currentStatus" id="currentStatus">
            <option value="">--Current Status--</option>
            <option value="available">Available</option>
            <option value="scheduled">Scheduled</option>
        </select>
        <input type="text" name="capacity" id="capacity" placeholder="Capacity" required>
        <select required name="classification" id="classification">
            <option value="" selected> --Select Class Type--</option>
            <option value="laboratory">Ground Floor</option>
            <option value="computerLab">First Floor</option>
            <option value="lectureHall">Second Floor</option>
            <option value="class">Third Floor</option>
            <option value="office">Girls Block</option>
        </select>
        <select required name="faculty" id="faculty">
            <option value="" selected>Select Faculty</option>
            <?php
            $facultyNames = getFacultyNames($conn);
            foreach ($facultyNames as $faculty) {
                echo '<option value="' . $faculty["facultyCode"] . '">' . $faculty["facultyName"] . '</option>';
            }
            ?>
        </select>
        <input type="submit" class="submit" value="Save Venue" name="addVenue" id="saveVenue">
        <input type="submit" class="submit" value="Update Venue" name="editVenue" id="editVenue" style="display:none;">
    </form>		  
 </div>
 </div>
</section>

<script src="javascript/main.js"></script>
<script src="./javascript/confirmation.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const addClass1 = document.getElementById('addClass1');
    const addClass2 = document.getElementById('addClass2');
    const addClassForm = document.getElementById('addClassForm');
    const overlay = document.getElementById('overlay');
    const closeButtons = document.querySelectorAll('#addClassForm .close');
    const editButtons = document.querySelectorAll('.edit');
    const deleteButtons = document.querySelectorAll('.delete');

    addClass1.addEventListener('click', function () {
        addClassForm.style.display = 'block';
        overlay.style.display = 'block';
        document.body.style.overflow = 'hidden'; 
    });

    addClass2.addEventListener('click', function () {
        addClassForm.style.display = 'block';
        overlay.style.display = 'block';
        document.body.style.overflow = 'hidden'; 
    });

    closeButtons.forEach(function (closeButton) {
        closeButton.addEventListener('click', function () {
            addClassForm.style.display = 'none';
            overlay.style.display = 'none';
            document.body.style.overflow = 'auto'; 
        });
    });

    editButtons.forEach(function(editButton) {
        editButton.addEventListener('click', function() {
            const id = editButton.getAttribute('data-id');
            fetch(`getVenue.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editId').value = data.Id;
                    document.getElementById('className').value = data.className;
                    document.getElementById('currentStatus').value = data.currentStatus;
                    document.getElementById('capacity').value = data.capacity;
                    document.getElementById('classification').value = data.classification;
                    document.getElementById('faculty').value = data.facultyCode;
                    document.getElementById('saveVenue').style.display = 'none';
                    document.getElementById('editVenue').style.display = 'block';
                    addClassForm.style.display = 'block';
                    overlay.style.display = 'block';
                    document.body.style.overflow = 'hidden'; 
                });
        });
    });

    deleteButtons.forEach(function(deleteButton) {
        deleteButton.addEventListener('click', function() {
            const id = deleteButton.getAttribute('data-id');
            if (confirm("Are you sure you want to delete this venue?")) {
                window.location.href = `createVenue.php?deleteVenue=true&id=${id}`;
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