<div class="navbar">
    <a class="brand" href="/myapp/index.php">Cricket App</a>
    <div class="nav-links">
        <a href="/myapp/index.php">Dashboard</a>
        <a href="/myapp/players/view.php">Players</a>
        <a href="/myapp/teams/view.php">Teams</a>
        <a href="/myapp/matches/view.php">Matches</a>
        <a href="/myapp/performance/view.php">Performance</a>
        <a href="/myapp/ranking/view.php">Ranking</a>
        <?php if ($isAdmin) { ?>
            <a href="/myapp/players/add.php">Add Player</a>
        <?php } ?>
        <a href="/myapp/logout.php">Logout</a>
    </div>
</div>
