<?php
session_start();
include('db.php');

// Security Check: Customer Only
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// ================= CUSTOMER KPIs =================
$total_appts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE customer_id='$user_id'"))['total'] ?? 0;
// NAYA KPI: Upcoming (Approved) Appointments
$upcoming_appts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE customer_id='$user_id' AND status='approved'"))['total'] ?? 0;
$completed_appts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE customer_id='$user_id' AND status='completed'"))['total'] ?? 0;
$pending_appts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE customer_id='$user_id' AND status='pending'"))['total'] ?? 0;

// Fetch Customer's Booking History
$sql = "SELECT a.*, b.name AS barber_name, s.name AS service_name, s.price 
        FROM appointments a 
        JOIN users b ON a.barber_id = b.id 
        JOIN services s ON a.service_id = s.id 
        WHERE a.customer_id = '$user_id' 
        ORDER BY a.booking_date DESC, a.booking_time DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Saloon - Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-brand img { max-height: 40px; margin-right: 10px; }
        
        /* Stat Card CSS for Clickable Filters */
        .stat-card { border: none; border-radius: 12px; transition: all 0.3s ease; cursor: pointer; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
        .active-filter { transform: scale(1.02); opacity: 1; border-color: #343a40 !important; }
        .inactive-filter { opacity: 0.5; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="customer_dashboard.php">
            <img src="logo.png" alt="Logo" class="rounded-circle bg-white p-1">
            Smart Saloon
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link active" href="customer_dashboard.php"><i class="fa-solid fa-house me-1"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="book_appointment.php"><i class="fa-solid fa-calendar-plus me-1"></i> Book New Session</a></li>
                <li class="nav-item ms-lg-3"><a class="btn btn-outline-danger btn-sm fw-bold" href="logout.php"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="mb-4">
        <h2 class="fw-bold text-dark m-0">Hello, <?php echo htmlspecialchars($user_name); ?>!</h2>
        <p class="text-muted m-0 mt-1">Manage your grooming sessions here.</p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card active-filter bg-white shadow-sm p-4 border-start border-4 border-primary" onclick="filterAppointments('all', this)">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-uppercase text-muted fw-bold">Total Bookings</small>
                        <h2 class="fw-bold text-dark m-0"><?php echo $total_appts; ?></h2>
                    </div>
                    <i class="fa-solid fa-layer-group fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card bg-white shadow-sm p-4 border-start border-4 border-info" onclick="filterAppointments('approved', this)">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-uppercase text-muted fw-bold">Upcoming</small>
                        <h2 class="fw-bold text-dark m-0"><?php echo $upcoming_appts; ?></h2>
                    </div>
                    <i class="fa-solid fa-calendar-day fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card bg-white shadow-sm p-4 border-start border-4 border-success" onclick="filterAppointments('completed', this)">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-uppercase text-muted fw-bold">Completed</small>
                        <h2 class="fw-bold text-dark m-0"><?php echo $completed_appts; ?></h2>
                    </div>
                    <i class="fa-solid fa-circle-check fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card bg-white shadow-sm p-4 border-start border-4 border-warning" onclick="filterAppointments('pending', this)">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-uppercase text-muted fw-bold">Pending</small>
                        <h2 class="fw-bold text-dark m-0"><?php echo $pending_appts; ?></h2>
                    </div>
                    <i class="fa-solid fa-clock fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="m-0 fw-bold"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> My Service History</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Ref ID</th>
                        <th>Assigned Barber</th>
                        <th>Service Details</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="appointmentTableBody">
                    <?php 
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $status = strtolower($row['status']);
                            $badge = ($status == 'approved') ? 'bg-info text-white' : (($status == 'completed') ? 'bg-success text-white' : (($status == 'cancelled') ? 'bg-danger text-white' : 'bg-warning text-dark'));
                            
                            echo "<tr class='appointment-row' data-status='{$status}'>";
                            echo "<td class='ps-4 fw-bold text-secondary'>#{$row['id']}</td>";
                            echo "<td class='fw-semibold'>{$row['barber_name']}</td>";
                            echo "<td><div class='fw-bold text-dark'>{$row['service_name']}</div><small class='text-muted fw-semibold'>Cost: {$row['price']} Rs</small></td>";
                            echo "<td><div><i class='fa-regular fa-calendar text-primary me-1'></i>".date('d-M-Y', strtotime($row['booking_date']))."</div>
                                      <small class='text-muted'><i class='fa-regular fa-clock me-1'></i>".date('h:i A', strtotime($row['booking_time']))."</small></td>";
                            echo "<td><span class='badge {$badge} text-uppercase px-3 py-2'>{$status}</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center py-5 text-muted fw-semibold'>You haven't booked any appointments yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // JS Logic for Frontend Filtering
    function filterAppointments(targetStatus, clickedCard) {
        // 1. Cards styling: Highlight active card, dim the others
        const allCards = document.querySelectorAll('.stat-card');
        allCards.forEach(card => {
            card.classList.remove('active-filter');
            card.classList.add('inactive-filter');
        });
        
        clickedCard.classList.remove('inactive-filter');
        clickedCard.classList.add('active-filter');

        // 2. Table rows filtering
        const rows = document.querySelectorAll('.appointment-row');
        let visibleCount = 0;

        // Purana 'no record' message hamesha remove karo filter se pehle
        const oldMsg = document.getElementById('no-filter-msg');
        if (oldMsg) oldMsg.remove();

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            
            if (targetStatus === 'all' || rowStatus === targetStatus) {
                row.style.display = ''; // Row show karo
                visibleCount++;
            } else {
                row.style.display = 'none'; // Row hide karo
            }
        });

        // 3. Agar filter ke baad koi data na mile, to message display karo
        if (visibleCount === 0 && rows.length > 0) {
            const tbody = document.getElementById('appointmentTableBody');
            const tr = document.createElement('tr');
            tr.id = 'no-filter-msg';
            
            // Customizing text based on targetStatus
            let statusText = targetStatus.toUpperCase();
            if(targetStatus === 'approved') statusText = 'UPCOMING';

            tr.innerHTML = `<td colspan="5" class="text-center py-5 text-muted fw-bold">
                                <i class="fa-solid fa-folder-open d-block mb-2 fs-3 opacity-50"></i>
                                NO <b>${statusText}</b> RECORD TO SHOW.
                            </td>`;
            tbody.appendChild(tr);
        }
    }
</script>
</body>
</html>