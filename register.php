<?php
require_once("connect.php");
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
if (isset($_POST["action"]) && ($_POST["action"] == "join")) {

  //找尋帳號是否已經註冊
  $query_RecFindUser = "SELECT account FROM client WHERE account=:account";
  $stmt = $pdo->prepare($query_RecFindUser);
  $stmt->bindParam(':account', $_POST["account"]);
  $stmt->execute();
  $RecFindUser = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($stmt->rowCount() > 0) {
    header("Location: register.php?errMsg=1&account={$_POST["account"]}");
  } else {
    //若沒有執行新增的動作 name=client_name client_name=account	
    $query_insert = "INSERT INTO client (client_name, account, passwd, gender, birthday, email, avatar, phone, client_address, join_date) VALUES (:client_name, :account, :passwd, :gender, :birthday, :email, :avatar, :phone, :client_address, NOW())";
    $stmt = $pdo->prepare($query_insert);
    $stmt->bindValue(':client_name', $_POST["client_name"], PDO::PARAM_STR);
    $stmt->bindValue(':account', $_POST["account"], PDO::PARAM_STR);
    $stmt->bindValue(':passwd', password_hash($_POST["passwd"], PASSWORD_DEFAULT), PDO::PARAM_STR);
    $stmt->bindValue(':gender', $_POST["gender"], PDO::PARAM_STR);
    $stmt->bindValue(':birthday', $_POST["birthday"], PDO::PARAM_STR);
    $stmt->bindValue(':email', $_POST["email"], PDO::PARAM_STR);
    $avatarName = $_FILES['avatar']['name']; // 獲取上傳的檔案名
    $avatarTmpName = $_FILES['avatar']['tmp_name']; // 獲取檔案臨時路徑
    $newFileName = time() . '_' . $avatarName; // 生成新的檔名（使用當前時間戳）
    move_uploaded_file($avatarTmpName, '/Applications/XAMPP/xamppfiles/htdocs/middle/images/' . $newFileName); // 移動上傳的文件到指定目錄
    $stmt->bindValue(':avatar', $newFileName, PDO::PARAM_STR); // 新檔名存入

    $stmt->bindValue(':phone', $_POST["phone"], PDO::PARAM_STR);
    $stmt->bindValue(':client_address', $_POST["client_address"], PDO::PARAM_STR);

    $stmt->execute();
    header("Location: register.php?loginStats=1");
  }
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>網站會員系統</title>
<script language="javascript">
function checkForm(){
  if(document.formJoin.account.value==""){		
    alert("請填寫帳號!");
    document.formJoin.account.focus();
    return false;
  }else{
    uid=document.formJoin.account.value;
    if(uid.length<5 || uid.length>12){
      alert( "您的帳號長度只能5至12個字元!" );
      document.formJoin.account.focus();
      return false;}
    if(!(uid.charAt(0)>='a' && uid.charAt(0)<='z')){
      alert("您的帳號第一字元只能為小寫字母!" );
      document.formJoin.account.focus();
      return false;}
    for(idx=0;idx<uid.length;idx++){
      if(uid.charAt(idx)>='A'&&uid.charAt(idx)<='Z'){
        alert("帳號不可以含有大寫字元!" );
        document.formJoin.account.focus();
        return false;}
      if(!(( uid.charAt(idx)>='a'&&uid.charAt(idx)<='z')||(uid.charAt(idx)>='0'&& uid.charAt(idx)<='9'))){
        alert( "您的帳號只能是數字,英文字母" );
        document.formJoin.account.focus();
        return false;}
      
    }
  }
  if(!check_passwd(document.formJoin.passwd.value,document.formJoin.passwdrecheck.value)){
    document.formJoin.passwd.focus();
    return false;}	
  if(document.formJoin.client_name.value==""){
    alert("請填寫姓名!");
    document.formJoin.client_name.focus();
    return false;}
  if(document.formJoin.birthday.value==""){
    alert("請填寫生日!");
    document.formJoin.birthday.focus();
    return false;}
  if(document.formJoin.email.value==""){
    alert("請填寫電子郵件!");
    document.formJoin.email.focus();
    return false;}
  if(!checkmail(document.formJoin.email)){
    document.formJoin.email.focus();
    return false;}
    if(document.formJoin.phone.value==""){
    alert("請填寫電話!");
    document.formJoin.phone.focus();
    return false;}
    if(document.formJoin.client_address.value==""){
    alert("請填寫地址!");
    document.formJoin.client_address.focus();
    return false;}
  return confirm('確定送出嗎？');
}
function check_passwd(pw1,pw2){
  if(pw1==''){
    alert("密碼不可以空白!");
    return false;}
  for(var idx=0;idx<pw1.length;idx++){
    if(pw1.charAt(idx) == ' ' || pw1.charAt(idx) == '\"'){
      alert("不能有空白或雙引號 !\n");
      return false;}
    if(pw1.length<5 || pw1.length>10){
      alert( "密碼長度只能5到10個字母與數字 !\n" );
      return false;}
    if(pw1!= pw2){
      alert("密碼二次輸入不一樣,請重新輸入 !\n");
      return false;}
  }
  return true;
}
function checkmail(myEmail) {
  var filter  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  if(filter.test(myEmail.value)){
    return true;}
  alert("電子郵件格式不正確");
  return false;
}
</script>
</head>

<body>
<?php if (isset($_GET["loginStats"]) && ($_GET["loginStats"] == "1")) { ?>
  <script language="javascript">
  alert('會員新增成功\n請用申請的帳號密碼登入。');
  window.location.href='login.php';		  
  </script>
<?php } ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center text-primary">加入會員</h2>
                    <?php if (isset($_GET["errMsg"]) && ($_GET["errMsg"] == "1")) { ?>
                        <div class="alert alert-danger">帳號 <?php echo $_GET["account"]; ?> 已經被使用！</div>
                    <?php } ?>
                    <form action="" method="POST" name="formJoin" id="formJoin" onSubmit="return checkForm();" enctype="multipart/form-data">
                        <div class="mb-3">
                            <h5>帳號資料</h5>
                            <div class="mb-3">
                                <label for="account" class="form-label">使用帳號</label>
                                <input name="account" type="text" id="account" class="form-control">
                                <small class="form-text text-muted">請填入5~12個字元以內的小寫英文字母、數字、以及_ 符號。</small>
                            </div>
                            <div class="mb-3">
                                <label for="passwd" class="form-label">使用密碼</label>
                                <input name="passwd" type="password" id="passwd" class="form-control">
                                <small class="form-text text-muted">請填入5~10個字元以內的英文字母、數字、以及各種符號組合。</small>
                            </div>
                            <div class="mb-3">
                                <label for="passwdrecheck" class="form-label">確認密碼</label>
                                <input name="passwdrecheck" type="password" id="passwdrecheck" class="form-control">
                                <small class="form-text text-muted">再次確認您的密碼</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h5>個人資料</h5>
                            <div class="mb-3">
                                <label for="client_name" class="form-label">真實姓名</label><span class="form-text text-danger">  *</span>
                                <input name="client_name" type="text" id="client_name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">性別</label><span class="form-text text-danger">  *</span>
                                <div class="form-check">
                                    <input name="gender" type="radio" value="男" class="form-check-input" id="gender-male">
                                    <label for="gender-male" class="form-check-label">男</label>
                                </div>
                                <div class="form-check">
                                    <input name="gender" type="radio" value="女" class="form-check-input" id="gender-female">
                                    <label for="gender-female" class="form-check-label">女</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="birthday" class="form-label">生日</label><span class="form-text text-danger">  *</span>
                                <input name="birthday" type="date" id="birthday" value="2000-01-01" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">電子郵件</label><span class="form-text text-danger">  *</span>
                                <input name="email" type="email" id="email" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="avatar" class="form-label">大頭貼</label>
                                <input name="avatar" type="file" id="avatar" class="form-control" accept="image/*">
                                <small class="form-text text-muted">上傳大頭貼</small>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">電話</label><span class="form-text text-danger">  *</span>
                                <input name="phone" type="text" id="phone" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="client_address" class="form-label">住址</label><span class="form-text text-danger">  *</span>
                                <input name="client_address" type="text" id="client_address" size="40" class="form-control">
                            </div>
                            <p><span class="form-text text-danger">*</span> 表示為必填的欄位</p>
                        </div>
                        <div class="text-center">
                            <input name="action" type="hidden" id="action" value="join">
                            <input type="submit" name="Submit2" value="送出申請" class="btn btn-primary">
                            <input type="reset" name="Submit3" value="重設資料" class="btn btn-secondary">
                            <input type="button" name="Submit" value="回上一頁" onClick="window.history.back();" class="btn btn-secondary">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>