<?php include('../header.php'); ?>

<?php

include('../config/db.php');

include('../pagination.php');

$playerSearch = isset($_GET['player']) ? trim($_GET['player']) : "";

$formatFilter = isset($_GET['format']) ? trim($_GET['format']) : "";

$typeFilter = isset($_GET['type']) ? trim($_GET['type']) : "";

$where = [];

if ($playerSearch !== "") {

    $safePlayer = $conn->real_escape_string($playerSearch);

    $where[] = "p.name LIKE '%$safePlayer%'";
}

if ($formatFilter !== "") {

    $safeFormat = $conn->real_escape_string($formatFilter);

    $where[] = "m.format = '$safeFormat'";
}

if ($typeFilter !== "") {

    $safeType = $conn->real_escape_string($typeFilter);

    $where[] = "m.Match_Type = '$safeType'";
}

$whereSQL = "";

if (!empty($where)) {

    $whereSQL = "WHERE " . implode(" AND ", $where);
}


// =========================
// PAGINATION
// =========================

$perPage = 8;

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$offset = ($page - 1) * $perPage;


$totalRows = $conn->query("
SELECT COUNT(*) AS total

FROM Performance pf

JOIN Player p
ON pf.player_id = p.player_id

JOIN Match_Details m
ON pf.match_id = m.match_id

$whereSQL
")->fetch_assoc()["total"];


$totalPages = max(1, ceil($totalRows / $perPage));

if ($page > $totalPages) {

    $page = $totalPages;

    $offset = ($page - 1) * $perPage;
}


// =========================
// FILTER OPTIONS
// =========================

$formats = $conn->query("
SELECT DISTINCT format
FROM Match_Details
WHERE format IS NOT NULL
AND format != ''
ORDER BY format
");

$types = $conn->query("
SELECT DISTINCT Match_Type
FROM Match_Details
WHERE Match_Type IS NOT NULL
AND Match_Type != ''
ORDER BY Match_Type
");


// =========================
// MAIN QUERY
// =========================

$result = $conn->query("
SELECT 
    p.name, 
    m.venue, 
    m.match_date, 
    m.format,
    m.Match_Type,
    pf.runs, 
    COALESCE(pf.wickets, 0) AS wickets, 
    pf.balls_faced,

    Calculate_Strike_Rate(
        pf.runs,
        pf.balls_faced
    ) AS strike_rate

FROM Performance pf

JOIN Player p
ON pf.player_id = p.player_id

JOIN Match_Details m
ON pf.match_id = m.match_id

$whereSQL

ORDER BY m.match_date DESC, p.name

LIMIT $perPage OFFSET $offset
");

?>

<hr>

<a href="../index.php">Home</a> |

<?php if ($isAdmin) { ?>

    <a href="add.php">Add Performance</a> |

<?php } ?>

<a href="view.php">View Performance</a>

<hr>


<form class="filter-bar" method="GET">

    <div class="filter-field search-field">

        <label for="player">Player</label>

        <input
            id="player"
            type="search"
            name="player"
            value="<?php echo htmlspecialchars($playerSearch); ?>"
        >

    </div>


    <div class="filter-field">

        <label for="format_filter">Format</label>

        <select id="format_filter" name="format">

            <option value="">All</option>

            <?php while ($formatRow = $formats->fetch_assoc()) { ?>

                <option
                    value="<?php echo htmlspecialchars($formatRow['format']); ?>"
                    <?php if ($formatFilter === $formatRow['format']) { echo "selected"; } ?>
                >

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

                <option
                    value="<?php echo htmlspecialchars($typeRow['Match_Type']); ?>"
                    <?php if ($typeFilter === $typeRow['Match_Type']) { echo "selected"; } ?>
                >

                    <?php echo htmlspecialchars($typeRow['Match_Type']); ?>

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

<h2>Performance List</h2>

<table>

<tr>

    <th>Player</th>
    <th>Format</th>
    <th>Match Type</th>
    <th>Venue</th>
    <th>Date</th>
    <th>Runs</th>
    <th>Wickets</th>
    <th>Balls</th>
    <th>Strike Rate</th>

</tr>

<?php

if ($totalRows == 0) {

    echo "<tr>";

    echo "<td colspan='9'>No performance records found for this filter.</td>";

    echo "</tr>";
}

while ($row = $result->fetch_assoc()) {

    echo "<tr>";

    echo "<td>".$row["name"]."</td>";

    echo "<td>".$row["format"]."</td>";

    echo "<td>".$row["Match_Type"]."</td>";

    echo "<td>".$row["venue"]."</td>";

    echo "<td>".$row["match_date"]."</td>";

    echo "<td>".$row["runs"]."</td>";

    echo "<td>".$row["wickets"]."</td>";

    echo "<td>".$row["balls_faced"]."</td>";

    echo "<td>".$row["strike_rate"]."</td>";

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