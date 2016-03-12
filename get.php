<?php
// 连接数据库，获取当前存储的最大userId
require 'getDb.php';
$sql  = "SELECT MAX(`uid`) FROM user";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$lastUid = $stmt->fetch();
if (null === $lastUid[0]) {
    $uid = 1;
} else {
    $uid = $lastUid[0] + 1;
}

while ($uid - $lastUid[0] <= 120) {
    // 获取远程页面
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, 'http://ife.baidu.com/user/profile?userId=' . $uid);
    $output = curl_exec($ch);
    curl_close($ch);

    if (strpos($output, '<p class="empty-tip">很抱歉，您访问的页面不存在 或 暂未开放~</p>')) {
        if ($uid > 6775) {
            sleep(10);
        } else {
            $uid++;
        }
        continue;
    }

    // 获取身份信息并过滤
    $startPos = strpos($output, '<span class="title">');
    $endPos   = strpos($output, '</main>');
    $length   = $endPos - $startPos;
    $output   = substr($output, $startPos, $length);
    $output   = strip_tags($output);
    $output   = htmlspecialchars($output, ENT_QUOTES);

    // 处理
    $arr  = array();
    $info = explode(':', $output);
    if ('团队' == $info[0]) {
        $arr['team']      = substr($info[1], 0, -6);
        $arr['user_name'] = substr($info[2], 0, -6);
        $arr['city']      = substr($info[3], 0, -6);
        $arr['status']    = substr($info[4], 0, -6);
        if ('在校生' == $arr['status']) {
            $arr['school']          = substr($info[5], 0, -6);
            $arr['major']           = substr($info[6], 0, -6);
            $arr['degree']          = substr($info[7], 0, -12);
            $arr['graduation_time'] = substr($info[8], 0, -18);
            $arr['description']     = $info[9];
        } else {
            $arr['office']      = substr($info[5], 0, -6);
            $arr['position']    = substr($info[6], 0, -18);
            $arr['description'] = $info[7];
        }
    } else {
        $arr['user_name'] = substr($info[1], 0, -6);
        $arr['city']      = substr($info[2], 0, -6);
        $arr['status']    = substr($info[3], 0, -6);
        if ('在校生' == $arr['status']) {
            $arr['school']          = substr($info[4], 0, -6);
            $arr['major']           = substr($info[5], 0, -6);
            $arr['degree']          = substr($info[6], 0, -12);
            $arr['graduation_time'] = substr($info[7], 0, -18);
            $arr['description']     = $info[8];
        } else {
            $arr['office']      = substr($info[4], 0, -6);
            $arr['position']    = substr($info[5], 0, -18);
            $arr['description'] = $info[6];
        }
    }

    // 插入到数据库中
    $arr['uid'] = $uid;
    $keys       = array_keys($arr);
    $sql        = '';
    for ($i = 0; $i < count($keys) - 1; $i++) {
        $sql .= '`' . $keys[$i] . '`' . ',';
    }
    $sql .= '`' . $keys[count($keys) - 1] . '`';
    $sql = "INSERT INTO user (" . $sql . ") VALUES (";
    foreach ($arr as $value) {
        $sql .= "'{$value}',";
    }
    $sql  = substr($sql, 0, -1) . ')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $uid++;
}
