<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <title>RTH - Panel</title>
    <link rel="stylesheet" href="/add/main.css">
    <meta property="og:title" content="RT-Hosting Panel">
    <meta property="og:description" content="RT-Hosting Panel | The place where you manage your servers">
    <meta property="og:image" content="https://rt-hosting.eu/PB.png">
    <meta property="og:url" content="https://rt-hosting.eu/panel">
    <meta property="og:color" content="#00ADB5">
    <meta property="og:type" content="website">
</head>

<?php
$sessionLifetime = 15 * 24 * 60 * 60; // 15 Tage
session_set_cookie_params($sessionLifetime);
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_start();

$servername = "db-node-01.toxic1835.xyz";
$username = "rth";
$password = "KJ72yQn(m8_6)Zar";
$dbname = "rth";

$conn = new mysqli($servername, $username, $password, $dbname);

$apiUrl = "https://193.141.60.104:8006/api2/json";
$apiToken = "root@pam!roottoken=51a4b148-0c63-440a-a839-ccf64a694326";
$nodeName = "host01";

if (session_status() == PHP_SESSION_ACTIVE) {
    if (!isset($_SESSION['discord_access_token'])) {
        header('Location: https://discord.com/api/oauth2/authorize?client_id=1160388346690932767&redirect_uri=https%3A%2F%2Frt-hosting.eu%2Fcallback.php&response_type=code&scope=identify');
        exit;
    }

    $accessToken = $_SESSION['discord_access_token'];

    $userURL = "https://discord.com/api/v10/users/@me";
    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ];

    $options = [
        'http' => [
            'header' => implode("\r\n", $headers),
            'method' => 'GET'
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($userURL, false, $context);

    if ($response === false) {
        unset($_SESSION['discord_access_token']);
        die('An error occurred by getting important data');
    }

    $responseData = json_decode($response, true);

    if (isset($responseData["username"])) {
        $dcid = $responseData['id'];
        $username = $responseData['username'];
    } else {
        unset($_SESSION['discord_access_token']);
        die('An error occurred by getting the discord-user-data.');
    }

} else {
    header('Location: https://discord.com/api/oauth2/authorize?client_id=1160388346690932767&redirect_uri=https%3A%2F%2Frt-hosting.eu%2Fcallback.php&response_type=code&scope=identify');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['_id'])) {
        if (isset($_POST['_method'])) {
            if (!empty($_POST['_id']) && ctype_digit($_POST['_id']))
            {
                $stmt = $conn->prepare("SELECT * FROM `servers` WHERE `dcid`=? AND `vmid`=?");
                $stmt->bind_param("ss", $dcid, $_POST['_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows !== 1) { $feedback = "verification for user (".$dcid.") and vm (".$_POST['_id'].") failed!"; }
                else {
                    if ($_POST['_method'] === "start") {
                        $feedback = startVm($_POST['_id']);
                    }
                    if ($_POST['_method'] === "stop") {
                        $feedback = stopVm($_POST['_id']);
                    }
                    if ($_POST['_method'] === "restart") {
                        $feedback = restartVm($_POST['_id']);
                    }
                    if ($_POST['_method'] === "pw") {
                        if (isset($_POST['_password'])) {
                            $feedback = changePW($_POST['_id'], $_POST['_password']);
                        }
                    }
                
                    if (isset($feedback)) {
                        $feedback .= "<br><br>Please note that the current VM status may differ, <a href='/panel'>click here</a> to update your website.";
                    }
                }
            }
            else {
                $feedback = "An error occurred by requesting (".$_POST['_method'].") [VMID_FORMAT]";
            }
        }
    }
}

function getVmStatus($vmid)
{
    global $apiUrl, $apiToken, $nodeName;

    $cstatusUrl = "{$apiUrl}/nodes/{$nodeName}/qemu/{$vmid}/status/current";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cstatusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: PVEAPIToken={$apiToken}"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return "No response";
    }

    $data = json_decode($response, true);
    if ($data === null || !isset($data["data"]["status"])) {
        return "No data";
    }

    return $data["data"]["status"];
}

function getVmIp($vmid)
{
    global $apiUrl, $apiToken, $nodeName;

    $cstatusUrl = "{$apiUrl}/nodes/{$nodeName}/qemu/{$vmid}/config";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cstatusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: PVEAPIToken={$apiToken}"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return "No response";
    }

    $data = json_decode($response, true);
    if ($data === null || !isset($data["data"]['ipconfig0'])) {
        return "No data";
    }

    $vmIP = str_replace("ip=", "", $data["data"]["ipconfig0"]);

    return explode("/", $vmIP)[0];
}

function stopVm($vmid)
{
    global $apiUrl, $apiToken, $nodeName;

    $cstatusUrl = "{$apiUrl}/nodes/{$nodeName}/qemu/{$vmid}/status/stop";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cstatusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: PVEAPIToken={$apiToken}"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return "No response";
    }

    return "Success";
}

function startVm($vmid)
{
    global $apiUrl, $apiToken, $nodeName;

    $cstatusUrl = "{$apiUrl}/nodes/{$nodeName}/qemu/{$vmid}/status/start";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cstatusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: PVEAPIToken={$apiToken}"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return "No response";
    }

    return "Success";
}

function restartVm($vmid)
{
    global $apiUrl, $apiToken, $nodeName;

    $cstatusUrl = "{$apiUrl}/nodes/{$nodeName}/qemu/{$vmid}/status/reboot";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cstatusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: PVEAPIToken={$apiToken}"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return "No response";
    }

    return "Success";
}

function changePW($vmid, $newPassword)
{
    if (empty($newPassword)) {
        return "Empty passwords aren't allowed!";
    }

    global $apiUrl, $apiToken, $nodeName;

    $jsonData = json_encode(array('cipassword' => $newPassword));
    $cstatusUrl = "{$apiUrl}/nodes/{$nodeName}/qemu/{$vmid}/config";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cstatusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: PVEAPIToken={$apiToken}",
        "Content-Type: application/json",
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    $response = curl_exec($ch);

    if ($response === false) {
        return "No response";
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        return "Password successful changed | <span style='color: #cc632b;' class='bold'>REMEMBER TO RESTART YOUR VM AFTER CHANGING PASSWORD!</span>";
    } else {
        return "An error occurred [UNKNOWN] report this to our developers";
    }
}

?>
<body>
    <div class="header">
        <div class="hl">
            <h1>RT - Panel</h1>
        </div>
        <div class="hr">
            <h3><a href="/">Homepage</a></h3>
            <h3><a href="/teamtasks">Team Tasks</a></h3>
            <h3><a href="https://discord.com/invite/J6CR7tjXhY" target="_blank">Discord</a></h3>
        </div>
    </div>
    <div class="main">
        <?php
            if (isset($username) && isset($dcid)) {
                echo 'Welcome back '.$username;
                
                if ($conn->connect_error) {
                    die("<p><span style='color: #ff0000;'>An error occurred while trying to connect to the database.</span></p>");
                }

                $sql = "SELECT * FROM `coinsys` WHERE `dcid`=$dcid";
                $result = $conn->query($sql);

                if ($result->num_rows == 1) {
                    $row = $result->fetch_assoc();
                    $coinsHolding = $row["coins"];

                    echo "<p>You're currently holding <span class='bold'>$coinsHolding Coins</span>!</p>";
                } else {
                    echo "<p><span style='color: #a70000;' class='bold'>You are not registered in the database. Write a message in Discord to collect coins!</span></p>";
                }

                if (isset($feedback)) {
                    echo "<div class='packs'>";
                    echo "<div class='feedback'>";
                    echo "<p><span class='bold'>Feedback:</span> ".$feedback."</p>";
                    echo "</div>";
                    echo "</div>";
                }
                
                $sql = "SELECT * FROM `servers` WHERE `dcid`=$dcid";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo "<div class=\"packs\">";
                    $counter = 1;
                        foreach ($result as $row) {
                            if ($row['done']) {
                                echo "<div class='pack'>";
                                echo "<h2>VM".$row['vmid']."</h2>";
                                echo "<p>IP: <span class='bold'>".getVmIp($row['vmid'])."</span></p>";
                                echo "<p>Pack: <span class='bold'>".$row['pack']."</span></p>";
                                echo "<p>Status: <span class='bold'>".getVmStatus($row['vmid'])."</span></p>";
                                echo "<form action='".$_SERVER["PHP_SELF"]."' method='POST'><input type='hidden' name='_id' value='".$row["vmid"]."'><input type='hidden' name='_method' value='start'><input type='submit' value='START'></form>";
                                echo "<form action='".$_SERVER["PHP_SELF"]."' method='POST'><input type='hidden' name='_id' value='".$row["vmid"]."'><input type='hidden' name='_method' value='restart'><input type='submit' value='RESTART'></form>";
                                echo "<form action='".$_SERVER["PHP_SELF"]."' method='POST'><input type='hidden' name='_id' value='".$row["vmid"]."'><input type='hidden' name='_method' value='stop'><input type='submit' value='STOP'></form>";
                                echo "<form action='".$_SERVER["PHP_SELF"]."' method='POST'><input type='hidden' name='_id' value='".$row["vmid"]."'><input type='hidden' name='_method' value='pw'><input name='_password' class='pw' type='password' placeholder='Password'><input type='submit' value='CHANGE'></form>";
                                echo "</div>";
                            } else {
                                echo "<div class='pack'>";
                                echo "<h3>".$row['vmid']."</h3>";
                                echo "Status: VM isn't paid or done, you'll have to wait.";
                                echo "</div>";
                            }

                            if ($counter % 3 == 0) {
                                echo "</div>";
                                echo "<div class='packs'>";
                            }
                            
                            $counter++;
                        }
                    echo "</div>";
                } else {
                    echo "<p><span style='color: #a70000;' class='bold'>You do not have a server yet! Write to our support to purchase a server.</span></p>";
                }
            }
        ?>
    </div>
</body>
</html>