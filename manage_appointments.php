<?php
session_start();
include('db.php');

// Security Check: Admin ya Barber
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'barber')) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];
$message = ""; $msg_type = "success";

// 1. UPDATE OPERATION: Status Change Logic (Approve / Cancel / Complete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $appointment_id = $_GET['id'];
    
    $new_status = '';
    if ($action == 'approve') $new_status = 'approved';
    elseif ($action == 'cancel') $new_status = 'cancelled';
    elseif ($action == 'complete') $new_status = 'completed';

    if ($new_status != '') {
        if ($user_role == 'barber') {
            $update_sql = "UPDATE appointments SET status='$new_status' WHERE id='$appointment_id' AND barber_id='$user_id'";
        } else {
            $update_sql = "UPDATE appointments SET status='$new_status' WHERE id='$appointment_id'";
        }

        if (mysqli_query($conn, $update_sql)) {
            $message = "Appointment status successfully updated to " . strtoupper($new_status) . "!";
            $msg_type = "success";
        } else {
            $message = "Error updating status: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    }
}

// 2. DYNAMIC FILTER LOGIC
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';

// 3. READ OPERATION
if ($user_role == 'admin') {
    $query = "SELECT appointments.*, c.name AS customer_name, b.name AS barber_name, services.name AS service_name, services.price 
              FROM appointments 
              JOIN users c ON appointments.customer_id = c.id 
              JOIN users b ON appointments.barber_id = b.id 
              JOIN services ON appointments.service_id = services.id 
              WHERE appointments.status = '$filter'
              ORDER BY appointments.booking_date DESC, appointments.booking_time DESC";
} else {
    $query = "SELECT appointments.*, c.name AS customer_name, services.name AS service_name, services.price 
              FROM appointments 
              JOIN users c ON appointments.customer_id = c.id 
              JOIN services ON appointments.service_id = services.id 
              WHERE appointments.barber_id = '$user_id' AND appointments.status = '$filter'
              ORDER BY appointments.booking_date DESC, appointments.booking_time DESC";
}
$result = mysqli_query($conn, $query);

// 4. COUNTERS FOR TABS
if ($user_role == 'admin') {
    $count_p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) AS total FROM appointments WHERE status='pending'"))['total'];
    $count_a = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) AS total FROM appointments WHERE status='approved'"))['total'];
    $count_comp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) AS total FROM appointments WHERE status='completed'"))['total'];
    $count_c = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) AS total FROM appointments WHERE status='cancelled'"))['total'];
} else {
    $count_p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) AS total FROM appointments WHERE status='pending' AND barber_id='$user_id'"))['total'];
    $count_a = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) AS total FROM appointments WHERE status='approved' AND barber_id='$user_id'"))['total'];
    $count_comp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) AS total FROM appointments WHERE status='completed' AND barber_id='$user_id'"))['total'];
    $count_c = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) AS total FROM appointments WHERE status='cancelled' AND barber_id='$user_id'"))['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Saloon ERP - Appointments Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background-color: #1e1e24; color: white; transition: all 0.3s; }
        .sidebar .nav-link { color: #a2a3b6; padding: 12px 20px; font-weight: 500; border-radius: 8px; margin: 5px 15px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #fd7e14; color: white; }
        .erp-card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .nav-tabs .nav-link { font-weight: 600; color: #6c757d; border: none; padding: 12px 20px; }
        .nav-tabs .nav-link.active { color: #1e1e24; border-bottom: 3px solid #fd7e14; background: transparent; }
        
        /* Search Bar Styling */
        .search-container .form-control:focus { border-color: #ced4da; box-shadow: none; }
        .search-container .input-group-text { background-color: white; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-3 col-lg-2 px-0 sidebar shadow sticky-top">
            <div class="text-center py-4 border-bottom border-secondary px-3">
                <img src="logo.png" alt="Smart Saloon Logo" class="img-fluid mb-2 rounded-circle shadow" style="max-height: 75px; background: white; padding: 5px;">
                <h6 class="fw-bold text-white mb-0">Smart Saloon ERP</h6>
                <small class="text-warning"><?php echo ($user_role == 'admin') ? 'Executive Admin' : 'Professional Stylist'; ?></small>
            </div>
            
            <div class="nav flex-column mt-4">
                <?php if($user_role == 'admin') { ?>
                    <a href="admin_dashboard.php" class="nav-link"><i class="fa-solid fa-chart-pie me-2"></i> Performance</a>
                    <a href="manage_services.php" class="nav-link"><i class="fa-solid fa-scissors me-2"></i> Services Control</a>
                    <a href="manage_appointments.php" class="nav-link active"><i class="fa-solid fa-calendar-check me-2"></i> Appointments Panel</a>
                    <a href="manage_users.php" class="nav-link"><i class="fa-solid fa-users-gear me-2"></i> Users & Barbers</a>
                <?php } else { ?>
                    <a href="barber_dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line me-2"></i> Stylist Analytics</a>
                    <a href="manage_appointments.php" class="nav-link active"><i class="fa-solid fa-calendar-check me-2"></i> My Appointments</a>
                <?php } ?>
                <div class="border-top border-secondary my-3 mx-3"></div>
                <a href="logout.php" class="nav-link text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Systems Logout</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 px-md-4 py-4">
            
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom flex-wrap gap-3">
                <h1 class="h3 fw-bold text-dark m-0">Global Appointments Ledger</h1>
                
                <div class="input-group search-container shadow-sm" style="max-width: 350px;">
                    <span class="input-group-text border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" id="tableSearch" class="form-control border-start-0 ps-0" placeholder="Search record..." onkeyup="filterTable()">
                </div>
            </div>

            <?php if(!empty($message)) { ?>
                <div class="alert alert-<?php echo $msg_type; ?> shadow-sm text-center fw-semibold"><?php echo $message; ?></div>
            <?php } ?>

            <ul class="nav nav-tabs border-bottom mb-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($filter == 'pending') ? 'active' : ''; ?>" href="manage_appointments.php?filter=pending">
                        <i class="fa-solid fa-spinner text-warning me-1"></i> Pending 
                        <span class="badge bg-warning text-dark ms-1 rounded-pill"><?php echo $count_p; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($filter == 'approved') ? 'active' : ''; ?>" href="manage_appointments.php?filter=approved">
                        <i class="fa-solid fa-calendar-check text-info me-1"></i> Approved 
                        <span class="badge bg-info text-white ms-1 rounded-pill"><?php echo $count_a; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($filter == 'completed') ? 'active' : ''; ?>" href="manage_appointments.php?filter=completed">
                        <i class="fa-solid fa-circle-check text-success me-1"></i> Completed 
                        <span class="badge bg-success text-white ms-1 rounded-pill"><?php echo $count_comp; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($filter == 'cancelled') ? 'active' : ''; ?>" href="manage_appointments.php?filter=cancelled">
                        <i class="fa-solid fa-circle-xmark text-danger me-1"></i> Cancelled 
                        <span class="badge bg-danger text-white ms-1 rounded-pill"><?php echo $count_c; ?></span>
                    </a>
                </li>
            </ul>

            <div class="card erp-card border-0 shadow-sm overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="appointmentsTable">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">ID</th>
                                <th>Customer Name</th>
                                <?php if($user_role == 'admin') echo "<th>Assigned Barber</th>"; ?>
                                <th>Requested Service</th>
                                <th>Schedule Date & Time</th>
                                <th>Bill Amount</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    // Added class 'searchable-row' for JS filtering
                                    echo "<tr class='searchable-row'>";
                                    echo "<td class='ps-3 fw-bold text-secondary'>#" . $row['id'] . "</td>";
                                    echo "<td class='fw-semibold text-dark'><i class='fa-solid fa-user-tag text-muted me-2 small'></i>" . htmlspecialchars($row['customer_name']) . "</td>";
                                    
                                    if ($user_role == 'admin') {
                                        echo "<td class='text-secondary fw-semibold'><i class='fa-solid fa-user-scissors me-2 small'></i>" . htmlspecialchars($row['barber_name']) . "</td>";
                                    }
                                    
                                    echo "<td class='fw-semibold'>" . htmlspecialchars($row['service_name']) . "</td>";
                                    echo "<td>
                                            <div class='small fw-bold text-dark'><i class='fa-regular fa-calendar me-1 text-primary'></i>" . date('d-M-Y', strtotime($row['booking_date'])) . "</div>
                                            <div class='small text-muted'><i class='fa-regular fa-clock me-1'></i>" . date('h:i A', strtotime($row['booking_time'])) . "</div>
                                          </td>";
                                    echo "<td><span class='badge bg-light text-dark border fw-bold'>" . $row['price'] . " Rs</span></td>";
                                    
                                    echo "<td class='text-center'>";
                                    if ($row['status'] == 'pending') {
                                        echo "<a href='manage_appointments.php?filter=pending&action=approve&id=" . $row['id'] . "' class='btn btn-sm btn-success fw-bold me-1 px-3'><i class='fa-solid fa-check me-1'></i> Approve</a>";
                                        echo "<a href='manage_appointments.php?filter=pending&action=cancel&id=" . $row['id'] . "' class='btn btn-sm btn-outline-danger fw-bold px-3' onclick=\"return confirm('Cancel scheduling?')\"><i class='fa-solid fa-xmark me-1'></i> Cancel</a>";
                                    } elseif ($row['status'] == 'approved') {
                                        // Admin and Barber both can complete the job from here
                                        echo "<a href='manage_appointments.php?filter=approved&action=complete&id=" . $row['id'] . "' class='btn btn-sm btn-dark text-warning fw-bold px-3 border border-secondary'><i class='fa-solid fa-scissors me-1 text-white'></i> Complete Job</a>";
                                    } else {
                                        $badge_style = ($row['status'] == 'completed') ? 'bg-success' : 'bg-danger';
                                        echo "<span class='badge " . $badge_style . " text-uppercase px-3 py-2 fw-bold'><i class='fa-solid fa-shield me-1'></i>" . $row['status'] . "</span>";
                                    }
                                    echo "</td>";
                                    
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center py-5 text-muted fw-semibold'><i class='fa-solid fa-folder-open display-6 d-block mb-2 opacity-50'></i>NO RECORD TO SHOW!</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function filterTable() {
        let input = document.getElementById("tableSearch").value.toLowerCase();
        let rows = document.querySelectorAll(".searchable-row");
        let visibleCount = 0;

        // Remove old 'No Result' message if it exists
        let oldMsg = document.getElementById("no-search-msg");
        if (oldMsg) oldMsg.remove();

        rows.forEach(row => {
            // Get all text content from the row
            let rowText = row.innerText.toLowerCase();
            
            if (rowText.includes(input)) {
                row.style.display = "";
                visibleCount++;
            } else {
                row.style.display = "none";
            }
        });

        // Show 'No Results Found' if no rows match the search query
        if (visibleCount === 0 && rows.length > 0) {
            let tbody = document.querySelector("#appointmentsTable tbody");
            let tr = document.createElement("tr");
            tr.id = "no-search-msg";
            let colCount = document.querySelector("#appointmentsTable thead tr").children.length;
            
            tr.innerHTML = `<td colspan="${colCount}" class="text-center py-5 text-muted fw-bold">
                                <i class="fa-solid fa-magnifying-glass-minus display-6 d-block mb-2 opacity-50"></i>
                                NO RECORD TO SHOW!
                            </td>`;
            tbody.appendChild(tr);
        }
    }
</script>
</body>
</html>