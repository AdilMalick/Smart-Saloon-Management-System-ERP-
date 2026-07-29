<?php
session_start();
include('db.php');

// Security Check: Only executive admin can access this services infrastructure
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$message = ""; $msg_type = "success";

// ================= C - CREATE OPERATION (Add New Service) =================
if (isset($_POST['add_service'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $duration = mysqli_real_escape_string($conn, $_POST['duration']); // Direct minutes save honge (e.g. 30, 45)

    if (!empty($name) && !empty($price) && !empty($duration)) {
        $insert_sql = "INSERT INTO services (name, price, duration) VALUES ('$name', '$price', '$duration')";
        if (mysqli_query($conn, $insert_sql)) {
            $message = "Excellent! New salon service catalog item successfully initialized.";
            $msg_type = "success";
        } else {
            $message = "Database Insertion Fault: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    } else {
        $message = "Validation Error: All form parameters must be fully declared!";
        $msg_type = "warning";
    }
}

// ================= D - DELETE OPERATION (Remove Service) =================
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    $delete_sql = "DELETE FROM services WHERE id = '$delete_id'";
    if (mysqli_query($conn, $delete_sql)) {
        $message = "Service entity successfully dropped from current business catalog mappings.";
        $msg_type = "warning";
    } else {
        $message = "Integrity Constraint Violation: Cannot delete service currently linked to active client appointments!";
        $msg_type = "danger";
    }
}

// ================= R - READ OPERATION (Fetch All Services) =================
$services_query = "SELECT * FROM services ORDER BY id DESC";
$services_result = mysqli_query($conn, $services_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Saloon ERP - Services Catalog Framework</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background-color: #1e1e24; color: white; transition: all 0.3s; }
        .sidebar .nav-link { color: #a2a3b6; padding: 12px 20px; font-weight: 500; border-radius: 8px; margin: 5px 15px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #fd7e14; color: white; }
        .erp-card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .search-box { position: relative; max-width: 300px; }
        .search-box i { position: absolute; left: 12px; top: 12px; color: #aaa; }
        .search-box input { padding-left: 35px; border-radius: 20px; }
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
                <a href="manage_services.php" class="nav-link active"><i class="fa-solid fa-scissors me-2"></i> Services Control</a>
                <a href="manage_appointments.php" class="nav-link"><i class="fa-solid fa-calendar-check me-2"></i> Appointments Panel</a>
                <a href="manage_users.php" class="nav-link"><i class="fa-solid fa-users-gear me-2"></i> Users & Barbers</a>
                <div class="border-top border-secondary my-3 mx-3"></div>
                <a href="logout.php" class="nav-link text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Systems Logout</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 px-md-4 py-4">
            
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <h1 class="h3 fw-bold text-dark m-0">Menu & Services Catalog Core Configuration</h1>
                <div class="search-box shadow-sm">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="serviceSearchInput" class="form-control" placeholder="Search menu...">
                </div>
            </div>

            <?php if(!empty($message)) { ?>
                <div class="alert alert-<?php echo $msg_type; ?> shadow-sm text-center fw-semibold"><?php echo $message; ?></div>
            <?php } ?>

            <div class="row g-4">
                
                <div class="col-lg-4">
                    <div class="card erp-card border-0 shadow-sm p-4 sticky-top" style="top:90px; z-index:1;">
                        <h5 class="fw-bold text-dark mb-3 border-bottom pb-2"><i class="fa-solid fa-circle-plus text-success me-2"></i>Initialize Service</h5>
                        
                        <form action="manage_services.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Service Menu Identity</label>
                                <input type="text" name="name" class="form-control py-2" placeholder="e.g. Royal Beard Grooming" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Base Price Tariff (PKR)</label>
                                <div class="input-group">
                                    <input type="number" name="price" class="form-control py-2" placeholder="e.g. 1200" min="1" required>
                                    <span class="input-group-text bg-light fw-bold text-muted">Rs</span>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Estimated Target Duration (Mins)</label>
                                <div class="input-group">
                                    <input type="number" name="duration" class="form-control py-2" placeholder="e.g. 30" min="1" required>
                                    <span class="input-group-text bg-light fw-bold text-muted">Mins</span>
                                </div>
                            </div>
                            
                            <button type="submit" name="add_service" class="btn btn-dark w-100 fw-bold py-2 shadow-sm text-uppercase"><i class="fa-solid fa-cloud-arrow-up me-2 text-warning"></i>Commit to Ledger</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card erp-card border-0 shadow-sm overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="serviceCatalogTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="ps-3">ID</th>
                                        <th>Service Specifications</th>
                                        <th>Target Slot Duration</th>
                                        <th>Configured Bill Tariff</th>
                                        <th class="text-center">Lifecycle Framework</th>
                                    </tr>
                                </thead>
                                <tbody id="serviceTableBody">
                                    <?php 
                                    if (mysqli_num_rows($services_result) > 0) {
                                        while($row = mysqli_fetch_assoc($services_result)) {
                                            echo "<tr class='service-catalog-item-row'>";
                                            echo "<td class='ps-3 fw-bold text-secondary service-id-token'>#" . $row['id'] . "</td>";
                                            echo "<td class='fw-bold text-dark service-name-token'><i class='fa-solid fa-scissors me-2 text-muted small'></i>" . htmlspecialchars($row['name']) . "</td>";
                                            
                                            // DIRECT PARSING: Database se direct integer value fetch ho kar 'Mins' ke sath display hogi
                                            echo "<td class='text-secondary fw-semibold'><i class='fa-regular fa-clock me-2 text-primary'></i>" . htmlspecialchars($row['duration']) . " Mins</td>";
                                            
                                            echo "<td><span class='badge bg-success-subtle text-success fw-bold px-3 py-2 border border-success-subtle' style='font-size:0.85rem;'>" . $row['price'] . " Rs</span></td>";
                                            
                                            echo "<td class='text-center'>
                                                    <a href='edit_service.php?id=" . $row['id'] . "' class='btn btn-sm btn-light border fw-bold px-3 me-1 shadow-sm'><i class='fa-regular fa-pen-to-square text-primary me-1'></i> Edit</a>
                                                    <a href='manage_services.php?delete_id=" . $row['id'] . "' class='btn btn-sm btn-outline-danger fw-semibold px-2' onclick=\"return confirm('Drop this structural service node from the operational master matrix?')\"><i class='fa-solid fa-trash-can me-1'></i> Drop</a>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr class='no-catalog-items-row'><td colspan='5' class='text-center py-5 text-muted fw-semibold'><i class='fa-solid fa-folder-minus display-6 d-block mb-2 opacity-50'></i>System diagnostics report zero active services in memory.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('serviceSearchInput').addEventListener('keyup', function() {
        const searchKeyword = this.value.toLowerCase().trim();
        const catalogRows = document.getElementsByClassName('service-catalog-item-row');
        let totalActiveMatches = 0;

        for (let i = 0; i < catalogRows.length; i++) {
            const nameField = catalogRows[i].getElementsByClassName('service-name-token')[0].textContent.toLowerCase();
            const idField = catalogRows[i].getElementsByClassName('service-id-token')[0].textContent.toLowerCase();

            if (nameField.includes(searchKeyword) || idField.includes(searchKeyword)) {
                catalogRows[i].style.display = "";
                totalActiveMatches++;
            } else {
                catalogRows[i].style.display = "none";
            }
        }

        let runtimeSearchRow = document.getElementById('search-zero-services-row');
        if (totalActiveMatches === 0 && catalogRows.length > 0) {
            if (!runtimeSearchRow) {
                runtimeSearchRow = document.createElement('tr');
                runtimeSearchRow.id = 'search-zero-services-row';
                runtimeSearchRow.innerHTML = '<td colspan="5" class="text-center py-4 text-danger fw-semibold"><i class="fa-solid fa-triangle-exclamation me-2"></i>NO RECORD TO SHOW!</td>';
                document.getElementById('serviceTableBody').appendChild(runtimeSearchRow);
            }
        } else {
            if (runtimeSearchRow) runtimeSearchRow.remove();
        }
    });
</script>

</body>
</html>