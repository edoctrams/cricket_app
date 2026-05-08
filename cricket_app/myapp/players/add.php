<?php

session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {

    die("Access Denied");
}

include('../config/db.php');

$error = "";


// =========================
// FETCH TEAMS
// =========================

$teams = $conn->query("
SELECT *
FROM Team
ORDER BY team_name
");


// =========================
// DROPDOWN OPTIONS
// =========================

$countries = [
    "India",
    "Australia",
    "England",
    "Pakistan",
    "South Africa",
    "New Zealand",
    "Sri Lanka",
    "West Indies",
    "Bangladesh",
    "Afghanistan"
];

$roles = [
    "Batsman",
    "Bowler",
    "All-Rounder",
    "Wicket Keeper"
];


// =========================
// FORM SUBMISSION
// =========================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Normalize name
    $name = ucwords(strtolower(trim($_POST["name"])));

    $country = trim($_POST["country"]);

    $role = trim($_POST["role"]);

    $team_id = (int) $_POST["team_id"];


    // =========================
    // VALIDATION
    // =========================

    if (
        $name == "" ||
        $country == "" ||
        $role == ""
    ) {

        $error = "Please fill all fields.";

    } else {

        // =========================
        // CHECK SAME NAME IN SAME TEAM
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
            INSERT INTO Player
            (name, country, role)

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

                    header("Location: view.php?added=1");

                    exit();

                } else {

                    $error = $conn->error;
                }

            } else {

                $error = $conn->error;
            }
        }
    }
}
?>

<?php include('../header.php'); ?>

<hr>

<a href="../index.php">Home</a> |
<a href="add.php">Add Player</a> |
<a href="view.php">View Players</a>

<hr>

<h2>Add Player</h2>

<?php if ($error != "") { ?>

    <div style="
        color:red;
        margin-bottom:15px;
        font-weight:bold;
        padding:10px;
        border-radius:8px;
        background:#ffe5e5;
    ">
        <?php echo $error; ?>
    </div>

<?php } ?>

<form method="POST">

    <label>Name:</label><br>

    <input
        type="text"
        name="name"
        required
    >

    <br><br>


<label>Country:</label><br>

<select
    name="country"
    required
    style="
        width:220px;
    "
>

    <option value="">Select Country</option>

    <?php foreach ($countries as $c) { ?>

        <option value="<?php echo $c; ?>">

            <?php echo $c; ?>

        </option>

    <?php } ?>

</select>

<br><br>


    <label>Role:</label><br>

<select
    name="role"
    required
    style="
        width:220px;
    "
>

    <option value="">Select Role</option>

    <?php foreach ($roles as $r) { ?>

        <option value="<?php echo $r; ?>">

            <?php echo $r; ?>

        </option>

    <?php } ?>

</select>

<br><br>


    <label>Team:</label><br>

    <select name="team_id" required>

        <?php while($team = $teams->fetch_assoc()) { ?>

            <option value="<?php echo $team['team_id']; ?>">

                <?php echo $team['team_name']; ?>

            </option>

        <?php } ?>

    </select>

    <br><br>


    <input type="submit" value="Add Player">

</form>

<?php include('../footer.php'); ?>