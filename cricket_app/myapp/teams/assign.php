<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}
?>
<?php include('../header.php'); ?>
<?php
include('../config/db.php');

// Fetch players
$players = $conn->query("SELECT * FROM Player");

// Fetch teams
$teams = $conn->query("SELECT * FROM Team");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $player_id = $_POST["player_id"];
    $team_id = $_POST["team_id"];

    $conn->query("INSERT INTO Plays_For (player_id, team_id, start_date)
                  VALUES ($player_id, $team_id, NOW())");

    echo "Assigned successfully!";
}
?>

<hr>
<a href="../index.php">Home</a> |
<a href="assign.php">Assign Player</a>
<hr>

<h2>Assign Player to Team</h2>

<form method="POST">

    Player:
    <select name="player_id">
        <?php while($p = $players->fetch_assoc()) { ?>
            <option value="<?php echo $p['player_id']; ?>">
                <?php echo $p['name']; ?>
            </option>
        <?php } ?>
    </select>
    <br><br>

    Team:
    <select name="team_id">
        <?php while($t = $teams->fetch_assoc()) { ?>
            <option value="<?php echo $t['team_id']; ?>">
                <?php echo $t['team_name']; ?>
            </option>
        <?php } ?>
    </select>
    <br><br>

    <input type="submit" value="Assign">
</form>
<?php include('../footer.php'); ?>
