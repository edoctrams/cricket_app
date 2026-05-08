<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}

include('../header.php');
include('../config/db.php');

$error = "";

// =========================
// FORM SUBMISSION
// =========================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $team_name = trim($_POST["team_name"]);

    // =========================
    // VALIDATION
    // =========================

    if ($team_name == "") {

        $error = "Team name cannot be empty.";

    } else {

        // Prevent duplicate teams
        $check = $conn->prepare("
        SELECT *
        FROM Team
        WHERE team_name = ?
        ");

        $check->bind_param("s", $team_name);

        $check->execute();

        $existing = $check->get_result();

        if ($existing->num_rows > 0) {

            $error = "Team already exists.";

        } else {

            // =========================
            // INSERT TEAM
            // =========================

            $stmt = $conn->prepare("
            INSERT INTO Team (team_name)
            VALUES (?)
            ");

            $stmt->bind_param("s", $team_name);

            if ($stmt->execute()) {

                header("Location: view.php");
                exit();

            } else {

                $error = $conn->error;
            }
        }
    }
}
?>

<hr>

<a href="../index.php">Home</a> |
<a href="add.php">Add Team</a> |
<a href="view.php">View Teams</a>

<hr>

<h2>Add Team</h2>

<?php if ($error != "") { ?>

    <div style="
        color:red;
        margin-bottom:15px;
        font-weight:bold;
    ">
        <?php echo $error; ?>
    </div>

<?php } ?>

<form method="POST">

    Team Name:<br>

    <input
        type="text"
        name="team_name"
        required
    >

    <br><br>

    <input type="submit" value="Add Team">

</form>

<?php include('../footer.php'); ?>