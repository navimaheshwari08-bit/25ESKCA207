<?php
session_start();

// 1. DATABASE CONFIGURATION INLINE
$host = "localhost"; $user = "root"; $pass = ""; $dbname = "student_management";
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) { die("Database Connection Failed: " . $conn->connect_error); }

// 2. ROUTER LOGIC: LOGOUT OPERATION
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// 3. AUTHENTICATION POST HANDLER
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password']) || $password === $user['password']) { 
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit();
        } else { $error = "Invalid password credential."; }
    } else { $error = "No account found with that email address."; }
}

$view = $_GET['action'] ?? 'login'; // Determine UI view state
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $view === 'forgot' ? 'Forgot Password' : 'Login Portal'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: #f4f6f9; min-height: 100vh; display: flex; align-items: center; }
        .animated-card { animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-10 col-md-6 col-lg-5">

                <!-- VIEW A: LOGIN SCREEN -->
                <?php if ($view === 'login'): ?>
                <div class="card shadow border-0 p-4 animated-card">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-dark">Welcome Back</h2>
                        <p class="text-muted small">Log in to manage your student registry portal</p>
                    </div>

                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger border-0 small shadow-sm"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if(isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
                        <div class="alert alert-warning border-0 small shadow-sm">🔒 Session guarded. Please sign in first.</div>
                    <?php endif; ?>

                    <form method="POST" action="index.php?action=login">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="admin@domain.com" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" name="login_submit" class="btn btn-primary fw-bold py-2 shadow-sm">Sign In</button>
                        </div>
                        <div class="text-center">
                            <a href="index.php?action=forgot" class="text-decoration-none small text-muted">Forgot Password?</a>
                        </div>
                    </form>
                </div>

                <!-- VIEW B: FORGOT PASSWORD SCREEN (WITH SCREEN-SWAP CODES) -->
                <?php elseif ($view === 'forgot'): ?>
                <div class="card shadow border-0 p-4 animated-card">
                    <div class="text-center p-3">
                        <div class="bg-light d-inline-block p-3 rounded-circle text-primary mb-3">
                            <i class="fa-solid fa-lock-open fa-2xl"></i>
                        </div>
                        <h3 class="fw-bold text-dark">Forgot Password?</h3>
                        <p class="text-muted small">Enter your email and we'll distribute a password reset link.</p>
                    </div>

                    <div id="confirmationMessage" class="alert alert-success d-none border-0 shadow-sm mb-4" role="alert">
                        <div class="d-flex">
                            <i class="fa-solid fa-envelope-circle-check fa-xl me-3 mt-1"></i>
                            <div>
                                <h6 class="alert-heading fw-bold mb-1">Link Dispatched!</h6>
                                <p class="mb-0 small text-secondary">A validation link has been sent to <strong id="targetEmail"></strong>.</p>
                            </div>
                        </div>
                    </div>

                    <form id="forgotPasswordForm">
                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Email Address</label>
                            <input type="email" id="recoveryEmail" class="form-control" placeholder="yourname@domain.com" required>
                        </div>
                        <div class="d-grid mb-2">
                            <button type="submit" class="btn btn-primary fw-bold py-2 shadow-sm">Send Reset Link</button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="index.php?action=login" class="text-decoration-none small text-muted"><i class="fa-solid fa-arrow-left me-1"></i> Back to Login</a>
                        </div>
                    </form>

                    <div id="backToLoginContainer" class="text-center d-none">
                        <a href="index.php?action=login" class="btn btn-outline-secondary btn-sm mt-2"><i class="fa-solid fa-arrow-left me-1"></i> Back to Login</a>
                    </div>
                </div>
                <script>
                    document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
                        e.preventDefault();
                        document.getElementById('targetEmail').innerText = document.getElementById('recoveryEmail').value;
                        document.getElementById('confirmationMessage').classList.remove('d-none');
                        this.classList.add('d-none');
                        document.getElementById('backToLoginContainer').classList.remove('d-none');
                    });
                </script>
                <?php endif; ?>

            </div>
        </div>
    </div>
</body>
</html>