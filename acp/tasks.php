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

$servername = "db-node-01.toxic1835.xyz";
$username = "rth";
$password = "KJ72yQn(m8_6)Zar";
$dbname = "rth";

$conn = new mysqli($servername, $username, $password, $dbname);

$tasks_webhook = "https://discord.com/api/webhooks/1162487099891650600/mGfj5keVIbeobI-xrVC3zfE0nTyfXe0OFA4f6xQSITVn7-j0yrZbSfxgdLoaXzS54Zm8";

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'https://rt-hosting.eu/acp/';

    $stmt = $conn->prepare("SELECT * FROM `acp_perms` WHERE `dcid`=?");
    $stmt->bind_param("s", $dcid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        header("HTTP/2 403 Forbidden");
        header('Location: https://rt-hosting.eu/');
        exit;
    }

    if (isset($_POST['_method']) && !empty($_POST['_method'])) {
        $method = $_POST['_method'];

        if ($method === "add_tt_access") {
            if (isset($_POST['tt_dcid']) && !empty($_POST['tt_dcid'])) {
                $tt_dcid = $_POST['tt_dcid'];
            } else {
                echo "<script>alert(\"You forgot to enter a valid discord id!\"); console.error(\"user forgot to enter a (valid) discord id!\");</script>";
                header("Location: ".$ref);
                exit;
            }
            
            $stmt = $conn->prepare("SELECT * FROM `acp_perms` WHERE `dcid`=? AND `add_tt_access`=1");
            $stmt->bind_param("s", $dcid);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows !== 1) {
                header("HTTP/2 403 Forbidden");
                
                header("Location: ".$ref);
                exit;
            }
            
            $stmt = $conn->prepare("SELECT * FROM `teamtasks_access` WHERE `dcid`=?");
            $stmt->bind_param("s", $tt_dcid);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                echo "<script>alert(\"User ID already exists!\"); console.error(\"the id already exists in our db!\");</script>";
                header("Location: ".$ref);
                exit;
            }

            $stmt = $conn->prepare("INSERT INTO `teamtasks_access`(`dcid`) VALUES (?)");
            $stmt->bind_param("s", $tt_dcid);
            if (!($stmt->execute())) {
                echo "<script>alert(\"Something went wrong! Report it or just retry.\"); console.error(\"an unknown error occurred by inserting the user to the db!\");</script>";
                header("Location: ".$ref);
                exit;
            }
        } else if ($method === "create_task") {
            if (isset($_POST['task_op']) && ctype_digit($_POST['task_op']) && !empty($_POST['task_op'])) {
                $t_op = $_POST['task_op'];
            } else {
                echo "<script>alert(\"You forgot to enter the task operator!\"); console.error(\"user forgot to enter the task operator id!\");</script>";
                header("Location: ".$ref);
                exit;
            }
            if (isset($_POST['task_title']) && !empty($_POST['task_title'])) {
                $task_title = $_POST['task_title'];
            } else {
                echo "<script>alert(\"You forgot to enter a task title!\"); console.error(\"user forgot to enter a task title!\");</script>";
                header("Location: ".$ref);
                exit;
            }
            if (isset($_POST['task_desc']) && !empty($_POST['task_desc'])) {
                $task_desc = $_POST['task_desc'];
            } else {
                echo "<script>alert(\"You forgot to enter the task description!\"); console.error(\"user forgot to enter the task description\");</script>";
                header("Location: ".$ref);
                exit;
            }
            if (isset($_POST['task_deadline']) && !empty($_POST['task_deadline'])) {
                $task_deadline = $_POST['task_deadline'];
            } else {
                echo "<script>alert(\"You forgot to enter a valid task deadline!\"); console.error(\"user forgot to enter a (valid) task deadline!\");</script>";
                header("Location: ".$ref);
                exit;
            }

            $stmt = $conn->prepare("SELECT * FROM `teamtasks` WHERE `dcid`=? AND `task_title`=? AND `task`=?");
            $stmt->bind_param("sss", $t_op, $task_title, $task_desc);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                echo "<script>alert(\"Task already exists!\"); console.error(\"the task already exists!\");</script>";
                header("Location: ".$ref);
                exit;
            }

            $stmt = $conn->prepare("INSERT INTO `teamtasks`(`dcid`, `task_title`, `task`, `creator_dcid`, `deadline`) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $t_op, $task_title, $task_desc, $dcid, $task_deadline);
            if (!($stmt->execute())) {
                echo "<script>alert(\"Something went wrong! Report it or just retry.\"); console.error(\"an unknown error occurred by inserting the user to the db!\");</script>";
                header("Location: ".$ref);
                exit;
            }

            $data = [
                "content" => "<@".$t_op."> (".$t_op.") you got a **new Task** from <@".$dcid."> (".$dcid.") \n\n **Deadline:** ".$task_deadline,
            ];

            $jsonData = json_encode($data);

            $ch = curl_init($tasks_webhook);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_exec($ch);
            curl_close($ch);

        } else {
            echo "<script>alert(\"Method not allowed!\"); console.error(\"this method is not allowed!\");</script>";
            header("Location: ".$ref);
            exit;
        }
    }

    header("Location: ".$ref);
}

?>
    <div class="navbar">
        <a href="index.php">ACP Home</a>
        <a href="tasks.php">Team Tasks</a>
    </div>
    <div class="content">
        <?php
        if (isset($feedback) && !empty($feedback)) {
            echo "<div class=\"content-inner\"><h2>Feedback:</h2><p>".$feedback."</p></div>";
        }
        ?>
        <div class="content-inner">
            <h2>Task Creator</h2>
            <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
                <input type="hidden" name="_method" value="create_task">
                <h4 class="task-inner-title">Task Operator (id):</h4>
                <input type="text" name="task_op" class="acp-input" placeholder="123">
                <h4 class="task-inner-title">Task Title</h4>
                <input type="text" name="task_title" class="acp-input" placeholder="Panel Design">
                <h4 class="task-inner-title">Task Description</h4>
                <textarea class="acp-input" name="task_desc" style="text-align: start;" cols="30" rows="5" placeholder="Make the first button bigger"></textarea>
                <h4 class="task-inner-title">Deadline:</h4>
                <input type="datetime" name="task_deadline" class="acp-input" placeholder="YYYY/MM/DD HH:MM:SS">
                <br>
                <input type="submit" value="Upload">
            </form>
        </div>
<?php

$stmt = $conn->prepare("SELECT * FROM `acp_perms` WHERE `dcid`=? AND `add_tt_access`='1'");
$stmt->bind_param("s", $dcid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    echo "<div class=\"content-inner\"><h2>Give Teamtasks Access</h2><form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\"><input type=\"hidden\" name=\"_method\" value=\"add_tt_access\"><h4 class=\"task-inner-title\">Discord ID:</h4><input type=\"text\" name=\"tt_dcid\" class=\"acp-input\" placeholder=\"123\"><br><input type=\"submit\" value=\"Add\"></form></div>";
}

?>  
    </div>
</body>
</html>
