<?php
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
    $feedback["status"] = "connection_error";
    $feedback["message"] = "An error occurred while trying to connect to the database";
    $feedback["err"] = "unknown";

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
    $feedback['err'] = "db";
    kill_401();
}
$row = $result->fetch_assoc();
$dcid = $row['dcid'];

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
    global $conn, $feedback, $dcid;

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

// GET REQUESTS
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $vmid = (isset($_GET["serverid"])) ? $_GET["serverid"] : "";
    $operation = (isset($_GET["o"])) ? $_GET["o"] : "";

    if (empty($operation)) {
        $feedback['status'] = "not_set";
        $feedback['message'] = "no operation was set";
        $feedback['err'] = "op_empty";

        header("HTTP/2 409 Not Found");
        $jsonData = json_encode($feedback, JSON_PRETTY_PRINT);
        echo $jsonData;
        exit;
    }

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

}

$conn->close();
// QUIT WITHOUT GIVING CONTENT
header('HTTP/2 204 No Content');
exit;
?>