<?php include('../header.php'); ?>
<?php
include('../config/db.php');
include('../pagination.php');

$venueSearch = isset($_GET['venue']) ? trim($_GET['venue']) : "";
$formatFilter = isset($_GET['format']) ? trim($_GET['format']) : "";
$typeFilter = isset($_GET['type']) ? trim($_GET['type']) : "";
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : "";
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : "";

$where = [];

if ($venueSearch !== "") {
    $safeVenue = $conn->real_escape_string($venueSearch);
    $where[] = "venue LIKE '%$safeVenue%'";
}

if ($formatFilter !== "") {
    $safeFormat = $conn->real_escape_string($formatFilter);
    $where[] = "format = '$safeFormat'";
}

if ($typeFilter !== "") {
    $safeType = $conn->real_escape_string($typeFilter);
    $where[] = "Match_Type = '$safeType'";
}

if ($dateFrom !== "") {
    $safeDateFrom = $conn->real_escape_string($dateFrom);
    $where[] = "match_date >= '$safeDateFrom'";
}

if ($dateTo !== "") {
    $safeDateTo = $conn->real_escape_string($dateTo);
    $where[] = "match_date <= '$safeDateTo'";
}

$whereSQL = "";
if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

$perPage = 8;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;
$totalRows = $conn->query("SELECT COUNT(*) AS total FROM Match_Details $whereSQL")->fetch_assoc()["total"];
$totalPages = max(1, ceil($totalRows / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$formats = $conn->query("SELECT DISTINCT format FROM Match_Details WHERE format IS NOT NULL AND format != '' ORDER BY format");
$types = $conn->query("SELECT DISTINCT Match_Type FROM Match_Details WHERE Match_Type IS NOT NULL AND Match_Type != '' ORDER BY Match_Type");

$result = $conn->query("SELECT * FROM Match_Details $whereSQL ORDER BY match_date DESC LIMIT $perPage OFFSET $offset");
?>

<hr>
<a href="../index.php">Home</a> |
<?php if ($isAdmin) { ?><a href="add.php">Add Match</a> |<?php } ?>
<a href="view.php">View Matches</a>
<hr>

<h2>Matches</h2>

<form class="filter-bar" method="GET">
    <div class="filter-field search-field">
        <label for="venue">Venue</label>
        <input id="venue" type="search" name="venue" value="<?php echo htmlspecialchars($venueSearch); ?>">
    </div>

    <div class="filter-field">
        <label for="format_filter">Format</label>
        <select id="format_filter" name="format">
            <option value="">All</option>
            <?php while ($formatRow = $formats->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($formatRow['format']); ?>" <?php if ($formatFilter === $formatRow['format']) { echo "selected"; } ?>>
                    <?php echo htmlspecialchars($formatRow['format']); ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="filter-field">
        <label for="type_filter">Match Type</label>
        <select id="type_filter" name="type">
            <option value="">All</option>
            <?php while ($typeRow = $types->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($typeRow['Match_Type']); ?>" <?php if ($typeFilter === $typeRow['Match_Type']) { echo "selected"; } ?>>
                    <?php echo htmlspecialchars($typeRow['Match_Type']); ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="filter-field">
        <label for="date_from">From</label>
        <input id="date_from" type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
    </div>

    <div class="filter-field">
        <label for="date_to">To</label>
        <input id="date_to" type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
    </div>

    <div class="filter-actions">
        <input type="submit" value="Filter">
        <a href="view.php">Reset</a>
    </div>
</form>

<table border="1" cellpadding="10">
<tr>
    <th>ID</th>
    <th>Venue</th>
    <th>Date</th>
    <th>Format</th>
    <th>Match Type</th>
</tr>

<?php
if ($totalRows == 0) {
    echo "<tr>";
    echo "<td colspan='5'>No matches found for this filter.</td>";
    echo "</tr>";
}

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$row["match_id"]."</td>";
    echo "<td>".$row["venue"]."</td>";
    echo "<td>".$row["match_date"]."</td>";
    echo "<td>".$row["format"]."</td>";
    echo "<td>".$row['Match_Type']."</td>";
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
