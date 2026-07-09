<?php
include 'db.php';

// ==========================================
// 1. BETTER DASHBOARD: FETCH AGGREGATE STATS
// ==========================================
$total_students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$avg_cgpa = $conn->query("SELECT AVG(cgpa) as avg_cgpa FROM students")->fetch_assoc()['avg_cgpa'];
$avg_cgpa = number_format((float)$avg_cgpa, 2);

// Count per branch/course
$branch_counts = $conn->query("SELECT course, COUNT(*) as count FROM students GROUP BY course");

// ==========================================
// 2. FILTERS & MULTI-FIELD SEARCH LOGIC
// ==========================================
// Default filter status: Active (unless 'all' or 'Inactive' is explicitly requested)
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'Active';
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build basic dynamic query
$sql = "SELECT * FROM students WHERE 1=1";
$params = [];
$types = "";

// Filter by Status
if ($status_filter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// Filter by Course/Branch
if (!empty($course_filter)) {
    $sql .= " AND course = ?";
    $params[] = $course_filter;
    $types .= "s";
}

// Multi-field search (Name, Email, Course, or CGPA)
if (!empty($search_query)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR course LIKE ? OR cgpa LIKE ?)";
    $like_search = "%" . $search_query . "%";
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
    $types .= "ssss";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advanced Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light p-4">
<div class="container-fluid max-width-xl">

    <!-- BETTER DASHBOARD: STATS ROW -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Students</h6>
                        <h2 class="mb-0 fw-bold"><?php echo $total_students; ?></h2>
                    </div>
                    <i class="fa-solid fa-users fa-2xl opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-success text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Average CGPA</h6>
                        <h2 class="mb-0 fw-bold"><?php echo $avg_cgpa; ?></h2>
                    </div>
                    <i class="fa-solid fa-chart-line fa-2xl opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-dark text-white p-3">
                <h6 class="text-uppercase mb-2">Students per Branch</h6>
                <div class="small" style="max-height: 40px; overflow-y: auto;">
                    <?php while($b = $branch_counts->fetch_assoc()): ?>
                        <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($b['course']); ?>: <?php echo $b['count']; ?></span>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTROLS: FILTERS & SEARCH BAR -->
    <div class="card shadow-sm border-0 p-3 mb-4">
        <form method="GET" action="" class="row g-3 align-items-end">
            <!-- Multi-field Search -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, CGPA..." value="<?php echo htmlspecialchars($search_query); ?>">
            </div>
            
            <!-- Course Filter -->
            <div class="col-md-3">
                <label class="form-label fw-semibold">Course Filter</label>
                <select name="course" class="form-select">
                    <option value="">All Courses</option>
                    <option value="Computer Science & Engineering" <?php if($course_filter == 'Computer Science & Engineering') echo 'selected'; ?>>CSE</option>
                    <option value="Information Technology" <?php if($course_filter == 'Information Technology') echo 'selected'; ?>>IT</option>
                    <option value="Data Science & AI" <?php if($course_filter == 'Data Science & AI') echo 'selected'; ?>>Data Science & AI</option>
                </select>
            </div>

            <!-- Student Status Filter -->
            <div class="col-md-3">
                <label class="form-label fw-semibold">Status Filter</label>
                <select name="status" class="form-select">
                    <option value="Active" <?php if($status_filter == 'Active') echo 'selected'; ?>>Active Only (Default)</option>
                    <option value="Inactive" <?php if($status_filter == 'Inactive') echo 'selected'; ?>>Inactive Only</option>
                    <option value="all" <?php if($status_filter == 'all') echo 'selected'; ?>>View All Students</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="col-md-2 d-grid gap-2 d-md-flex">
                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-magnifying-glass"></i></button>
                <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>

    <!-- DATA TABLE -->
    <div class="card shadow-sm border-0 p-3">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>CGPA</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <!-- Profile Photo Output -->
                        <td>
                            <img src="uploads/<?php echo !empty($row['photo']) ? $row['photo'] : 'default-avatar.png'; ?>" 
                                 class="rounded-circle object-fit-cover" width="45" height="45" 
                                 onerror="this.src='https://via.placeholder.com/45?text=User'">
                        </td>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                        <td><span class="badge bg-light text-dark border fw-bold"><?php echo htmlspecialchars($row['cgpa']); ?></span></td>
                        <td>
                            <span class="badge <?php echo $row['status'] == 'Active' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No students found matching those criteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>