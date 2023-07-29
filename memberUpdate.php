<?php
function GetSQLValueString($theValue, $theType)
{
	switch ($theType) {
		case "string":
			$theValue = ($theValue != "") ? filter_var($theValue, FILTER_SANITIZE_MAGIC_QUOTES) : "";
			break;
		case "int":
			$theValue = ($theValue != "") ? filter_var($theValue, FILTER_SANITIZE_NUMBER_INT) : "";
			break;
		case "email":
			$theValue = ($theValue != "") ? filter_var($theValue, FILTER_VALIDATE_EMAIL) : "";
			break;
		case "url":
			$theValue = ($theValue != "") ? filter_var($theValue, FILTER_VALIDATE_URL) : "";
			break;
	}
	return $theValue;
}
require_once("connect.php");
session_start();
//檢查是否經過登入
if (!isset($_SESSION["loginMember"]) || ($_SESSION["loginMember"] == "")) {
	header("Location: login.php");
}
//執行登出動作
if (isset($_GET["logout"]) && ($_GET["logout"] == "true")) {
	unset($_SESSION["loginMember"]);
	unset($_SESSION["memberLevel"]);
	header("Location: login.php");
}
//重新導向頁面
$redirectUrl = "member.php";
//執行更新動作
if (isset($_POST["action"]) && ($_POST["action"] == "update")) {
	$query_update = "UPDATE client SET passwd=:passwd, client_name=:client_name, gender=:gender, birthday=:birthday, email=:email, avatar=:avatar, phone=:phone, client_address=:client_address WHERE client_id=:client_id";
	$stmt = $pdo->prepare($query_update);

	// 檢查是否有修改密碼
	$mpass = $_POST["passwdo"];
	if (($_POST["passwd"] != "") && ($_POST["passwd"] == $_POST["passwdrecheck"])) {
		$mpass = password_hash($_POST["passwd"], PASSWORD_DEFAULT);
	}

	$stmt->bindParam(':passwd', $mpass, PDO::PARAM_STR);
	$stmt->bindParam(':client_name', GetSQLValueString($_POST["client_name"], 'string'), PDO::PARAM_STR);
	$stmt->bindParam(':gender', GetSQLValueString($_POST["gender"], 'string'), PDO::PARAM_STR);
	$stmt->bindParam(':birthday', GetSQLValueString($_POST["birthday"], 'string'), PDO::PARAM_STR);
	$stmt->bindParam(':email', GetSQLValueString($_POST["email"], 'email'), PDO::PARAM_STR);
	$avatar = file_get_contents($_FILES["avatar"]["tmp_name"]);
	$stmt->bindParam(':avatar', $avatar, PDO::PARAM_LOB);

	//stmt->bindParam(':avatar', $_POST["avatar"], PDO::PARAM_STR);
	$stmt->bindParam(':phone', GetSQLValueString($_POST["phone"], 'string'), PDO::PARAM_STR);
	$stmt->bindParam(':client_address', GetSQLValueString($_POST["client_address"], 'string'), PDO::PARAM_STR);
	$stmt->bindParam(':client_id', GetSQLValueString($_POST["client_id"], 'int'), PDO::PARAM_INT);

	$stmt->execute();
	$stmt->closeCursor();

	//若有修改密碼，則登出回到首頁。
	if (($_POST["passwd"] != "") && ($_POST["passwd"] == $_POST["passwdrecheck"])) {
		unset($_SESSION["loginMember"]);
		unset($_SESSION["memberLevel"]);
		$redirectUrl = "login.php";
	}
	//重新導向
	header("Location: $redirectUrl");
}
//繫結登入會員資料
$query_RecMember = "SELECT * FROM client WHERE account=:account";
$RecMember = $pdo->prepare($query_RecMember);
$RecMember->bindParam(':account', $_SESSION["loginMember"], PDO::PARAM_STR);
$RecMember->execute();
$row_RecMember = $RecMember->fetch(PDO::FETCH_ASSOC);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>網站會員系統</title>
<style>
		.avatar {
	width: 100px;
	height: 100px;
	border-radius: 50%;
	}

	.flex-container {
		display: flex;
	}

	.form-container {
		flex-grow: 1;
		padding: 20px;
	}

	.member-info-container {
		flex-grow: 1;
		padding: 20px;
		display: flex;
		flex-direction: column;
		align-items: center;
	}
	</style>
<script language="javascript">
function checkForm(){
	if(document.formJoin.passwd.value!="" || document.formJoin.passwdrecheck.value!=""){
		if(!check_passwd(document.formJoin.passwd.value,document.formJoin.passwdrecheck.value)){
			document.formJoin.passwd.focus();
			return false;
		}
	}	
	if(document.formJoin.client_name.value==""){
		alert("請填寫姓名!");
		document.formJoin.client_name.focus();
		return false;
	}
	if(document.formJoin.birthday.value==""){
		alert("請填寫生日!");
		document.formJoin.birthday.focus();
		return false;
	}
	if(document.formJoin.email.value==""){
		alert("請填寫電子郵件!");
		document.formJoin.email.focus();
		return false;
	}
	if(!checkmail(document.formJoin.email)){
		document.formJoin.email.focus();
		return false;
	}
	if(document.formJoin.phone.value==""){
		alert("請填寫電話!");
		document.formJoin.phone.focus();
		return false;
	}
	if(document.formJoin.client_address.value==""){
		alert("請填寫地址!");
		document.formJoin.client_address.focus();
		return false;
	}
	return confirm('確定送出嗎？');
}
function check_passwd(pw1,pw2){
	if(pw1==''){
		alert("密碼不可以空白!");
		return false;
	}
	for(var idx=0;idx<pw1.length;idx++){
		if(pw1.charAt(idx) == ' ' || pw1.charAt(idx) == '\"'){
			alert("不能有空白或雙引號 !\n");
			return false;
		}
		if(pw1.length<5 || pw1.length>10){
			alert( "密碼長度只能5到10個字母與數字 !\n" );
			return false;
		}
		if(pw1!= pw2){
			alert("密碼二次輸入不一樣,請重新輸入 !\n");
			return false;
		}
	}
	return true;
}
function checkmail(myEmail) {
	var filter  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if(filter.test(myEmail.value)){
		return true;
	}
	alert("電子郵件格式不正確");
	return false;
}
</script>
</head>

<body>
<div class="container form-container d-flex justify-content-center">
	<form action="" method="POST" name="formJoin" id="formJoin" onSubmit="return checkForm();" enctype="multipart/form-data" style="width: 50%;">
		<h2 class="text-primary text-center ">修改資料</h2>
		<div class="form-container border">
			<h3>帳號資料</h3>
			<p><strong>使用帳號</strong>: <?php echo $row_RecMember["account"]; ?></p>
			<p><strong>使用密碼</strong>:
				<input name="passwd" type="password" id="passwd" class="form-control">
				<input name="passwdo" type="hidden" id="passwdo" value="<?php echo $row_RecMember["passwd"]; ?>">
			</p>
			<p>
				<strong>確認密碼</strong>:
				<input name="passwdrecheck" type="password" id="passwdrecheck" class="form-control"><br>
				<span>若不修改密碼，請不要填寫。<br>若修改密碼，系統會自動登出，請用新密碼登入。</span>
			</p>
			<h3>個人資料</h3>
			<p><strong>真實姓名</strong>:<span class="form-text text-danger">  *</span>
				<input name="client_name" type="text" id="client_name" value="<?php echo $row_RecMember["client_name"]; ?>" class="form-control">
			</p>
			<p><strong>性　　別</strong>:<span class="form-text text-danger">  *</span>
				<input name="gender" type="radio" value="女" <?php if ($row_RecMember["gender"] == "女")
					echo "checked"; ?> class="form-check-input">女
				<input name="gender" type="radio" value="男" <?php if ($row_RecMember["gender"] == "男")
					echo "checked"; ?> class="form-check-input">男
			</p>
			<p><strong>生　　日</strong>:<span class="form-text text-danger">  *</span>
				<input name="birthday" type="date" id="birthday" value="<?php echo $row_RecMember["birthday"]; ?>" class="form-control">
			</p>
			<p><strong>電子郵件</strong>:<span class="form-text text-danger">  *</span>
				<input name="email" type="email" id="email" value="<?php echo $row_RecMember["email"]; ?>" class="form-control">
			</p>
			<p><strong>大頭貼</strong>:
				<input name="avatar" type="file" id="avatar" value="<?php $row_RecMember["avatar"]; ?>" class="form-control">
			</p>
			<p><strong>電　　話</strong>:<span class="form-text text-danger">  *</span>
				<input name="phone" type="text" id="phone" value="<?php echo $row_RecMember["phone"]; ?>" class="form-control">
			</p>
			<p><strong>住　　址</strong>:<span class="form-text text-danger">  *</span>
				<input name="client_address" type="text" id="client_address" value="<?php echo $row_RecMember["client_address"]; ?>" size="40" class="form-control">
				
			</p>
			<p><span class="form-text text-danger">*</span> 表示為必填的欄位</p>
		</div>
		<p>
			<input name="client_id" type="hidden" id="client_id" value="<?php echo $row_RecMember["client_id"]; ?>">
			<input name="action" type="hidden" id="action" value="update">
			<input type="submit" name="Submit2" value="修改資料" class="btn btn-primary">
			<input type="reset" name="Submit3" value="重設資料" class="btn btn-secondary">
			<input type="button" name="Submit" value="回上一頁" onClick="window.history.back();" class="btn btn-secondary">
		</p>
	</form>
</div>


</body>
</html>
<?php
close($pdo);
?>