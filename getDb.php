<?php
$dsn = 'mysql:host=HOST;dbname=spiderife';
$pdo = new PDO($dsn, 'ACCOUNT', 'PASSWORD') or die('连接数据库失败');
