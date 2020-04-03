<?php
$url = $_SERVER["SCRIPT_NAME"];

include("csvdb.inc");
$compsCsv = loadCsv("$dataFolder/comps.csv");		//	会社一覧
$links = loadCsv("$dataFolder/links.csv");			//	リンク情報
$infos = loadCsv("$dataFolder/infos.csv");			//	会社情報
$C = getKeyMap($links);
$CC = getKeyMap($compsCsv);
$CI = getKeyMap($infos);

$recs = array();

for($i = 1; $i < count($links); $i++){
	$recs[] = getRecordByRow($i, $links);
}
for($i = 0; $i < count($recs); $i++){
	$comp = getRecordByOid($recs[$i]["oid"], $compsCsv);
	$recs[$i] = array_merge($comp, $recs[$i]);
}

$comps = array();
foreach($compsCsv as $comp){
	$oid = $comp[$CC["oid"]];
	$info = getRecordByOid($oid, $infos);
	$comps[$oid] = array_merge(array("org" => $comp[$CC["org"]]), $info);
}
function compcmp($a, $b){
	return strcmp($a["org"], $b["org"]);
}
uasort($comps, "compcmp");

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
<H2>楽水会参加予定企業 企業情報一覧</H2>
<H3><a href="showEvent.php">説明会一覧</a>も御覧ください。</H3>
<table>
<tr><th>社名</th><th>連絡先情報</th><th>全体情報</th>
<?php
foreach($comps as $comp){
	echo '<tr>';
	echo '<td>'. $comp['org'] .'</td>';
	echo '<td>'. $comp['contact'] .'</td>';
	echo '<td>' . $comp['desc'] .'</td>';
	echo '</tr>';
}
?>
</table>

</body>
</html>
