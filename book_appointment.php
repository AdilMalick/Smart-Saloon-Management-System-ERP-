<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'customer') {
    header("Location: login.php");
    exit();
}

$message = ""; $msg_type = "success";

// 1. Barbers List for dropdown
$barbers_query = "SELECT id, name FROM users WHERE role = 'barber'";
$barbers_result = mysqli_query($conn, $barbers_query);

// 2. Services List for dropdown (with price and duration)
$services_query = "SELECT id, name, price, duration FROM services";
$services_result = mysqli_query($conn, $services_query);

// 3. Process Booking Submission
if (isset($_POST['book_now'])) {
    $customer_id = $_SESSION['user_id']; 
    $barber_id = $_POST['barber_id'];
    $service_id = $_POST['service_id'];
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];

    // Duration extraction for selected service
    $current_service_query = "SELECT duration FROM services WHERE id = '$service_id'";
    $current_service_res = mysqli_query($conn, $current_service_query);
    $current_service_data = mysqli_fetch_assoc($current_service_res);
    $new_duration = $current_service_data['duration'];

    // Overlap Verification check
    $check_slot_sql = "SELECT appointments.*, services.name AS active_service_name 
                       FROM appointments 
                       JOIN services ON appointments.service_id = services.id
                       WHERE appointments.barber_id = '$barber_id' 
                         AND appointments.booking_date = '$booking_date' 
                         AND appointments.status != 'cancelled'
                         AND '$booking_time' < ADDTIME(appointments.booking_time, SEC_TO_TIME(services.duration * 60))
                         AND ADDTIME('$booking_time', SEC_TO_TIME('$new_duration' * 60)) > appointments.booking_time";
                       
    $check_result = mysqli_query($conn, $check_slot_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $message = "Sorry! This Barber is currently busy. They already have an ongoing booking that conflicts with this time slot.";
        $msg_type = "danger";
    } else {
        $sql = "INSERT INTO appointments (customer_id, barber_id, service_id, booking_date, booking_time, status) 
                VALUES ('$customer_id', '$barber_id', '$service_id', '$booking_date', '$booking_time', 'pending')";

        if (mysqli_query($conn, $sql)) {
            $message = "Appointment successfully booked! Wait for barber approval.";
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
    <title>Book Appointment - Smart Saloon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-brand img { max-height: 40px; margin-right: 10px; }
        .booking-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .icon-header { width: 70px; height: 70px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; }
        .form-label { font-weight: 600; color: #495057; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-group-text { background-color: white; border-right: none; color: #6c757d; }
        .form-control, .form-select { border-left: none; }
        .form-control:focus, .form-select:focus { border-color: #dee2e6; box-shadow: none; }
        .input-group:focus-within .input-group-text, .input-group:focus-within .form-control, .input-group:focus-within .form-select { border-color: #0d6efd; color: #0d6efd; }
        .btn-book { background-color: #1e1e24; color: white; transition: all 0.3s ease; }
        .btn-book:hover { background-color: #fd7e14; color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(253, 126, 20, 0.3); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="customer_dashboard.php">
            <img src="logo.png" alt="Logo" class="rounded-circle bg-white p-1">
            Smart Saloon
        </a>
        <div class="ms-auto">
            <a href="customer_dashboard.php" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card booking-card p-4">
                <div class="card-body">
                    
                    <div class="text-center mb-4">
                        <div class="icon-header mb-3 shadow-sm">
                            <i class="fa-solid fa-calendar-check fa-2x text-primary"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Book a Grooming Session</h3>
                        <p class="text-muted small">Choose your preferred barber, service, date, and timing below.</p>
                    </div>

                    <?php if(!empty($message)) { ?>
                        <div class="alert alert-<?php echo $msg_type; ?> shadow-sm text-center fw-semibold small rounded-3 py-2">
                            <?php if($msg_type == 'success') echo '<i class="fa-solid fa-circle-check me-1"></i>'; else echo '<i class="fa-solid fa-triangle-exclamation me-1"></i>'; ?>
                            <?php echo $message; ?>
                        </div>
                    <?php } ?>

                    <form action="book_appointment.php" method="POST">
                        
                        <div class="mb-4">
                            <label class="form-label">Select Preferred Barber</label>
                            <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text border-end-0"><i class="fa-solid fa-user-tie"></i></span>
                                <select name="barber_id" class="form-select border-start-0 text-capitalize py-2" required>
                                    <option value="">-- Choose Barber --</option>
                                    <?php while($barber = mysqli_fetch_assoc($barbers_result)) { ?>
                                        <option value="<?php echo $barber['id']; ?>"><?php echo $barber['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Select Service</label>
                            <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text border-end-0"><i class="fa-solid fa-scissors"></i></span>
                                <select name="service_id" class="form-select border-start-0 py-2" required>
                                    <option value="">-- Choose Service --</option>
                                    <?php 
                                    mysqli_data_seek($services_result, 0);
                                    while($service = mysqli_fetch_assoc($services_result)) { ?>
                                        <option value="<?php echo $service['id']; ?>">
                                            <?php echo $service['name']; ?> (<?php echo $service['price']; ?> Rs - <?php echo $service['duration']; ?> mins)
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <label class="form-label">Choose Date</label>
                                <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                    <span class="input-group-text border-end-0"><i class="fa-solid fa-calendar-day"></i></span>
                                    <input type="date" name="booking_date" class="form-control border-start-0 py-2" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label">Choose Time Slot</label>
                                <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                    <span class="input-group-text border-end-0"><i class="fa-regular fa-clock"></i></span>
                                    <input type="time" name="booking_time" class="form-control border-start-0 py-2" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="book_now" class="btn btn-book w-100 py-3 fw-bold rounded-3 mt-2">
                            <i class="fa-solid fa-check-circle me-2"></i> Confirm Booking
                        </button>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>