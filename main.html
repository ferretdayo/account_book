<?php

	//DB処理
	//接続設定
	$sv = "localhost";
	$dbname = "accountbook";
	$user = "ferret";
	$pass = "firstaccountbook";
	//DBに接続する
	$link = mysqli_connect($sv,$user,$pass,$dbname);
	$conn = mysql_connect($sv,$user,$pass) or die(mysql_error());
	mysql_select_db($dbname) or die(mysql_error());
	
	//変更ボタンの作業での変数
	$change_no = 0;
	$row_no = 0;
	
	//初期化
	$no = 1;
	$row = 0;
	$used_money=0;
	$used_detail="";
	$money_error = "";
	$today_error = "";
	$day_error = "";
	//今日の日付を取得
	$today = date('Y/m/d');
	$month = 0;
	$year = 0;
	$day = 0;

	if($_SERVER["REQUEST_METHOD"]=="POST"){
		if(isset($_POST["submit"])){
			//値の取得
			$used_money = htmlspecialchars($_POST["used_money"]);
			$used_detail = htmlspecialchars($_POST["used_detail"]);
			$used_day = htmlspecialchars($_POST["used_day"]);
			//最大値番号を取得
			$sql = "SELECT MAX(no) AS maxno FROM account";
			$result = mysql_query($sql,$conn) or die(mysql_error());
			$row = mysql_fetch_array($result);
			if($result){
				echo "成功";
			}
			//半角英数字に変換
			$used_money = mb_convert_kana($used_money,"as");
			$used_day = mb_convert_kana($used_day,"as");
			//"/"の数を数える
			if(substr_count($used_day,"/") != 2){
				$day_error = "日付の記述は\"年/月/日\"の形で入力してください<br>";
			}else{
				//年月日に分割
				list($year,$month,$day)=explode("/",$used_day);
				
				//数字ではない場合
				if(!is_numeric($used_money)){
					$money_error = "使用した金額欄に数字ではないものが含まれています。<br>";
				}
				//ちゃんとした日付でない場合
				if(!checkdate($month,$day,$year)){
					$today_error = "日付をyear/month/dayのように記述してください。<br>";
				}
				//データベースに書き込み（何もエラーがない場合）
				if(($money_error == "")&&($today_error == "")&&($day_error=="")){
					$no = $row["maxno"]+1;
					//todo データベースに書き込み処理
					$sql = "INSERT INTO account ";
					$sql .= "VALUE('$no','$used_day','$used_money','$used_detail')";
					$result = mysql_query($sql,$conn) or die(mysql_error());
					if($result){
						echo "書き込み成功";
					}
				}
			}
		}
		if(isset($_POST["change"])){
			//変更の場合の場所
			$change_no = key($_POST["change"]);
		}
		if(isset($_POST["delete"])){
			//削除の場合のDBの処理
		}

	}

?>

<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<title>家計簿orおこづかい帳</title>

</head>
<body>

<h1>簡易家計簿</h1>



	<form name="myform" action="" method="POST" enctype="multipart/form-data">
		日付<input type="text" name="used_day" value="<?=$today?>" size="15">&nbsp;
		金額<input type="text" name="used_money" value="" size="8">円&nbsp;
		内容<input type="text" name="used_detail" value="" size="30">&nbsp;
		<input type="submit" name="submit" value="入力"><br>

		<?php
			//エラーあった場合の表示
			if($money_error != ""){
				echo $money_error;
			}
			if($today_error != ""){
				echo $today_error;
			}
			if($day_error != ""){
				echo $day_error;
			}
			//DBの内容表示
			$sql = "SELECT * FROM account ORDER BY no DESC";
			//$result = mysqli_query($link,$sql) or die(mysql_error());でも可
			$result = mysql_query($sql,$conn) or die(mysql_error());
			echo "<br>";
			echo "<table border='1' bgcolor='#FFFFFF' align='left' width='700'>";
			while($row = mysql_fetch_array($result)){		//mysqli_fetch_assoc($result)でも書ける
				$row_no = $row['no'];
				if($change_no!=$row_no){					//変更ボタン押されてないところ
					echo "<tr><td align='right'>".$row['no']."</td>";
					echo "<td align='right'>".$row['date']."</td>";
					echo "<td align='right'>".$row['money']."円</td>";
					echo "<td align='right'>".$row['detail']."</td>";
				}else{										//変更ボタン押されたところだけ
					echo "<tr><td align='right'>".$row['no']."</td>";
					echo "<td align='right'><input type='text' size='10' value=".$row['date']."></td>";
					echo "<td align='right'><input type='text' size='2' value=".$row['money'].">円</td>";
					echo "<td align='right'><input type='text' size='30' value=".$row['detail']."></td>";
				}
				if($change_no==$row_no){					//変更ボタンが押されたところだけ決定ボタンを表示
					echo "<td align='center'><input type='submit' value='決定' name='deside[$row_no]'></td>";
				}else{
					echo "<td align='center'><input type='submit' value='変更' name='change[$row_no]'></td>";
				}
				echo "<td align='center'><input type='submit' value='削除' name='delete[$row_no]'></td>";
				echo "</tr>";
			}
			echo "</table>";
			echo $change_no;
		?>

	</form>

</body>
</html>
