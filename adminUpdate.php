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

// 检查用户是否已登录
if (!isset($_SESSION["loginMember"]) || ($_SESSION["loginMember"] == "")) {
  header("Location: login.php");
}

// 检查用户是否具有足够的权限
if ($_SESSION["memberLevel"] == "member") {
  header("Location: member.php");
}

// 执行注销操作
if (isset($_GET["logout"]) && ($_GET["logout"] == "true")) {
  unset($_SESSION["loginMember"]);
  unset($_SESSION["memberLevel"]);
  header("Location: login.php");
}

// 执行更新操作
// if (isset($_POST["action"]) && ($_POST["action"] == "update")) {
//   $query_update = "UPDATE client SET passwd=?, username=?, gender=?, birthday=?, email=?, avatar=?, phone=?, c_address=? WHERE client_id=?";
//   $stmt = $pdo->prepare($query_update);

//   // 检查密码是否已修改
//   $mpass = $_POST["passwdo"];
//   if (($_POST["passwd"] != "") && ($_POST["passwd"] == $_POST["passwdrecheck"])) {
//     $mpass = password_hash($_POST["passwd"], PASSWORD_DEFAULT);
//   }

//   $stmt->bindValue(1, $mpass, PDO::PARAM_STR);
//   $stmt->bindValue(2, GetSQLValueString($_POST["username"], 'string'), PDO::PARAM_STR);
//   $stmt->bindValue(3, GetSQLValueString($_POST["gender"], 'string'), PDO::PARAM_STR);
//   $stmt->bindValue(4, GetSQLValueString($_POST["birthday"], 'string'), PDO::PARAM_STR);
//   $stmt->bindValue(5, GetSQLValueString($_POST["email"], 'email'), PDO::PARAM_STR);
//   $avatar = file_get_contents($_FILES["avatar"]["tmp_name"]);
// 	$stmt->bindParam(':avatar', $avatar, PDO::PARAM_LOB);
//   //$stmt->bindValue(6, GetSQLValueString($_POST["avatar"], 'url'), PDO::PARAM_STR);
//   $stmt->bindValue(7, GetSQLValueString($_POST["phone"], 'string'), PDO::PARAM_STR);
//   $stmt->bindValue(8, GetSQLValueString($_POST["c_address"], 'string'), PDO::PARAM_STR);
//   $stmt->bindValue(9, GetSQLValueString($_POST["client_id"], 'int'), PDO::PARAM_INT);
//   $stmt->execute();
//   $stmt->closeCursor();


if (isset($_POST["action"]) && ($_POST["action"] == "update")) {
  $query_update = "UPDATE client SET passwd=:passwd, username=:username, gender=:gender, birthday=:birthday, email=:email, avatar=:avatar, phone=:phone, c_address=:c_address WHERE client_id=:client_id";
  $stmt = $pdo->prepare($query_update);

  // 檢查是否有修改密碼
  $mpass = $_POST["passwdo"];
  if (($_POST["passwd"] != "") && ($_POST["passwd"] == $_POST["passwdrecheck"])) {
    $mpass = password_hash($_POST["passwd"], PASSWORD_DEFAULT);
  }

  $stmt->bindParam(':passwd', $mpass, PDO::PARAM_STR);
  $stmt->bindParam(':username', GetSQLValueString($_POST["username"], 'string'), PDO::PARAM_STR);
  $stmt->bindParam(':gender', GetSQLValueString($_POST["gender"], 'string'), PDO::PARAM_STR);
  $stmt->bindParam(':birthday', GetSQLValueString($_POST["birthday"], 'string'), PDO::PARAM_STR);
  $stmt->bindParam(':email', GetSQLValueString($_POST["email"], 'email'), PDO::PARAM_STR);
  $avatar = file_get_contents($_FILES["avatar"]["tmp_name"]);
  $stmt->bindParam(':avatar', $avatar, PDO::PARAM_LOB);

  //stmt->bindParam(':avatar', $_POST["avatar"], PDO::PARAM_STR);
  $stmt->bindParam(':phone', GetSQLValueString($_POST["phone"], 'string'), PDO::PARAM_STR);
  $stmt->bindParam(':c_address', GetSQLValueString($_POST["c_address"], 'string'), PDO::PARAM_STR);
  $stmt->bindParam(':client_id', GetSQLValueString($_POST["client_id"], 'int'), PDO::PARAM_INT);

  $stmt->execute();
  $stmt->closeCursor();


  // 更新后重定向
  header("Location: admin.php");
}


// 选择管理员数据
$query_RecAdmin = "SELECT * FROM client WHERE account='{$_SESSION["loginMember"]}'";
$RecAdmin = $pdo->query($query_RecAdmin);
$row_RecAdmin = $RecAdmin->fetch(PDO::FETCH_ASSOC);

// 绑定成员数据
$query_RecMember = "SELECT * FROM client WHERE client_id=:id";
$stmt = $pdo->prepare($query_RecMember);
$stmt->bindValue(':id', $_GET["id"], PDO::PARAM_INT);
$stmt->execute();
$row_RecMember = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();
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
  if(document.formJoin.username.value==""){
    alert("請填寫姓名!");
    document.formJoin.username.focus();
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
  if(document.formJoin.c_address.value==""){
    alert("請填寫地址!");
    document.formJoin.c_address.focus();
    return false;
  }
  if(document.formJoin.phone.value==""){
    alert("請填寫電話!");
    document.formJoin.phone.focus();
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
      alert("密碼不可以含有空白或雙引號 !\n");
      return false;
    }
    if(pw1.length<5 || pw1.length>10){
      alert( "密碼長度只能5到10個字母 !\n" );
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

<div class="form-container d-flex justify-content-center">
    <form action="" method="POST" name="formJoin" id="formJoin" onSubmit="return checkForm();" enctype="multipart/form-data" style="width: 50%;">
        <h2 class="text-primary text-center m-3">修改資料</h2>
        <div class="border">
          <div class="m-3">
            <h3>帳號資料</h3>
            <p><strong>帳號</strong> : <?php echo $row_RecMember["account"]; ?></p>
            <div class="mb-3">
                <label for="passwd" class="form-label">使用密碼</label>
                <input name="passwd" type="password" class="form-control" id="passwd">
                <input name="passwdo" type="hidden" id="passwdo" value="<?php echo $row_RecMember["passwd"]; ?>">
            </div>
            <div class="mb-3">
                <label for="passwdrecheck" class="form-label">確認密碼</label>
                <input name="passwdrecheck" type="password" class="form-control" id="passwdrecheck">
                <span class="form-text">若不修改密碼，請不要填寫。<br>若修改密碼，系統會自動登出，請用新密碼登入。</span>
            </div>
            
            <h3>個人資料</h3>
            <div class="mb-3">
                <label for="username" class="form-label">真實姓名</label><span class="form-text text-danger">  *</span>
                <input name="username" type="text" class="form-control" id="username" value="<?php echo $row_RecMember["username"]; ?>">
                
            </div>
            <div class="mb-3">
                <label class="form-label">性別</label><span class="form-text text-danger">  *</span>
                <div class="form-check">
                    <input name="gender" type="radio" class="form-check-input" value="女" <?php if ($row_RecMember["gender"] == "女")
                      echo "checked"; ?>>
                    <label class="form-check-label">女</label>
                </div>
                <div class="form-check">
                    <input name="gender" type="radio" class="form-check-input" value="男" <?php if ($row_RecMember["gender"] == "男")
                      echo "checked"; ?>>
                    <label class="form-check-label">男</label>
                </div>
                
            </div>
            <div class="mb-3">
                <label for="birthday" class="form-label">生日</label><span class="form-text text-danger">  *</span>
                <input name="birthday" type="date" class="form-control" id="birthday" value="<?php echo $row_RecMember["birthday"]; ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">電子郵件</label><span class="form-text text-danger">  *</span>
                <input name="email" type="email" class="form-control" id="email" value="<?php echo $row_RecMember["email"]; ?>">
                
                <span class="form-text">請確認您的電子郵件</span>
            </div>
            <div class="mb-3">
                <label for="avatar" class="form-label">大頭貼</label>
                <input name="avatar" type="file" class="form-control" id="avatar">
                <span class="form-text">上傳你的大頭貼</span>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">電話</label><span class="form-text text-danger">  *</span>
                <input name="phone" type="text" class="form-control" id="phone" value="<?php echo $row_RecMember["phone"]; ?>">
            </div>
            <div class="mb-3">
                <label for="c_address" class="form-label">地址</label><span class="form-text text-danger">  *</span>
                <input name="c_address" type="text" class="form-control" id="c_address" value="<?php echo $row_RecMember["c_address"]; ?>" size="40">
            </div>
            <p><span class="form-text text-danger">*</span> 表示為必填的欄位</p>
            <p>
                <input name="client_id" type="hidden" id="client_id" value="<?php echo $row_RecMember["client_id"]; ?>">
                <input name="action" type="hidden" id="action" value="update">
                <input type="submit" name="Submit2" value="修改資料" class="btn btn-primary">
                <input type="reset" name="Submit3" value="重設資料" class="btn btn-secondary">
                <input type="button" name="Submit" value="回上一頁" onClick="window.history.back();" class="btn btn-secondary">
            </p>
             </div>
        </div>
    </form>
</div>

</body>
</html>
<?php
close($pdo);
?>

