<?php
	//DB処理
	//接続設定
	$sv = "";
	$dbname = "";
	$user = "";
	$pass = "";
	//DBに接続する
	//$link = mysqli_connect($sv,$user,$pass,$dbname);
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
	$change_check = array();
	$ischeck = 0;
	$sql = "";
	$pay_sql = "";
	$earn_sql = "";
	$data_list = array();
	$now_page = 1;
	$max_page = 0;
	$min_page = 1;
	$error = array	(
					'1'=>'日付の記述は\"年/月/日\"の形で入力してください。',
					'2'=>'支出,収入欄に数字ではないものが含まれています。',
					'3'=>'日付をyear/month/dayのように記述してください。',
					'4'=>'支出,収入欄に負の値が入っています。',
					);
	//今日の日付を取得
	$today = date('Y/m/d');
	//表示する関数（1~10件11~20と
	function show($data_list,$i,$sort_flg){
		foreach($data_list as $key => $value){
			if($sort_flg == 0){
				if(($key <= 10*$i)&&($key >= 11*($i-1))){
					echo $value;
				}
			}else{
				echo $value;
			}
		}
	}
				
	function input_check($newday,$newpay,$newearn){
		//"/"の数を数える
		if(substr_count($newday,"/") != 2){
			return 1;
		}else{
			//年月日に分割
			list($year,$month,$day)=explode("/",$newday);
			
			//数字ではない場合
			if(!is_numeric($newpay)||!is_numeric($newearn)){
				return 2;
			}else{
				if($newpay<0&&$newearn<0){
					return 4;
				}
			}
			//ちゃんとした日付でない場合
			if(!checkdate($month,$day,$year)){
				return 3;
			}
		}
		return 0;
	}
	
	function input_show($row,$id_no,$change_check,$sort_flg,&$ischeck/*参照渡し*/,&$data_list){
		$flg = 0;
		$id_no = $row['no'];
		$account_list = "";
		foreach($change_check as $key => $value){
			if($key == $id_no){										//変更ボタン押されたところだけ
				$account_list = "<tr>";
				$account_list .= "<td align='center'><input type='checkbox' name='check[{$id_no}]' checked></td>";
				$account_list .= "<td align='right'>".$row['no']."</td>\n";
				$account_list .= "<td align='right'><input type='text' size='10' name='update_day[{$id_no}]' value=".$row['date']."></td>\n";
				$account_list .= "<td align='right'><input type='text' size='2' name='update_pay[{$id_no}]' value=".$row['pay'].">円</td>\n";
				$account_list .= "<td align='right'><input type='text' size='2' name='update_earn[{$id_no}]' value=".$row['earn'].">円</td>\n";
				$account_list .= "<td align='right'><input type='text' size='30' name='update_detail[{$id_no}]' value=".$row['detail']."></td>\n";
				if($sort_flg == 0){	//ソートされてないとき
					$account_list .= "<td align='center'><input type='submit' value='決定' name='deside[{$id_no}]'></td>\n";
				}
				$flg = 1;
				$ischeck = 1;
			}
		}
		if($flg == 0){					//変更ボタン押されてないところ
			$account_list .= "<tr>";
			if($sort_flg == 0){
				$account_list .= "<td align='center'><input type='checkbox' name='check[{$id_no}]'></td>";
			}
			$account_list .= "<td align='right'>".$row['no']."</td>\n";
			$account_list .= "<td align='right'>".$row['date']."</td>\n";
			$account_list .= "<td align='right'>".$row['pay']."円</td>\n";
			$account_list .= "<td align='right'>".$row['earn']."円</td>\n";
			$account_list .= "<td align='right'>".$row['detail']."</td>\n";
			if($sort_flg == 0){
				$account_list .= "<td align='center'><input type='submit' value='変更' name='change[{$id_no}]'></td>\n";
			}
		}
		if($sort_flg == 0){	//ソートされていないとき
			$account_list .= "<td align='center'><input type='submit' value='削除' name='delete[{$id_no}]'></td>\n";
		}
		$account_list .= "</tr>\n";
		array_push($data_list,$account_list);
	}

	if($_SERVER["REQUEST_METHOD"] == "POST"){
		if(isset($_POST["submit"])){
			//値の取得
			$used_pay = htmlspecialchars($_POST["used_pay"]);
			$used_earn = htmlspecialchars($_POST["used_earn"]);
			$used_detail = htmlspecialchars($_POST["used_detail"]);
			$used_day = htmlspecialchars($_POST["used_day"]);
			//最大値番号を取得
			$sql = "SELECT MAX(no) AS maxno FROM account";
			$result = mysql_query($sql,$conn) or die(mysql_error());
			$row = mysql_fetch_array($result);
			//半角英数字に変換
			$used_pay = mb_convert_kana($used_pay,"as");
			$used_earn = mb_convert_kana($used_earn,"as");
			$used_day = mb_convert_kana($used_day,"as");
			//エラーの場所の番号
			$error_no=input_check($used_day,$used_pay,$used_earn);
			//データベースに書き込み（何もエラーがない場合）
			if($error_no == 0){
				$no = $row["maxno"]+1;
				$sql = "INSERT INTO account ";
				$sql .= "VALUE('{$no}','{$used_day}','{$used_pay}','{$used_earn}','{$used_detail}')";
				$result = mysql_query($sql,$conn) or die(mysql_error());
				if($result){
					echo "書き込み成功";
				}
			}	
		}
		//変更ボタン押された場合
		if(isset($_POST["change"])){
			//変更の場合の場所
			$change_check = $_POST["change"];
		}
		//決定が押された場合
		if(isset($_POST["deside"])){		
			$update_no = key($_POST["deside"]);		//更新の場所を取得
			$update_day = htmlspecialchars($_POST["update_day"][$update_no]);
			$update_pay = htmlspecialchars($_POST["update_pay"][$update_no]);
			$update_earn = htmlspecialchars($_POST["update_earn"][$update_no]);
			$update_detail = htmlspecialchars($_POST["update_detail"][$update_no]);
			//半角英数字に変換
			$update_pay = mb_convert_kana($update_pay,"as");
			$update_earn = mb_convert_kana($update_earn,"as");
			$update_day = mb_convert_kana($update_day,"as");
			//エラーの場所の番号
			$error_no = input_check($update_day,$update_pay,$update_earn);
			if($error_no == 0){
				$sql = "UPDATE account SET date='{$update_day}' , pay='{$update_pay}' , earn='{$update_earn}' , detail='{$update_detail}' WHERE no='{$update_no}'";
				$result = mysql_query($sql,$conn) or die(mysql_error());
				if($result){
					echo "更新成功";
				}
			}
		}
		if(isset($_POST["delete"])){
			//削除の場合のDBの処理
			$delete_no = key($_POST["delete"]);		//削除する場所を取得
			$sql = "DELETE FROM account WHERE no='{$delete_no}'";
			$result = mysql_query($sql,$conn) or die(mysql_error());
			if($result){
				echo $delete_no." : 削除成功";
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
		//一括変更ボタン押した場合
		if(isset($_POST["change_check"])){
			if(isset($_POST["check"])){
				$change_check=$_POST["check"];
			}
		}
		//一括決定ボタン押した場合
		if(isset($_POST["deside_check"])){
			if(isset($_POST["check"])){
				$deside_check = $_POST["check"];
				foreach($deside_check as $key => $value){
					/*
					✔された場所($key)を$_POST[HTMLのname][$key]で
					取得することが出来る。
					*/
					$update_day = htmlspecialchars($_POST["update_day"][$key]);
					$update_pay = htmlspecialchars($_POST["update_pay"][$key]);
					$update_earn = htmlspecialchars($_POST["update_earn"][$key]);
					$update_detail = htmlspecialchars($_POST["update_detail"][$key]);
					//半角英数字に変換
					$update_pay = mb_convert_kana($update_pay,"as");
					$update_earn = mb_convert_kana($update_earn,"as");
					$update_day = mb_convert_kana($update_day,"as");
					//エラーの場所の番号
					$error_no = input_check($update_day,$update_pay,$update_earn);
					if($error_no == 0){
						$sql = "UPDATE account SET date='{$update_day}' , pay='{$update_pay}' , earn='{$update_earn}' , detail='{$update_detail}' WHERE no='{$key}'";
						$result = mysql_query($sql,$conn) or die(mysql_error());
						if($result){
							echo $key." : 更新成功<br>";
						}
					}
				}
			}
		}
		//一括削除ボタンを押された場合
		if(isset($_POST["delete_check"])){
			if(isset($_POST["check"])){
				$delete_check = $_POST["check"];
				foreach($delete_check as $key => $value){
					$sql = "DELETE FROM account WHERE no='{$key}'";
					$result = mysql_query($sql,$conn) or die(mysql_error());
					if($result){
						echo $key." : 削除成功<br>";
					}
				}
			}
		}
	}
	//次へのボタンを押された場合
	if(isset($_GET['next'])){
		$now_page = $_GET['next'] + 1;
	}
	//前へのボタンが押された場合
	if(isset($_GET['privious'])){
		$now_page = $_GET['privious'] - 1;
	}

?>

<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<title>家計簿orおこづかい帳</title>
<link href="home.css" rel="stylesheet" type="text/css">
</head>
<body>

<p class="h">簡易家計簿</p>

	<form name="myform" action="" method="POST" enctype="multipart/form-data">
	<?php 
		if($sort_flg==0){
	?>
		日付<input type="text" name="used_day" value="<?=$today?>" size="15">&nbsp;
		支出<input type="text" name="used_pay" value="0" size="8">円&nbsp;
		収入<input type="text" name="used_earn" value="0" size="8">円&nbsp;
		内容<input type="text" name="used_detail" value="" size="30">&nbsp;
		<input type="submit" name="submit" value="入力"><br>
		
		<?php
			}
			//エラーあった場合の表示
			if($error_no != 0){
				echo $error[$error_no]."<br>";
			}
			//最大数を取得
			$sql = "SELECT MAX(no) AS maxno FROM account";
			$result = mysql_query($sql,$conn) or die(mysql_error());
			$row = mysql_fetch_array($result);
			if($error_no == 0){
				$no = $row["maxno"];
				$max_page = (int)($no / 10)+1;	//最大ページを取得
				echo $max_page;
			}
			//クエリ
			if($sort_flg == 1){	//ソート時
				$sql = "SELECT * FROM account WHERE date >= '{$now_month}' AND date < '{$last_month}' ORDER BY no DESC";
			}else{
				$sql = "SELECT * FROM account ORDER BY no DESC";
			}
			//$result = mysqli_query($link,$sql) or die(mysql_error());でも可
			$result = mysql_query($sql,$conn) or die(mysql_error());
			if(!mysql_fetch_array($result)){
				if($sort_flg == 1){	//ソートした際、何もなかった場合
					echo "<br>該当するものはありませんでした。ʅ(｡◔‸◔｡)ʃ<br>";
				}else{
					echo "<br>何もありません<br>";
				}
			}else{
				$result = mysql_query($sql,$conn) or die(mysql_error());	//一回mysql_fetch_arrayで呼び出したため、1行目なくなるので、もう一度定義
				echo "<br>";
				echo "<table border='1' bgcolor='#FFFFFF' align='left' width='700'>\n";
				echo "<thead><tr>";
				if($sort_flg == 0){		//ソートされてない場合
					echo "<th>✔</th>";
				}
				echo "<th>No</th><th>日付</th><th>支出</th><th>収入</th><th>詳細</th>";
				if($sort_flg == 0){
					echo "<th colspan='2'></th>";
				}
				//DBの中身を代入
				while($row = mysql_fetch_array($result)){		//mysqli_fetch_assoc($result)でも書ける
					//表示する内容を配列に代入する関数input_show
					input_show($row,$id_no,$change_check,$sort_flg,$ischeck,$data_list);
				}
				//DBの内容を表示
				show($data_list,$now_page,$sort_flg);
				//支出と収入の総額をはじき出す
				if($sort_flg == 0){	//ソートされていないとき
					$pay_sql = "SELECT SUM(pay) AS sum_pay FROM account";
					$earn_sql = "SELECT SUM(earn) AS sum_earn FROM account";
				}else{	//ソートされた時
					$pay_sql = "SELECT SUM(pay) AS sum_pay FROM account WHERE date >= '{$now_month}' AND date < '{$last_month}'";
					$earn_sql = "SELECT SUM(earn) AS sum_earn FROM account WHERE date >= '{$now_month}' AND date < '{$last_month}'";
				}
				//クエリの結果を代入
				$pay_result = mysql_query($pay_sql,$conn) or die(mysql_error());
				$earn_result = mysql_query($earn_sql,$conn) or die(mysql_error());
				//返ってきた配列(一つだけの要素しか入っていない)
				$pay_row = mysql_fetch_array($pay_result);
				$earn_row = mysql_fetch_array($earn_result);
				//要素の値を代入
				$sum_pay = $pay_row["sum_pay"];
				$sum_earn = $earn_row["sum_earn"];
				if($sort_flg == 0){	//ソートされてない場合
					echo "<tr><td colspan='3' align='center'>総額</td>";
				}else{
					echo "<tr><td colspan='2' align='center'>総額</td>";
				}
				$sum_pay_earn = $sum_earn-$sum_pay;
				echo "<td colspan='1' align='right'>{$sum_pay}円</td><td align='right'>{$sum_earn}円</td><td>&nbsp;</td>";
				if($sort_flg == 0){	//ソートされてない場合
					if($ischeck == 1){		//一括変更された場合
						echo "<td align='center'><input type='submit' name='deside_check' value='一括決定'></td>";
					}else{
						echo "<td align='center'><input type='submit' name='change_check' value='一括変更'></td>";
					}
					echo "<td align='center'><input type='submit' name='delete_check' value='一括削除'></td>";
				}
				echo "</tr>";
				echo "<tr>";
				if($sort_flg == 0){	//ソートされてない場合
					echo "<td align='center' colspan='3'>収入-支出</td>";
				}else{
					echo "<td align='center' colspan='2'>収入-支出</td>";
				}
				echo "<td align='right' colspan='2'>{$sum_pay_earn}円</td><td colspan='3'></td>";
				echo "</tr>";
				echo "</table>\n<br>";
			}
			echo "<br clear='all'>";	//回り込みを解除
			if($sort_flg == 1){
				echo "<input type='submit' value='ソート解除' name='unsort'>";
			}else{
				echo "月のソート : <input type='month' name='month'>&nbsp;";
				echo "<input type='submit' value='ソートする' name='sort'>";
			}
		?>
	</form>
	<table>
		<form method='GET' action='' enctype="multipart/form-data">
		<?php
			if($sort_flg == 0){
				if($max_page != $now_page){	//次のページ
					echo "<input type='hidden' name='next' value='$now_page'>";
					echo "<input type='submit' value='次へ'>";
				}
				?>
		</form>
		<form method='GET' action='' enctype="multipart/form-data">
				<?
				if($min_page != $now_page){	//前のページ
					echo "<input type='hidden' name='privious' value='$now_page'>";
					echo "<input type='submit' value='前へ'>";
				}
			}
		?>
  
		</form>
	</table>
</body>
</html>
