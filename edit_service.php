<?php
session_start();
include('db.php');

// Security Check: Sirf Admin hi services ko edit kar sakta hai
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$message = ""; $msg_type = "success";
$service_id = "";
$name = ""; $price = ""; $duration = "";

// 1. READ OPERATION: URL se ID le kar purana data fields mein bharna
if (isset($_GET['id'])) {
    $service_id = $_GET['id'];
    
    $fetch_sql = "SELECT * FROM services WHERE id = '$service_id'";
    $fetch_result = mysqli_query($conn, $fetch_sql);
    
    if (mysqli_num_rows($fetch_result) > 0) {
        $service = mysqli_fetch_assoc($fetch_result);
        $name = $service['name'];
        $price = $service['price'];
        $duration = $service['duration'];
    } else {
        header("Location: manage_services.php");
        exit();
    }
} else {
    header("Location: manage_services.php");
    exit();
}

// 2. UPDATE OPERATION: Form submit hone par data update karna
if (isset($_POST['update_service'])) {
    $service_id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];

    $update_sql = "UPDATE services SET name='$name', price='$price', duration='$duration' WHERE id='$service_id'";
    
    if (mysqli_query($conn, $update_sql)) {
        // Direct success message dikha kar redirect karenge taake updated list dikhe
        header("Location: manage_services.php?msg=updated");
        exit();
    } else {
        $message = "Error updating service: " . mysqli_error($conn);
        $msg_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Grooming Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .edit-card {
            border: none;
            border-radius: 12px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-5 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="manage_services.php"><- Back to Services List</a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card edit-card shadow-sm p-3">
                <div class="card-body">
                    <h3 class="fw-bold text-dark mb-2">Edit Service Details</h3>
                    <p class="text-muted small mb-4">Modify the service attributes below to update the shop menu.</p>

                    <?php if(!empty($message)) { ?>
                        <div class="alert alert-<?php echo $msg_type; ?> shadow-sm text-center fw-semibold small"><?php echo $message; ?></div>
                    <?php } ?>

                    <form action="edit_service.php?id=<?php echo $service_id; ?>" method="POST">
                        <input type="hidden" name="id" value="<?php echo $service_id; ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Service Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Price (PKR)</label>
                            <input type="number" name="price" class="form-control" value="<?php echo $price; ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Duration (Minutes)</label>
                            <input type="number" name="duration" class="form-control" value="<?php echo $duration; ?>" required>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="manage_services.php" class="btn btn-outline-secondary w-50 fw-semibold">Cancel</a>
                            <button type="submit" name="update_service" class="btn btn-dark w-50 fw-bold" style="background-color: #1e1e24;">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>