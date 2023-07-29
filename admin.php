<?php
require_once("connect.php");
session_start();
//檢查是否經過登入
if (!isset($_SESSION["loginMember"]) || ($_SESSION["loginMember"] == "")) {
  header("Location: login.php");
}
//檢查權限是否足夠
if ($_SESSION["memberLevel"] == "member") {
  header("Location: member.php");
}
//執行登出動作
if (isset($_GET["logout"]) && ($_GET["logout"] == "true")) {
  unset($_SESSION["loginMember"]);
  unset($_SESSION["memberLevel"]);
  header("Location: login.php");
}
//刪除會員
if (isset($_GET["action"]) && ($_GET["action"] == "delete")) {
  $query_delMember = "DELETE FROM client WHERE client_id=?";
  $stmt = $pdo->prepare($query_delMember);
  $stmt->bindValue(1, $_GET["id"], PDO::PARAM_INT);
  $stmt->execute();
  $stmt->closeCursor();
  //重新導向回到主畫面
  header("Location: admin.php");
}

//停權
if (isset($_POST["suspended"])) {
  $suspendedMembers = $_POST["suspended"];
  foreach ($suspendedMembers as $memberId) {
    $query_updateSuspended = "UPDATE client SET is_suspended = 1 WHERE client_id = ?";
    $stmt = $pdo->prepare($query_updateSuspended);
    $stmt->bindValue(1, $memberId, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();
  }
}

//選取管理員資料
$query_RecAdmin = "SELECT client_id, username, logintime FROM client WHERE account=?";
$stmt = $pdo->prepare($query_RecAdmin);
$stmt->bindValue(1, $_SESSION["loginMember"], PDO::PARAM_STR);
$stmt->execute();
$row_RecAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();
$mid = $row_RecAdmin["client_id"];
$mname = $row_RecAdmin["username"];
$mlogintime = $row_RecAdmin["logintime"];

//選取所有一般會員資料
//預設每頁筆數
$pageRow_records = 5;
//預設頁數
$num_pages = 1;
//若已經有翻頁，將頁數更新
if (isset($_GET['page'])) {
  $num_pages = $_GET['page'];
}
//本頁開始記錄筆數 = (頁數-1)*每頁記錄筆數
$startRow_records = ($num_pages - 1) * $pageRow_records;
//未加限制顯示筆數的SQL敘述句
$query_RecMember = "SELECT * FROM client WHERE client_level<>'admin' ORDER BY join_date DESC";
//加上限制顯示筆數的SQL敘述句，由本頁開始記錄筆數開始，每頁顯示預設筆數
$query_limit_RecMember = $query_RecMember . " LIMIT {$startRow_records}, {$pageRow_records}";
//以加上限制顯示筆數的SQL敘述句查詢資料到 $resultMember 中
$RecMember = $pdo->query($query_limit_RecMember);
//以未加上限制顯示筆數的SQL敘述句查詢資料到 $all_resultMember 中
$all_RecMember = $pdo->query($query_RecMember);
//計算總筆數
$total_records = $all_RecMember->rowCount();
//計算總頁數=(總筆數/每頁筆數)後無條件進位。
$total_pages = ceil($total_records / $pageRow_records);
?>

<!DOCTYPE html>
<html>
<head>
  <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
  <style>
        .member-data-container {
            width: 100%;
            /* margin: 0 auto; */
            padding: 20px;
        }
       
    </style>
  <title>網站會員系統</title>
  
</head>
<body>
<div class="member-data-container">
        <h2 class="text-center text-primary my-3">會員資料列表</h2>
        <div class="table-responsive">
            <table class="table table-bordered mt-3">
                <thead align="center">
                    <tr>
                        <th>&nbsp;</th>
                        <th>
                            <p>姓名</p>
                        </th>
                        <th>
                            <p>帳號</p>
                        </th>
                        <th>
                            <p>email</p>
                        </th>
                        <th>
                            <p>birthday</p>
                        </th>
                        <th>
                            <p>加入時間</p>
                        </th>
                        <th>
                            <p>上次登入</p>
                        </th>
                        <th>
                            <p>停權</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row_RecMember = $RecMember->fetch(PDO::FETCH_ASSOC)) { ?>
                      <tr align="center">
                          <td>
                              <p>
                                  <a class="btn btn-primary" href="adminUpdate.php?id=<?php echo $row_RecMember["client_id"]; ?>">修改</a>
                                  <a class="btn btn-danger" href="?action=delete&id=<?php echo $row_RecMember["client_id"]; ?>"
                                      onClick="return deletesure();">刪除</a>
                              </p>
                          </td>
                          <td >
                              <p><?php echo $row_RecMember["username"]; ?></p>
                          </td>
                          <td >
                              <p><?php echo $row_RecMember["account"]; ?></p>
                          </td>
                          <td >
                              <p><?php echo $row_RecMember["email"]; ?></p>
                          </td>
                          <td >
                              <p><?php echo $row_RecMember["birthday"]; ?></p>
                          </td>
                          <td >
                              <p><?php echo $row_RecMember["join_date"]; ?></p>
                          </td>
                          <td >
                              <p><?php echo $row_RecMember["logintime"]; ?></p>
                          </td>
                          <td >
                              <input type="checkbox" name="suspended[]"
                                  value="<?php echo $row_RecMember["client_id"]; ?>" <?php if ($row_RecMember["is_suspended"] == 1)
                                       echo "checked"; ?>>
                          </td>
                      </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="row">
            <div class="col-6">
                <h3>資料筆數：<?php echo $total_records; ?></h3>
            </div>
            <div class="col-6 text-end">
                <p>
                <?php if ($num_pages > 1) { ?>
                  <a href="?page=1" class="btn btn-link">第一頁</a>
                  <a href="?page=<?php echo $num_pages - 1; ?>" class="btn btn-link">上一頁</a>
                      <?php } else { ?>
                        <span class="btn btn-link disabled">第一頁</span>
                        <span class="btn btn-link disabled">上一頁</span>
                      <?php }
                for ($i = 1; $i <= $total_pages; $i++) {
                  if ($i == $num_pages) {
                    echo '<span class="btn btn-primary">' . $i . '</span> ';
                  } else {
                    echo '<a href="?page=' . $i . '" class="btn btn-link">' . $i . '</a> ';
                  }
                }
                if ($num_pages < $total_pages) { ?>
                        <a href="?page=<?php echo $num_pages + 1; ?>" class="btn btn-link">下一頁</a>
                        <a href="?page=<?php echo $total_pages; ?>" class="btn btn-link">最末頁</a>
                      <?php } else { ?>
                        <span class="btn btn-link disabled">下一頁</span>
                        <span class="btn btn-link disabled">最末頁</span>
                      <?php } ?>

                </p>
            </div>
        </div>
        <hr>
        <p><strong><?php echo $mname; ?></strong> 管理員您好。</p>
        <p>本次登入的時間為：<?php echo $mlogintime; ?></p>
        <p>帳號管理：<a href="register.php" class="btn btn-primary mx-1">新增會員</a><a href="?logout=true"
                class="btn btn-warning mx-1">登出系統</a></p>
    </div>

  <script>
  function deletesure(){
     if (confirm('\n刪除後無法恢復!')) return true;
     return false;
  }
  </script>
  
  <!-- 停權 -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script>
    $(document).ready(function() {
      $('input[name="suspended\[\]"]').change(function() {
        var memberId = $(this).val();
        var isSuspended = $(this).is(':checked') ? 1 : 0;
        $.ajax({
          type: "POST",
          url: "update_suspended.php",
          data: { memberId: memberId, isSuspended: isSuspended },
          success: function(response) {
            // 更新成功後的處理
            console.log(response);
          },
          error: function(xhr, status, error) {
            // 錯誤處理
            console.log(error);
          }
        });
      });
    });
  </script>

</body>
</html>