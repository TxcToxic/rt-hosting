<?php
// MADE BY TOXIC1835 WITH <3
// Yes i reused the "leaked" source, but it was mine too lol

// INITIALIZATION
header('Content-Type: application/json');

$feedback = array(
    'status' => '',
    'message' => ''
);

// GET AUTH TOKEN
$headers = apache_request_headers();
$auth_head = (!empty($headers['Authorization'])) ? $headers['Authorization'] : "";

check_auth($auth_head);

// CHECK AUTH FORMAT
function check_auth($auth_head) {
    global $feedback;

    if (empty($auth_head)) {
        $feedback['status'] = "unauthorized";
        $feedback['message'] = "you are not authorized";
        $feedback['err'] = "empty";
        kill_401();
    }
    if (strlen($auth_head) !== 91) {
        $feedback['status'] = "unauthorized";
        $feedback['message'] = "you are not authorized";
        $feedback['err'] = "format_len";
        kill_401();
    }
    if (strpos($auth_head, "RTToken-") !== 0) {
        $feedback['status'] = "unauthorized";
        $feedback['message'] = "you are not authorized";
        $feedback['err'] = "format_key";
        kill_401();
    }
    if (strpos($auth_head, ".") !== 58) {
        $feedback['status'] = "unauthorized";
        $feedback['message'] = "you are not authorized";
        $feedback['err'] = "format_secret";
        kill_401();
    }
}

// JUST KILL SITE WITH CODE 401
function kill_401() {
    global $feedback;

    $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);

    header('HTTP/2 401 Unauthorized');
    echo $jsonData;
    exit;
}

// SPLIT TOKEN AND SECRET
list($token, $secret) = explode(".", $auth_head, 2);
$secretHash = hash('sha512', $secret);

// DATABASE DATA
$servername = "db-node-01.toxic1835.xyz";
$username = "rth";
$password = "KJ72yQn(m8_6)Zar";
$dbname = "rth";

// DB CONNECTION
$conn = new mysqli($servername, $username, $password, $dbname);

// PROXMOX DATA
$apiUrl = "https://193.141.60.104:8006/api2/json";
$apiToken = "root@pam!roottoken=51a4b148-0c63-440a-a839-ccf64a694326";
$nodeName = "host01";

// CHECK IF DB CONN ERROR
if ($conn->connect_error) {
    $feedback["status"] = "db_connection_error";
    $feedback["message"] = "an error occurred while trying to connect to the database";
    $feedback["err"] = "db_connection_failure";

    header("HTTP/2 500 Internal Server Error");
    $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
    echo $jsonData;
    exit;
}

// CHECK IF TOKEN EXISTS
$stmt = $conn->prepare("SELECT * FROM `api` WHERE `rttoken` = ? AND `secret` = ?");
$stmt->bind_param("ss", $token, $secretHash);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    $feedback['status'] = "unauthorized";
    $feedback['message'] = "you are not authorized";
    $feedback['err'] = "identification_failure";
    kill_401();
}
$row = $result->fetch_assoc();
$dcid = $row['dcid'];
$api_tariff = $row['tariff'];

// GET CPR FROM api_tariffs
$stmt = $conn->prepare("SELECT * FROM `api_tariffs` WHERE `tariff` = ?");
$stmt->bind_param("s", $api_tariff);
$stmt->execute();
$result = $stmt->get_result();
$cpr = 5;

if ($result->num_rows === 1) { $row = $result->fetch_assoc(); $cpr = $row['cpr']; }

// GET CURRENT COINS
$stmt = $conn->prepare("SELECT * FROM `coinsys` WHERE `dcid` = ?");
$stmt->bind_param("s", $dcid);
$stmt->execute();
$result = $stmt->get_result();
$curcoins = 0;

if ($result->num_rows === 1) { $row = $result->fetch_assoc(); $curcoins = $row['coins']; }

// REMOVE COINS

if (!($cpr <= 0)) {
    if ($curcoins - $cpr >= 0) {
        $stmt = $conn->prepare("UPDATE `coinsys` SET `coins` = `coins` - ? WHERE `dcid` = ?");
        $stmt->bind_param("ss", $cpr, $dcid);

        if (!($stmt->execute())) {
            $feedback["status"] = "db_error";
            $feedback["message"] = "an error occurred while trying to update your coins";
            $feedback["err"] = "db_update_failure";

            header("HTTP/2 500 Internal Server Error");
            $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
            echo $jsonData;
    exit;
        }
    } else {
        $feedback["status"] = "user_error";
        $feedback["message"] = "upgrade your api tariff, buy some coins or write some messages in our discord and help people";
        $feedback["err"] = "not_enough_coins";

        header("HTTP/2 409 Conflict");
        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;
    }
}

// CHECK VM OWNER
function vmOwnerCheck($vmid) {
    global $conn, $dcid, $feedback;

    $stmt = $conn->prepare("SELECT * FROM `servers` WHERE `dcid`=? AND `vmid`=?");
    $stmt->bind_param("ss", $dcid, $vmid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) { return true; }
    
    $feedback['status'] = "unauthorized";
    $feedback['message'] = "you are not the owner of this server";
    $feedback['err'] = "not_server_owner";

    kill_401();
}

// GET VM IP DYNAMIC
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
        return "no_response";
    }

    $data = json_decode($response, true);
    if ($data === null || !isset($data["data"]['ipconfig0'])) {
        return "no_ip_config";
    }

    $vmIP = str_replace("ip=", "", $data["data"]["ipconfig0"]);

    return explode("/", $vmIP)[0];
}

// GET VM STATUS 
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
        return "no_response";
    }

    $data = json_decode($response, true);
    if ($data === null || !isset($data["data"]["status"])) {
        return "no_status_found";
    }

    return $data["data"]["status"];
}

// GET VMs
function getVms()
{
    global $conn, $feedback, $dcid;

    $stmt = $conn->prepare("SELECT * FROM `servers` WHERE `dcid`=?");
    $stmt->bind_param("s", $dcid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $vmArray = array();

        foreach ($result as $row) {
            $vmId = $row['vmid'];

            $vmData = array(
                'status' => getVmStatus($vmId),
                'ip' => getVmIp($vmId),
                'pack' => $row['pack'],
                'done' => ($row['done'] == 1) ? true : false,
                'kvm' => ($row['kvm'] === 1) ? true : false
            );

            $vmArray["$vmId"] = $vmData;
        }

        $feedback['status'] = "success";
        $feedback['message'] = "your requested data has been delivered";
        $feedback['req_data'] = $vmArray;


        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;

    } else {
        $feedback['status'] = "not_found";
        $feedback['message'] = "no server could be identified to this api token";
        $feedback['err'] = "no_server_found";

        header("HTTP/2 404 Not Found");
        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;
    }
}

// Just to keep code cleaner
function getVmIpAPI($vmid) 
{
    global $feedback, $dcid;

    $feedback['status'] = "success";
    $feedback['message'] = "your requested data has been delivered";
    $feedback['server_ip'] = getVmIp($vmid);

    $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);

    echo $jsonData;

    exit;

}

// CHECK IF VMID WAS GIVEN
function vmid_isset($vmid) {
    global $feedback;

    if (empty($vmid) || !isset($vmid)) { 
        header("HTTP/2 409 Conflict");
        $feedback['status'] = "not_set";
        $feedback['message'] = "no server id was set";
        $feedback['err'] = "srvid_empty";

        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;
    }
    return true;
}

// GET SINGLE VM DATA
function getVmData($vmid)
{
    // VM Owner check already proceed, so no double check

    global $conn, $feedback, $dcid;

    $stmt = $conn->prepare("SELECT * FROM `servers` WHERE `dcid`=? AND `vmid`=? LIMIT 1");
    $stmt->bind_param("ss", $dcid, $vmid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $vmArray = array(
        'status' => getVmStatus($vmid),
        'ip' => getVmIp($vmid),
        'pack' => $row['pack'],
        'done' => ($row['done'] === 1) ? true : false,
        'kvm' => ($row['kvm'] === 1) ? true : false
    );

    $feedback['status'] = "success";
    $feedback['message'] = "your requested data has been delivered";
    $feedback['req_data'] = $vmArray;

    $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
    echo $jsonData;
    exit;

}

function restartVm($vmid)
{
    global $apiUrl, $apiToken, $nodeName, $feedback;

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
        header("HTTP/2 409 Conflict");
        $feedback['status'] = "failed_restart";
        $feedback['message'] = "an error occurred by restarting this server";
        $feedback['err'] = "no_response";

        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;
    }

    $feedback['status'] = "success";
    $feedback['message'] = "the server is restarting";

    $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);

    echo $jsonData;
    exit;
}

function stopVm($vmid)
{
    global $apiUrl, $apiToken, $nodeName, $feedback;

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
        header("HTTP/2 409 Conflict");
        $feedback['status'] = "failed_stop";
        $feedback['message'] = "an error occurred by stopping this server";
        $feedback['err'] = "no_response";

        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;
    }

    $feedback['status'] = "success";
    $feedback['message'] = "the server is stopping";

    $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);

    echo $jsonData;
    exit;
}

function startVm($vmid)
{
    global $apiUrl, $apiToken, $nodeName, $feedback;

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
        header("HTTP/2 409 Conflict");
        $feedback['status'] = "failed_start";
        $feedback['message'] = "an error occurred by starting this server";
        $feedback['err'] = "no_response";

        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;
    }

    $feedback['status'] = "success";
    $feedback['message'] = "the server is starting";

    $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);

    echo $jsonData;
    exit;
}

// CHANGE VMs PASSWORD
function changePW($vmid, $newPassword)
{
    global $apiUrl, $apiToken, $nodeName, $feedback;

    if (empty($newPassword)) {
        header("HTTP/2 406 Not Acceptable");
        $feedback['status'] = "not_set";
        $feedback['message'] = "no password was set";
        $feedback['err'] = "pw_empty";

        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;
    }

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
        header("HTTP/2 409 Conflict");
        $feedback['status'] = "failed_pw_change";
        $feedback['message'] = "an error occurred by changing the password of this server";
        $feedback['err'] = "unknown";

        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $feedback['status'] = "success";
        $feedback['message'] = "the password for the user root has been changed | dont forget to restart your server";

        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);

        echo $jsonData;
        exit;
    } else {
        $feedback["status"] = "unknown_backend_api_failure";
        $feedback["message"] = "an error occurred while trying to change the password for root";
        $feedback["err"] = "api_failure";

        header("HTTP/2 500 Internal Server Error");
        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;
    }
}


function op_isset($operation) 
{
    global $feedback;

    if (empty($operation) || !isset($operation)) { 
        header("HTTP/2 409 Conflict");
        $feedback['status'] = "not_set";
        $feedback['message'] = "no operation was set";
        $feedback['err'] = "op_empty";

        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;
    }
    return true;
}

// MANAGING REQUESTS | USING FUNCTIONS
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $vmid = (isset($_GET["sid"])) ? $_GET["sid"] : "";
    $operation = (isset($_GET["op"])) ? $_GET["op"] : "";

    op_isset($operation);

    if ($operation === "get_servers") {
        getVms();
    } else if ($operation === "get_ip") {
        vmid_isset($vmid);
        if (vmOwnerCheck($vmid)) { getVmIpAPI($vmid); }
    } else if ($operation === "get_data") {
        vmid_isset($vmid);
        if (vmOwnerCheck($vmid)) { getVmData($vmid); }
    } else {
        $feedback['status'] = "not_found";
        $feedback['message'] = "the given operation was not found";
        $feedback['err'] = "op_not_found";

        header("HTTP/2 404 Not Found");
        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;
    }

} else if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vmid = (isset($_POST["sid"])) ? $_POST["sid"] : "";
    $operation = (isset($_POST["op"])) ? $_POST["op"] : "";

    op_isset($operation);

    $stmt = $conn->prepare("SELECT `done` FROM `servers` WHERE `dcid` = ? AND `vmid` = ?");
    $stmt->bind_param("ss", $dcid, $vmid);
    $stmt->execute();
    $result = $stmt->get_result();
    $vm_is_done = false;

    if ($result->num_rows === 1) { $row = $result->fetch_assoc(); $vm_is_done = (bool)$row['done']; }

    if (!($vm_is_done)) {
        $feedback['status'] = "not_done";
        $feedback['message'] = "your server or the payment wasn't done yet";
        $feedback['err'] = "not_done";

        header("HTTP/2 403 Forbidden");
        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;
    }

    if ($operation === "restart") {
        vmid_isset($vmid);
        if (vmOwnerCheck($vmid)) { restartVm($vmid); }
    } else if ($operation === "stop") {
        vmid_isset($vmid);
        if (vmOwnerCheck($vmid)) { stopVm($vmid); }
    } else if ($operation === "start") {
        vmid_isset($vmid);
        if (vmOwnerCheck($vmid)) { startVm($vmid); }
    } else if ($operation === "change_password") {
        $wishPassword = (isset($_POST["npw"])) ? $_POST["npw"] : "";
        vmid_isset($vmid);
        if (vmOwnerCheck($vmid)) { changePW($vmid, $wishPassword); }
    } else {
        $feedback['status'] = "not_found";
        $feedback['message'] = "the given operation was not found";
        $feedback['err'] = "op_not_found";

        header("HTTP/2 404 Not Found");
        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;
    }
} else {
    $feedback['status'] = "not_supported";
    $feedback['message'] = "the method you are using to contact our api is not allowed or supported";
    $feedback['err'] = "method_not_supported";

    header("HTTP/2 405 Method Not Allowed");
    $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
    echo $jsonData;
    exit;
}

$conn->close();
// QUIT WITHOUT GIVING CONTENT
header('HTTP/2 204 No Content');
exit;
?>