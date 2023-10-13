<!DOCTYPE html>
<html>
<head>
    <title>RTH - ACP</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="header">
        <div class="hl">
            <h1>RT - Admin Control Panel</h1>
        </div>
        <div class="hr">
            <h3><a href="/">Homepage</a></h3>
            <h3><a href="/panel">Panel</a></h3>
            <h3><a href="/teamtasks">Team Tasks</a></h3>
            <h3><a href="https://discord.com/invite/J6CR7tjXhY" target="_blank">Discord</a></h3>
        </div>
    </div>
<?php
$sessionLifetime = 15 * 24 * 60 * 60; // 15 Tage
session_set_cookie_params($sessionLifetime);
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_start();

$done_tasks_webhook = "https://discord.com/api/webhooks/1162064165842718834/oqiyDAhxkbQ3VWIvpfAkMFpeKg4A6_KL25exQtrYFIkLxd6fLX9TK7oE5ksBqLwEtodz";

$servername = "db-node-01.toxic1835.xyz";
$username = "rth";
$password = "KJ72yQn(m8_6)Zar";
$dbname = "rth";

$conn = new mysqli($servername, $username, $password, $dbname);


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

$stmt = $conn->prepare("SELECT * FROM `acp_perms` WHERE `dcid`=?");
$stmt->bind_param("s", $dcid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("HTTP/2 403 Forbidden");
    header('Location: https://rt-hosting.eu/');
    exit;
}
?>
    <div class="navbar">
        <a href="index.php">ACP Home</a>
        <a href="tasks.php">Team Tasks</a>
    </div>
    <div class="content">
        <div class="content-inner">
            <h2>Admin Control Panel</h2>
            <p>Welcome <?php echo $username; ?> to the Admin Control Panel</p>
        </div>
    </div>
</body>
</html>
