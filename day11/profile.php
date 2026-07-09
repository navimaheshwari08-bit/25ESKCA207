<?php
// Dummy data mimicking a session fetch from your MySQL user record
$user = [
    'name' => 'Samriddhi',
    'email' => 'samriddhi@example.com',
    'profile_picture' => 'default-avatar.png' // Or actual database path reference
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile & Security</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

    <!-- REQUIRED FEATURE: Profile Picture in Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fa-solid fa-shield-halved me-2"></i>AuthSystem</a>
            <div class="ms-auto d-flex align-items-center">
                <span class="text-white me-3 d-none d-sm-inline">Welcome, <?php echo $user['name']; ?></span>
                <!-- Dynamic Avatar in Navbar -->
                <img src="<?php echo $user['profile_picture']; ?>" alt="Navbar Avatar" class="rounded-circle border border-2 border-light object-fit-cover" width="40" height="40" onerror="this.src='https://via.placeholder.com/40?text=U'">
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row g-4 justify-content-center">
            
            <!-- REQUIRED FEATURE: Profile Page Avatar Presentation Card -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 text-center p-4">
                    <div class="my-3">
                        <img src="<?php echo $user['profile_picture']; ?>" alt="Profile Picture" class="rounded-circle border img-thumbnail object-fit-cover shadow-sm mb-3" width="130" height="130" onerror="this.src='https://via.placeholder.com/130?text=User'">
                        <h4 class="fw-bold mb-0"><?php echo $user['name']; ?></h4>
                        <small class="text-muted"><?php echo $user['email']; ?></small>
                    </div>
                    <hr class="opacity-25">
                    <div class="d-grid">
                        <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-camera me-2"></i>Update Picture</button>
                    </div>
                </div>
            </div>

            <!-- REQUIRED FEATURE: Change Password UI -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-key me-2 text-warning"></i>Change Password</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="" method="POST" id="changePasswordForm">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Current Password</label>
                                <input type="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">New Password</label>
                                <input type="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold small">Confirm New Password</label>
                                <input type="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <button type="submit" class="btn btn-dark fw-bold px-4">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>v