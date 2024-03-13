<?php
    // ポイント情報管理
    if($_SERVER["REQUEST_METHOD"] != "POST"){
        //POST以外ははじく
        header("HTTP/1.0 404 Not Found");
        return;
    }
    header("Content-Type: text/html; charset=UTF-8");

    require_once('./clsMyPoint.php');
    $classMyPoint = new myPoint();
    $result = $classMyPoint->getPoints($condition);
    $json_str = array(
        'result'=>true,
        'list'=>$result,
    );
    $json = json_encode($json_str, JSON_UNESCAPED_UNICODE);
    header("Content-Type: application/json; charset=UTF-8");
    echo $json;
    exit;

