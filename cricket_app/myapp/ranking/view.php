
<?php include('../header.php'); ?>
<?php
include('../config/db.php');
include('../pagination.php');

$playerSearch = isset($_GET['player']) ? trim($_GET['player']) : "";
$formatFilter = isset($_GET['format']) ? trim($_GET['format']) : "";
$typeFilter = isset($_GET['type']) ? trim($_GET['type']) : "";

// Build filter conditions
$where = [];

if ($playerSearch !== "") {
    $safePlayer = $conn->real_escape_string($playerSearch);
    $where[] = "p.name LIKE '%$safePlayer%'";
}

if ($formatFilter !== "") {
    $safeFormat = $conn->real_escape_string($formatFilter);
    $where[] = "r.format LIKE '%$safeFormat%'";
}

if ($typeFilter !== "") {
    $safeType = $conn->real_escape_string($typeFilter);
    $where[] = "r.format LIKE '%$safeType%'";
}

$whereSQL = "";
if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

$perPage = 8;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;
$totalRows = $conn->query("
SELECT COUNT(*) AS total
FROM Ranking r
JOIN Player p ON r.player_id = p.player_id
$whereSQL
")->fetch_assoc()["total"];
$totalPages = max(1, ceil($totalRows / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$formats = $conn->query("SELECT DISTINCT SUBSTRING_INDEX(format, ' ', 1) AS format_name FROM Ranking WHERE format IS NOT NULL AND format != '' ORDER BY format_name");
$types = $conn->query("
SELECT DISTINCT
    REPLACE(REPLACE(SUBSTRING_INDEX(format, '(', -1), ')', ''), '(', '') AS type_name
FROM Ranking
WHERE format LIKE '%(%'
ORDER BY type_name
");

// Final query
$result = $conn->query("
SELECT r.rank_position, r.format, p.name
FROM Ranking r
JOIN Player p ON r.player_id = p.player_id
$whereSQL
ORDER BY r.format, r.rank_position
LIMIT $perPage OFFSET $offset
");
?>
<hr>
<a href="../index.php">Home</a> |
<?php if ($isAdmin) { ?><a href="auto.php">Generate Rankings</a> |<a href="add.php">Add Ranking</a> |<?php } ?>
<a href="view.php">View Rankings</a>
<hr>
<?php if (isset($_GET['generated'])) { ?>
    <p><?php echo (int)$_GET['generated']; ?> rankings generated from performance data.</p>
<?php } ?>
<form class="filter-bar" method="GET">
    <div class="filter-field search-field">
        <label for="player">Player</label>
        <input id="player" type="search" name="player" value="<?php echo htmlspecialchars($playerSearch); ?>">
    </div>

    <div class="filter-field">
        <label for="format_filter">Format</label>
        <select id="format_filter" name="format">
            <option value="">All</option>
            <?php while ($formatRow = $formats->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($formatRow['format_name']); ?>" <?php if ($formatFilter === $formatRow['format_name']) { echo "selected"; } ?>>
                    <?php echo htmlspecialchars($formatRow['format_name']); ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="filter-field">
        <label for="type_filter">Match Type</label>
        <select id="type_filter" name="type">
            <option value="">All</option>
            <?php while ($typeRow = $types->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($typeRow['type_name']); ?>" <?php if ($typeFilter === $typeRow['type_name']) { echo "selected"; } ?>>
                    <?php echo htmlspecialchars($typeRow['type_name']); ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="filter-actions">
        <input type="submit" value="Filter">
        <a href="view.php">Reset</a>
    </div>
</form>

<br>

<h2>Player Rankings</h2>

<table border="1" cellpadding="10">
<tr>
    <th>Player</th>
    <th>Format</th>
    <th>Rank</th>
</tr>

<?php
if ($totalRows == 0) {
    echo "<tr>";
    echo "<td colspan='3'>No rankings found. Add performance data, generate rankings, or reset the filters.</td>";
    echo "</tr>";
}

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$row['name']."</td>";
    echo "<td>".$row['format']."</td>";
    echo "<td>".$row['rank_position']."</td>";
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
