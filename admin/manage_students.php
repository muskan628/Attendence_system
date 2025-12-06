<?php
include('../includes/session_check.php');
include('../includes/db_connect.php');

/* ----------------- 1) READ FILTER INPUTS ----------------- */

// Current page for pagination
$perPage = 20; // students per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 
    ? (int)$_GET['page'] 
    : 1;

$offset = ($page - 1) * $perPage;

// Search/filter values
$search_auid  = $_GET['search_auid']  ?? '';
$search_name  = $_GET['search_name']  ?? '';
$filter_dept  = $_GET['filter_dept']  ?? '';

/* ----------------- 2) LOAD DEPARTMENTS FOR DROPDOWN ----------------- */
$deptOptions = [];
$resDept = $conn->query("SELECT DISTINCT name AS department_name FROM Departments ORDER BY name");
if ($resDept && $resDept->num_rows > 0) {
    while ($row = $resDept->fetch_assoc()) {
        $deptOptions[] = $row['department_name'];
    }
}

/* ----------------- 3) BUILD WHERE CLAUSE FOR FILTERS ----------------- */

$whereParts = [];
$params = [];
$types = "";

// AUID exact/partial match
if (!empty($search_auid)) {
    $whereParts[] = "auid LIKE ?";
    $params[] = "%" . $search_auid . "%";
    $types .= "s";
}

// Name partial match (student_name)
if (!empty($search_name)) {
    $whereParts[] = "student_name LIKE ?";
    $params[] = "%" . $search_name . "%";
    $types .= "s";
}

// Department exact match
if (!empty($filter_dept)) {
    $whereParts[] = "department = ?";
    $params[] = $filter_dept;
    $types .= "s";
}

$whereSQL = "";
if (count($whereParts) > 0) {
    $whereSQL = "WHERE " . implode(" AND ", $whereParts);
}

/* ----------------- 4) TOTAL ROW COUNT (FOR PAGINATION) ----------------- */

$countSql = "SELECT COUNT(*) AS total FROM admission $whereSQL";
$stmtCount = $conn->prepare($countSql);
if ($stmtCount && !empty($whereParts)) {
    // bind dynamic filters
    $stmtCount->bind_param($types, ...$params);
}
$stmtCount->execute();
$countResult = $stmtCount->get_result();
$totalRows = 0;
if ($countResult && $countResult->num_rows > 0) {
    $totalRows = (int)$countResult->fetch_assoc()['total'];
}
$stmtCount->close();

$totalPages = $totalRows > 0 ? ceil($totalRows / $perPage) : 1;
if ($page > $totalPages) $page = $totalPages;

/* ----------------- 5) FETCH STUDENT ROWS WITH LIMIT/OFFSET ------------- */

$mainSql = "SELECT id, auid, student_name, department, program, current_class 
            FROM admission 
            $whereSQL
            ORDER BY id DESC
            LIMIT ? OFFSET ?";

$stmt = $conn->prepare($mainSql);

if (!empty($whereParts)) {
    // add types for LIMIT (i) and OFFSET (i)
    $fullTypes = $types . "ii";
    $paramsWithLimit = $params;
    $paramsWithLimit[] = $perPage;
    $paramsWithLimit[] = $offset;

    $stmt->bind_param($fullTypes, ...$paramsWithLimit);
} else {
    $stmt->bind_param("ii", $perPage, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>

<div class="sidebar">
    <h2>Akal University</h2>
    <ul>
        <li onclick="window.location.href='ad_dashboard.php'">Dashboard</li>
        <li>Detailed Reports</li>
        <li>Defaulter List</li>
        <li>Manage Users</li>
        <li class="active">Manage Students</li>
        <li onclick="window.location.href='manage_departments.php'">Manage Departments</li>
        <li onclick="window.location.href='settings.php'">Settings</li>
    </ul>
    
    <!-- Logout Button -->
    <div class="logout-box">
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="main">
    <h1>Manage Students</h1>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert-success" id="msg-alert">
            <div class="alert-icon">✓</div>
            <div class="alert-text">
                <strong>Success</strong>
                <span><?= htmlspecialchars($_GET['msg']) ?></span>
            </div>
            <button class="alert-close" onclick="document.getElementById('msg-alert').style.display='none';">×</button>
        </div>
    <?php endif; ?>

    <!-- 🔍 SEARCH + FILTER BAR -->
    <div class="table-card">
        <h2>Search & Filter</h2>
        <form method="GET" action="manage_students.php" class="filter-form">
            <div class="form-row-inline">
                <div class="form-group">
                    <label for="search_auid">AUID</label>
                    <input type="text" id="search_auid" name="search_auid" 
                           value="<?= htmlspecialchars($search_auid) ?>" 
                           placeholder="Search by AUID">
                </div>

                <div class="form-group">
                    <label for="search_name">Name</label>
                    <input type="text" id="search_name" name="search_name" 
                           value="<?= htmlspecialchars($search_name) ?>" 
                           placeholder="Search by Name">
                </div>

                <div class="form-group">
                    <label for="filter_dept">Department</label>
                    <select id="filter_dept" name="filter_dept">
                        <option value="">All Departments</option>
                        <?php foreach ($deptOptions as $dept): ?>
                            <option value="<?= htmlspecialchars($dept) ?>"
                                <?= ($filter_dept === $dept) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-top:24px;">
                    <button type="submit" class="btn-primary">Apply</button>
                    <a href="manage_students.php" class="btn-outline">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- 📋 STUDENT LIST -->
    <div class="table-card">
        <h2>Student List (<?= $totalRows ?> found)</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>AUID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Program</th>
                <th>Class</th>
                <th>Actions</th>
            </tr>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td><?= htmlspecialchars($row['auid']) ?></td>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= htmlspecialchars($row['department']) ?></td>
                        <td><?= htmlspecialchars($row['program']) ?></td>
                        <td><?= htmlspecialchars($row['current_class']) ?></td>
                        <td>
                            <a href="edit_student.php?id=<?= (int)$row['id'] ?>">Edit</a>
                            |
                            <form action="delete_student.php" method="POST" style="display:inline;" 
                                  onsubmit="return confirm('Are you sure you want to delete this student?');">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                <button type="submit" style="background:none;border:none;color:red;cursor:pointer;">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No students found.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- 📄 PAGINATION -->
    <!-- 📄 SMART PAGINATION -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php
            $baseQuery = $_GET;
            
            // Helper to generate link
            function pageLink($p, $base, $label=null, $active=false) {
                $base['page'] = $p;
                $url = '?' . http_build_query($base);
                $cls = $active ? 'active-page' : '';
                $lbl = $label ?? $p;
                return "<a href=\"{$url}\" class=\"{$cls}\">{$lbl}</a>";
            }

            // Prev
            if ($page > 1) {
                echo pageLink($page - 1, $baseQuery, '&laquo; Prev');
            } else {
                echo '<span class="disabled">&laquo; Prev</span>';
            }

            // Calculate pages to show
            // Always show 1 and Last.
            // Show window around current page (e.g. current-2 to current+2)
            $window = 2;
            $pagesToShow = [1, $totalPages]; // Ensure first and last are always there
            for ($i = max(2, $page - $window); $i <= min($totalPages - 1, $page + $window); $i++) {
                $pagesToShow[] = $i;
            }
            $pagesToShow = array_unique($pagesToShow);
            sort($pagesToShow);

            $prevPageNum = 0;
            foreach ($pagesToShow as $p) {
                // If gap exists, show dots
                if ($prevPageNum > 0 && $p > $prevPageNum + 1) {
                    echo '<span class="pagination-dots">...</span>';
                }
                
                echo pageLink($p, $baseQuery, null, ($p == $page));
                $prevPageNum = $p;
            }

            // Next
            if ($page < $totalPages) {
                echo pageLink($page + 1, $baseQuery, 'Next &raquo;');
            } else {
                echo '<span class="disabled">Next &raquo;</span>';
            }
            ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
<?php
$stmt->close();
?>
