<?php

session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include('header.php');
include('config/db.php');
include('pagination.php');

$error = "";


// =========================
// ADD PLAYER + TEAM
// =========================

if ($_SERVER["REQUEST_METHOD"] == "POST" && $isAdmin) {

    // Normalize player name
    $name = ucwords(strtolower(trim($_POST["name"])));

    $country = trim($_POST["country"]);

    $role = trim($_POST["role"]);

    $team_id = (int) $_POST["team_id"];


    // =========================
    // DUPLICATE CHECK
    // SAME NAME IN SAME TEAM
    // =========================

    $duplicate = $conn->prepare("
    SELECT pf.player_id

    FROM Plays_For pf

    JOIN Player p
    ON pf.player_id = p.player_id

    WHERE LOWER(TRIM(p.name)) = LOWER(TRIM(?))
    AND pf.team_id = ?
    ");

    $duplicate->bind_param(
        "si",
        $name,
        $team_id
    );

    $duplicate->execute();

    $existing = $duplicate->get_result();


    if ($existing->num_rows > 0) {

        $error = "Player with same name already exists in this team.";

    } else {

        // =========================
        // INSERT PLAYER
        // =========================

        $stmt = $conn->prepare("
        INSERT INTO Player (name, country, role)
        VALUES (?, ?, ?)
        ");

        $stmt->bind_param(
            "sss",
            $name,
            $country,
            $role
        );

        if ($stmt->execute()) {

            // Get inserted player ID

            $player_id = $conn->insert_id;


            // =========================
            // ASSIGN TEAM
            // =========================

            $assign = $conn->prepare("
            INSERT INTO Plays_For
            (player_id, team_id, start_date)

            VALUES (?, ?, CURDATE())
            ");

            $assign->bind_param(
                "ii",
                $player_id,
                $team_id
            );

            if ($assign->execute()) {

                header("Location: index.php?added=1");
                exit();

            } else {

                $error = $conn->error;
            }

        } else {

            $error = $conn->error;
        }
    }
}


// =========================
// PAGINATION
// =========================

$perPage = 6;

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$offset = ($page - 1) * $perPage;


// =========================
// FILTERS
// =========================

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


// =========================
// TOTAL ROWS
// =========================

$totalRows = $conn->query("
SELECT COUNT(DISTINCT p.player_id) AS total

FROM Player p

LEFT JOIN Plays_For pf
ON p.player_id = pf.player_id

LEFT JOIN Team t
ON pf.team_id = t.team_id

$whereSQL
")->fetch_assoc()["total"];


$totalPages = max(1, ceil($totalRows / $perPage));

if ($page > $totalPages) {

    $page = $totalPages;

    $offset = ($page - 1) * $perPage;
}


// =========================
// FILTER DROPDOWNS
// =========================

$countries = $conn->query("
SELECT DISTINCT country
FROM Player
WHERE country IS NOT NULL
AND country != ''
ORDER BY country
");

$roles = $conn->query("
SELECT DISTINCT role
FROM Player
WHERE role IS NOT NULL
AND role != ''
ORDER BY role
");

$teams = $conn->query("
SELECT team_name
FROM Team
ORDER BY team_name
");

$teamOptions = $conn->query("
SELECT *
FROM Team
ORDER BY team_name
");


// =========================
// FETCH PLAYERS
// =========================

$result = $conn->query("
SELECT
    p.player_id,
    p.name,
    p.country,
    p.role,

    GROUP_CONCAT(
        DISTINCT t.team_name
        SEPARATOR ', '
    ) AS team_name

FROM Player p

LEFT JOIN Plays_For pf
ON p.player_id = pf.player_id

LEFT JOIN Team t
ON pf.team_id = t.team_id

$whereSQL

GROUP BY p.player_id

ORDER BY p.name

LIMIT $perPage OFFSET $offset
");


// =========================
// DASHBOARD COUNTS
// =========================

$totalPlayers = $conn->query("
SELECT COUNT(*) AS total
FROM Player
")->fetch_assoc()["total"];

$totalTeams = $conn->query("
SELECT COUNT(*) AS total
FROM Team
")->fetch_assoc()["total"];

$totalMatches = $conn->query("
SELECT COUNT(*) AS total
FROM Match_Details
")->fetch_assoc()["total"];

?>

<section class="dashboard-hero">

    <div>

        <p class="eyebrow">Match control room</p>

        <h1>Cricket Dashboard</h1>

        <p class="hero-copy">
            Track squads, fixtures, player performances,
            and rankings like a live scorecard.
        </p>

    </div>

</section>


<section class="cards">

    <div class="card">
        <h3>Players</h3>
        <p><?php echo $totalPlayers; ?></p>
    </div>

    <div class="card">
        <h3>Teams</h3>
        <p><?php echo $totalTeams; ?></p>
    </div>

    <div class="card">
        <h3>Matches</h3>
        <p><?php echo $totalMatches; ?></p>
    </div>

</section>


<section class="dashboard-grid <?php echo $isAdmin ? '' : 'viewer-grid'; ?>">

<?php if ($isAdmin) { ?>

<div class="panel form-panel">

    <div class="section-heading">

        <p class="eyebrow">Team sheet</p>

        <h2>Add Player</h2>

    </div>

    <?php if ($error != "") { ?>

        <div style="
            color:red;
            margin-bottom:15px;
            font-weight:bold;
            background:#ffe5e5;
            padding:10px;
            border-radius:8px;
        ">
            <?php echo $error; ?>
        </div>

    <?php } ?>

    <?php if (isset($_GET['added'])) { ?>

        <p>Player added successfully.</p>

    <?php } ?>

    <form method="POST">

        <label for="name">Name</label>

        <input
            id="name"
            type="text"
            name="name"
            required
        >


        <label for="country">Country</label>

        <select
            id="country"
            name="country"
            required
            style="
                width:100%;
                max-height:120px;
            "
        >

            <option value="">Select Country</option>

            <option value="India">India</option>

            <option value="Australia">Australia</option>

            <option value="England">England</option>

            <option value="Pakistan">Pakistan</option>

            <option value="South Africa">South Africa</option>

            <option value="New Zealand">New Zealand</option>

            <option value="Sri Lanka">Sri Lanka</option>

            <option value="West Indies">West Indies</option>

            <option value="Bangladesh">Bangladesh</option>

            <option value="Afghanistan">Afghanistan</option>

        </select>


        <label for="role">Role</label>

        <select
            id="role"
            name="role"
            required
            style="
                width:100%;
                max-height:120px;
            "
        >

            <option value="">Select Role</option>

            <option value="Batsman">Batsman</option>

            <option value="Bowler">Bowler</option>

            <option value="All-Rounder">All-Rounder</option>

            <option value="Wicket Keeper">Wicket Keeper</option>

        </select>


        <label for="team_id">Team</label>

        <select id="team_id" name="team_id" required>

            <?php while($team = $teamOptions->fetch_assoc()) { ?>

                <option value="<?php echo $team['team_id']; ?>">

                    <?php echo $team['team_name']; ?>

                </option>

            <?php } ?>

        </select>

        <br><br>

        <input type="submit" value="Add Player">

    </form>

</div>

<?php } ?>


<div class="panel table-panel">

    <div class="section-heading">

        <p class="eyebrow">Scorebook</p>

        <h2>Players List</h2>

    </div>


    <form class="filter-bar" method="GET">

        <div class="filter-field search-field">

            <label for="search">Search Player</label>

            <input
                id="search"
                type="search"
                name="search"
                placeholder="Search by name"
                value="<?php echo htmlspecialchars($search); ?>"
            >

        </div>


        <div class="filter-field">

            <label for="country_filter">Country</label>

            <select id="country_filter" name="country">

                <option value="">All</option>

                <?php while ($countryRow = $countries->fetch_assoc()) { ?>

                    <option
                        value="<?php echo htmlspecialchars($countryRow['country']); ?>"
                        <?php if ($countryFilter === $countryRow['country']) { echo "selected"; } ?>
                    >

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

                    <option
                        value="<?php echo htmlspecialchars($roleRow['role']); ?>"
                        <?php if ($roleFilter === $roleRow['role']) { echo "selected"; } ?>
                    >

                        <?php echo htmlspecialchars($roleRow['role']); ?>

                    </option>

                <?php } ?>

            </select>

        </div>


        <div class="filter-field">

            <label for="team_filter">Team</label>

            <select id="team_filter" name="team">

                <option value="">All</option>

                <option value="No Team"
                    <?php if ($teamFilter === "No Team") { echo "selected"; } ?>
                >
                    No Team
                </option>

                <?php while ($teamRow = $teams->fetch_assoc()) { ?>

                    <option
                        value="<?php echo htmlspecialchars($teamRow['team_name']); ?>"
                        <?php if ($teamFilter === $teamRow['team_name']) { echo "selected"; } ?>
                    >

                        <?php echo htmlspecialchars($teamRow['team_name']); ?>

                    </option>

                <?php } ?>

            </select>

        </div>


        <div class="filter-actions">

            <input type="submit" value="Apply">

            <a href="index.php">Reset</a>

        </div>

    </form>


    <table>

        <tr>
            <th>Name</th>
            <th>Team</th>
            <th>Country</th>
            <th>Role</th>
        </tr>

        <?php

        while ($row = $result->fetch_assoc()) {

            $team = $row["team_name"]
                ? $row["team_name"]
                : "No Team";

            echo "<tr>";

            echo "<td>".$row["name"]."</td>";

            echo "<td>".$team."</td>";

            echo "<td>".$row["country"]."</td>";

            echo "<td>".$row["role"]."</td>";

            echo "</tr>";
        }

        ?>

    </table>

    <?php

    $params = $_GET;

    unset($params['page']);

    renderPagination($page, $totalPages, $params);

    ?>

</div>

</section>

<?php include('footer.php'); ?>