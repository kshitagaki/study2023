<?php
    // ポイント情報管理
    if($_SERVER["REQUEST_METHOD"] != "POST"){
        //POST以外ははじく
        header("HTTP/1.0 404 Not Found");
        return;
    }
    header("Content-Type: text/html; charset=UTF-8");

    $json = null;
    $json['dt'] = $_POST['dt'];
    $json['lon'] = $_POST['lon'];
    $json['lat'] = $_POST['lat'];
    $json['name'] = $_POST['name'];
    $json['url'] = $_POST['url'];
    $json['remarks'] = $_POST['remarks'];

    if ($_POST['mode'] == 'delete') {
        require_once('./clsMyPoint.php');
        $classMyPoint = new myPoint();
        $result = $classMyPoint->deletePoint($json);

    } else {
        require_once('./clsMyPoint.php');
        $classMyPoint = new myPoint();
        $result = $classMyPoint->setPoint($json);
    }
    
    exit;

