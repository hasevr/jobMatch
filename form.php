<?php 
$url=$_SERVER['REQUEST_URI'];
include("csvdb.inc");
if (!file_exists("run.txt")){
	echo "現在メンテナンス中です。しばらくお待ち下さい。";
	exit();
}

$comps = loadCsv("$dataFolder/comps.csv");		//	会社一覧
$comp = getRecordByCid($_GET["cid"], $comps);
if (@count($comp) == 0){
	echo "URLが不正です。<br>";
	exit();
}

lock();	//	リンク先保存するため、linksはロックが必要
$links = loadCsv("$dataFolder/links.csv");	//	リンク情報
$C = getKeyMap($links);

//var_dump($comp);
$oid = $comp["oid"];
if (!$oid){
	echo "会社IDが見つかりません。<br>";
}

//	$links 更新
$errors = array();
$update = false;
if (@$_POST["update"]){
	$update = true;
	//var_dump($_POST);
	$newRow = 0;
	$rowsToDelete = array();
	foreach($_POST as $k => $v){
		$ka = explode("_", $k);
		$col = $ka[0];
		$row = @$ka[1];
		//	新しい行の追加
		if (count($ka) == 2 && $row == 0){
			if ($newRow == 0){
				$newRow = count($links);
				$links[] =  array($C["oid"] => $oid);
			}
			$row = $newRow;
		}
		if ($col=="del" && @$links[$row][$C["oid"]] == $oid){		//	削除
			$rowsToDelete[] = $row;
		}
		//	更新
		if (array_key_exists($row, $links) &&
			array_key_exists($col, $C) && 
			$links[$row][$C["oid"]] == $oid){
			//	時刻の変換
			if ($col == "start" || $col == "end"){
				//	Unix時間に変換
				//echo "v = $v<br>";
				if ($v){
					preg_match('/([1-2]?[0-9])\/([0-3]?[0-9])'.
						'([ 　]+([0-2]?[0-9]):([0-6]?[0-9]))?/', $v, $matches);
					$v = strtotime(sprintf("2000-%02d-%02d %02d:%02d:00", 
							$matches[1], $matches[2], @$matches[4], @$matches[5]));
				}
			}
			//	CSVの更新
			$links[$row][$C[$col]] = $v;
		}
	}
	rsort($rowsToDelete);
	foreach($rowsToDelete as $r){
		unset($links[$r]);
	}
	$links = array_values($links);
	$new = $links[count($links)-1];
	//echo "new="; var_dump($new);
	$found = false;
	foreach($links[0] as $col => $key){
		if ($key!="oid" && @$new[$col]){
			$found = true;
			break;
		}
	}
	if (!$found){
		unset($links[count($links)-1]);
	}
	//foreach($links as $k => $v){ echo "$k: "; var_dump($v); echo "<br>"; }
	saveCsv("$dataFolder/links.csv", $links);
}
unlock();	//	保存が済んだのでロック終了

$records = array();
for($row=0; $row < count($links); $row++){
	if ($links[$row][$C["oid"]] == $oid){
		$records[] = getRecordByRow($row, $links);
	}
}
$records[] = getNewRecord($links);
function rcmp($a, $b){
	if (!$a["start"]) return 1;
	if (!$b["start"]) return -1;
	return $a["start"] - $b["start"];
}
usort($records, "rcmp");

?>
<html>
<head>
<title>説明会情報記入フォーム</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<style>
.error{
	background-color:cyan;
}
</style>
</head>
<body>
<h1>説明会情報更新フォーム</h1>
<?php echo '会社名：'.$comp['org']. '  ご担当：'. $comp['name'] . '様<br>'; ?>

<h2>説明会情報</h2>
就活中の学生には<a href="show.php">技術セミナー参加予定企業説明会一覧</a>を見るように連絡します。

このページには、以下に記載頂いたコメントとリンクが掲載されます。これらは、更新するとすぐに反映されます。<br>

<?php
if ($update){
	echo '<strong>';
	if (count($errors) > 0){	//	エラーがある場合
		echo '入力内容にエラーがあります。<span class="error">水色</span>の項目を修正の上再度「上書き更新」ボタンを押してください。';
	}else{
		echo "データがサーバに保存され、一覧が更新されました。";
	}
	echo '</strong><br>';
}else{
	echo '<br>';
}
?>

<form enctype="multipart/form-data" method="POST" action="<?php echo $url;?>">
<?php
	echo "<hr>";
	foreach($records as $rec){
		echo '<p>';
		$r = $rec["row"];
		//	日時
		echo "開始日時(例：4/20 9:00)：";
		echo '<input size="8" type="text" name="'. "start_$r" .'" ';
		echo 'value="'. 
			($rec["start"] ? date("n/j G:i", $rec["start"]) : "") . 
			'"/> ';
		echo "終了日時：";
		echo '<input size="8" type="text" name="'. "end_$r" .'" ';
		echo 'value="'. 
			($rec["end"] ? date("n/j G:i", $rec["end"]) : "") . 
			'"/> ';
		//	イベント名
		echo "イベント名：";
		echo '<input size="30" type="text" name="' . "event_$r". '" ';
		echo 'value="'. $rec["event"]. '"/><br>';
		//	説明
		echo '説明：<textarea rows="2", cols="100" name="' ."desc_$r". '">';
		echo $rec['desc'] . '</textarea><br>';
		//	リンク先
		echo 'リンク先：';
		echo '<input size="60" type="text" name="'. "link_$r" .'"';
		echo 'value="'. $rec["link"]. '"/> ';
		//	削除
		if ($rec != $records[count($records)-1]){
			echo 'このイベントを削除：<input type="checkbox" name="'."del_$r". '" ';
			echo 'value="del">';
		}
		echo "</p><hr>";
	}
?>
<input type=submit name="update" value="  上書き更新  "><br>
<br>
<br>
他のウィンドウ、デバイスから更新した結果を読み出す場合は
<input type=submit name="read" value=" 変更を破棄して同期 ">
を押してしてください。
</form>
</body>
</html>
