<?php	//	Write access log and jump
include("csvdb.inc");
$counterFile = $dataFolder . "/counter.txt";
if (!@$_COOKIE["id"]){
	lock();
	$count = @file_get_contents($counterFile);
	if ($count === FALSE) $count = 0;
	$count ++;
	file_put_contents($counterFile, $count);
	setcookie("id", $count);
	unlock();
	$id = $count;
}else{
	$id = $_COOKIE["id"];
}
$logFile = $dataFolder . "/accessLog.csv";
$log="";
if (!file_exists($logFile)){
	$log .= "org,to,id,ip,referer\n";
}
$to = urldecode($_GET["to"]);
$org = urldecode($_GET["org"]);
$log .= mb_convert_encoding($org, "sjis-win"). ',' .$to. ',' .$id. ',' .$_SERVER['REMOTE_ADDR']. ',' .$_SERVER['HTTP_REFERER']. "\n";

file_put_contents($logFile, $log, FILE_APPEND);
header('Location: '. $to);
exit;
/*
	foreach($_SERVER as $k => $v){
		echo "$k = $v<br>";
	}
*/
?>
