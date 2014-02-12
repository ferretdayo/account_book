<?php
	//DB処理
	//接続設定
	$sv = "";
	$dbname = "";
	$user = "";
	$pass = "";
	//DBに接続する
	$link = mysqli_connect($sv,$user,$pass,$dbname);
	$conn = mysql_connect($sv,$user,$pass) or die(mysql_error());
	mysql_select_db($dbname) or die(mysql_error());
	
	//変更ボタンの作業での変数
	$change_no = 0;
	$id_no = 0;
	
	//初期化
	$no = 1;
	$row = 0;
	$error_no = 0;
	$sort_flg = 0;
	$error = array	(
					'1'=>"日付の記述は\"年/月/日\"の形で入力してください。",
					'2'=>"金額欄に数字ではないものが含まれています。",
					'3'=>"日付をyear/month/dayのように記述してください。"
					);
	//今日の日付を取得
	$today = date('Y/m/d');
	
	function input_check($newday,$newmoney){
		//"/"の数を数える
		if(substr_count($newday,"/") != 2){
			return 1;
		}else{
			//年月日に分割
			list($year,$month,$day)=explode("/",$newday);
			
			//数字ではない場合
			if(!is_numeric($newmoney)){
				return 2;
			}
			//ちゃんとした日付でない場合
			if(!checkdate($month,$day,$year)){
				return 3;
			}
		}
		return 0;
	}
	
	function show($row,$id_no,$change_no,$sort_flg){
		$id_no = $row['no'];
		if($change_no!=$id_no){					//変更ボタン押されてないところ
			echo "<tr><td align='right'>".$row['no']."</td>\n";
			echo "<td align='right'>".$row['date']."</td>\n";
			echo "<td align='right'>".$row['money']."円</td>\n";
			echo "<td align='right'>".$row['detail']."</td>\n";
		}else{										//変更ボタン押されたところだけ
			echo "<tr><td align='right'>".$row['no']."</td>\n";
			echo "<td align='right'><input type='text' size='10' name='update_day' value=".$row['date']."></td>\n";
			echo "<td align='right'><input type='text' size='2' name='update_money' value=".$row['money'].">円</td>\n";
			echo "<td align='right'><input type='text' size='30' name='update_detail' value=".$row['detail']."></td>\n";
		}
		if($sort_flg==0){	//ソートされていないとき
			if($change_no==$id_no){					//変更ボタンが押されたところだけ決定ボタンを表示
				echo "<td align='center'><input type='submit' value='決定' name='deside[{$id_no}]'></td>\n";
			}else{
				echo "<td align='center'><input type='submit' value='変更' name='change[{$id_no}]'></td>\n";
			}
			echo "<td align='center'><input type='submit' value='削除' name='delete[{$id_no}]'></td>\n";
		}
		echo "</tr>\n";
	}

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
			//半角英数字に変換
			$used_money = mb_convert_kana($used_money,"as");
			$used_day = mb_convert_kana($used_day,"as");
			//エラーの場所の番号
			$error_no=input_check($used_day,$used_money);
			//データベースに書き込み（何もエラーがない場合）
			if($error_no==0){
				$no = $row["maxno"]+1;
				$sql = "INSERT INTO account ";
				$sql .= "VALUE('{$no}','{$used_day}','{$used_money}','{$used_detail}')";
				$result = mysql_query($sql,$conn) or die(mysql_error());
				if($result){
					echo "書き込み成功";
				}
			}	
		}
		if(isset($_POST["change"])){
			//変更の場合の場所
			$change_no = key($_POST["change"]);
		}
		if(isset($_POST["deside"])){
			//決定が押された場合
			$update_no = key($_POST["deside"]);		//更新の場所を取得
			$update_day = htmlspecialchars($_POST["update_day"]);
			$update_money = htmlspecialchars($_POST["update_money"]);
			$update_detail = htmlspecialchars($_POST["update_detail"]);
			//半角英数字に変換
			$update_money = mb_convert_kana($update_money,"as");
			$update_day = mb_convert_kana($update_day,"as");
			//エラーの場所の番号
			$error_no = input_check($update_day,$update_money);
			if($error_no==0){
				$sql = "UPDATE account SET date='{$update_day}' , money='{$update_money}' , detail='{$update_detail}' WHERE no='{$update_no}'";
				$result = mysql_query($sql,$conn) or die(mysql_error());
				if($result){
					echo "更新成功";
				}
			}
		}
		if(isset($_POST["delete"])){
			//削除の場合のDBの処理
			$delete_no = key($_POST["delete"]);		//削除する場所を取得
			echo $delete_no;
			$sql = "DELETE FROM account WHERE no='{$delete_no}'";
			$result = mysql_query($sql,$conn) or die(mysql_error());
			if($result){
				echo "削除成功";
			}
		}
		
		//ソートの処理
		if(isset($_POST["sort"])){
			$sort_date = $_POST["month"];	//2014-02のようなデータ
			if($sort_date!=""){
				$sort_flg = 1;
				$sort_date = strtr($sort_date,"-","/");	//"-"を"/"に置換
				/*
					2014/02の最初(2014/02/01)
					2014/02の最後(2014/03/01)
				*/
				list($sort_year,$sort_month)=explode("/",$sort_date);
				//今月
				$now_month = date('Y/m/d',mktime(0,0,0,$sort_month,1,$sort_year));
				//来月
				$last_month = date('Y/m/d',mktime(0,0,0,$sort_month+1,1,$sort_year));
			}
		}
		//ソート解除
		if(isset($_POST["unsort"])){
			$sort_flg = 0;
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
	<?php 
		if($sort_flg==0){
	?>
		日付<input type="text" name="used_day" value="<?=$today?>" size="15">&nbsp;
		金額<input type="text" name="used_money" value="" size="8">円&nbsp;
		内容<input type="text" name="used_detail" value="" size="30">&nbsp;
		<input type="submit" name="submit" value="入力"><br>
		
		<?php
			}
			//エラーあった場合の表示
			if($error_no!=0){
				echo $error[$error_no]."<br>";
			}
			//クエリ
			if($sort_flg==1){	//ソート時
				$sql = "SELECT * FROM account WHERE date >= '{$now_month}' AND date < '{$last_month}' ORDER BY no DESC";
			}else{
				$sql = "SELECT * FROM account ORDER BY no DESC";
			}
			//$result = mysqli_query($link,$sql) or die(mysql_error());でも可
			$result = mysql_query($sql,$conn) or die(mysql_error());
			if(!mysql_fetch_array($result)&&$sort_flg==1){	//ソートした際、何もなかった場合
				echo "<br>該当するものはありませんでした。ʅ(｡◔‸◔｡)ʃ<br>";
			}else{
				$result = mysql_query($sql,$conn) or die(mysql_error());	//一回mysql_fetch_arrayで呼び出したため、1行目なくなるので、もう一度定義
				echo "<br>";
				echo "<table border='1' bgcolor='#FFFFFF' align='left' width='700'>\n";
				echo "<thead><tr><th>No</th><th>日付</th><th>金額</th><th>詳細</th>";
				if($sort_flg==0){
					echo "<th colspan='2'></th>";
				}
				//DBの中身を表示
				while($row = mysql_fetch_array($result)){		//mysqli_fetch_assoc($result)でも書ける
					show($row,$id_no,$change_no,$sort_flg);		//表示する関数show
				}
				//総額をはじき出す
				if($sort_flg==0){	//ソートされていないとき
					$sql = "SELECT SUM(money) AS sum_money FROM account";
				}else{	//ソートされた時
					$sql = "SELECT SUM(money) AS sum_money FROM account WHERE date >= '{$now_month}' AND date < '{$last_month}'";
				}
				$result = mysql_query($sql,$conn) or die(mysql_error());
				$row = mysql_fetch_array($result);
				$sum_money = $row["sum_money"];
				echo "<tr><td>総額</td>";
				echo "<td colspan='2' align='right'>{$sum_money}円</td>";
				echo "</tr>";
				echo "</table>\n<br>";
			}
			echo "<br clear='all'>";
			if($sort_flg==1){
				echo "<input type='submit' value='ソート解除' name='unsort'>";
			}else{
				echo "月のソート : <input type='month' name='month'>&nbsp;";
				echo "<input type='submit' value='ソートする' name='sort'>";
			}
		?>
  
	</form>

</body>
</html>
