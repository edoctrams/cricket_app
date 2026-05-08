<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: view.php");
    exit();
}

include('../config/db.php');

$formats = ['ODI', 'T20', 'Test'];
$types = ['International', 'Domestic'];
$generated = 0;

$conn->begin_transaction();

// Clear old rankings before rebuilding them from performance data.
$conn->query("DELETE FROM Ranking");

$rankingStmt = $conn->prepare("
    INSERT INTO Ranking (player_id, format, rank_position)
    VALUES (?, ?, ?)
");

foreach ($formats as $format) {
    foreach ($types as $type) {

        $players = $conn->query("
        SELECT p.player_id, SUM(pf.runs) as total_runs
        FROM Performance pf
        JOIN Player p ON pf.player_id = p.player_id
        JOIN Match_Details m ON pf.match_id = m.match_id
        WHERE m.format = '$format' AND m.Match_Type = '$type'
        GROUP BY p.player_id
        ORDER BY total_runs DESC, p.player_id ASC
        ");

        $rank = 1;
        $rankingFormat = "$format ($type)";

        while ($row = $players->fetch_assoc()) {
            $player_id = $row['player_id'];

            $rankingStmt->bind_param("isi", $player_id, $rankingFormat, $rank);
            $rankingStmt->execute();

            $rank++;
            $generated++;
        }
    }
}

$conn->commit();

header("Location: view.php?generated=$generated");
exit();
?>
