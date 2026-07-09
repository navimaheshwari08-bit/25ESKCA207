<?php
// Initialize error array and input variables
$errors = [];
$name = $address = $gender = $course = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Name Validation (Required, No numbers)
    if (empty($_POST["name"])) {
        $errors[] = "Name is required.";
    } else {
        $name = trim($_POST["name"]);
        if (preg_match('/[0-9]/', $name)) {
            $errors[] = "Name cannot contain numbers.";
        }
    }

    // 2. Gender Validation (Required)
    if (empty($_POST["gender"])) {
        $errors[] = "Gender selection is required.";
    } else {
        $gender = $_POST["gender"];
    }

    // 3. Course Validation (Required)
    if (empty($_POST["course"])) {
        $errors[] = "Please select a course.";
    } else {
        $course = $_POST["course"];
    }

    // 4. Address Validation (Required, minimum length of 10 characters)
    if (empty($_POST["address"])) {
        $errors[] = "Address is required.";
    } else {
        $address = trim($_POST["address"]);
        if (strlen($address) < 10) {
            $errors[] = "Address must be at least 10 characters long.";
        }
    }

    // If there are no errors, redirect/forward to confirmation page using sessions
    if (empty($errors)) {
        session_start();
        $_SESSION['student_data'] = [
            'name' => $name,
            'gender' => $gender,
            'course' => $course,
            'address' => $address,
            'photo_name' => !empty($_FILES['profile_photo']['name']) ? $_FILES['profile_photo']['name'] : 'No photo uploaded (UI Placeholder)'
        ];
        header("Location: confirmation.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h3>Student Registration Form</h3>
                </div>
                <div class="card-body p-4">

                    <!-- Styled Error Box -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <h5 class="alert-heading fw-bold">Please correct the following errors:</h5>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                        
                        <!-- Name Field -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="John Doe">
                        </div>

                        <!-- Gender (Radio Buttons) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">Gender</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" <?php if ($gender == "Male") echo "checked"; ?>>
                                <label class="form-check-input-label" for="genderMale">Male</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female" <?php if ($gender == "Female") echo "checked"; ?>>
                                <label class="form-check-input-label" for="genderFemale">Female</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="genderOther" value="Other" <?php if ($gender == "Other") echo "checked"; ?>>
                                <label class="form-check-input-label" for="genderOther">Other</label>
                            </div>
                        </div>

                        <!-- Course (Dropdown) -->
                        <div class="mb-3">
                            <label for="course" class="form-label fw-bold">Course Selection</label>
                            <select class="form-select" id="course" name="course">
                                <option value="" selected disabled>Choose a course...</option>
                                <option value="Computer Science" <?php if ($course == "Computer Science") echo "selected"; ?>>Computer Science</option>
                                <option value="Information Technology" <?php if ($course == "Information Technology") echo "selected"; ?>>Information Technology</option>
                                <option value="Business Administration" <?php if ($course == "Business Administration") echo "selected"; ?>>Business Administration</option>
                                <option value="Digital Marketing" <?php if ($course == "Digital Marketing") echo "selected"; ?>>Digital Marketing</option>
                            </select>
                        </div>

                        <!-- Address (Textarea) -->
                        <div class="mb-3">
                            <label for="address" class="form-label fw-bold">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter your full home address (min 10 characters)"><?php echo htmlspecialchars($address); ?></textarea>
                        </div>

                        <!-- Photo Upload UI (Front-end UI only as requested) -->
                        <div class="mb-4">
                            <label for="profile_photo" class="form-label fw-bold">Profile Photo (UI Placeholder)</label>
                            <input class="form-control" type="file" id="profile_photo" name="profile_photo" accept="image/*">
                            <div class="form-text text-muted">Nicely styled file input. File handling backend is mock-only.</div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-block">Register Student</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
