<?php
include 'db.php';
$result = $conn->query("SELECT * FROM students ORDER BY date_registered DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container card shadow p-4">
        <h2 class="mb-4">Registered Students</h2>
        
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Course</th>
                    <th>Address</th>
                    <th>Date Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if(!empty($row['photo'])): ?>
                            <img src="uploads/<?php echo $row['photo']; ?>" class="rounded-circle" width="50" height="50" style="object-fit: cover;">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/50" class="rounded-circle" width="50" height="50">
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['gender']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['course']); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <!-- Showing the TIMESTAMP data field -->
                    <td><small class="text-muted"><?php echo date('d M Y, h:i A', strtotime($row['date_registered'])); ?></small></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>