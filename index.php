<?php
// COOKIE USAGE - Avoid relogin every time the user reloads the page

$sessionLifetime = 15 * 24 * 60 * 60; // 15 Tage
session_set_cookie_params($sessionLifetime);
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_start();

// DATABASE Connection - establish

$servername = "db-node-01.toxic1835.xyz";
$username = "rth";
$password = "KJ72yQn(m8_6)Zar";
$dbname = "rth";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("<p><span style='color: #ff0000;'>Error: " . $e->getMessage() . "</span></p>");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RT - Hosting</title>
    <link rel="stylesheet" href="/add/main.css">
    <meta property="og:title" content="RT-Hosting">
    <meta property="og:site_name" content="RT-Hosting">
    <meta property="og:description" content="RT-Hosting is a german hosting service that provides 1€ servers">
    <meta property="og:image" content="https://rt-hosting.eu/PB.png">
    <meta property="og:url" content="https://rt-hosting.eu">
    <meta property="og:color" content="#00ADB5">
    <meta property="og:type" content="website">
</head>
<body>
    <div class="header">
        <div class="hl">
            <h1>RT - Hosting</h1>
        </div>
        <div class="hr">
            <h3><a href="panel">Panel</a></h3>
            <h3><a href="https://discord.com/invite/J6CR7tjXhY" target="_blank">Discord</a></h3>
        </div>
    </div>
    <div class="main">
        <?php
            $sql = "SELECT * FROM `prices` WHERE `root`=1";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                echo "<h2>Root-Server</h2>";
                echo "<div class=\"packs\">";
                    $counter = 1;
                        foreach ($result as $row) {
                            echo "<div class='pack'>";
                            echo "<h3>".$row['pack']."</h3>";
                            if ($row['cores'] == 1) {
                                echo "<p>Core: 1</p>";
                            } else {
                                echo "<p>Cores: ".$row['cores']."</p>";
                            }
                            echo "<p>RAM: ".$row['ram']." GB</p>";
                            echo "<p>Space: ".$row['storage']." GB</p>";
                            echo "<p>Traffic: ".$row['traffic']."</p>";
                            echo "<p>Price: <span class=\"bold\">".$row['price']."€</span></p>";
                            echo "</div>";

                            if ($counter % 3 == 0) {
                                echo "</div>";
                                echo "<div class='packs'>";
                            }
                            
                            $counter++;
                        }
                echo "</div>";
            } else {
                echo 'No prices were found! [ROOT]';
            }
            $sql = "SELECT * FROM `prices` WHERE `root`=0";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                echo "<h2>V-Server</h2>";
                echo "<div class=\"packs\">";
                    $counter = 1;
                        foreach ($result as $row) {
                            echo "<div class='pack'>";
                            echo "<h3>".$row['pack']."</h3>";
                            if ($row['cores'] == 1) {
                                echo "<p>Core: 1</p>";
                            } else {
                                echo "<p>Cores: ".$row['cores']."</p>";
                            }
                            echo "<p>RAM: ".$row['ram']." GB</p>";
                            echo "<p>Space: ".$row['storage']." GB</p>";
                            echo "<p>Traffic: ".$row['traffic']."</p>";
                            echo "<p>Price: <span class=\"bold\">".$row['price']."€</span></p>";
                            echo "</div>";

                            if ($counter % 3 == 0) {
                                echo "</div>";
                                echo "<div class='packs'>";
                            }
                            
                            $counter++;
                        }
                echo "</div>";
            } else {
                echo 'No prices were found! [VIRTUAL]';
            }
        ?>
    </div>
</body>
</html>