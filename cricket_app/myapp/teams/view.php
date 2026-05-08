<?php include('../header.php'); ?>
<?php
include('../config/db.php');
include('../pagination.php');

$search = isset($_GET['search']) ? trim($_GET['search']) : "";

$whereSQL = "";
if ($search !== "") {
    $safeSearch = $conn->real_escape_string($search);
    $whereSQL = "WHERE team_name LIKE '%$safeSearch%'";
}

$perPage = 8;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;
$totalRows = $conn->query("SELECT COUNT(*) AS total FROM Team $whereSQL")->fetch_assoc()["total"];
$totalPages = max(1, ceil($totalRows / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$result = $conn->query("SELECT * FROM Team $whereSQL ORDER BY team_name LIMIT $perPage OFFSET $offset");
?>

<hr>
<a href="../index.php">Home</a> |
<?php if ($isAdmin) { ?><a href="add.php">Add Team</a> |<?php } ?>
<a href="view.php">View Teams</a>
<hr>

<h2>Teams List</h2>

<form class="filter-bar" method="GET">
    <div class="filter-field search-field">
        <label for="search">Search Team</label>
        <input id="search" type="search" name="search" value="<?php echo htmlspecialchars($search); ?>">
    </div>

    <div class="filter-actions">
        <input type="submit" value="Filter">
        <a href="view.php">Reset</a>
    </div>
</form>

<table border="1" cellpadding="10">
<tr>
    <th>ID</th>
    <th>Team Name</th>
</tr>

<?php
if ($totalRows == 0) {
    echo "<tr>";
    echo "<td colspan='2'>No teams found for this filter.</td>";
    echo "</tr>";
}

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row["team_id"] . "</td>";
    echo "<td>" . $row["team_name"] . "</td>";
    echo "</tr>";
}
?>
</table>
<?php
$params = $_GET;
unset($params['page']);
renderPagination($page, $totalPages, $params);
?>
<?php include('../footer.php'); ?>
