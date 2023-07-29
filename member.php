<?php
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
//繫結登入會員資料
$query_RecMember = "SELECT * FROM client WHERE account = :account";
$stmt = $pdo->prepare($query_RecMember);
$stmt->bindValue(':account', $_SESSION["loginMember"]);
$stmt->execute();
$row_RecMember = $stmt->fetch(PDO::FETCH_ASSOC);


// 檢查是否被停權
if ($row_RecMember["is_suspended"] == 1) {
  unset($_SESSION["loginMember"]);
  unset($_SESSION["memberLevel"]);
  $message = urlencode("您已被停權，請聯絡管理員。");
  header("Location: login.php?message=$message");
  exit;
}

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
        .avatar {
            width: 300px;
            height: 300px;
            border-radius: 50%;
        }

        .member-system-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .avatar {
            max-width: 300px;
        }
    </style>
<title>會員系統</title>

</head>

<body>

<div class="member-system-container">
        <h2 class="text-center">會員系統</h2>
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><?php echo $row_RecMember["client_name"]; ?> 您好。</h5>
                <p class="card-text">本次登入的時間為：<br><?php echo $row_RecMember["login_time"]; ?></p>
                <div class="mb-3">
            <?php
            // 顯示頭像
            if (!empty($row_RecMember['avatar'])) {
              $avatarFileName = $row_RecMember['avatar'];
              $avatarFilePath = '/Applications/XAMPP/xamppfiles/htdocs/middle/images/' . $avatarFileName;

              // 读取图像文件
              $imageData = file_get_contents($avatarFilePath);

              if ($imageData !== false) {
                $imageType = mime_content_type($avatarFilePath);

                // 输出图像
                echo '<img class="avatar" src="data:' . $imageType . ';base64,' . base64_encode($imageData) . '" />';
              } else {
                echo '無頭像';
              }
            } else {
              echo '無頭像';
            }


            ?>
        </div>
                <a href="memberUpdate.php" class="btn btn-primary">修改資料</a>
                <a href="?logout=true" class="btn btn-secondary">登出系統</a>
            </div>
        </div>
    </div>
</body>
</html>
