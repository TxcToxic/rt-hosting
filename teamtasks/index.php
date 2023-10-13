<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RTH - Team Tasks</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <div class="header">
        <div class="hl">
            <h1>RT - Team Tasks</h1>
        </div>
        <div class="hr">
            <h3><a href="/">Homepage</a></h3>
            <h3><a href="/panel">Panel</a></h3>
            <h3><a href="/acp">ACP</a></h3>
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

$stmt = $conn->prepare("SELECT * FROM `teamtasks_access` WHERE `dcid`=?");
$stmt->bind_param("s", $dcid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("HTTP/2 403 Forbidden");
    echo "<div class=\"tasks\"><div class=\"task\"><h3 class=\"forbidden\">Access Forbidden!</h3><p>You're a staff member? write the founders on <a href=\"https://discord.com/invite/J6CR7tjXhY\" target=\"_blank\">discord</a>!</p></div></div>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['task_id'])) {
        $taskid = $_POST['task_id'];

        $stmt = $conn->prepare("SELECT * FROM `teamtasks` WHERE `dcid`=? AND `id`=?");
        $stmt->bind_param("ss", $dcid, $taskid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $task_data = $result->fetch_assoc();
            $tasktitle = $task_data['task_title'];
            $taskguy   = $task_data['dcid'];
            $taskid    = $task_data['id'];
            $creation  = $task_data['creation'];
            $creator   = $task_data['creator_dcid'];

            $data = [
                'embeds' => [
                    [
                        'title' => $tasktitle,
                        'description' => '<@'.$taskguy.'> ('.$taskguy.') completed a task',
                        'color' => 65280,
                        'fields' => [
                            [
                                'name' => 'Task Creator:',
                                'value' => '<@'.$creator.'> ('.$creator.')',
                            ],
                            [
                                'name' => 'Task Creation Date:',
                                'value' => $creation,
                            ],
                            [
                                'name' => 'Task ID:',
                                'value' => $taskid,
                            ],
                        ],
                    ],
                ],
            ];

            $jsonData = json_encode($data);

            $ch = curl_init($done_tasks_webhook);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_exec($ch);
            curl_close($ch);

            $stmt = $conn->prepare("UPDATE `teamtasks` SET `done` = 1 WHERE `dcid` = ? AND `id` = ?");
            $stmt->bind_param("ss", $dcid, $taskid);
            if (!($stmt->execute())) {
                header("HTTP/2 409 Conflict");
                echo "<script>alert(\"Something went wrong! Report it or just retry.\"); console.error(\"an unknown error occurred by updating your task!\");</script>";
                exit;
            }
        } else {
            echo "<script>alert(\"nice try :)\"); console.error(\"you failed to exploit our website :c!\");</script>";
        }
    }
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'https://rt-hosting.eu/teamtasks/';
    header("Location:".$ref);
}

$stmt = $conn->prepare("SELECT * FROM `teamtasks` WHERE `dcid`=?");
$stmt->bind_param("s", $dcid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
?>

    <h2 class="open">Open Tasks</h2>
    <div class="tasks">
        <?php
                $open_tasks = 0;
                foreach ($result as $row) {
                    if ((bool)$row['done'] === false) {
                        if ($open_tasks < 2)
                        {
                            echo "<div class=\"task\"><h3 class=\"task-title\">Title: ".$row['task_title']."</h3><h4 class=\"task-inner-title\">Description:</h4><p>".str_replace("\n", "<br>", $row['task'])."</p><h4 class=\"task-inner-title\">Created By:</h4><p>".$row['creator_dcid']."</p><h4 class=\"task-inner-title\">Created At:</h4><p>".$row['creation']."</p><h4 class=\"task-inner-title\">Deadline:</h4><p>".$row['deadline']."</p><form action='".$_SERVER['PHP_SELF']."' method='POST'><input type='hidden' name='task_id' value='".$row['id']."'><input type='submit' value='Done'></form></div>";
                        }
                        $open_tasks++;
                    }
                }
                if ($open_tasks > 2) {
                    $open_tasks = $open_tasks - 2;

                    echo "<div class=\"task\"><p>+".$open_tasks." more hidden tasks</p></div>";
                }
        ?>
    </div>
    <hr class="line">
    <h2 class="done">Done Tasks</h2>
    <div class="tasks">
        <?php
            foreach ($result as $row) {

                if ((bool)$row['done'] === true) {
                    echo "<div class=\"task\"><h3 class=\"task-title-done\">Title: ".$row['task_title']."</h3><h4 class=\"task-inner-title\">Created By:</h4><p>".$row['creator_dcid']."</p></div>";
                }
            }
        ?>
    </div>
    <?php } ?>
</body>
</html>