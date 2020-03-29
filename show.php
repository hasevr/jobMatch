<?php
$url = $_SERVER["SCRIPT_NAME"];

include("csvdb.inc");
$comps = loadCsv("$dataFolder/comps.csv");		//	会社一覧
$links = loadCsv("$dataFolder/links.csv");			//	リンク情報
$C = getKeyMap($links);

$recs = array();

for($i = 1; $i < count($links); $i++){
	$recs[] = getRecordByRow($i, $links);
}
for($i = 0; $i < count($recs); $i++){
	$comp = getRecordByOid($recs[$i]["oid"], $comps);
	$recs[$i] = array_merge($comp, $recs[$i]);
}

function rcmpOrg($a, $b){
	return strcmp($a["org"], $b["org"]);
}
function rcmpStart($a, $b){
	if (!$a["start"]) return 1;
	if (!$b["start"]) return -1;
	return $a["start"] - $b["start"];
}
if (@$_GET["sort"] == "org"){
	function rcmp($a, $b){
		$rv = rcmpOrg($a, $b);
		if ($rv == 0){
			$rv = rcmpStart($a, $b);
		}
		return $rv;
	}
}else{
	function rcmp($a, $b){
		$rv = rcmpStart($a, $b);
		if ($rv == 0){
			$rv = rcmpOrg($a, $b);
		}
		return $rv;
	}
}
usort($recs, "rcmp");

?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<style>
table{
  border-collapse: collapse;
  margin: 0;
}
th {
  border-width: 0 0.2em 0.2em 0.2em;
  border-color: white;
  border-style: solid;
  padding: 0.1em;
}
td {
  border-width: 0 0.2em 0.2em 0.2em;
  border-color: white;
  border-style: solid;
  padding: 0.1em;
  width:<?php echo floor(100/$ncol);?>%;
}

th, td{
  background: #faf0e6;
}
a{
	text-decoration: none;
	color: #008080;
}
a.link, span.link{
	font-weight: normal;
}
</style>
</head>
<body>
<H2>楽水会参加予定企業説明会一覧</H2>
<p>イベント名が各社のページにリンクされています。</p>
<table>
<tr><th><a href="<?php echo $url?>?sort=date">日時</a></th>
	<th><a href="<?php echo $url?>?sort=org">社名</a></th><th>イベント名</th><th>説明</th>
<?php
foreach($recs as $rec){
	echo '<tr>';
	echo '<td>'. (@$rec['start'] ? date('n/j G:i', $rec['start']): "");
	if (@$rec['end']){
		echo ' - ';
		if (date('n/j', $rec['start']) == date('n/j', $rec['end'])){
			echo date('G:i', $rec['end']);
		}else{
			echo date('n/j G:i', $rec['end']);
		}
	}
	echo '</td>';
	echo '<td>' . $rec['org'] .'</td>';
	if ($rec['link']) {
		echo '<td><a href="' . $rec['link'] . '">' . $rec['event'] .'</a></td>';
	}else{
		echo '<td>' . $rec['event'] .'</td>';
	}
	echo '<td>' . $rec['desc'] .'</td>';
	echo '</tr>';
}
?>
</table>
</body>
</html>
