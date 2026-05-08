<?php include('../header.php'); ?>
<hr>
<a href="../index.php">Home</a> |
<?php if ($isAdmin) { ?><a href="add.php">Add Player</a> |<?php } ?>
<a href="view.php">View Players</a>
<hr>

<?php
include('../config/db.php');
include('../pagination.php');

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$countryFilter = isset($_GET['country']) ? trim($_GET['country']) : "";
$roleFilter = isset($_GET['role']) ? trim($_GET['role']) : "";
$teamFilter = isset($_GET['team']) ? trim($_GET['team']) : "";

$where = [];

if ($search !== "") {
    $safeSearch = $conn->real_escape_string($search);
    $where[] = "p.name LIKE '%$safeSearch%'";
}

if ($countryFilter !== "") {
    $safeCountry = $conn->real_escape_string($countryFilter);
    $where[] = "p.country = '$safeCountry'";
}

if ($roleFilter !== "") {
    $safeRole = $conn->real_escape_string($roleFilter);
    $where[] = "p.role = '$safeRole'";
}

if ($teamFilter !== "") {
    $safeTeam = $conn->real_escape_string($teamFilter);
    if ($safeTeam === "No Team") {
        $where[] = "t.team_name IS NULL";
    } else {
        $where[] = "t.team_name = '$safeTeam'";
    }
}

$whereSQL = "";
if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

$perPage = 8;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;
$totalRows = $conn->query("
SELECT COUNT(DISTINCT p.player_id) AS total
FROM Player p
LEFT JOIN Plays_For pf ON p.player_id = pf.player_id
LEFT JOIN Team t ON pf.team_id = t.team_id
$whereSQL
")->fetch_assoc()["total"];
$totalPages = max(1, ceil($totalRows / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$countries = $conn->query("SELECT DISTINCT country FROM Player WHERE country IS NOT NULL AND country != '' ORDER BY country");
$roles = $conn->query("SELECT DISTINCT role FROM Player WHERE role IS NOT NULL AND role != '' ORDER BY role");
$teams = $conn->query("SELECT team_name FROM Team ORDER BY team_name");

$result = $conn->query("
SELECT p.player_id, p.name, p.country, p.role,
GROUP_CONCAT(DISTINCT t.team_name SEPARATOR ', ') AS teams
FROM Player p
LEFT JOIN Plays_For pf ON p.player_id = pf.player_id
LEFT JOIN Team t ON pf.team_id = t.team_id
$whereSQL
GROUP BY p.player_id
ORDER BY p.name
LIMIT $perPage OFFSET $offset
");
?>

<h2>Players List</h2>

<form class="filter-bar" method="GET">
    <div class="filter-field search-field">
        <label for="search">Search Player</label>
        <input id="search" type="search" name="search" value="<?php echo htmlspecialchars($search); ?>">
    </div>

    <div class="filter-field">
        <label for="country_filter">Country</label>
        <select id="country_filter" name="country">
            <option value="">All</option>
            <?php while ($countryRow = $countries->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($countryRow['country']); ?>" <?php if ($countryFilter === $countryRow['country']) { echo "selected"; } ?>>
                    <?php echo htmlspecialchars($countryRow['country']); ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="filter-field">
        <label for="role_filter">Role</label>
        <select id="role_filter" name="role">
            <option value="">All</option>
            <?php while ($roleRow = $roles->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($roleRow['role']); ?>" <?php if ($roleFilter === $roleRow['role']) { echo "selected"; } ?>>
                    <?php echo htmlspecialchars($roleRow['role']); ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="filter-field">
        <label for="team_filter">Team</label>
        <select id="team_filter" name="team">
            <option value="">All</option>
            <?php while ($teamRow = $teams->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($teamRow['team_name']); ?>" <?php if ($teamFilter === $teamRow['team_name']) { echo "selected"; } ?>>
                    <?php echo htmlspecialchars($teamRow['team_name']); ?>
                </option>
            <?php } ?>
            <option value="No Team" <?php if ($teamFilter === "No Team") { echo "selected"; } ?>>No Team</option>
        </select>
    </div>

    <div class="filter-actions">
        <input type="submit" value="Filter">
        <a href="view.php">Reset</a>
    </div>
</form>

<table border="1" cellpadding="10">
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Country</th>
    <th>Role</th>
    <th>Team</th>  
    <?php if ($isAdmin) { ?><th>Actions</th><?php } ?>
</tr>

<?php
$columnCount = $isAdmin ? 6 : 5;
if ($totalRows == 0) {
    echo "<tr>";
    echo "<td colspan='$columnCount'>No players found for this filter.</td>";
    echo "</tr>";
}

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row["player_id"] . "</td>";
    echo "<td>" . $row["name"] . "</td>";
    echo "<td>" . $row["country"] . "</td>";
    echo "<td>" . $row["role"] . "</td>";

    // SHOW TEAM 
   echo "<td>" . ($row["teams"] ? $row["teams"] : "No Team") . "</td>";

    if ($isAdmin) {
        echo "<td>
                <a href='edit.php?id=".$row["player_id"]."'>Edit</a> |
                <a href='delete.php?id=".$row["player_id"]."'>Delete</a>
              </td>";
    }
    echo "</tr>";
}
?>

</table>
<?php
$params = $_GET;
unset($params['page']);
renderPagination($page, $totalPages, $params);
?>

<br>
<?php if ($isAdmin) { ?><a href="add.php">Add Player</a><?php } ?>
<?php include('../footer.php'); ?>
