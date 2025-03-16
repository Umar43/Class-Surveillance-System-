document.addEventListener("DOMContentLoaded", function() {
  const addCourseForm = document.getElementById("addCourseForm");
  const addCourse = document.getElementById("addCourse");
  const overlay = document.querySelector('#overlay');
  const addFaculty = document.getElementById('addFaculty');
  const addFacultyForm = document.getElementById("addFacultyForm");

  console.log("Add Course Button:", addCourse); // Debugging
  console.log("Add Faculty Button:", addFaculty); // Debugging
  console.log("Close Buttons:", document.querySelectorAll('.close')); // Debugging

  // Open Add Course Form
  addCourse.addEventListener("click", function() {
    console.log("Add Course Button Clicked"); // Debugging
    addCourseForm.style.display = "block";
    overlay.style.display = "block";
    document.body.style.overflow = 'hidden';
  });

  // Open Add Faculty Form
  addFaculty.addEventListener("click", function() {
    console.log("Add Faculty Button Clicked"); // Debugging
    addFacultyForm.style.display = "block";
    overlay.style.display = "block";
    document.body.style.overflow = 'hidden';
  });

  // Close Forms
  var closeButtons = document.querySelectorAll('#addCourseForm .close, #addFacultyForm .close');

  closeButtons.forEach(function(closeButton) {
    closeButton.addEventListener('click', function() {
      console.log("Close Button Clicked"); // Debugging
      addCourseForm.style.display = "none";
      addFacultyForm.style.display = "none";
      overlay.style.display = 'none';
      document.body.style.overflow = 'auto';
    });
  });

  // Close Forms When Clicking Outside
  overlay.addEventListener('click', function() {
    console.log("Overlay Clicked"); // Debugging
    addCourseForm.style.display = "none";
    addFacultyForm.style.display = "none";
    overlay.style.display = 'none';
    document.body.style.overflow = 'auto';
  });
});