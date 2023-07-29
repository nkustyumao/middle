<?php
$mySQL_host = 'localhost'; //資料庫伺服器的位置  IP
$mySQL_user = 'root'; //權限 帳號
$mySQL_pass = ''; //權限 密碼
$mySQL_DB = 'book'; //資料庫名稱

$connString = "mysql:host={$mySQL_host}; port=3306; dbname={$mySQL_DB}; charset=utf8";

$accessOptions = [
     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
     PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
];


try {
     $pdo = new PDO($connString, $mySQL_user, $mySQL_pass, $accessOptions);
     //echo "連結資料庫成功!!!";
} catch (Exception $ex) {
     echo "存取資料庫時發生錯誤，訊息:" . $ex->getMessage() . "<br>";
     echo "苦主:" . $ex->getFile() . "<br>";
     echo "行號:" . $ex->getLine() . "<br>";
     echo "Code:" . $ex->getCode() . "<br>";
     echo "堆疊:" . $ex->getTraceAsString() . "<br>";
}

function close($pdo)
{
     $pdo = null;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <title>Document</title>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>