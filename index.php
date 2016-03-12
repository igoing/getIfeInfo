<?php
require 'getDb.php';
$sql = 'SELECT COUNT(`id`) FROM user';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetch();
echo "当前共获取了{$result[0]}人的信息";