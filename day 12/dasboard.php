<?php
session_start();

// 1. INLINE SESSION GUARD
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=unauthorized");
    exit();
}

// 2. INLINE DATABASE CONNECTION
$host = "localhost"; $user = "root"; $pass = ""; $dbname = "student_management";
$conn = new mysqli($host, $user, $pass, $dbname);

// 3. STUDENT REGISTRATION LOGIC (CREATE)
$success_msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'register') {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $course = $_POST['course'];
    $cgpa = floatval($_POST['cgpa']);
    $address = $_POST['address'];
    $status = $_POST['status'];

    // Handle Image Upload
    $photoName = "default-avatar.png";
    if (isset($_FILES['photoUpload']) && $_FILES['photoUpload']['error'] == 0) {
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        $photoName = time() . '_' . basename($_FILES['photoUpload']['name']);
        move_uploaded_file($_FILES['photoUpload']['tmp_name'], "uploads/" . $photoName);
    }

    $stmt = $conn->prepare("INSERT INTO students (name, email, gender, course, cgpa, address, photo, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdsss", $fullName, $email, $gender, $course, $cgpa, $address, $photoName, $status);
    if($stmt->execute()) { $success_msg = "Student record registered successfully!"; }
}

// 4. METRICS AGGREGATION PIPELINES
$total_students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$avg_cgpa = $conn->query("SELECT AVG(cgpa) as avg_cgpa FROM students")->fetch_assoc()['avg_cgpa'];
$avg_cgpa = number_format((float)$avg_cgpa, 2);
$branch_counts = $conn->query("SELECT course, COUNT(*) as count FROM students GROUP BY course");

// 5. SEARCH & FILTER ENGINES (READ)
$status_filter = $_GET['status'] ?? 'Active';
$course_filter = $_GET['course'] ?? '';
$search_query = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM students WHERE 1=1";
$params = []; $types = "";

if ($status_filter !== 'all') { $sql .= " AND status = ?"; $params[] = $status_filter; $types .= "s"; }
if (!empty($course_filter)) { $sql .= " AND course = ?"; $params[] = $course_filter; $types .= "s"; }
if (!empty($search_query)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR course LIKE ? OR cgpa LIKE ?)";
    $like = "%" . $search_query . "%";
    array_push($params, $like, $like, $like, $like);
    $types .= "ssss";
}
$sql .= " ORDER BY date_registered DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$main_results = $stmt->get_result();

// Recent 5 Entries Widget query
$recent_results = $conn->query("SELECT name, course FROM students ORDER BY date_registered DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Administrative Dashboard Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: #f4f6f9; }
        .animated-card { animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <!-- NAVBAR INTERFACE LAYER -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="#"><i class="fa-solid fa-graduation-cap me-2"></i>EduRegistry Pro</a>
            <div class="ms-auto d-flex align-items-center">
                <span class="text-white me-3 d-none d-sm-inline">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="index.php?action=logout" class="btn btn-sm btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-4">
        
        <!-- AGGREGATE SUMMARY STATS ROW -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-primary text-white p-3 animated-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="text-uppercase mb-1 small text-white-50">Total Enrolled</h6><h2 class="mb-0 fw-bold"><?php echo $total_students; ?></h2></div>
                        <i class="fa-solid fa-users fa-2xl opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-success text-white p-3 animated-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="text-uppercase mb-1 small text-white-50">Average Score</h6><h2 class="mb-0 fw-bold"><?php echo $avg_cgpa; ?> CGPA</h2></div>
                        <i class="fa-solid fa-chart-line fa-2xl opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-dark text-white p-3 animated-card">
                    <h6 class="text-uppercase mb-2 small text-muted">Distribution Metrics</h6>
                    <div class="small">
                        <?php while($b = $branch_counts->fetch_assoc()): ?>
                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($b['course']); ?>: <?php echo $b['count']; ?></span>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- MANAGEMENT SUBMISSION CAPTURE DOCK -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 p-4 animated-card">
                    <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-user-plus text-primary me-2"></i>New Registration</h5>
                    <?php if(!empty($success_msg)): ?>
                        <div class="alert alert-success border-0 small py-2"><?php echo $success_msg; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="dashboard.php" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="register">
                        <div class="mb-2">
                            <label class="form-label small mb-1 fw-semibold">Full Name</label>
                            <input type="text" name="fullName" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1 fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control form-control-sm" required>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small mb-1 fw-semibold">Gender</label>
                                <select name="gender" class="form-select form-select-sm">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1 fw-semibold">CGPA</label>
                                <input type="number" step="0.01" max="10" name="cgpa" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1 fw-semibold">Course Choice</label>
                            <select name="course" class="form-select form-select-sm" required>
                                <option value="Computer Science & Engineering">Computer Science & Engineering</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Data Science & AI">Data Science & AI</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1 fw-semibold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1 fw-semibold">Address</label>
                            <textarea name="address" class="form-control form-control-sm" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small mb-1 fw-semibold">Photo Upload</label>
                            <input type="file" id="imageInput" name="photoUpload" class="form-control form-control-sm" accept="image/*">
                            <div class="mt-2 text-center">
                                <img id="previewImage" src="#" alt="Preview" class="img-thumbnail d-none shadow-sm" style="max-width: 80px; max-height: 80px; border-radius: 50%;">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold py-2 shadow-sm">Save Student Record</button>
                    </form>
                </div>

                <!-- RECENT REGISTRATIONS WIDGET PANEL -->
                <div class="card shadow-sm border-0 mt-4 animated-card">
                    <div class="card-header bg-dark text-white py-2"><small class="fw-bold"><i class="fa-solid fa-clock-history me-1"></i> Recent Registrations Widget</small></div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0 small text-muted">
                            <tbody>
                                <?php while($r = $recent_results->fetch_assoc()): ?>
                                <tr>
                                    <td class="p-2"><strong><?php echo htmlspecialchars($r['name']); ?></strong></td>
                                    <td class="p-2"><?php echo htmlspecialchars($r['course']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SEARCH SYSTEM ENGINE AND MAIN TABLE OUTPUT -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 p-3 mb-3 animated-card">
                    <form method="GET" action="dashboard.php" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small mb-1 fw-semibold text-muted">Multi-Field Search</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Parameters..." value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1 fw-semibold text-muted">Branch</label>
                            <select name="course" class="form-select form-select-sm">
                                <option value="">All Branches</option>
                                <option value="Computer Science & Engineering" <?php if($course_filter == 'Computer Science & Engineering') echo 'selected'; ?>>CSE</option>
                                <option value="Information Technology" <?php if($course_filter == 'Information Technology') echo 'selected'; ?>>IT</option>
                                <option value="Data Science & AI" <?php if($course_filter == 'Data Science & AI') echo 'selected'; ?>>Data Science & AI</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1 fw-semibold text-muted">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="Active" <?php if($status_filter == 'Active') echo 'selected'; ?>>Active Only</option>
                                <option value="Inactive" <?php if($status_filter == 'Inactive') echo 'selected'; ?>>Inactive Only</option>
                                <option value="all" <?php if($status_filter == 'all') echo 'selected'; ?>>View All</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-filter"></i></button>
                            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-sync"></i></a>
                        </div>
                    </form>
                </div>

                <!-- CORE DATA ENGINE DISPLAY TABLE -->
                <div class="card shadow-sm border-0 p-0 animated-card table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary small">
                            <tr>
                                <th>Avatar</th>
                                <th>Identity Info</th>
                                <th>Course Branch</th>
                                <th>CGPA</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if ($main_results->num_rows > 0): ?>
                                <?php while($row = $main_results->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <img src="uploads/<?php echo $row['photo']; ?>" class="rounded-circle shadow-sm" width="40" height="40" style="object-fit: cover;" onerror="this.src='https://via.placeholder.com/40?text=User'">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($row['email']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['course']); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo $row['cgpa']; ?></span></td>
                                    <td>
                                        <span class="badge <?php echo $row['status'] == 'Active' ? 'bg-success' : 'bg-danger'; ?>"><?php echo $row['status']; ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No student criteria records match this search profile context.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- CLIENT SIDE REALTIME IMAGE FILE READ PREVIEW ENGINE -->
    <script>
        document.getElementById('imageInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('previewImage');
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>