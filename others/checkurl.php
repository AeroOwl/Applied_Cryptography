<?php
/*
•功能：檢測頁面是否合法連接過來

•如果為非法，就轉向到登陸窗口
 */
$_SESSION['HTTP_REFERER'] = isset($_SESSION['BACKURL'])?$_SESSION['BACKURL'] : '';
//$_SESSION['BACKURL'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PATHINFO'].(isset($_SERVER['QUERY_STRING'])? '?'.$_SERVER['QUERY_STRING'] : '');


function checkurl(){

    //如果直接從瀏覽器連接到頁面，就連接到登陸窗口

    echo 'referer:'.$_SESSION['HTTP_REFERER'];

    if(!isset($_SESSION['HTTP_REFERER'])) {

        header('location: index.html');

        exit;

    }

    $urlar = parse_url($_SESSION['HTTP_REFERER']);

    //如果頁面的域名不是服務器域名,就連接到登陸窗口

    if($_SERVER['HTTP_HOST'] != $urlar['host'] && $urlar['host'] != '127.0.0.1' && $urlar['host'] != 'https://2016.ac/') {

        header('location: index.html');

        exit;

    }

}

