<?php
include('db.php');

$message = "";
$msg_type = "danger";

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Pehle check karna ke email pehle se exist toh nahi karti
    $check_email = "SELECT * FROM users WHERE email = '$email'";
    $run_check = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($run_check) > 0) {
        $message = "THIS EMAIL IS ALREADY REGISTERED.";
        $msg_type = "danger";
    } else {
        // Password ko secure hash mein convert karna (taake password_verify kaam kare)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Database mein insert karna
        $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', '$role')";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Account successfully created! <a href='login.php' class='alert-link text-decoration-none fw-bold'>Login from here</a>";
            $msg_type = "success";
        } else {
            $message = "Error: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Saloon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e1e24 0%, #2a2a35 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .register-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            background: rgba(255, 255, 255, 0.98);
            margin-top: 50px; /* Space for the floating logo */
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
        
        /* Input Styling */
        .form-label { font-weight: 600; color: #495057; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-group-text { background-color: transparent; border-right: none; color: #6c757d; }
        .form-control, .form-select { border-left: none; }
        .form-control:focus, .form-select:focus { border-color: #dee2e6; box-shadow: none; }
        .input-group:focus-within .input-group-text, 
        .input-group:focus-within .form-control,
        .input-group:focus-within .form-select { border-color: #fd7e14; color: #fd7e14; }
        
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
<body class="d-flex align-items-center justify-content-center py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card register-card p-4 mx-2">
                
                <div class="logo-container">
                    <img src="logo.png" alt="Smart Saloon Logo">
                </div>

                <div class="card-body pt-0">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-dark m-0">Create an Account</h3>
                        <p class="text-muted small mt-1">Join Smart Saloon to book your sessions</p>
                    </div>

                    <?php if(!empty($message)) { ?>
                        <div class="alert alert-<?php echo $msg_type; ?> text-center py-2 small fw-semibold shadow-sm rounded-3">
                            <?php if($msg_type == 'danger') { ?>
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            <?php } else { ?>
                                <i class="fa-solid fa-circle-check me-1"></i>
                            <?php } ?>
                            <?php echo $message; ?>
                        </div>
                    <?php } ?>

                    <form action="register.php" method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <div class="input-group shadow-sm rounded-3 overflow-hidden border">
                                <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                                <input type="text" name="name" class="form-control py-2 border-0" placeholder="FULL NAME" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="input-group shadow-sm rounded-3 overflow-hidden border">
                                <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control py-2 border-0" placeholder="name@example.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group shadow-sm rounded-3 overflow-hidden border">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" id="passwordField" class="form-control py-2 border-0" placeholder="••••••••" required>
                                <span class="input-group-text password-toggle" onclick="togglePassword()">
                                    <i class="fa-regular fa-eye" id="toggleIcon"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Select Your Role</label>
                            <div class="input-group shadow-sm rounded-3 overflow-hidden border">
                                <span class="input-group-text"><i class="fa-solid fa-user-tag"></i></span>
                                <select name="role" class="form-select py-2 border-0" required>
                                    <option value="" disabled selected>Choose your account type...</option>
                                    <option value="customer">Customer (Want a haircut/trim)</option>
                                    <option value="barber">Barber / Stylist (Shop employee)</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" name="register" class="btn btn-custom w-100 py-2.5 fw-bold shadow-sm mt-2">
                            <i class="fa-solid fa-user-plus me-2"></i> Register Now
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-0 text-muted small">Already have an account? 
                            <a href="login.php" class="text-decoration-none fw-bold" style="color: #fd7e14;">Login Here</a>
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