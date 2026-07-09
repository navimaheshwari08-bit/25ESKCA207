<?php
session_start();

// Redirect back to form if no data exists
if (!isset($_SESSION['student_data'])) {
    header("Location: register.php");
    exit();
}

$student = $_SESSION['student_data'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmation</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h3>🎉 Registration Successful!</h3>
                </div>
                <div class="card-body p-4">
                    <h5 class="card-title mb-4 text-secondary border-bottom pb-2">Submitted Student Details</h5>
                    
                    <table class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                <th scope="row" class="w-30 bg-light">Full Name</th>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="bg-light">Gender</th>
                                <td><?php echo htmlspecialchars($student['gender']); ?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="bg-light">Course</th>
                                <td><?php echo htmlspecialchars($student['course']); ?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="bg-light">Address</th>
                                <td><?php echo nl2br(htmlspecialchars($student['address'])); ?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="bg-light">Uploaded File Name</th>
                                <td><code class="text-dark"><?php echo htmlspecialchars($student['photo_name']); ?></code></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="text-center mt-4">
                        <a href="register.php" class="btn btn-outline-primary">Register Another Student</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>