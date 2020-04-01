<?php
include("csvdb.inc");
if ($_GET["pass"] != $accessLogPass) exit;
$csv = loadCsv("$dataFolder/accessLog.csv");
$C = getKeyMap($csv);

$log = array();
for($r = 1; $r != count($csv); $r++){
	$row = $csv[$r];
	$log[$row[$C['org']]][$row[$C['id']]][] = array('to' => $row[$C['to']], 'referer' => $row[$C['referer']]);
}
?>
<html>
<head>
<title>アクセスログ</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<body>
<?
echo '<table border=1><tr><th>社名</th><th>アクセス数</th><th>アクセス先毎のアクセス数</th></tr>';
foreach($log as $org => $ids){
	echo '<tr><td>';
	echo $org. '</td><td>' .count($ids). '</td><td>';
	$tos = array();
	foreach($ids as $id => $idc){
		foreach($idc as $r){
			$tos[$r['to']][$id][] = $r['referer'];
		}
	}
	echo '<table>';
	foreach($tos as $to => $refs){
		//var_dump($tos);
		echo '<tr><td>' .$to. '</td><td>'. count($refs) . '</td></tr>';
	}
	echo '</table></td></tr>';
}
echo '</table><br>';
echo 'アクセス数は、ブラウザユニークです。同一ブラウザからのアクセスは重複してカウントしません。';
?>
</body>
</html>
