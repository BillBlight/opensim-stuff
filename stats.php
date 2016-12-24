<?php
$loginuri = "http://localhost:8002";
function ping ($host, $timeout = 1) {
    /* ICMP ping packet with a pre-calculated checksum */
    $package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
    $socket = socket_create(AF_INET, SOCK_RAW, 1);
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));
    socket_connect($socket, $host, null);

    $ts = microtime(true);
    socket_send($socket, $package, strLen($package), 0);
    if (socket_read($socket, 255)) {    
        $result = true;
    } else {
        $result = false;
    }
    socket_close($socket);

    return $result;
}
if (ping($loginuri, 10)) {
	$gstatus = "ONLINE";
}else{
	$gstatus = "OFFLINE";
}
$host = "localhost";
$user = "root";
$pass = "password";
$dbname = "robust";
$mysqli = new mysqli($host,$user,$pass,$dbname);
$presenceuseraccount = 0;
$preshguser = 0;
if ($pres = $mysqli->query("SELECT * FROM Presence")) {
	while ($presrow = $pres->fetch_array()) {
		if ($luser = $mysqli->query("SELECT * FROM UserAccounts WHERE PrincipalID = '".$presrow['UserID']."'")) {
			++$presenceuseraccount;
		}else{
			++$preshguser;
		}
	}
}
$monthago = time() - 2592000;
$pastmonth = 0;
if ($tpres = $mysqli->query("SELECT * FROM GridUser WHERE Logout < '".$monthago."'")) {
	$pastmonth = $tpres->num_rows;
}
$totalaccounts = 0;
if ($useraccounts = $mysqli->query("SELECT * FROM UserAccounts")) {
	$totalaccounts = $useraccounts->num_rows;
}
$totalregions = 0;
$totalvarregions = 0;
$totalsingleregions = 0;
$totalsize = 0;
if($regiondb = $mysqli->query("SELECT * FROM regions")) {
	while ($regions = $regiondb->fetch_array()) {
		++$totalregions;
		if ($regions['sizeX'] == 256) {
			++$totalsingleregions;
		}else{
			++$totalvarregions;
		}
		$rsize = $regions['sizeX'] * $regions['sizeY'];
		$totalsize += $rsize;
	}
}
$arr = ['GridStatus' => $gstatus,
	'InWorld' => number_format($presenceuseraccount),
	'HGVisitors' => number_format($preshguser),
	'MonthLogin' => number_format($pastmonth),
	'TotalAccounts' => number_format($totalaccounts),
	'Regions' => number_format($totalregions),
	'VarRegions' => number_format($totalvarregions),
	'SingleRegions' => number_format($totalsingleregions),
	'TotalLandSize' => number_format($totalsize)];
if ($_GET['format'] == "json") {
	header('Content-type: application/json');
	echo json_encode($arr);
}else if ($_GET['format'] == "xml") {
	function array2xml($array, $wrap='Stats', $upper=true) {
	    $xml = '';
	    if ($wrap != null) {
	        $xml .= "<$wrap>\n";
	    }
	    foreach ($array as $key=>$value) {
	        if ($upper == true) {
	            $key = strtoupper($key);
	        }
	        $xml .= "<$key>" . htmlspecialchars(trim($value)) . "</$key>";
	    }
	    if ($wrap != null) {
	        $xml .= "\n</$wrap>\n";
	    }
	    return $xml;
	}
	header('Content-type: text/xml');
	print array2xml($arr);
}else{
	foreach($arr as $k => $v) {
		echo '<B>'.$k.': </B>'.$v.'<br>';
	}
}
$mysqli->close();
?>