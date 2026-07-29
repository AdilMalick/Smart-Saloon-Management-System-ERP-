<?php
session_start();
include('db.php');

// Security Check: Only logged-in barbers can access this kernel node
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'barber') {
    header("Location: login.php");
    exit();
}

$barber_id = $_SESSION['user_id'];
$barber_name = $_SESSION['user_name'] ?? 'Professional Stylist';
$message = ""; $msg_type = "success";

// Dynamic Routing Layer: Toggle between Performance Matrix & Tasks Grid
$view = $_GET['view'] ?? 'performance';

// ================= ACTION CONTROLS: UPDATE APPOINTMENT LIFECYCLE =================
if (isset($_GET['action']) && isset($_GET['appointment_id'])) {
    $appt_id = mysqli_real_escape_string($conn, $_GET['appointment_id']);
    $action = $_GET['action'];
    
    if ($action == 'approve') {
        $update_status = 'approved';
        $msg_text = "Appointment successfully confirmed and slot locked!";
        $msg_class = "success";
    } elseif ($action == 'cancel') {
        $update_status = 'cancelled';
        $msg_text = "Appointment status set to cancelled successfully.";
        $msg_class = "warning";
    } elseif ($action == 'complete') {
        $update_status = 'completed';
        $msg_text = "Excellent! Job successfully completed and added to gross revenue metrics.";
        $msg_class = "success";
    }

    $update_sql = "UPDATE appointments SET status = '$update_status' WHERE id = '$appt_id' AND barber_id = '$barber_id'";
    if (mysqli_query($conn, $update_sql)) {
        $message = $msg_text;
        $msg_type = $msg_class;
    } else {
        $message = "Database Error: " . mysqli_error($conn);
        $msg_type = "danger";
    }
}

// ================= INTELLIGENCE INTEGRATED COUNTERS =================
$cancelled_slots = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE barber_id = '$barber_id' AND status = 'cancelled'"))['total'] ?? 0;
$approved_slots = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE barber_id = '$barber_id' AND status = 'approved'"))['total'] ?? 0;
$pending_slots = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE barber_id = '$barber_id' AND status = 'pending'"))['total'] ?? 0;
$completed_slots = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE barber_id = '$barber_id' AND status = 'completed'"))['total'] ?? 0;

// REFACTORED EARNINGS: Revenue calculated strictly from completed jobs
$earnings_query = "SELECT SUM(s.price) as gross FROM appointments a JOIN services s ON a.service_id = s.id WHERE a.barber_id = '$barber_id' AND a.status = 'completed'";
$total_earnings = mysqli_fetch_assoc(mysqli_query($conn, $earnings_query))['gross'] ?? 0;

// ================= MASTER DATA STREAM QUERY =================
if ($view == 'tasks') {
    // Jobs Tracker displays currently approved upcoming active obligations
    $schedule_sql = "SELECT a.*, u.name AS customer_name, u.email AS customer_email, s.name AS service_name, s.price, s.duration 
                     FROM appointments a 
                     JOIN users u ON a.customer_id = u.id 
                     JOIN services s ON a.service_id = s.id 
                     WHERE a.barber_id = '$barber_id' AND a.status = 'approved' AND a.booking_date >= CURDATE()
                     ORDER BY a.booking_date ASC, a.booking_time ASC";
} else {
    // Performance Matrix fetches ALL historical states into one single unified pool
    $schedule_sql = "SELECT a.*, u.name AS customer_name, u.email AS customer_email, s.name AS service_name, s.price, s.duration 
                     FROM appointments a 
                     JOIN users u ON a.customer_id = u.id 
                     JOIN services s ON a.service_id = s.id 
                     WHERE a.barber_id = '$barber_id' 
                     ORDER BY a.booking_date DESC, a.booking_time DESC";
}
$schedule_result = mysqli_query($conn, $schedule_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Saloon ERP - Barber Operations Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background-color: #1e1e24; color: white; transition: all 0.3s; }
        .sidebar .nav-link { color: #a2a3b6; padding: 12px 20px; font-weight: 500; border-radius: 8px; margin: 5px 15px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #fd7e14; color: white; }
        .erp-card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        
        /* Interactive KPI Tabs styling */
        .kpi-card { border: 3px solid transparent; border-radius: 12px; transition: all 0.2s; color: white; cursor: pointer; position: relative; overflow: hidden; }
        .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0,0,0,0.15)!important; }
        .kpi-card.active-filter { border-color: #1e1e24; box-shadow: 0 0 15px rgba(0,0,0,0.3); transform: scale(1.02); }
        
        .search-box { position: relative; max-width: 350px; }
        .search-box i { position: absolute; left: 12px; top: 12px; color: #aaa; }
        .search-box input { padding-left: 35px; border-radius: 20px; }
        .date-divider { background-color: #e9ecef; color: #495057; font-weight: 700; font-size: 0.9rem; }
        .clear-filter-btn { font-size: 0.8rem; cursor: pointer; text-decoration: underline; color: #fd7e14; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        
        <!-- ================= BARBER LEFT NAVIGATION SIDEBAR ================= -->
        <div class="col-md-3 col-lg-2 px-0 sidebar shadow sticky-top">
            <div class="text-center py-4 border-bottom border-secondary px-3">
                <img src="logo.png" alt="Smart Saloon Logo" class="img-fluid mb-2 rounded-circle shadow" style="max-height: 75px; background: white; padding: 5px;">
                <h6 class="fw-bold text-white mb-0"><?php echo htmlspecialchars($barber_name); ?></h6>
                <small class="text-warning">Stylist Specialist</small>
            </div>
            
            <div class="nav flex-column mt-4">
                <a href="barber_dashboard.php?view=performance" class="nav-link <?php echo $view == 'performance' ? 'active' : ''; ?>"><i class="fa-solid fa-chart-line me-2"></i> Performance Dashboard</a>
                <a href="barber_dashboard.php?view=tasks" class="nav-link <?php echo $view == 'tasks' ? 'active' : ''; ?>"><i class="fa-solid fa-list-check me-2"></i> Jobs & Tasks Tracker</a>
                <div class="border-top border-secondary my-3 mx-3"></div>
                <a href="logout.php" class="nav-link text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Systems Logout</a>
            </div>
        </div>

        <!-- ================= MAIN CORE WINDOW ================= -->
        <div class="col-md-9 col-lg-10 px-md-4 py-4">
            
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <div>
                    <h1 class="h3 fw-bold text-dark m-0">
                        <?php echo $view == 'tasks' ? 'Upcoming Operational Jobs Registry' : 'Stylist Performance Assignment Ledger'; ?>
                    </h1>
                    <small class="text-muted">Logged Session Mode: Active Barber Sync Matrix</small>
                </div>
                
                <div class="search-box shadow-sm">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="barberSearchInput" class="form-control" placeholder="Search customer, id or service...">
                </div>
            </div>

            <?php if(!empty($message)) { ?>
                <div class="alert alert-<?php echo $msg_type; ?> shadow-sm text-center fw-semibold"><?php echo $message; ?></div>
            <?php } ?>

            <!-- ================= DYNAMIC 5 TABS ROW (PERFORMANCE MODE ONLY) ================= -->
            <?php if ($view == 'performance') { ?>
                <div class="row g-2 mb-4 row-cols-2 row-cols-md-3 row-cols-lg-5">
                    
                    <!-- TAB 1: PENDING -->
                    <div class="col">
                        <div class="kpi-card bg-warning text-dark p-3 shadow-sm" id="card-pending" onclick="filterByStatus('pending')">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-uppercase fw-bold opacity-75" style="font-size:0.65rem;">Pending Requests</small>
                                    <h3 class="fw-bold mb-0 mt-1"><?php echo $pending_slots; ?></h3>
                                </div>
                                <i class="fa-solid fa-clock-rotate-left fa-xl opacity-25"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: APPROVED -->
                    <div class="col">
                        <div class="kpi-card bg-info p-3 shadow-sm" id="card-approved" onclick="filterByStatus('approved')">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-uppercase fw-bold opacity-75" style="font-size:0.65rem;">Approved Slots</small>
                                    <h3 class="fw-bold mb-0 mt-1"><?php echo $approved_slots; ?></h3>
                                </div>
                                <i class="fa-solid fa-calendar-check fa-xl opacity-25"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: COMPLETED (NEW TAB REQUESTED BY USER) -->
                    <div class="col">
                        <div class="kpi-card bg-success p-3 shadow-sm" id="card-completed" onclick="filterByStatus('completed')">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-uppercase fw-bold opacity-75" style="font-size:0.65rem;">Completed Jobs</small>
                                    <h3 class="fw-bold mb-0 mt-1"><?php echo $completed_slots; ?></h3>
                                </div>
                                <i class="fa-solid fa-circle-check fa-xl opacity-25"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: CANCELLED -->
                    <div class="col">
                        <div class="kpi-card bg-danger p-3 shadow-sm" id="card-cancelled" onclick="filterByStatus('cancelled')">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-uppercase fw-bold opacity-75" style="font-size:0.65rem;">Cancelled / Rejected</small>
                                    <h3 class="fw-bold mb-0 mt-1"><?php echo $cancelled_slots; ?></h3>
                                </div>
                                <i class="fa-solid fa-calendar-xmark fa-xl opacity-25"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: PROFIT / REVENUE COUNTER -->
                    <div class="col">
                        <div class="kpi-card bg-dark p-3 shadow-sm" id="card-revenue" style="color: #ffc107;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-uppercase fw-bold opacity-75 text-white" style="font-size:0.65rem;">Gross Revenue</small>
                                    <h4 class="fw-bold mb-0 mt-1 text-truncate"><?php echo $total_earnings; ?> Rs</h4>
                                </div>
                                <i class="fa-solid fa-wallet fa-xl opacity-50 text-white"></i>
                            </div>
                        </div>
                    </div>

                </div>
            <?php } ?>

            <!-- ================= TASKS TRACKER DATE FILTER ================= -->
            <?php if ($view == 'tasks') { ?>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card erp-card border-0 shadow-sm p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-bold text-secondary text-uppercase m-0">
                                    <i class="fa-solid fa-calendar-day text-primary me-1"></i> Search By Specific Date
                                </label>
                                <span id="clearDateBtn" class="clear-filter-btn d-none" onclick="resetDateFilter()">Reset Date</span>
                            </div>
                            <input type="date" id="barberDateFilterInput" class="form-control py-2 shadow-sm">
                        </div>
                    </div>
                </div>
            <?php } ?>


            <!-- ================= UNIFIED MASTER TABLE LAYER ================= -->
            <div class="card erp-card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-dark">
                        <i class="fa-solid fa-stream text-warning me-2"></i>
                        <?php echo $view == 'tasks' ? 'Chronological Active Duty Streams' : 'Assigned Client Stream Mappings'; ?>
                    </h5>
                    <span class="badge bg-secondary text-uppercase py-2 px-3" id="filterTrackerLabel">Showing: All Entries</span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="barberScheduleTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Job ID</th>
                                <th>Client Profile Identity</th>
                                <th>Date & Time Slot</th>
                                <th>Requested Service</th>
                                <th>Duration</th>
                                <th>Billing Rate</th>
                                <th>Status Badge</th>
                                <th class="text-center">Operational Flags</th>
                            </tr>
                        </thead>
                        <tbody id="barberTableBody">
                            <?php 
                            if (mysqli_num_rows($schedule_result) > 0) {
                                $last_printed_date = "";
                                
                                while($row = mysqli_fetch_assoc($schedule_result)) {
                                    $status = $row['status'];
                                    $db_date = $row['booking_date'];
                                    
                                    // Grouping headers logic for Tasks View
                                    if ($view == 'tasks' && $db_date != $last_printed_date) {
                                        $last_printed_date = $db_date;
                                        $friendly_date = date('l, d F Y', strtotime($db_date));
                                        $row_highlight_class = ($db_date == date('Y-m-d')) ? 'table-warning text-dark fw-bold' : 'date-divider';
                                        
                                        echo "<tr class='date-group-header-row' data-date-token='".htmlspecialchars($db_date)."'>";
                                        echo "<td colspan='8' class='{$row_highlight_class} ps-3 py-2'><i class='fa-solid fa-calendar-day text-warning me-2'></i> Scheduled For: {$friendly_date}</td>";
                                        echo "</tr>";
                                    }

                                    // Dynamic Badge Selector
                                    $status_badge = 'bg-warning text-dark';
                                    if ($status == 'approved') $status_badge = 'bg-info text-white';
                                    if ($status == 'cancelled') $status_badge = 'bg-danger text-white';
                                    if ($status == 'completed') $status_badge = 'bg-success text-white';

                                    echo "<tr class='barber-job-row' data-status='" . $status . "' data-date='" . htmlspecialchars($db_date) . "'>";
                                    echo "<td class='ps-3 fw-bold text-secondary job-id-token'>#" . $row['id'] . "</td>";
                                    echo "<td>
                                            <div class='fw-bold text-dark client-name-token'>" . htmlspecialchars($row['customer_name']) . "</div>
                                            <div class='text-muted small' style='font-size:0.75rem;'><i class='fa-regular fa-envelope me-1'></i>" . htmlspecialchars($row['customer_email']) . "</div>
                                          </td>";
                                    echo "<td>
                                            <div class='fw-bold text-dark'>" . date('d-M-Y', strtotime($row['booking_date'])) . "</div>
                                            <div class='text-muted small fw-semibold' style='font-size:0.75rem;'><i class='fa-regular fa-clock me-1 text-primary'></i>" . date('h:i A', strtotime($row['booking_time'])) . "</div>
                                          </td>";
                                    echo "<td class='fw-bold text-dark service-name-token'>" . htmlspecialchars($row['service_name']) . "</td>";
                                    echo "<td class='text-secondary fw-semibold'>" . htmlspecialchars($row['duration']) . " Mins</td>";
                                    echo "<td class='fw-bold " . ($status == 'completed' ? 'text-success' : 'text-dark') . "'>" . $row['price'] . " Rs</td>";
                                    echo "<td><span class='badge " . $status_badge . " text-uppercase px-3 py-2' style='font-size:0.7rem;'>" . $status . "</span></td>";
                                    
                                    // Action buttons lifecycle controllers
                                    echo "<td class='text-center'>";
                                    if ($status == 'pending') {
                                        echo "<a href='barber_dashboard.php?view={$view}&action=approve&appointment_id=" . $row['id'] . "' class='btn btn-sm btn-success fw-bold px-2 me-1 shadow-sm'><i class='fa-solid fa-check me-1'></i> Approve</a>";
                                        echo "<a href='barber_dashboard.php?view={$view}&action=cancel&appointment_id=" . $row['id'] . "' class='btn btn-sm btn-outline-danger fw-semibold px-2 shadow-sm' onclick=\"return confirm('Cancel this slot?')\"><i class='fa-solid fa-xmark me-1'></i> Cancel</a>";
                                    } elseif ($status == 'approved') {
                                        echo "<a href='barber_dashboard.php?view={$view}&action=complete&appointment_id=" . $row['id'] . "' class='btn btn-sm btn-dark text-warning fw-bold px-3 shadow-sm border border-secondary'><i class='fa-solid fa-scissors me-1 text-white'></i> Complete Job</a>";
                                    } else {
                                        echo "<span class='text-muted small fw-bold'><i class='fa-solid fa-lock me-1 opacity-50'></i> Closed</span>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center py-5 text-muted fw-semibold'><i class='fa-solid fa-folder-open display-6 d-block mb-2 opacity-50'></i>System trace reports zero records matching target criteria.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= HIGH-PERFORMANCE MASTER TABS ENGINE ================= -->
<script>
    let activeStatusFilter = 'all';

    // Click tabs controller to switch lists fluently
    function filterByStatus(statusType) {
        if (activeStatusFilter === statusType) {
            // If clicking the active card again, reset filter to show all rows
            activeStatusFilter = 'all';
            document.querySelectorAll('.kpi-card').forEach(card => card.classList.remove('active-filter'));
            document.getElementById('filterTrackerLabel').textContent = "Showing: All Entries";
        } else {
            // Set active state filter and highlight the chosen tab card
            activeStatusFilter = statusType;
            document.querySelectorAll('.kpi-card').forEach(card => card.classList.remove('active-filter'));
            
            const selectedTabCard = document.getElementById('card-' + statusType);
            if(selectedTabCard) selectedTabCard.classList.add('active-filter');
            
            document.getElementById('filterTrackerLabel').textContent = "Showing: " + statusType.toUpperCase() + " List";
        }
        executeMasterPipeline();
    }

    // Live filtering constraints tracker for standard date filter
    const dateInputEl = document.getElementById('barberDateFilterInput');
    if (dateInputEl) {
        dateInputEl.addEventListener('change', function() {
            const clearBtn = document.getElementById('clearDateBtn');
            if(this.value) {
                clearBtn.classList.remove('d-none');
                document.getElementById('filterTrackerLabel').textContent = "Showing Date: " + this.value;
            } else {
                clearBtn.classList.add('d-none');
                document.getElementById('filterTrackerLabel').textContent = "Showing: All Entries";
            }
            executeMasterPipeline();
        });
    }

    function resetDateFilter() {
        if(dateInputEl) {
            dateInputEl.value = '';
            document.getElementById('clearDateBtn').classList.add('d-none');
            document.getElementById('filterTrackerLabel').textContent = "Showing: All Entries";
            executeMasterPipeline();
        }
    }

    // Real-time Search Box Input stream connection
    document.getElementById('barberSearchInput').addEventListener('keyup', function() {
        executeMasterPipeline();
    });

    // Central Data Pipeline to filter the single main table dynamically
    function executeMasterPipeline() {
        const searchQuery = document.getElementById('barberSearchInput').value.toLowerCase().trim();
        const dateFilterValue = dateInputEl ? dateInputEl.value : '';
        const dataRows = document.getElementsByClassName('barber-job-row');
        let visibleRowsCount = 0;

        for (let i = 0; i < dataRows.length; i++) {
            const row = dataRows[i];
            const rowStatus = row.getAttribute('data-status');
            const rowDate = row.getAttribute('data-date');
            
            const clientName = row.getElementsByClassName('client-name-token')[0].textContent.toLowerCase();
            const serviceName = row.getElementsByClassName('service-name-token')[0].textContent.toLowerCase();
            const jobId = row.getElementsByClassName('job-id-token')[0].textContent.toLowerCase();

            const textMatches = clientName.includes(searchQuery) || serviceName.includes(searchQuery) || jobId.includes(queryMatchClean(searchQuery));
            const statusMatches = (activeStatusFilter === 'all' || rowStatus === activeStatusFilter);
            const dateMatches = (dateFilterValue === '' || rowDate === dateFilterValue);

            if (textMatches && statusMatches && dateMatches) {
                row.style.display = "";
                visibleRowsCount++;
            } else {
                row.style.display = "none";
            }
        }

        // Date Headers Sync for Tasks tracker view mode
        const dateHeaders = document.getElementsByClassName('date-group-header-row');
        for (let h = 0; h < dateHeaders.length; h++) {
            const header = dateHeaders[h];
            const dateToken = header.getAttribute('data-date-token');
            
            let nextEl = header.nextElementSibling;
            let hasVisibleChildren = false;
            
            while (nextEl && !nextEl.classList.contains('date-group-header-row')) {
                if (nextEl.classList.contains('barber-job-row') && nextEl.style.display === "") {
                    hasVisibleChildren = true;
                    break;
                }
                nextEl = nextEl.nextElementSibling;
            }
            
            if(dateFilterValue !== '' && dateToken !== dateFilterValue) {
                header.style.display = "none";
            } else {
                header.style.display = hasVisibleChildren ? "" : "none";
            }
        }

        // Dynamic "No Records Found" message handler inside the main body
        let dynamicAlertRow = document.getElementById('search-zero-pipeline-row');
        if (visibleRowsCount === 0 && dataRows.length > 0) {
            if (!dynamicAlertRow) {
                dynamicAlertRow = document.createElement('tr');
                dynamicAlertRow.id = 'search-zero-pipeline-row';
                dynamicAlertRow.innerHTML = '<td colspan="8" class="text-center py-4 text-danger fw-semibold"><i class="fa-solid fa-ban me-2"></i>NO RECORD TO SHOW!</td>';
                document.getElementById('barberTableBody').appendChild(dynamicAlertRow);
            }
        } else {
            if (dynamicAlertRow) dynamicAlertRow.remove();
        }
    }

    function queryMatchClean(str) {
        return str.replace('#', '');
    }
</script>

</body>
</html>