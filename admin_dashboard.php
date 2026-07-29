<?php
session_start();
include('db.php');

// Security Guard
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// ================= DYNAMIC COUNTERS FOR CLICKABLE CARDS =================
$pending_slots = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE status = 'pending'"))['total'] ?? 0;
$approved_slots = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE status = 'approved'"))['total'] ?? 0;
$completed_slots = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE status = 'completed'"))['total'] ?? 0;
$cancelled_slots = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE status = 'cancelled'"))['total'] ?? 0;

// REVENUE: Sirf completed jobs se calculate hoga
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(s.price) as gross FROM appointments a JOIN services s ON a.service_id = s.id WHERE a.status = 'completed'"))['gross'] ?? 0;

// Fetch ALL appointments for the dynamic table
$sql = "SELECT a.*, 
               u1.name AS customer_name, u1.email AS customer_email, 
               u2.name AS barber_name, 
               s.name AS service_name, s.price 
        FROM appointments a 
        JOIN users u1 ON a.customer_id = u1.id 
        JOIN users u2 ON a.barber_id = u2.id 
        JOIN services s ON a.service_id = s.id 
        ORDER BY a.booking_date DESC, a.booking_time DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Saloon ERP - Executive Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background-color: #1e1e24; color: white; transition: all 0.3s; }
        .sidebar .nav-link { color: #a2a3b6; padding: 12px 20px; font-weight: 500; border-radius: 8px; margin: 5px 15px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #fd7e14; color: white; }
        
        /* Clickable Card Styling */
        .kpi-card { border: 3px solid transparent; border-radius: 12px; transition: all 0.2s; color: white; cursor: pointer; }
        .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        .kpi-card.active-filter { border-color: #212529; transform: scale(1.03); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .erp-card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 px-0 sidebar shadow sticky-top">
            <div class="text-center py-4 border-bottom border-secondary px-3">
                <img src="logo.png" alt="Smart Saloon Logo" class="img-fluid mb-2 rounded-circle shadow" style="max-height: 75px; background: white; padding: 5px;">
                <h6 class="fw-bold text-white mb-0">Smart Saloon ERP</h6>
                <small class="text-warning">Executive Administrator</small>
            </div>
            <div class="nav flex-column mt-4">
                <a href="admin_dashboard.php" class="nav-link active"><i class="fa-solid fa-chart-pie me-2"></i> Performance</a>
                <a href="manage_services.php" class="nav-link"><i class="fa-solid fa-scissors me-2"></i> Services Control</a>
                <a href="manage_appointments.php" class="nav-link"><i class="fa-solid fa-calendar-check me-2"></i> Appointments Panel</a>
                <a href="manage_users.php" class="nav-link"><i class="fa-solid fa-users-gear me-2"></i> Users & Barbers</a>
                <div class="border-top border-secondary my-3 mx-3"></div>
                <a href="logout.php" class="nav-link text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Systems Logout</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 px-md-4 py-4">
            <h1 class="h3 fw-bold text-dark mb-4">Executive Operations Dashboard</h1>

            <div class="row g-3 mb-4 row-cols-2 row-cols-md-5">
                <div class="col" onclick="filterAdminStatus('pending')">
                    <div class="kpi-card bg-warning text-dark p-3 shadow-sm h-100" id="card-pending">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><small class="text-uppercase fw-bold opacity-75" style="font-size:0.7rem;">Pending</small><h3 class="fw-bold mb-0"><?php echo $pending_slots; ?></h3></div>
                            <i class="fa-solid fa-clock opacity-25 fa-2x"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col" onclick="filterAdminStatus('approved')">
                    <div class="kpi-card bg-info p-3 shadow-sm h-100" id="card-approved">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><small class="text-uppercase fw-bold opacity-75" style="font-size:0.7rem;">Approved</small><h3 class="fw-bold mb-0"><?php echo $approved_slots; ?></h3></div>
                            <i class="fa-solid fa-calendar-check opacity-25 fa-2x"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col" onclick="filterAdminStatus('completed')">
                    <div class="kpi-card bg-success p-3 shadow-sm h-100" id="card-completed">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><small class="text-uppercase fw-bold opacity-75" style="font-size:0.7rem;">Completed</small><h3 class="fw-bold mb-0"><?php echo $completed_slots; ?></h3></div>
                            <i class="fa-solid fa-circle-check opacity-25 fa-2x"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col" onclick="filterAdminStatus('cancelled')">
                    <div class="kpi-card bg-danger p-3 shadow-sm h-100" id="card-cancelled">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><small class="text-uppercase fw-bold opacity-75" style="font-size:0.7rem;">Cancelled</small><h3 class="fw-bold mb-0"><?php echo $cancelled_slots; ?></h3></div>
                            <i class="fa-solid fa-circle-xmark opacity-25 fa-2x"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col">
                    <div class="kpi-card bg-dark text-warning p-3 shadow-sm h-100" style="cursor: default;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><small class="text-uppercase fw-bold text-white opacity-75" style="font-size:0.7rem;">Gross Revenue</small><h3 class="fw-bold mb-0 text-truncate"><?php echo $total_revenue; ?> Rs</h3></div>
                            <i class="fa-solid fa-wallet text-white opacity-25 fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card erp-card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold"><i class="fa-solid fa-list-check text-primary me-2"></i> Real-time Operations Matrix</h5>
                    <button class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">Reset View</button>
                </div>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3">ID</th>
                                <th>Client Info</th>
                                <th>Assigned Stylist</th>
                                <th>Service & Price</th>
                                <th>Date & Time</th>
                                <th>Current Status</th>
                            </tr>
                        </thead>
                        <tbody id="adminTableBody">
                            <?php 
                            if(mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    $status = $row['status'];
                                    $badge = ($status == 'approved') ? 'bg-info' : (($status == 'completed') ? 'bg-success' : (($status == 'cancelled') ? 'bg-danger' : 'bg-warning text-dark'));
                                    
                                    echo "<tr class='admin-appt-row' data-status='{$status}'>";
                                    echo "<td class='ps-3 fw-bold text-secondary'>#{$row['id']}</td>";
                                    echo "<td><div class='fw-bold'>".htmlspecialchars($row['customer_name'])."</div><small class='text-muted'>".htmlspecialchars($row['customer_email'])."</small></td>";
                                    echo "<td><span class='badge bg-light text-dark border px-2 py-1'><i class='fa-solid fa-user-scissors me-1'></i>".htmlspecialchars($row['barber_name'])."</span></td>";
                                    echo "<td><div class='fw-semibold'>".htmlspecialchars($row['service_name'])."</div><small class='fw-bold ".($status == 'completed'?'text-success':'text-muted')."'>{$row['price']} Rs</small></td>";
                                    echo "<td><div>".date('d-M-Y', strtotime($row['booking_date']))."</div><small class='text-muted'>".date('h:i A', strtotime($row['booking_time']))."</small></td>";
                                    echo "<td><span class='badge {$badge} text-uppercase px-3 py-2' style='font-size:0.7rem;'>{$status}</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr id='no-data-row'><td colspan='6' class='text-center py-5 text-muted fw-semibold'>System mein abhi koi record nahi hai.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    let currentAdminFilter = 'all';
    
    function filterAdminStatus(status) {
        // Toggle logic: If clicking the same active card, turn it off (show all)
        if(currentAdminFilter === status) {
            currentAdminFilter = 'all';
            document.querySelectorAll('.kpi-card').forEach(c => c.classList.remove('active-filter'));
        } else {
            currentAdminFilter = status;
            document.querySelectorAll('.kpi-card').forEach(c => c.classList.remove('active-filter'));
            document.getElementById('card-' + status).classList.add('active-filter');
        }
        
        applyFilter();
    }

    function resetFilters() {
        currentAdminFilter = 'all';
        document.querySelectorAll('.kpi-card').forEach(c => c.classList.remove('active-filter'));
        applyFilter();
    }

    function applyFilter() {
        const rows = document.getElementsByClassName('admin-appt-row');
        let visibleCount = 0;
        
        for(let i=0; i<rows.length; i++) {
            const rowStatus = rows[i].getAttribute('data-status');
            if (currentAdminFilter === 'all' || rowStatus === currentAdminFilter) {
                rows[i].style.display = "";
                visibleCount++;
            } else {
                rows[i].style.display = "none";
            }
        }

        // Handle "No data" message if a filter is empty
        let noDataRow = document.getElementById('no-data-row');
        if (visibleCount === 0 && rows.length > 0) {
            if (!noDataRow) {
                document.getElementById('adminTableBody').insertAdjacentHTML('beforeend', "<tr id='no-data-row'><td colspan='6' class='text-center py-4 text-muted fw-semibold'>Is filter status ka koi data nahi hai.</td></tr>");
            } else {
                noDataRow.style.display = "";
            }
        } else if (noDataRow) {
            noDataRow.style.display = "none";
        }
    }
</script>
</body>
</html>