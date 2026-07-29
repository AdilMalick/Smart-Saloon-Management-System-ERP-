<?php
session_start();
include('db.php');

// Security Check: Only executive admin can access this system control
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// ================= AJAX BACKEND ENGINE API =================
if (isset($_GET['fetch_appointments']) && isset($_GET['uid']) && isset($_GET['urole'])) {
    $uid = mysqli_real_escape_string($conn, $_GET['uid']);
    $urole = strtolower($_GET['urole']);
    
    $query = ($urole == 'customer') ? "a.customer_id = '$uid'" : "a.barber_id = '$uid'";
    $partner_header = ($urole == 'customer') ? "Assigned Barber" : "Booked Customer";
    
    $api_sql = "SELECT a.*, (CASE WHEN '$urole'='customer' THEN b.name ELSE c.name END) AS relative_name, s.name AS service_name, s.price 
                FROM appointments a 
                LEFT JOIN users b ON a.barber_id = b.id 
                LEFT JOIN users c ON a.customer_id = c.id 
                JOIN services s ON a.service_id = s.id 
                WHERE $query 
                ORDER BY a.booking_date DESC, a.booking_time DESC";
    
    $api_res = mysqli_query($conn, $api_sql);
    
    echo '<table class="table table-sm table-striped align-middle border text-start mb-0" style="font-size:0.85rem;">
            <thead class="table-secondary">
                <tr>
                    <th>Date/Time</th>
                    <th>'.$partner_header.'</th>
                    <th>Service</th>
                    <th>Bill</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';
            
    if (mysqli_num_rows($api_res) > 0) {
        while($arow = mysqli_fetch_assoc($api_res)) {
            $b_style = 'bg-warning text-dark';
            if($arow['status'] == 'approved') $b_style = 'bg-info text-white';
            if($arow['status'] == 'completed') $b_style = 'bg-success text-white';
            if($arow['status'] == 'cancelled') $b_style = 'bg-danger text-white';
            
            echo '<tr class="appointment-row">
                    <td>
                        <div class="fw-bold">'.date('d-M-Y', strtotime($arow['booking_date'])).'</div>
                        <div class="text-muted" style="font-size:0.75rem;">'.date('h:i A', strtotime($arow['booking_time'])).'</div>
                    </td>
                    <td class="fw-semibold">'.htmlspecialchars($arow['relative_name']).'</td>
                    <td>'.htmlspecialchars($arow['service_name']).'</td>
                    <td class="fw-bold">'.$arow['price'].' Rs</td>
                    <td><span class="badge '.$b_style.' text-uppercase status-badge" style="font-size:0.7rem; padding:4px 8px;">'.$arow['status'].'</span></td>
                  </tr>';
        }
    } else {
        echo '<tr><td colspan="5" class="text-center py-4 text-muted fw-semibold">Is profile ki koi appointment history record nahi hai.</td></tr>';
    }
    echo '</tbody></table>';
    exit(); 
}

$message = ""; $msg_type = "success";

// ================= SOFT DELETE OPERATION =================
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    if ($delete_id == $_SESSION['user_id']) {
        $message = "CRITICAL WARNING: System restricts deleting your own active Administrator profile!";
        $msg_type = "danger";
    } else {
        $soft_delete_sql = "UPDATE users SET is_deleted = 1 WHERE id = '$delete_id'";
        if (mysqli_query($conn, $soft_delete_sql)) {
            $message = "User account successfully archived! Data preserved for historical activity reports.";
            $msg_type = "warning";
        } else {
            $message = "Database Error: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    }
}

// ================= RESTORE USER OPERATION =================
if (isset($_GET['restore_id'])) {
    $restore_id = $_GET['restore_id'];
    $restore_sql = "UPDATE users SET is_deleted = 0 WHERE id = '$restore_id'";
    if (mysqli_query($conn, $restore_sql)) {
        $message = "User account successfully restored to active status!";
        $msg_type = "success";
    } else {
        $message = "Database Error: " . mysqli_error($conn);
        $msg_type = "danger";
    }
}

// ================= ACTIVE TAB FILTER DEFINITION =================
$user_tab = isset($_GET['tab']) ? $_GET['tab'] : 'customers';

// Fetching Base Clusters
$barber_result = mysqli_query($conn, "SELECT * FROM users WHERE role = 'barber' AND is_deleted = 0 ORDER BY id DESC");
$customer_result = mysqli_query($conn, "SELECT * FROM users WHERE role = 'customer' AND is_deleted = 0 ORDER BY id DESC");
$deleted_result = mysqli_query($conn, "SELECT * FROM users WHERE is_deleted = 1 ORDER BY id DESC");

$count_barbers = mysqli_num_rows($barber_result);
$count_customers = mysqli_num_rows($customer_result);
$count_deleted = mysqli_num_rows($deleted_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Saloon ERP - Advanced User Auditing</title>
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
        .search-box { position: relative; max-width: 350px; }
        .search-box i { position: absolute; left: 12px; top: 12px; color: #aaa; }
        .search-box input { padding-left: 35px; border-radius: 20px; }
        
        /* New CSS for Clickable Summary Counters */
        .status-card { transition: all 0.2s ease-in-out; cursor: pointer; border: 2px solid transparent; }
        .status-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
        .status-card.active-filter { transform: scale(1.05); border-color: #343a40; opacity: 1 !important; }
        .status-card.inactive-filter { opacity: 0.5; }
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
                <a href="admin_dashboard.php" class="nav-link"><i class="fa-solid fa-chart-pie me-2"></i> Performance</a>
                <a href="manage_services.php" class="nav-link"><i class="fa-solid fa-scissors me-2"></i> Services Control</a>
                <a href="manage_appointments.php" class="nav-link"><i class="fa-solid fa-calendar-check me-2"></i> Appointments Panel</a>
                <a href="manage_users.php" class="nav-link active"><i class="fa-solid fa-users-gear me-2"></i> Users & Barbers</a>
                <div class="border-top border-secondary my-3 mx-3"></div>
                <a href="logout.php" class="nav-link text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Systems Logout</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 px-md-4 py-4">
            
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <h1 class="h3 fw-bold text-dark m-0">User Accounts Authentication Ledger</h1>
                
                <div class="search-box shadow-sm">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="erpUserSearch" class="form-control" placeholder="Search record...">
                </div>
            </div>

            <?php if(!empty($message)) { ?>
                <div class="alert alert-<?php echo $msg_type; ?> shadow-sm text-center fw-semibold"><?php echo $message; ?></div>
            <?php } ?>

            <ul class="nav nav-tabs border-bottom mb-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($user_tab == 'customers') ? 'active' : ''; ?>" href="manage_users.php?tab=customers">
                        <i class="fa-solid fa-users text-success me-1"></i> Active Customers 
                        <span class="badge bg-success text-white ms-1 rounded-pill"><?php echo $count_customers; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($user_tab == 'barbers') ? 'active' : ''; ?>" href="manage_users.php?tab=barbers">
                        <i class="fa-solid fa-user-scissors text-primary me-1"></i> Active Barbers 
                        <span class="badge bg-primary text-white ms-1 rounded-pill"><?php echo $count_barbers; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($user_tab == 'deleted') ? 'active' : ''; ?>" href="manage_users.php?tab=deleted">
                        <i class="fa-solid fa-box-archive text-danger me-1"></i> Archived / Deleted Accounts 
                        <span class="badge bg-danger text-white ms-1 rounded-pill"><?php echo $count_deleted; ?></span>
                    </a>
                </li>
            </ul>

            <div class="card erp-card border-0 shadow-sm overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="erpUserTable">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">ID</th>
                                <th>Profile Identity</th>
                                <th>Email Credentials</th>
                                <th>Registration Date</th>
                                <th class="text-center">Activity Logs</th>
                                <th class="text-center">Action Control</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <?php
                            if ($user_tab == 'customers') $target_result = $customer_result;
                            elseif ($user_tab == 'barbers') $target_result = $barber_result;
                            else $target_result = $deleted_result;

                            if (mysqli_num_rows($target_result) > 0) {
                                while($row = mysqli_fetch_assoc($target_result)) {
                                    $target_id = $row['id'];
                                    $target_role = $row['role'];

                                    $field = ($target_role == 'customer') ? 'customer_id' : 'barber_id';
                                    
                                    $total_query = "SELECT COUNT(id) as total FROM appointments WHERE $field = '$target_id'";
                                    $approved_query = "SELECT COUNT(id) as total FROM appointments WHERE $field = '$target_id' AND status = 'approved'";
                                    $pending_query = "SELECT COUNT(id) as total FROM appointments WHERE $field = '$target_id' AND status = 'pending'";
                                    $completed_query = "SELECT COUNT(id) as total FROM appointments WHERE $field = '$target_id' AND status = 'completed'";
                                    $cancelled_query = "SELECT COUNT(id) as total FROM appointments WHERE $field = '$target_id' AND status = 'cancelled'";
                                    $rev_query = "SELECT SUM(s.price) as revenue FROM appointments a JOIN services s ON a.service_id = s.id WHERE a.$field = '$target_id' AND a.status = 'completed'";

                                    $t_count = mysqli_fetch_assoc(mysqli_query($conn, $total_query))['total'] ?? 0;
                                    $a_count = mysqli_fetch_assoc(mysqli_query($conn, $approved_query))['total'] ?? 0;
                                    $p_count = mysqli_fetch_assoc(mysqli_query($conn, $pending_query))['total'] ?? 0;
                                    $comp_count = mysqli_fetch_assoc(mysqli_query($conn, $completed_query))['total'] ?? 0;
                                    $c_count = mysqli_fetch_assoc(mysqli_query($conn, $cancelled_query))['total'] ?? 0;
                                    $revenue = mysqli_fetch_assoc(mysqli_query($conn, $rev_query))['revenue'] ?? 0;

                                    echo "<tr class='user-row-data'>";
                                    echo "<td class='ps-3 fw-bold text-secondary target-id-col'>#" . $row['id'] . "</td>";
                                    echo "<td class='fw-semibold text-dark target-name-col'>" . htmlspecialchars($row['name']) . " <span class='badge bg-light text-dark text-capitalize border small'>" . $row['role'] . "</span></td>";
                                    echo "<td class='text-muted small target-email-col'><i class='fa-regular fa-envelope me-1'></i>" . htmlspecialchars($row['email']) . "</td>";
                                    echo "<td class='text-secondary small'>" . date('d-M-Y', strtotime($row['created_at'])) . "</td>";
                                    
                                    echo "<td class='text-center'>
                                            <button type='button' class='btn btn-sm btn-light border fw-bold text-dark shadow-sm px-3 audit-trigger-btn' 
                                                data-bs-toggle='modal' 
                                                data-bs-target='#activityModal' 
                                                data-id='".$target_id."'
                                                data-name='".htmlspecialchars($row['name'])."'
                                                data-role='".strtoupper($row['role'])."'
                                                data-reg='".date('d-M-Y', strtotime($row['created_at']))."'
                                                data-total='".$t_count."'
                                                data-approved='".$a_count."'
                                                data-pending='".$p_count."'
                                                data-completed='".$comp_count."'
                                                data-cancelled='".$c_count."'
                                                data-revenue='".$revenue."'>
                                                <i class='fa-solid fa-magnifying-glass-chart text-primary me-1'></i> Inspect History
                                            </button>
                                          </td>";
                                    
                                    echo "<td class='text-center'>";
                                    if ($row['is_deleted'] == 0) {
                                        echo "<a href='manage_users.php?tab=".$user_tab."&delete_id=" . $row['id'] . "' class='btn btn-sm btn-outline-danger fw-semibold px-3' onclick=\"return confirm('Archive this account? Active appointments will be preserved!')\"><i class='fa-solid fa-user-minus me-1'></i> Delete</a>";
                                    } else {
                                        echo "<a href='manage_users.php?tab=".$user_tab."&restore_id=" . $row['id'] . "' class='btn btn-sm btn-success fw-bold px-3'><i class='fa-solid fa-trash-arrow-up me-1'></i> Restore</a>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr class='no-records-row'><td colspan='6' class='text-center py-5 text-muted fw-semibold'><i class='fa-solid fa-folder-open display-6 d-block mb-2 opacity-50'></i>NO RECORD TO SHOW!</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="activityModalLabel"><i class="fa-solid fa-shield-halved text-warning me-2"></i>Advanced Account Operations & Activity Audit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                
                <div class="row align-items-center mb-4 text-center text-md-start">
                    <div class="col-md-2 text-center mb-3 mb-md-0">
                        <i class="fa-solid fa-id-card text-secondary display-3"></i>
                    </div>
                    <div class="col-md-6">
                        <h3 class="fw-bold text-dark mb-1" id="modal-user-name">User Name</h3>
                        <span class="badge bg-secondary px-3 py-1 fw-bold" id="modal-user-role">CUSTOMER</span>
                        <div class="text-muted small mt-2"><i class="fa-regular fa-calendar-check me-1"></i>Platform Onboarding Date: <span id="modal-user-reg" class="fw-bold">Date</span></div>
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                        <div class="card border-0 bg-success-subtle text-success p-3 rounded-3 text-center">
                            <h6 class="text-uppercase fw-bold opacity-75 mb-1 style" style="font-size:0.75rem;" id="modal-revenue-title">Gross Volume</h6>
                            <h3 class="fw-bold mb-0" id="modal-user-revenue">0 Rs</h3>
                        </div>
                    </div>
                </div>
                
                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3"><i class="fa-solid fa-filter me-2"></i>Click to Filter Records</h6>
                
                <div class="row g-2 mb-4 text-center">
                    <div class="col">
                        <div class="bg-dark text-white p-2 rounded shadow-sm status-card active-filter" onclick="filterAppointments('all', this)">
                            <div class="small opacity-75">Total</div>
                            <h4 class="fw-bold mb-0" id="modal-user-total">0</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="bg-info text-white p-2 rounded shadow-sm status-card" onclick="filterAppointments('approved', this)">
                            <div class="small opacity-75">Approved</div>
                            <h4 class="fw-bold mb-0" id="modal-user-approved">0</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="bg-warning text-dark p-2 rounded shadow-sm status-card" onclick="filterAppointments('pending', this)">
                            <div class="small opacity-75">Pending</div>
                            <h4 class="fw-bold mb-0" id="modal-user-pending">0</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="bg-success text-white p-2 rounded shadow-sm status-card" onclick="filterAppointments('completed', this)">
                            <div class="small opacity-75">Completed</div>
                            <h4 class="fw-bold mb-0" id="modal-user-completed">0</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="bg-danger text-white p-2 rounded shadow-sm status-card" onclick="filterAppointments('cancelled', this)">
                            <div class="small opacity-75">Cancelled</div>
                            <h4 class="fw-bold mb-0" id="modal-user-cancelled">0</h4>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3"><i class="fa-solid fa-list-ul me-2"></i>Itemized Historical Schedule Stream</h6>
                <div class="card border-0 shadow-sm overflow-hidden mb-2">
                    <div id="modal-live-table-container" class="table-responsive text-center py-4 text-muted">
                        <i class="fa-solid fa-spinner fa-spin display-6 d-block mb-2 text-primary"></i> Streaming ledger entries securely from ERP database kernel...
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // 1. CLIENT-SIDE INSTANT REAL-TIME SEARCH SCRIPT (For Main Table)
    document.getElementById('erpUserSearch').addEventListener('keyup', function() {
        const searchVal = this.value.toLowerCase().trim();
        const tableRows = document.getElementsByClassName('user-row-data');
        let visibleRowsCount = 0;

        for (let i = 0; i < tableRows.length; i++) {
            const nameText = tableRows[i].getElementsByClassName('target-name-col')[0].textContent.toLowerCase();
            const emailText = tableRows[i].getElementsByClassName('target-email-col')[0].textContent.toLowerCase();
            const idText = tableRows[i].getElementsByClassName('target-id-col')[0].textContent.toLowerCase();

            if (nameText.includes(searchVal) || emailText.includes(searchVal) || idText.includes(searchVal)) {
                tableRows[i].style.display = "";
                visibleRowsCount++;
            } else {
                tableRows[i].style.display = "none";
            }
        }

        let noRecRow = document.getElementById('search-no-records-row');
        if (visibleRowsCount === 0 && tableRows.length > 0) {
            if (!noRecRow) {
                noRecRow = document.createElement('tr');
                noRecRow.id = 'search-no-records-row';
                noRecRow.innerHTML = '<td colspan="6" class="text-center py-4 text-danger fw-semibold"><i class="fa-solid fa-ban me-2"></i>NO RECORD TO SHOW!</td>';
                document.getElementById('userTableBody').appendChild(noRecRow);
            }
        } else {
            if (noRecRow) noRecRow.remove();
        }
    });

    // 2. BACKEND AJAX DISPATCHER FOR MODAL STREAMING
    const activityModal = document.getElementById('activityModal');
    if (activityModal) {
        activityModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            
            const uid = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const role = button.getAttribute('data-role');
            const reg = button.getAttribute('data-reg');
            const total = button.getAttribute('data-total');
            const approved = button.getAttribute('data-approved');
            const pending = button.getAttribute('data-pending');
            const completed = button.getAttribute('data-completed');
            const cancelled = button.getAttribute('data-cancelled');
            const revenue = button.getAttribute('data-revenue');

            // Set basic variables
            document.getElementById('modal-user-name').textContent = name;
            document.getElementById('modal-user-role').textContent = role;
            document.getElementById('modal-user-reg').textContent = reg;
            document.getElementById('modal-user-total').textContent = total;
            document.getElementById('modal-user-approved').textContent = approved;
            document.getElementById('modal-user-pending').textContent = pending;
            document.getElementById('modal-user-completed').textContent = completed;
            document.getElementById('modal-user-cancelled').textContent = cancelled;
            document.getElementById('modal-user-revenue').textContent = revenue + " Rs";

            const titleElement = document.getElementById('modal-revenue-title');
            if (role === 'BARBER') {
                titleElement.textContent = "Gross Stylist Earnings";
                document.getElementById('modal-user-role').className = "badge bg-primary px-3 py-1 fw-bold";
            } else {
                titleElement.textContent = "Total Financial Value Spent";
                document.getElementById('modal-user-role').className = "badge bg-success px-3 py-1 fw-bold";
            }

            // Set live spinner loader before AJAX resolves
            const tableContainer = document.getElementById('modal-live-table-container');
            tableContainer.innerHTML = '<div class="py-4 text-center text-muted"><i class="fa-solid fa-spinner fa-spin display-6 d-block mb-2 text-primary"></i>Streaming itemized entries from kernel database layer...</div>';

            // FIRE ASYNCHRONOUS AJAX NETWORK FETCH
            fetch(`manage_users.php?fetch_appointments=1&uid=${uid}&urole=${role.toLowerCase()}`)
                .then(response => response.text())
                .then(htmlResponse => {
                    tableContainer.innerHTML = htmlResponse;
                    // Reset to "Total" filter whenever new data loads
                    const totalBox = document.querySelector('.status-card');
                    filterAppointments('all', totalBox); 
                })
                .catch(err => {
                    tableContainer.innerHTML = '<div class="py-3 text-center text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i>AJAX Stream System Error: Unable to fetch data ledger layers.</div>';
                });
        });
    }

    // 3. FRONTEND FILTERING LOGIC FOR MODAL BUTTONS
    function filterAppointments(targetStatus, clickedElement) {
        // UI Logic: Dim non-selected boxes and highlight the selected one
        const allCards = document.querySelectorAll('.status-card');
        allCards.forEach(card => {
            card.classList.remove('active-filter');
            card.classList.add('inactive-filter');
        });
        
        clickedElement.classList.remove('inactive-filter');
        clickedElement.classList.add('active-filter');

        // Logic: Filter Table Rows
        const tableContainer = document.getElementById('modal-live-table-container');
        const rows = tableContainer.querySelectorAll('.appointment-row');
        let visibleCount = 0;

        // Remove old 'no records' message if exists
        const oldNoMsg = document.getElementById('frontend-no-record');
        if(oldNoMsg) oldNoMsg.remove();

        rows.forEach(row => {
            const badge = row.querySelector('.status-badge');
            if(badge) {
                const rowStatus = badge.innerText.toLowerCase().trim();
                if(targetStatus === 'all' || rowStatus === targetStatus) {
                    row.style.display = ''; // Show row
                    visibleCount++;
                } else {
                    row.style.display = 'none'; // Hide row
                }
            }
        });

        // Agar filter lagne ke baad us category mein koi record na ho to message show karo
        if(visibleCount === 0 && rows.length > 0) {
            const tbody = tableContainer.querySelector('tbody');
            if(tbody) {
                const tr = document.createElement('tr');
                tr.id = 'frontend-no-record';
                tr.innerHTML = `<td colspan="5" class="text-center py-4 text-muted fw-bold"><i class="fa-solid fa-folder-open d-block mb-2 fs-3 opacity-50"></i>NO <b>${targetStatus.toUpperCase()}</b> RECORD TO SHOW.</td>`;
                tbody.appendChild(tr);
            }
        }
    }
</script>

</body>
</html>