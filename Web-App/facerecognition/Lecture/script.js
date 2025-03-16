var labels = [];
let detectedFaces = [];
let sendingData = false; 

// Function to enable/disable the Launch Attendance button
function updateLaunchButton() {
    const courseSelect = document.getElementById('courseSelect');
    const venueSelect = document.getElementById('venueSelect');
    const startButton = document.getElementById('startButton');

    // Enable the button only if both course and venue are selected
    if (courseSelect.value && venueSelect.value) {
        startButton.disabled = false;
    } else {
        startButton.disabled = true;
    }
}

// Add event listeners to course and venue dropdowns
document.getElementById('courseSelect').addEventListener('change', updateLaunchButton);
document.getElementById('venueSelect').addEventListener('change', updateLaunchButton);

// Function to send attendance data to the server
function sendAttendanceDataToServer() {
    const course = document.getElementById('courseSelect').value;
    const venue = document.getElementById('venueSelect').value;

    fetch('http://127.0.0.1:5000/end', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Add the selected course and venue to the data
        data.course = course;
        data.venue = venue;

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'takeAttendance.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        showMessage('Attendance recorded successfully.');
                        alert('Attendance added successfully!'); // Show success popup
                    } else {
                        showMessage('Error: ' + response.message);
                    }
                } else {
                    showMessage('Error: Unable to record attendance.');
                }
            }
        };

        xhr.send(JSON.stringify(data));
    })
    .catch(error => console.error('Error:', error));
}

// Function to show a message to the user
function showMessage(message) {
    var messageDiv = document.getElementById('messageDiv');
    messageDiv.style.display = "block";
    messageDiv.innerHTML = message;
    console.log(message);
    messageDiv.style.opacity = 1;
    setTimeout(function() {
        messageDiv.style.opacity = 0;
    }, 5000);
}

// Event listener for the "Launch Attendance Process" button
document.getElementById("startButton").addEventListener("click", function() {
    fetch('http://127.0.0.1:5000/start', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "Session started") {
            document.getElementById("videoContainer").style.display = "block";
            document.getElementById("videoFeed").src = "http://127.0.0.1:5000/video_feed";
            document.getElementById("startButton").style.display = "none";
            document.getElementById("endButton").style.display = "inline-block";
        } else {
            alert(data.error);
        }
    })
    .catch(error => console.error('Error:', error));
});

// Event listener for the "End Attendance Process" button
document.getElementById("endButton").addEventListener("click", function() {
    fetch('http://127.0.0.1:5000/end', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById("videoContainer").style.display = "none";
        document.getElementById("startButton").style.display = "inline-block";
        document.getElementById("endButton").style.display = "none";
        console.log(data);
    })
    .catch(error => console.error('Error:', error));
});

// Event listener for the "END Attendance Taking" button
document.getElementById("endAttendance").addEventListener("click", function() {
    sendAttendanceDataToServer();
    document.getElementById("videoContainer").style.display = "none";
    document.getElementById("startButton").style.display = "inline-block";
    document.getElementById("endButton").style.display = "none";
});