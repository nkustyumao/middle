<?php
require_once("connect.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $memberId = $_POST["memberId"];
    $isSuspended = $_POST["isSuspended"];

    $query_updateSuspended = "UPDATE client SET is_suspended = ? WHERE client_id = ?";
    $stmt = $pdo->prepare($query_updateSuspended);
    $stmt->bindValue(1, $isSuspended, PDO::PARAM_INT);
    $stmt->bindValue(2, $memberId, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();

    echo "更新成功";
}
?>
