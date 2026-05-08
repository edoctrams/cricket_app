<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}

include('../header.php');
include('../config/db.php');

// =========================
// FETCH PLAYERS & MATCHES
// =========================

$players = $conn->query("SELECT * FROM Player");

$matches = $conn->query("SELECT * FROM Match_Details");

$error = "";

// =========================
// FORM SUBMISSION
// =========================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $player_id = (int)$_POST["player_id"];

    $match_id = (int)$_POST["match_id"];

    $runs = ($_POST["runs"] !== "") ? (int)$_POST["runs"] : 0;

    $wickets = ($_POST["wickets"] !== "") ? (int)$_POST["wickets"] : 0;

    $balls = ($_POST["balls"] !== "") ? (int)$_POST["balls"] : 0;


    // =========================
    // BASIC VALIDATION
    // =========================

    if ($runs < 0 || $wickets < 0 || $balls < 0) {

        $error = "Negative values are not allowed.";

    } else {

        // =========================
        // INSERT PERFORMANCE
        // =========================

        $sql = "
        INSERT INTO Performance
        (player_id, match_id, runs, wickets, balls_faced)

        VALUES
        ($player_id, $match_id, $runs, $wickets, $balls)
        ";

        if ($conn->query($sql)) {

            header("Location: view.php");
            exit();

        } else {

            // Trigger / MySQL errors appear here
            $error = $conn->error;
        }
    }
}
?>

<hr>

<a href="../index.php">Home</a> |
<a href="add.php">Add Performance</a> |
<a href="view.php">View Performance</a>

<hr>

<h2>Add Performance</h2>

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

    Player:<br>

    <select name="player_id">

        <?php while($p = $players->fetch_assoc()) { ?>

            <option value="<?php echo $p['player_id']; ?>">

                <?php echo $p['name']; ?>

            </option>

        <?php } ?>

    </select>

    <br><br>


    Match:<br>

    <select name="match_id">

        <?php while($m = $matches->fetch_assoc()) { ?>

            <option value="<?php echo $m['match_id']; ?>">

                <?php echo $m['venue']." - ".$m['match_date']; ?>

            </option>

        <?php } ?>

    </select>

    <br><br>


    Runs:<br>

    <input type="number" name="runs">

    <br><br>


    Wickets:<br>

    <input type="number" name="wickets" value="0">

    <br><br>


    Balls Faced:<br>

    <input type="number" name="balls">

    <br><br>


    <input type="submit" value="Add Performance">

</form>

<?php include('../footer.php'); ?>