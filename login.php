<?php
// Session start karna taake pooray system mein user ka data yaad rahe
session_start();
include('db.php');

$error = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Database mein check karna ke email exist karti hai ya nahi
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Password ko verify karna (jo register ke waqt secure hash kiya tha)
        if (password_verify($password, $user['password'])) {
            
            // Session variables mein data save karna
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Role ke mutabiq sahi dashboard par bhejna (Redirect)
            if ($_SESSION['user_role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($_SESSION['user_role'] == 'barber') {
                header("Location: barber_dashboard.php");
            } else {
                header("Location: customer_dashboard.php");
            }
            exit(); // Code ko mazeed chalne se rokne ke liye
            
        } else {
            $error = "Incorrect Password. Please try again.";
        }
    } else {
        $error = "No account found with this email address!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Saloon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e1e24 0%, #2a2a35 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            background: rgba(255, 255, 255, 0.98);
        }
        .logo-container {
            width: 80px;
            height: 80px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -40px auto 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 5px;
        }
        .logo-container img {
            max-width: 100%;
            border-radius: 50%;
        }
        .form-label { font-weight: 600; color: #495057; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-group-text { background-color: transparent; border-right: none; color: #6c757d; }
        .form-control { border-left: none; }
        .form-control:focus { border-color: #dee2e6; box-shadow: none; }
        .input-group:focus-within .input-group-text, 
        .input-group:focus-within .form-control { border-color: #fd7e14; color: #fd7e14; }
        
        /* Password Toggle Icon */
        .password-toggle { cursor: pointer; border-left: none; background-color: transparent; border-color: #dee2e6; color: #6c757d; }
        .input-group:focus-within .password-toggle { border-color: #fd7e14; color: #fd7e14; }

        .btn-custom {
            background-color: #1e1e24;
            color: white;
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        .btn-custom:hover {
            background-color: #fd7e14;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(253, 126, 20, 0.4);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4 mt-5">
            <div class="card login-card p-4 mx-2 mt-4">
                
                <div class="logo-container">
                    <img src="logo.png" alt="Smart Saloon Logo">
                </div>

                <div class="card-body pt-0">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-dark m-0">Welcome Back</h3>
                        <p class="text-muted small mt-1">Sign in to continue to Smart Saloon</p>
                    </div>

                    <?php if(!empty($error)) { ?>
                        <div class="alert alert-danger text-center py-2 small fw-semibold shadow-sm rounded-3">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo $error; ?>
                        </div>
                    <?php } ?>

                    <form action="login.php" method="POST">
                        
                        <div class="mb-4">
                            <label class="form-label">Email Address</label>
                            <div class="input-group shadow-sm rounded-3 overflow-hidden border">
                                <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control py-2 border-0" placeholder="name@example.com" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label d-flex justify-content-between">
                                <span>Password</span>
                                <a href="#" class="text-decoration-none text-muted text-capitalize" style="font-size: 0.75rem;">Forgot?</a>
                            </label>
                            <div class="input-group shadow-sm rounded-3 overflow-hidden border">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" id="passwordField" class="form-control py-2 border-0" placeholder="••••••••" required>
                                <span class="input-group-text password-toggle" onclick="togglePassword()">
                                    <i class="fa-regular fa-eye" id="toggleIcon"></i>
                                </span>
                            </div>
                        </div>

                        <button type="submit" name="login" class="btn btn-custom w-100 py-2.5 fw-bold shadow-sm mt-2">
                            <i class="fa-solid fa-right-to-bracket me-2"></i> Secure Login
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-2 border-top">
                        <p class="mb-0 text-muted small">Don't have an account? 
                            <a href="register.php" class="text-decoration-none fw-bold" style="color: #fd7e14;">Register Here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // JS Logic for Show/Hide Password
    function togglePassword() {
        const passwordField = document.getElementById("passwordField");
        const toggleIcon = document.getElementById("toggleIcon");
        
        if (passwordField.type === "password") {
            passwordField.type = "text";
            toggleIcon.classList.remove("fa-eye");
            toggleIcon.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            toggleIcon.classList.remove("fa-eye-slash");
            toggleIcon.classList.add("fa-eye");
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>