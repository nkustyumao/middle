<?php
require_once("connect.php");
session_start();

// 檢查是否經過登入，若有登入則重新導向
if (isset($_SESSION["loginMember"]) && ($_SESSION["loginMember"] != "")) {
  if ($_SESSION["memberLevel"] == "member") {
    header("Location: member.php");
  } else {
    header("Location: admin.php");
  }
}

// 檢查是否為會員
if (isset($_POST["account"])) {
  $muser = $_POST["account"];
  $query_RecFindUser = "SELECT account, email FROM client WHERE account='{$muser}'";
  $RecFindUser = $pdo->query($query_RecFindUser);
  if ($RecFindUser->rowCount() == 0) {
    header("Location: passwordMail.php?errMsg=1&account={$muser}");
  } else {
    $row_RecFindUser = $RecFindUser->fetch(PDO::FETCH_ASSOC);
    $username = $row_RecFindUser["account"];
    $usermail = $row_RecFindUser["email"];
    $newpasswd = base64_encode(random_bytes(6));
    $mpass = password_hash($newpasswd, PASSWORD_DEFAULT);
    $query_update = "UPDATE client SET passwd='{$mpass}' WHERE account='{$username}'";
    $pdo->query($query_update);
    $mailcontent = "您好，<br />您的帳號為：{$username} <br/>您的新密碼為：{$newpasswd} <br/>";
    $mailFrom = "=?UTF-8?B?" . base64_encode("會員管理系統") . "?= <service@e-happy.com.tw>";
    $mailto = $usermail;
    $mailSubject = "=?UTF-8?B?" . base64_encode("補寄密碼信") . "?=";
    $mailHeader = "From:" . $mailFrom . "\r\n";
    $mailHeader .= "Content-type:text/html;charset=UTF-8";
    if (!@mail($mailto, $mailSubject, $mailcontent, $mailHeader)) {
      die("郵寄失敗！");
    }
    header("Location: passworMail.php?mailStats=1");
  }
}
?>

<html> 
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>網站會員系統</title>
</head>

<body>
<?php if (isset($_GET["mailStats"]) && ($_GET["mailStats"] == "1")) { ?>
  <script>alert('密碼信補寄成功！');window.location.href='login.php';</script>
<?php } ?>
<table width="780" border="0" align="center" cellpadding="4" cellspacing="0">
  <tr>
    <td class="tdbline">
      <table width="100%" border="0" cellspacing="0" cellpadding="10">
        <tr valign="top">     
          <td width="200">
            <div class="boxtl"></div><div class="boxtr"></div><div class="regbox">
              <?php if (isset($_GET["errMsg"]) && ($_GET["errMsg"] == "1")) { ?>
                <div class="errDiv">帳號「 <strong><?php echo $_GET["account"]; ?></strong>」沒有人使用！</div>
              <?php } ?>
              <p class="heading">忘記密碼？</p>
              <form name="form1" method="post" action="">
                <p>請輸入您申請的帳號，系統將自動產生一個十位數的密碼寄到您註冊的信箱。</p>
                <p><strong>帳號</strong>：<br>
                  <input name="account" type="text" class="logintextbox" id="m_mail"></p>
                <p align="center">
                  <input type="submit" name="button" id="button" value="寄密碼信">
                  <input type="button" name="button2" id="button2" value="回上一頁" onClick="window.history.back();">
                </p>
              </form>
              <hr size="1" />
              <p class="heading">還沒有會員帳號?</p>
              <p align="right"><a href="register.php">馬上申請會員</a></p>
            </div>
            <div class="boxbl"></div><div class="boxbr"></div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
