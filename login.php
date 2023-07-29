<?php
require_once("connect.php");
session_start();

// 檢查是否有警示停權訊息
if (isset($_GET['message'])) {
  $message = urldecode($_GET['message']);
  echo "<script>alert('$message');</script>";
}

//檢查是否經過登入，若有登入則重新導向
if (isset($_SESSION["loginMember"]) && ($_SESSION["loginMember"] != "")) {
  //若帳號等級為 member 則導向會員中心
  if ($_SESSION["memberLevel"] == "member") {
    header("Location: member.php");
    //否則則導向管理中心
  } else {
    header("Location: admin.php");
  }
}

// 執行會員登入
if (isset($_POST["account"]) && isset($_POST["passwd"])) {
  // 繫結登入會員資料
  $query_RecLogin = "SELECT account, passwd, client_level FROM client WHERE account = :account";
  $stmt = $pdo->prepare($query_RecLogin);
  $stmt->bindParam(':account', $_POST["account"], PDO::PARAM_STR);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($result) {
    $account = $result["account"];
    $passwd = $result["passwd"];
    $client_level = $result["client_level"];
  }
  $stmt->closeCursor();

  //比對密碼，若登入成功則呈現登入狀態
  if (password_verify($_POST["passwd"], $passwd)) {
    //計算登入次數及更新登入時間
    $query_RecLoginUpdate = "UPDATE client SET  login_time=NOW() WHERE account=:account";
    $stmt = $pdo->prepare($query_RecLoginUpdate);
    $stmt->bindParam(":account", $_POST["account"], PDO::PARAM_STR);
    $stmt->execute();
    $stmt->closeCursor();
    //設定登入者的名稱及等級
    $_SESSION["loginMember"] = $account;
    $_SESSION["memberLevel"] = $client_level;
    //使用Cookie記錄登入資料
    if (isset($_POST["rememberme"]) && ($_POST["rememberme"] == "true")) {
      setcookie("remUser", $_POST["account"], time() + 30 * 24 * 60 * 60);
      setcookie("remPass", $_POST["passwd"], time() + 30 * 24 * 60 * 60);
    } else {
      if (isset($_COOKIE["remUser"])) {
        setcookie("remUser", $_POST["account"], time() - 100);
        setcookie("remPass", $_POST["passwd"], time() - 100);
      }
    }
    //若帳號等級為 member 則導向會員中心
    if ($_SESSION["memberLevel"] == "member") {
      header("Location: member.php");
      //否則則導向管理中心
    } else {
      header("Location: admin.php");
    }
  } else {
    header("Location: login.php?errMsg=1");
  }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>會員系統</title>
  
</head>
<body>
  <?php if (isset($_GET["errMsg"]) && ($_GET["errMsg"] == "1")) { ?>
    <div class="alert alert-danger" role="alert">帳號或密碼錯誤！</div>
  <?php } ?>
  <div class="container">
    <div class="text-center mt-5 p-3 border">
      <h2>登入會員</h2>
      <form name="form1" method="post" action="">
        <div class="mb-3">
          <label for="account" class="form-label ">帳號：</label><br>
          <input name="account" type="text" class="form-control w-25 mx-auto" id="account" value="<?php if (isset($_COOKIE["remUser"]) && ($_COOKIE["remUser"] != ""))
            echo $_COOKIE["remUser"]; ?>">
        </div>
        <div class="mb-3">
          <label for="passwd" class="form-label">密碼：</label><br>
          <input name="passwd" type="password" class="form-control w-25 mx-auto" id="passwd" value="<?php if (isset($_COOKIE["remPass"]) && ($_COOKIE["remPass"] != ""))
            echo $_COOKIE["remPass"]; ?>">
        </div>
        <div class="mb-3 mx-auto">
          <input name="rememberme" type="checkbox" class="form-check-input" id="rememberme" value="true" checked>
          <label for="rememberme" class="form-check-label">記住帳號密碼</label>
        </div>
        <div class="mb-3">
          <input type="submit" name="button" id="button" value="登入系統" class="btn btn-success">
        </div>
        <div>
        <butotn class="btn btn-primary"><a href="register.php">註冊會員</a></button>
  </div>
      </form>
    
    </div>
  </div>
</body>
</html>
<?php
close($pdo);
?>