<?php
 session_start();

require_once('classes/database.php');
$con = new database();
$error = "";
if (isset($_POST['multisave'])) {
    
    // Getting the account information
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    // Getting the personal information
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $birthday = $_POST['birthday'];
    $sex = $_POST['sex'];
  
    // Getting the address information
    $street = $_POST['user_street'];
    $barangay = $_POST['barangay_text'];
    $city = $_POST['city_text'];
    $province = $_POST['region_text'];

   // Handle file upload
   $target_dir = "uploads/";
   $original_file_name = basename($_FILES["profile_picture"]["name"]);
   // NEW CODE: Initialize $new_file_name with $original_file_name
    $new_file_name = $original_file_name; 
    $target_file = $target_dir . $original_file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $uploadOk = 1;
   
 // Check if file already exists and rename if necessary
 if (file_exists($target_file)) {
   // Generate a unique file name by appending a timestamp
   $new_file_name = pathinfo($original_file_name, PATHINFO_FILENAME) . '_' . time() . '.' . $imageFileType;
   $target_file = $target_dir . $new_file_name;
 } else {
   // Update $target_file with the original file name
   $target_file = $target_dir . $original_file_name;
}                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           
    // Check if file is an actual image or fake image
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["profile_picture"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            echo "The file " . htmlspecialchars($new_file_name) . " has been uploaded.";

            // Save the user data and the path to the profile picture in the database
            $profile_picture_path = 'uploads/'.$new_file_name; // Save the new file name (without directory)
            
            $userID = $con->signupUser($firstname, $lastname, $birthday, $sex, $email, $username, $password, $profile_picture_path);

            if ($userID) {
                // Signup successful, insert address into users_address table
                if ($con->insertAddress($userID, $street, $barangay, $city, $province)) {
                    // Address insertion successful, redirect to login page
                    header('location:index.php');
                    exit; // Stop further execution
                } else {
                    // Address insertion failed, display error message
                    $error = "Error occurred while signing up. Please try again.";
                }
            } else {
                // Signup failed, display error message
                echo "Sorry, there was an error signing up.";
            }
        } else {
            // File upload failed, display error message
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="./bootstrap-4.5.3-dist/css/bootstrap.css">
  <link rel="stylesheet" href="./bootstrap-5.3.3-dist/css/bootstrap.css">
  <!-- JQuery for Address Selector -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <title>Form with MultiStep and Address Selector</title>
  <style>
    .form-step {
      display: none;
    }
    .form-step-active {
      display: block;
    }
  </style>
</head>
<body>

<div class="container custom-container rounded-3 shadow my-5 p-3 px-5">
  <h3 class="text-center mt-4">Registration Form</h3>
  <form id="registration-form" method="post" action="" enctype="multipart/form-data" novalidate>
    <!-- Step 1 -->
    <div class="form-step form-step-active" id="step-1">
      <div class="card mt-4">
        <div class="card-header bg-info text-white">Account Information</div>
        <div class="card-body">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" name="username" id="username" placeholder="Enter username" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please enter a valid username.</div>
            <div id="usernameFeedback" class="invalid-feedback"></div> <!-- New feedback div -->
        </div>
          <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please enter a valid email.</div>
            <div id="emailFeedback" class="invalid-feedback"></div> <!-- New feedback div -->
          </div>
          <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" name="password" placeholder="Enter password" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one special character.</div>
          </div>

          <div class="form-group">
            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" class="form-control" name="confirmPassword" placeholder="Re-enter your password" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please confirm your password.</div>
          </div>

        </div>
      </div>
      <button type="button" id="nextButton" class="btn btn-primary mt-3" onclick="nextStep()">Next</button>
    </div>
<!-- Step 2 -->
    <div class="form-step" id="step-2">
          <!-- Step 2: Personal Information -->
          <div class="card mt-4">
            <div class="card-header bg-info text-white">Personal Information</div>
            <div class="card-body">
              <div class="form-row">
                <div class="form-group col-md-6 col-sm-12">
                  <label for="firstName">First Name:</label>
                  <input type="text" class="form-control" name="firstname" placeholder="Enter first name" required>
                  <div class="valid-feedback">Looks good!</div>
                  <div class="invalid-feedback">Please enter a valid first name.</div>
                </div>
                <div class="form-group col-md-6 col-sm-12">
                  <label for="lastName">Last Name:</label>
                  <input type="text" class="form-control" name="lastname" placeholder="Enter last name" required>
                  <div class="valid-feedback">Looks good!</div>
                  <div class="invalid-feedback">Please enter a valid last name.</div>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="birthday">Birthday:</label>
                  <input type="date" class="form-control" name="birthday" id="birthday" required>
                  <div class="valid-feedback">Great!</div>
                  <div class="invalid-feedback">Please enter a valid birthday.</div>
                </div>
                <div class="form-group col-md-6">
                  <label for="sex">Sex:</label>
                  <select class="form-control" name="sex" required>
                    <option selected disabled value="">Select Sex</option>
                    <option>Male</option>
                    <option>Female</option>
                  </select>
                  <div class="valid-feedback">Looks good.</div>
                  <div class="invalid-feedback">Please select a sex.</div>
                </div>
              </div>

              <div class="form-group">
                <label for="profilePicture">Profile Picture:</label>
                <input type="file" class="form-control" name="profile_picture" accept="image/*" required>
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback">Please upload a profile picture.</div>
            </div>
      <button type="button" class="btn btn-secondary mt-3" onclick="prevStep()">Previous</button>
      <button type="button" class="btn btn-primary mt-3" onclick="nextStep()">Next</button>
    </div>
    </div>
    </div>

    <!-- Step 3 -->
    <div class="form-step" id="step-3">
      <div class="card mt-4">
        <div class="card-header bg-info text-white">Address Information</div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Region<span class="text-danger"> *</span></label>
            <select name="user_region" class="form-control form-control-md" id="region"></select>
            <input type="hidden" class="form-control form-control-md" name="region_text" id="region-text">
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please select a region.</div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label class="form-label">Province<span class="text-danger"> *</span></label>
              <select name="user_province" class="form-control form-control-md" id="province"></select>
              <input type="hidden" class="form-control form-control-md" name="province_text" id="province-text" required>
              <div class="valid-feedback">Looks good!</div>
              <div class="invalid-feedback">Please select your province.</div>
            </div>
            <div class="form-group col-md-6">
              <label class="form-label">City / Municipality<span class="text-danger"> *</span></label>
              <select name="user_city" class="form-control form-control-md" id="city"></select>
              <input type="hidden" class="form-control form-control-md" name="city_text" id="city-text" required>
              <div class="valid-feedback">Looks good!</div>
              <div class="invalid-feedback">Please select your city/municipality.</div>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Barangay<span class="text-danger"> *</span></label>
            <select name="user_barangay" class="form-control form-control-md" id="barangay"></select>
            <input type="hidden" class="form-control form-control-md" name="barangay_text" id="barangay-text" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please select your barangay.</div>
          </div>
          <div class="form-group">
            <label class="form-label">Street <span class="text-danger"> *</span></label>
            <input type="text" class="form-control form-control-md" name="user_street" id="street-text" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please select your street.</div>
          </div>
        </div>
      </div>
      <button type="button" class="btn btn-secondary mt-3" onclick="prevStep()">Previous</button>
      <button type="submit" name="multisave" class="btn btn-primary mt-3">Sign Up</button>
      <a class="btn btn-outline-danger mt-3" href="index.php">Go Back</a>
    </div>
  </form>
</div>

<script src="./bootstrap-5.3.3-dist/js/bootstrap.js"></script>
<!-- Script for Address Selector -->
<script src="ph-address-selector.js"></script>
<!-- AJAX for live checking of existing usernames -->
<script>
$(document).ready(function(){
    $('#username').on('input', function(){
        var username = $(this).val();
        if(username.length > 0) {
            $.ajax({
                url: 'check_username.php',
                method: 'POST',
                data: {username: username},
                dataType: 'json',
                success: function(response) {
                    if(response.exists) {
                        $('#username').removeClass('is-valid').addClass('is-invalid');
                        $('#usernameFeedback').text('Username is already taken.');
                        $('#nextButton').prop('disabled', true); // Disable the Next button
                    } else {
                        $('#username').removeClass('is-invalid').addClass('is-valid');
                        $('#usernameFeedback').text('');
                        $('#nextButton').prop('disabled', false); // Enable the Next button
                    }
                }
            });
        } else {
            $('#username').removeClass('is-valid is-invalid');
            $('#usernameFeedback').text('');
            $('#nextButton').prop('disabled', false); // Enable the Next button if username is empty
        }
    });
});

</script>
<!-- AJAX for live checking of existing emails -->
<script>
$(document).ready(function(){
    $('#email').on('input', function(){
        var email = $(this).val();
        if(email.length > 0) {
            $.ajax({
                url: 'check_email.php',
                method: 'POST',
                data: {email: email},
                dataType: 'json',
                success: function(response) {
                    if(response.exists) {
                        $('#email').removeClass('is-valid').addClass('is-invalid');
                        $('#emailFeedback').text('Email is already taken.');
                        $('#nextButton').prop('disabled', true); // Disable the Next button
                    } else {
                        $('#email').removeClass('is-invalid').addClass('is-valid');
                        $('#emailFeedback').text('');
                        $('#nextButton').prop('disabled', false); // Enable the Next button
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        } else {
            $('#email').removeClass('is-valid is-invalid');
            $('#emailFeedback').text('');
            $('#nextButton').prop('disabled', false); // Enable the Next button if email is empty
        }
    });
});
</script>

<!-- Script for Form Validation -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
      const form = document.querySelector("form");
      const birthdayInput = document.getElementById("birthday");
      const steps = document.querySelectorAll(".form-step");
      let currentStep = 0;

  
      // Set the max attribute of the birthday input to today's date
      const today = new Date().toISOString().split('T')[0];    
      birthdayInput.setAttribute('max', today);

      // Add event listeners for real-time validation
      const inputs = form.querySelectorAll("input, select");
      inputs.forEach(input => {
        input.addEventListener("input", () => validateInput(input));
        input.addEventListener("change", () => validateInput(input));
      });

      //MultiStep Logic 
  // Add an event listener to the form's submit event
  form.addEventListener("submit", (event) => {
  // Prevent form submission if the current step is not valid
  if (!validateStep(currentStep)) {
    event.preventDefault();
    event.stopPropagation();
  }

  // Add the 'was-validated' class to the form for Bootstrap styling
  form.classList.add("was-validated");
}, false);

// Function to move to the next step
window.nextStep = () => {
  // Only proceed to the next step if the current step is valid
  if (validateStep(currentStep)) {
    steps[currentStep].classList.remove("form-step-active"); // Hide the current step
    currentStep++; // Increment the current step index
    steps[currentStep].classList.add("form-step-active"); // Show the next step
  }
};

// Function to move to the previous step
window.prevStep = () => {
  steps[currentStep].classList.remove("form-step-active"); // Hide the current step
  currentStep--; // Decrement the current step index
  steps[currentStep].classList.add("form-step-active"); // Show the previous step
};

// Function to validate all inputs in the current step
function validateStep(step) {
  let valid = true;
  // Select all input and select elements in the current step
  const stepInputs = steps[step].querySelectorAll("input, select");

  // Validate each input element
  stepInputs.forEach(input => {
    if (!validateInput(input)) {
      valid = false; // If any input is invalid, set valid to false
    }
  });

  return valid; // Return the overall validity of the step
}

  
      function validateInput(input) {
        if (input.name === 'password') {
          return validatePassword(input);
        } else if (input.name === 'confirmPassword') {
          return validateConfirmPassword(input);
        } else {
          if (input.checkValidity()) {
            input.classList.remove("is-invalid");
            input.classList.add("is-valid");
            return true;
          } else {
            input.classList.remove("is-valid");
            input.classList.add("is-invalid");
            return false;
          }
        }
      }
  
      function validatePassword(passwordInput) {
        const password = passwordInput.value;
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        if (regex.test(password)) {
          passwordInput.classList.remove("is-invalid");
          passwordInput.classList.add("is-valid");
          return true;
        } else {
          passwordInput.classList.remove("is-valid");
          passwordInput.classList.add("is-invalid");
          return false;
        }
      }
  
      function validateConfirmPassword(confirmPasswordInput) {
        const passwordInput = form.querySelector("input[name='password']");
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
      
        if (password === confirmPassword && password !== '') {
          confirmPasswordInput.classList.remove("is-invalid");
          confirmPasswordInput.classList.add("is-valid");
          return true;
        } else {
          confirmPasswordInput.classList.remove("is-valid");
          confirmPasswordInput.classList.add("is-invalid");
          return false;
        }
      }

       document.addEventListener("keydown", (event) => {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission
        }
    });

      
    
});</script>
  
  </body>
  </html>
  

